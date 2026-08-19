<?php

namespace App\Modules\Shipping\Models;

use App\Modules\Orders\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingLabel extends Model
{
    protected $fillable = [
        'order_id', 'carrier_id', 'tracking_number',
        'label_pdf', 'weight', 'cost',
        'status', 'shipped_at', 'delivered_at',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'cost' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        $orderClass = class_exists(Order::class) ? Order::class : \App\Models\Order\Order::class;
        return $this->belongsTo($orderClass);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'carrier_id');
    }

    public function trackingUpdates(): HasMany
    {
        return $this->hasMany(ShippingTracking::class, 'label_id');
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'قيد الانتظار',
            'printed' => 'تم الطباعة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'returned' => 'مرتجع',
            default => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'printed' => 'blue',
            'shipped' => 'indigo',
            'delivered' => 'green',
            'returned' => 'red',
            default => 'gray',
        };
    }

    public function getTrackingLink(): string
    {
        return $this->carrier?->getTrackingLink($this->tracking_number) ?? '#';
    }

    public function markShipped(): void
    {
        $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }

    public function markDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }
}
