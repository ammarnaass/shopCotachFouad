<?php

namespace App\Modules\Shipping\Models;

use App\Modules\Coupons\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = [
        'zone_id', 'name', 'type', 'carrier_id',
        'flat_rate_amount',
        'cost_per_kg', 'free_threshold',
        'min_order_value', 'max_order_value',
        'free_shipping_min', 'free_shipping_requires',
        'weight_ranges',
        'product_ids',
        'covered_cities', 'excluded_cities',
        'api_settings',
        'zone_rates',
        'estimated_days',
        'tax_status', 'status', 'sort_order',
    ];

    protected $casts = [
        'weight_ranges' => 'array',
        'product_ids' => 'array',
        'covered_cities' => 'array',
        'excluded_cities' => 'array',
        'api_settings' => 'array',
        'zone_rates' => 'array',
        'flat_rate_amount' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'free_threshold' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_order_value' => 'decimal:2',
        'free_shipping_min' => 'decimal:2',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'carrier_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(ShippingLabel::class, 'method_id');
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'flat_rate' => 'شحن ثابت',
            'free_shipping' => 'شحن مجاني',
            'weight_based' => 'حسب الوزن',
            'zone_based' => 'حسب المنطقة',
            'product_based' => 'حسب المنتج',
            'courier_api' => 'API شركة شحن',
            default => $this->type,
        };
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'flat_rate' => 'fa-tag',
            'free_shipping' => 'fa-gift',
            'weight_based' => 'fa-weight-hanging',
            'zone_based' => 'fa-map-marked-alt',
            'product_based' => 'fa-box',
            'courier_api' => 'fa-plug',
            default => 'fa-truck',
        };
    }

    public function getTypeColor(): string
    {
        return match ($this->type) {
            'flat_rate' => 'blue',
            'free_shipping' => 'green',
            'weight_based' => 'purple',
            'zone_based' => 'orange',
            'product_based' => 'indigo',
            'courier_api' => 'teal',
            default => 'gray',
        };
    }

    public function calculateCost(float $weight = 0, float $subtotal = 0, array $cartItems = [], ?Coupon $coupon = null): ?float
    {
        return match ($this->type) {
            'flat_rate' => (float) $this->flat_rate_amount,
            'free_shipping' => $this->qualifiesForFreeShipping($subtotal, $coupon) ? 0 : null,
            'weight_based' => $this->calculateWeightCost($weight),
            'product_based' => $this->calculateProductCost($cartItems),
            'courier_api' => $this->getCourierQuote($weight, $cartItems),
            'zone_based' => $this->calculateZoneRateCost(),
            default => null,
        };
    }

    private function qualifiesForFreeShipping(float $subtotal, ?Coupon $coupon = null): bool
    {
        return match ($this->free_shipping_requires) {
            'min_amount' => $subtotal >= (float) $this->free_shipping_min,
            'coupon' => $coupon !== null,
            'both' => $subtotal >= (float) $this->free_shipping_min && $coupon !== null,
            default => false,
        };
    }

    private function calculateWeightCost(float $weight): ?float
    {
        $ranges = $this->weight_ranges ?? [];
        if (empty($ranges)) {
            return null;
        }

        usort($ranges, fn ($a, $b) => $a['max'] <=> $b['max']);

        foreach ($ranges as $range) {
            if ($weight <= (float) ($range['max'] ?? 0)) {
                return (float) ($range['cost'] ?? 0);
            }
        }

        $lastRange = end($ranges);

        return (float) ($lastRange['cost'] ?? 0);
    }

    private function calculateProductCost(array $cartItems): ?float
    {
        $productIds = $this->product_ids ?? [];
        if (empty($productIds)) {
            return null;
        }

        $total = 0;
        foreach ($cartItems as $item) {
            $itemId = is_array($item) ? ($item['product_id'] ?? 0) : $item;
            if (in_array($itemId, $productIds)) {
                $total += (float) ($this->flat_rate_amount ?? 0);
            }
        }

        return $total > 0 ? $total : null;
    }

    private function getCourierQuote(float $weight, array $items): ?float
    {
        return (float) ($this->flat_rate_amount ?? 0);
    }

    private function calculateZoneRateCost(): ?float
    {
        $rates = $this->zone_rates ?? [];

        return (float) ($rates['default'] ?? $this->flat_rate_amount ?? 0);
    }
}
