<?php

namespace App\Services;

use App\Models\Catalog\Product;
use App\Models\Shipping\ShippingMethod;
use App\Models\Shipping\ShippingOfficePickup;
use App\Models\Shipping\ShippingZone;

class DynamicShippingService
{
    /**
     * Get available shipping methods for a product in a location.
     */
    public function getAvailableMethods(
        int $productId,
        string $countryCode,
        string $city,
        string $deliveryType = 'home',
        ?int $companyId = null
    ): array {
        $product = Product::with(['shippingRule', 'shippingCompany'])->findOrFail($productId);

        // 1. Find matching zones
        $zones = ShippingZone::where('status', 'active')
            ->when($companyId ?? $product->shipping_company_id, fn($q, $id) => $q->where('company_id', $id))
            ->get()
            ->filter(fn($z) => $z->isCityInZone($city, $countryCode) && $z->supportsDelivery($deliveryType))
            ->values();

        // 2. Get methods from zones
        $available = [];
        $unavailable = [];

        foreach ($zones as $zone) {
            $methods = $zone->activeMethods()->with('carrier')->get();

            if ($methods->isNotEmpty()) {
                foreach ($methods as $method) {
                    $cost = $this->calculateMethodCost($method, $zone, $deliveryType, $product);

                    // Apply product rules
                    if (!$this->isMethodAllowedForProduct($method, $product)) {
                        $unavailable[] = [
                            'id' => $method->id,
                            'name' => $method->name,
                            'carrier' => $method->carrier?->name,
                            'reason' => 'غير متاح لهذا المنتج',
                        ];
                        continue;
                    }

                    // Check city coverage
                    if (!$this->isCityCovered($method, $city)) {
                        continue;
                    }

                    // Get pickup location if office_pickup
                    $pickupLocation = null;
                    if ($method->type === 'office_pickup' && $method->carrier_id) {
                        $pickupLocation = $this->getPickupLocation($method->carrier_id, $city, $countryCode);
                    }

                    $item = [
                        'id' => $method->id,
                        'name' => $method->name,
                        'type' => $method->type,
                        'carrier' => $method->carrier?->name ?? $zone->company?->name ?? 'الشحن',
                        'carrier_id' => $method->carrier_id ?? $zone->company_id,
                        'zone_id' => $zone->id,
                        'delivery_type' => $deliveryType,
                        'cost' => $cost,
                        'is_free' => $cost === 0.0,
                        'estimated_days' => $method->estimated_days ?? $zone->estimatedDays($deliveryType === 'express' ? 'express' : 'standard'),
                        'is_cod_available' => true,
                        'pickup_location' => $pickupLocation,
                    ];

                    $available[] = $item;
                }
            } else {
                // Generate options directly from Zone costs
                $carrierName = $zone->company?->name ?? 'توصيل محلي';
                $carrierId = $zone->company_id;

                if ($deliveryType === 'home' || $deliveryType === 'both') {
                    $cost = (float) ($zone->home_cost ?? $zone->cost ?? 500);
                    $available[] = [
                        'id' => null,
                        'name' => 'توصيل إلى المنزل (' . $carrierName . ')',
                        'type' => 'standard',
                        'carrier' => $carrierName,
                        'carrier_id' => $carrierId,
                        'zone_id' => $zone->id,
                        'delivery_type' => 'home',
                        'cost' => $cost,
                        'is_free' => $cost === 0.0,
                        'estimated_days' => $zone->estimated_days_standard ?? '2-4 أيام',
                        'is_cod_available' => true,
                        'pickup_location' => null,
                    ];
                }

                if ($deliveryType === 'office' || ($deliveryType === 'both' && $zone->office_cost !== null)) {
                    $cost = (float) ($zone->office_cost ?? $zone->cost ?? 400);
                    $available[] = [
                        'id' => null,
                        'name' => 'استلام من المكتب (' . $carrierName . ')',
                        'type' => 'office_pickup',
                        'carrier' => $carrierName,
                        'carrier_id' => $carrierId,
                        'zone_id' => $zone->id,
                        'delivery_type' => 'office',
                        'cost' => $cost,
                        'is_free' => $cost === 0.0,
                        'estimated_days' => $zone->estimated_days_standard ?? '2-3 أيام',
                        'is_cod_available' => true,
                        'pickup_location' => null,
                    ];
                }
            }
        }

        // 3. Fallback when no specific micro-zone matched
        if (empty($available)) {
            $defaultZone = ShippingZone::where('status', 'active')
                ->where(function ($q) use ($countryCode) {
                    $q->where('is_default', true)
                      ->orWhereJsonContains('countries', $countryCode)
                      ->orWhereJsonContains('countries', '*');
                })
                ->first();

            $cost = $defaultZone ? (float) ($defaultZone->home_cost ?? $defaultZone->cost ?? 500) : 500.0;
            $companyName = $defaultZone?->company?->name ?? 'شحن قياسي';

            $available[] = [
                'id' => null,
                'name' => 'توصيل قياسي (' . $companyName . ')',
                'type' => 'standard',
                'carrier' => $companyName,
                'carrier_id' => $defaultZone?->company_id,
                'zone_id' => $defaultZone?->id,
                'delivery_type' => $deliveryType ?: 'home',
                'cost' => $cost,
                'is_free' => $cost === 0.0,
                'estimated_days' => $defaultZone?->estimated_days_standard ?? '2-4 أيام',
                'is_cod_available' => true,
                'pickup_location' => null,
            ];
        }

        return [
            'available' => $available,
            'unavailable' => $unavailable,
        ];
    }

