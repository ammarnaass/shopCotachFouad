<?php

namespace App\Modules\Cart\Models;

use App\Modules\Coupons\Models\Coupon;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id', 'coupon_id'];

    public function user(): BelongsTo
    {
        $userClass = class_exists(User::class) ? User::class : \App\Models\User\User::class;
        return $this->belongsTo($userClass);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon(): BelongsTo
    {
        $couponClass = class_exists(Coupon::class) ? Coupon::class : \App\Models\Catalog\Coupon::class;
        return $this->belongsTo($couponClass);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->price * $item->quantity);
    }

    public function getTotalItemsAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getDiscountAttribute(): float
    {
        if (! $this->coupon) {
            return 0;
        }

        return $this->coupon->calculateDiscount($this->subtotal);
    }
}
