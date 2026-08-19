<?php

namespace App\Modules\Shipping\Services;

use App\Modules\Coupons\Models\Coupon;
use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Shipping\Models\ShippingZone;
use App\Modules\Shipping\Models\ShippingLabel;
use App\Modules\Shipping\Models\ShippingTracking;

class ShippingCalculator
{
    /**
     * Calculate shipping options for a given address and cart.
     *
     * @param array  $cartItems   Array of cart items with product_id, quantity, price, weight
     * @param int|null $countryId Country ID
     * @param int|null $stateId   State/region ID
     * @param int|null $cityId    City ID
     * @param Coupon|null $coupon Active coupon
     * @return array
     */
    public function calculate(array $cartItems, ?int $countryId, ?int $stateId = null, ?int $cityId = null, ?Coupon $coupon = null): array
    {
        // 1. Find matching zone
        $zone = ShippingZone::findMatchingZone($countryId, $stateId, $cityId);
        if (!$zone) {
            // Fallback to default zone
            $zone = ShippingZone::where('is_default', true)->where('status', 'active')->first();
        }
        if (!$zone) {
            return [
                'error' => 'لا توجد منطقة شحن متاحة لهذا العنوان',
                'zone' => null,
                'options' => [],
                'total_weight' => 0,
            ];
        }

        // 2. Get active methods for zone
        $methods = $zone->activeMethods()->orderBy('sort_order')->get();

        // 3. Calculate total weight
        $totalWeight = $this->calculateWeight($cartItems);

        // 4. Calculate subtotal
        $subtotal = $this->calculateSubtotal($cartItems);

        // 5. Build options
        $options = [];
        foreach ($methods as $method) {
            $cost = $method->calculateCost($totalWeight, $subtotal, $cartItems, $coupon);
            if ($cost !== null) {
                $options[] = [
                    'id' => $method->id,
                    'name' => $method->name,
                    'type' => $method->type,
                    'type_label' => $method->getTypeLabel(),
                    'carrier' => $method->carrier?->name,
                    'carrier_logo' => $method->carrier?->logo,
                    'cost' => $cost,
                    'estimated_days' => $method->estimated_days,
                    'is_free' => $cost == 0,
                ];
            }
        }

        // 6. Sort by cost (cheapest first)
        usort($options, fn($a, $b) => $a['cost'] <=> $b['cost']);

        return [
            'zone' => $zone->name,
            'zone_id' => $zone->id,
            'options' => $options,
            'total_weight' => $totalWeight,
        ];
    }

    /**
     * Calculate shipping using legacy zone-based logic (for backward compatibility).
     */
    public function calculateByCity(string $city, string $countryCode = '', string $method = 'standard', string $deliveryType = 'home', float $subtotal = 0, float $weight = 0): array
    {
        // Find zone matching city
        $zone = $this->findZoneByCity($city, $countryCode);
        if (!$zone) {
            $zone = ShippingZone::where('is_default', true)->where('status', 'active')->first();
        }
        if (!$zone) {
            return ['error' => 'لا توجد منطقة شحن متاحة لهذا العنوان', 'cost' => 0];
        }

        $cost = $zone->calculateCost($city, $countryCode, $method, $deliveryType, $subtotal, $weight);

        return [
            'zone' => $zone->name,
            'zone_id' => $zone->id,
            'cost' => $cost,
            'estimated_days' => $zone->estimatedDays($method),
        ];
    }

    /**
     * Calculate cost for a specific shipping method.
     */
    public function calculateMethodCost(int $methodId, float $weight = 0, float $subtotal = 0, array $cartItems = [], ?Coupon $coupon = null): ?float
    {
        $method = ShippingMethod::find($methodId);
        if (!$method || !$method->status) return null;

        return $method->calculateCost($weight, $subtotal, $cartItems, $coupon);
    }

    /**
     * Create a shipping label for an order.
     */
    public function createLabel(int $orderId, int $carrierId, float $weight = 0, float $cost = 0): ShippingLabel
    {
        $trackingNumber = $this->generateTrackingNumber();

        return ShippingLabel::create([
            'order_id' => $orderId,
            'carrier_id' => $carrierId,
            'tracking_number' => $trackingNumber,
            'weight' => $weight,
            'cost' => $cost,
            'status' => 'pending',
        ]);
    }

    /**
     * Add a tracking update for a shipping label.
     */
    public function addTrackingUpdate(int $labelId, string $status, ?string $location = null, ?string $description = null): ShippingTracking
    {
        return ShippingTracking::create([
            'label_id' => $labelId,
            'status' => $status,
            'location' => $location,
            'description' => $description,
            'tracked_at' => now(),
        ]);
    }

    /**
     * Track a shipment by tracking number.
     */
    public function trackShipment(string $trackingNumber): ?array
    {
        $label = ShippingLabel::where('tracking_number', $trackingNumber)
            ->with(['carrier', 'trackingUpdates', 'order'])
            ->first();

        if (!$label) return null;

        return [
            'tracking_number' => $label->tracking_number,
            'carrier' => $label->carrier?->name,
            'status' => $label->status,
            'status_label' => $label->getStatusLabel(),
            'tracking_link' => $label->getTrackingLink(),
            'weight' => $label->weight,
            'cost' => $label->cost,
            'shipped_at' => $label->shipped_at?->format('Y-m-d H:i'),
            'delivered_at' => $label->delivered_at?->format('Y-m-d H:i'),
            'order_number' => $label->order?->order_number,
            'updates' => $label->trackingUpdates->map(fn($t) => [
                'status' => $t->status,
                'status_label' => $t->getStatusLabel(),
                'location' => $t->location,
                'description' => $t->description,
                'tracked_at' => $t->tracked_at->format('Y-m-d H:i'),
            ])->toArray(),
        ];
    }

    // --- Private helpers ---

    private function calculateWeight(array $cartItems): float
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $qty = $item['quantity'] ?? 1;
            $w = $item['weight'] ?? 0;
            $total += $w * $qty;
        }
        return $total;
    }

    private function calculateSubtotal(array $cartItems): float
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $qty = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $total += $price * $qty;
        }
        return $total;
    }

    private function findZoneByCity(string $city, string $countryCode): ?ShippingZone
    {
        return ShippingZone::where('status', 'active')
            ->where(function ($q) use ($city) {
                $q->whereNull('cities')
                  ->orWhereJsonContains('cities', $city)
                  ->orWhereJsonContains('cities', '*')
                  ->orWhereJsonContains('cities', $city);
            })
            ->where(function ($q) use ($countryCode) {
                $q->whereNull('countries')
                  ->orWhereJsonContains('countries', $countryCode)
                  ->orWhereJsonContains('countries', '*');
            })
            ->orderBy('priority')
            ->first();
    }

    private function generateTrackingNumber(): string
    {
        return 'SH' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
    }
}
