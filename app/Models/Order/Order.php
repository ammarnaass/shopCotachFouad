<?php

namespace App\Models\Order;

use App\Models\Catalog\Coupon;
use App\Models\Shipping\ShippingAddress;
use App\Models\Shipping\ShippingCompany;
use App\Models\Shipping\ShippingMethod;
use App\Models\User\User;
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
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function shippingCompany(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
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
            $method = ShippingMethod::find((int) $m[1]);
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
