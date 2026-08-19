<?php

namespace App\Modules\Coupons\Models;

use App\Modules\Cart\Models\Cart;
use App\Modules\Orders\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'max_discount',
        'expiry_date', 'usage_limit', 'used_count', 'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'expiry_date' => 'datetime',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
    ];

    public static function booted(): void
    {
        static::creating(function ($coupon) {
            if (empty($coupon->code)) {
                $coupon->code = strtoupper(Str::random(8));
            }
        });
    }

    public function isValid(float $orderTotal = 0): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }
        if ($this->min_order && $orderTotal < $this->min_order) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderTotal): float
    {
        if (! $this->isValid($orderTotal)) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? ($orderTotal * (float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = (float) $this->max_discount;
        }

        return min($discount, $orderTotal);
    }

    public function carts(): HasMany
    {
        $cartClass = class_exists(Cart::class) ? Cart::class : \App\Models\Cart\Cart::class;
        return $this->hasMany($cartClass);
    }

    public function orders(): HasMany
    {
        $orderClass = class_exists(Order::class) ? Order::class : \App\Models\Order\Order::class;
        return $this->hasMany($orderClass);
    }
}
