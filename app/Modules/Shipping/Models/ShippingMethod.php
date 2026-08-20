<?php

namespace App\Modules\Shipping\Models;

use App\Modules\Shipping\Exceptions\InvalidShippingConfigurationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    use HasFactory;

    protected static function newFactory(): \Database\Factories\Shipping\ShippingMethodFactory
    {
        return \Database\Factories\Shipping\ShippingMethodFactory::new();
    }
    protected $fillable = [
        'shipping_zone_id',
        'zone_id', // حقل قديم — مطلوب للتوافقية مع schema الحالي
        'name',
        'code',
        'calculation_type',
        'base_cost',
        'cost_per_kg',
        'free_shipping_threshold',
        'min_delivery_days',
        'max_delivery_days',
        'status',
        'sort_order',
        'method_order',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'min_delivery_days' => 'integer',
        'max_delivery_days' => 'integer',
        'sort_order' => 'integer',
        'method_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShippingMethod $method) {
            // مزامنة حقلين zone_id و shipping_zone_id
            if (empty($method->shipping_zone_id) && !empty($method->zone_id)) {
                $method->shipping_zone_id = $method->zone_id;
            } elseif (empty($method->zone_id) && !empty($method->shipping_zone_id)) {
                $method->zone_id = $method->shipping_zone_id;
            }

            // مزامنة التكلفة الأساسية base_cost مع flat_rate_amount
            if (empty($method->base_cost) && isset($method->attributes['flat_rate_amount'])) {
                $method->base_cost = (float) $method->attributes['flat_rate_amount'];
            } elseif (!isset($method->attributes['flat_rate_amount']) && !empty($method->base_cost)) {
                $method->attributes['flat_rate_amount'] = (float) $method->base_cost;
            }

            // مزامنة حد الشحن المجاني
            if (empty($method->free_shipping_threshold) && isset($method->attributes['free_shipping_min'])) {
                $method->free_shipping_threshold = (float) $method->attributes['free_shipping_min'];
            } elseif (!isset($method->attributes['free_shipping_min']) && !empty($method->free_shipping_threshold)) {
                $method->attributes['free_shipping_min'] = (float) $method->free_shipping_threshold;
            }

            // مزامنة نوع الاحتساب calculation_type مع type
            if (empty($method->calculation_type)) {
                $type = $method->attributes['type'] ?? 'flat_rate';
                $method->calculation_type = match ($type) {
                    'weight_based' => 'weight_based',
                    'price_based'  => 'price_based',
                    default        => 'flat',
                };
            }
            if (empty($method->attributes['type'])) {
                $method->attributes['type'] = match ($method->calculation_type) {
                    'weight_based' => 'weight_based',
                    default        => 'flat_rate',
                };
            }

            // توليد كود فريد لطريقة الشحن إن لم يوجد
            if (empty($method->code)) {
                $base = \Illuminate\Support\Str::slug($method->name ?? 'shipping');
                $method->code = ($base ?: 'shipping') . '-' . ($method->shipping_zone_id ?? 'zone') . '-' . uniqid();
            }
        });
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'carrier_id');
    }

    public function getTypeLabel(): string
    {
        $type = $this->type ?? $this->calculation_type;
        return match ($type) {
            'flat', 'flat_rate' => 'شحن ثابت',
            'free_shipping' => 'شحن مجاني',
            'weight_based' => 'حسب الوزن',
            'price_based' => 'حسب السعر',
            'zone_based' => 'حسب المنطقة',
            'product_based' => 'حسب المنتج',
            'courier_api' => 'API شركة شحن',
            default => $type ?? 'ثابت',
        };
    }

    public function getFlatRateAmountAttribute(): ?float
    {
        return isset($this->attributes['flat_rate_amount'])
            ? (float) $this->attributes['flat_rate_amount']
            : (float) ($this->base_cost ?? 0);
    }

    public function getFreeShippingMinAttribute(): ?float
    {
        return isset($this->attributes['free_shipping_min'])
            ? (float) $this->attributes['free_shipping_min']
            : ($this->free_shipping_threshold ? (float) $this->free_shipping_threshold : null);
    }

    public function getEstimatedDaysAttribute(): ?string
    {
        if (isset($this->attributes['estimated_days']) && !empty($this->attributes['estimated_days'])) {
            return $this->attributes['estimated_days'];
        }
        if ($this->min_delivery_days && $this->max_delivery_days) {
            return "{$this->min_delivery_days}-{$this->max_delivery_days}";
        }
        return null;
    }

    public function getTypeAttribute(): string
    {
        return $this->attributes['type'] ?? ($this->calculation_type === 'weight_based' ? 'weight_based' : 'flat_rate');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'active')
              ->orWhere('status', 1)
              ->orWhere('status', '1')
              ->orWhere('status', true);
        });
    }

    /**
     * احتساب تكلفة الشحن بدقة وصرامة — لا قيم افتراضية ضمنية،
     * كل حالة غير معروفة بـ calculation_type تُرمى كخطأ إعداد بدل تخمين سعر.
     */
    public function calculateCost(float $subtotal, float $weightKg = 0): float
    {
        if ($this->free_shipping_threshold !== null && $subtotal >= (float) $this->free_shipping_threshold) {
            return 0.0;
        }

        $cost = match ($this->calculation_type) {
            'flat' => (float) $this->base_cost,
            'weight_based' => (float) $this->base_cost + (max($weightKg, 0) * (float) $this->cost_per_kg),
            'price_based' => (float) $this->base_cost,
            default => throw new InvalidShippingConfigurationException(
                "نوع احتساب شحن غير معروف: {$this->calculation_type} لطريقة الشحن #{$this->id}"
            ),
        };

        // لا نسمح بأي حال بتكلفة سالبة — قد تنتج عن إعداد خاطئ بالإدارة
        return max($cost, 0.0);
    }
}
