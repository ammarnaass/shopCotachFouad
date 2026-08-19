<?php

namespace App\Modules\Orders\Models;

use App\Modules\Coupons\Models\Coupon;
use App\Modules\Shipping\Models\ShippingAddress;
use App\Modules\Shipping\Models\ShippingCompany;
use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Payments\Models\Payment;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'guest_email', 'guest_phone', 'is_instant_buy',
        'order_number', 'status', 'payment_status',
        'shipping_status', 'subtotal', 'shipping_cost', 'discount',
        'tax', 'cod_fee', 'grand_total', 'notes', 'cancel_reason',
        'shipping_address_id', 'shipping_company_id', 'tracking_number',
        'shipping_method', 'shipping_method_id', 'delivery_type', 'coupon_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'cod_fee' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public const STATUSES = [
        'pending' => 'قيد الانتظار',
        'confirmed' => 'مؤكد',
        'processing' => 'قيد التجهيز',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغي',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'قيد الانتظار',
        'paid' => 'مدفوع',
        'failed' => 'فشل',
        'refunded' => 'مسترد',
    ];

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.strtoupper(uniqid());
            }
        });
    }

    public function user(): BelongsTo
    {
        $userClass = class_exists(User::class) ? User::class : \App\Models\User\User::class;
        return $this->belongsTo($userClass);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): BelongsTo
    {
        $addressClass = class_exists(ShippingAddress::class) ? ShippingAddress::class : \App\Models\Shipping\ShippingAddress::class;
        return $this->belongsTo($addressClass);
    }

    public function shippingCompany(): BelongsTo
    {
        $companyClass = class_exists(ShippingCompany::class) ? ShippingCompany::class : \App\Models\Shipping\ShippingCompany::class;
        return $this->belongsTo($companyClass);
    }

    public function shippingMethod(): BelongsTo
    {
        $methodClass = class_exists(ShippingMethod::class) ? ShippingMethod::class : \App\Models\Shipping\ShippingMethod::class;
        return $this->belongsTo($methodClass);
    }

    public function payment(): HasMany
    {
        $paymentClass = class_exists(Payment::class) ? Payment::class : \App\Models\Order\Payment::class;
        return $this->hasMany($paymentClass);
    }

    public function coupon(): BelongsTo
    {
        $couponClass = class_exists(Coupon::class) ? Coupon::class : \App\Models\Catalog\Coupon::class;
        return $this->belongsTo($couponClass);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getDeliveryTypeLabelAttribute(): string
    {
        return match ($this->delivery_type) {
            'office' => 'توصيل إلى المكتب',
            'home' => 'توصيل إلى المنزل',
            default => $this->delivery_type ?? '—',
        };
    }

    public function getShippingMethodLabelAttribute(): string
    {
        if ($this->shippingMethod) {
            return $this->shippingMethod->name;
        }

        $slug = $this->shipping_method;

        if ($slug && preg_match('/^method_(\d+)$/', $slug, $m)) {
            $methodClass = class_exists(ShippingMethod::class) ? ShippingMethod::class : \App\Models\Shipping\ShippingMethod::class;
            $method = $methodClass::find((int) $m[1]);
            if ($method) {
                return $method->name;
            }
        }

        return match ($slug) {
            'express' => 'شحن سريع',
            'standard' => 'شحن عادي',
            default => $slug ?? '—',
        };
    }

    public static function extractShippingMethodId(string $slug): ?int
    {
        if (preg_match('/^method_(\d+)$/', $slug, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'processing' => 'indigo',
            'shipped' => 'purple',
            'delivered' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