    /**
     * Calculate cost for a specific method in a zone.
     */
    public function calculateMethodCost(ShippingMethod $method, ShippingZone $zone, string $deliveryType, Product $product): float
    {
        // If method has its own flat rate, use it
        if ($method->flat_rate_amount !== null) {
            $cost = (float) $method->flat_rate_amount;
        } else {
            // Fall back to zone's delivery-type-specific cost
            $costField = match (true) {
                $deliveryType === 'office' => 'office_cost',
                default => 'home_cost',
            };
            $cost = (float) ($zone->{$costField} ?? $zone->cost ?? 0);
        }

        // Add weight-based cost
        $weight = (float) ($product->weight ?? 0);
        if ($method->cost_per_kg && $weight > 0) {
            $cost += $weight * (float) $method->cost_per_kg;
        } elseif ($zone->cost_per_kg && $weight > 0) {
            $cost += $weight * (float) $zone->cost_per_kg;
        }

        return round($cost, 2);
    }

    /**
     * Check if a method is allowed for a product based on product rules.
     */
    private function isMethodAllowedForProduct(ShippingMethod $method, Product $product): bool
    {
        $rule = $product->shippingRule;
        if (!$rule) return true;

        $allowedIds = $rule->allowed_methods ?? [];
        $excludedIds = $rule->excluded_methods ?? [];

        if (!empty($excludedIds) && in_array($method->id, $excludedIds)) {
            return false;
        }

        if (!empty($allowedIds) && !in_array($method->id, $allowedIds)) {
            return false;
        }

        // Check weight
        if ($rule->max_weight && ($product->weight ?? 0) > $rule->max_weight) {
            return false;
        }

        // Check zones
        $allowedZones = $rule->allowed_zones ?? [];
        $excludedZones = $rule->excluded_zones ?? [];
        if (!empty($excludedZones) && in_array($method->zone_id, $excludedZones)) {
            return false;
        }
        if (!empty($allowedZones) && !in_array($method->zone_id, $allowedZones)) {
            return false;
        }

        // Check fragile/hazardous
        if ($rule->fragile || $rule->hazardous) {
            // These products might require special handling
        }

        return true;
    }

    /**
     * Check if a method covers a specific city.
     */
    private function isCityCovered(ShippingMethod $method, string $city): bool
    {
        $covered = $method->covered_cities ?? [];
        $excluded = $method->excluded_cities ?? [];

        if (!empty($covered) && !in_array($city, $covered)) {
            return false;
        }

        if (!empty($excluded) && in_array($city, $excluded)) {
            return false;
        }

        return true;
    }

    /**
     * Get pickup location for a carrier in a city.
     */
    private function getPickupLocation(int $carrierId, string $city, string $countryCode): ?array
    {
        $office = ShippingOfficePickup::where('carrier_id', $carrierId)
            ->where('city', $city)
            ->where('country_code', $countryCode)
            ->where('is_active', true)
            ->first();

        if (!$office) {
            $office = ShippingOfficePickup::where('carrier_id', $carrierId)
                ->where('country_code', $countryCode)
                ->where('is_active', true)
                ->first();
        }

        if (!$office) return null;

        return [
            'name' => $office->name,
            'address' => $office->address,
            'working_hours' => $office->working_hours,
            'phone' => $office->phone,
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
        ];
    }

    /**
     * Get supported delivery types for a product + location.
     */
    public function getSupportedDeliveryTypes(int $productId, string $countryCode, string $city): array
    {
        $product = Product::with('shippingCompany')->findOrFail($productId);
        $companyId = $product->shipping_company_id;

        $zone = ShippingZone::where('status', 'active')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->get()
            ->first(fn($z) => $z->isCityInZone($city, $countryCode));

        $deliveryType = $zone?->delivery_type ?? 'home';

        if ($deliveryType === 'office') {
            return ['office'];
        } elseif ($deliveryType === 'both') {
            return ['home', 'office'];
        }
        return ['home'];
    }
}
