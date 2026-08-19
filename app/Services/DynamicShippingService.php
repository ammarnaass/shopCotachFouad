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
            $carrierName = $zone->company?->name ?? 'توصيل محلي';
            $carrierId = $zone->company_id;
            $zoneDelivery = $zone->delivery_type ?? 'both';

            // 1. Home Delivery Option (if zone supports home or both)
            if ($zoneDelivery === 'home' || $zoneDelivery === 'both') {
                $cost = (float) ($zone->home_cost ?? $zone->cost ?? 0);
                $isFree = ($zone->free_threshold && $product->price >= $zone->free_threshold) || $cost === 0.0;
                $available[] = [
                    'id' => $zone->id . '_home',
                    'name' => 'توصيل إلى المنزل' . ($zone->company ? ' (' . $carrierName . ')' : ''),
                    'type' => 'standard',
                    'carrier' => $carrierName,
                    'carrier_id' => $carrierId,
                    'zone_id' => $zone->id,
                    'delivery_type' => 'home',
                    'cost' => $cost,
                    'is_free' => $isFree,
                    'estimated_days' => $zone->estimated_days_standard ?? '2-4 أيام',
                    'is_cod_available' => true,
                    'pickup_location' => null,
                ];

                // Express home delivery if configured
                if (!empty($zone->home_express_cost) || !empty($zone->express_cost)) {
                    $expressCost = (float) ($zone->home_express_cost ?? $zone->express_cost);
                    $available[] = [
                        'id' => $zone->id . '_express',
                        'name' => 'توصيل سريع للمنزل' . ($zone->company ? ' (' . $carrierName . ')' : ''),
                        'type' => 'express',
                        'carrier' => $carrierName,
                        'carrier_id' => $carrierId,
                        'zone_id' => $zone->id,
                        'delivery_type' => 'home',
                        'cost' => $expressCost,
                        'is_free' => false,
                        'estimated_days' => $zone->estimated_days_express ?? '24-48 ساعة',
                        'is_cod_available' => true,
                        'pickup_location' => null,
                    ];
                }
            }

            // 2. Office Pickup Option (if zone supports office or both)
            if ($zoneDelivery === 'office' || $zoneDelivery === 'both') {
                $officeCost = (float) ($zone->office_cost ?? $zone->cost ?? 0);
                $isFree = ($zone->free_threshold && $product->price >= $zone->free_threshold) || $officeCost === 0.0;
                $available[] = [
                    'id' => $zone->id . '_office',
                    'name' => 'استلام من المكتب' . ($zone->company ? ' (' . $carrierName . ')' : ''),
                    'type' => 'office_pickup',
                    'carrier' => $carrierName,
                    'carrier_id' => $carrierId,
                    'zone_id' => $zone->id,
                    'delivery_type' => 'office',
                    'cost' => $officeCost,
                    'is_free' => $isFree,
                    'estimated_days' => $zone->estimated_days_standard ?? '2-3 أيام',
                    'is_cod_available' => true,
                    'pickup_location' => null,
                ];
            }
        }

        // Only use default zone if admin explicitly set a zone as is_default = true
        if (empty($available)) {
            $defaultZone = ShippingZone::where('status', 'active')
                ->where('is_default', true)
                ->first();

            if ($defaultZone && $defaultZone->supportsDelivery($deliveryType)) {
                $carrierName = $defaultZone->company?->name ?? 'شحن قياسي';
                $cost = (float) ($defaultZone->home_cost ?? $defaultZone->cost ?? 500);

                $available[] = [
                    'id' => null,
                    'name' => 'توصيل قياسي (' . $carrierName . ')',
                    'type' => 'standard',
                    'carrier' => $carrierName,
                    'carrier_id' => $defaultZone->company_id,
                    'zone_id' => $defaultZone->id,
                    'delivery_type' => $deliveryType ?: 'home',
                    'cost' => $cost,
                    'is_free' => $cost === 0.0,
                    'estimated_days' => $defaultZone->estimated_days_standard ?? '2-4 أيام',
                    'is_cod_available' => true,
                    'pickup_location' => null,
                ];
            }
        }

        return [
            'available' => $available,
            'unavailable' => $unavailable,
        ];
    }

    /**
     * Calculate cost for a specific method in a zone.
     */
    public function calculateMethodCost($method, $zone, string $deliveryType, Product $product): float
    {
        // If method has its own flat rate, use it
        if (isset($method->flat_rate_amount) && $method->flat_rate_amount !== null) {
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
        if (!empty($method->cost_per_kg) && $weight > 0) {
            $cost += $weight * (float) $method->cost_per_kg;
        } elseif (!empty($zone->cost_per_kg) && $weight > 0) {
            $cost += $weight * (float) $zone->cost_per_kg;
        }

        return round($cost, 2);
    }

    /**
     * Check if a method is allowed for a product based on product rules.
     */
    private function isMethodAllowedForProduct($method, Product $product): bool
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
    private function isCityCovered($method, string $city): bool
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

        if (!$zone) {
            return [];
        }

        $deliveryType = $zone->delivery_type ?? 'home';

        if ($deliveryType === 'office') {
            return ['office'];
        } elseif ($deliveryType === 'both') {
            return ['home', 'office'];
        }
        return ['home'];
    }
}
