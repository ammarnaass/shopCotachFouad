<?php

namespace App\Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingTracking extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'label_id', 'status', 'location', 'description', 'tracked_at',
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
    ];

    public function label(): BelongsTo
    {
        return $this->belongsTo(ShippingLabel::class, 'label_id');
    }

    public static function getStatuses(): array
    {
        return [
            'picked_up' => 'تم الاستلام',
            'in_transit' => 'في الطريق',
            'out_for_delivery' => 'خارج للتوصيل',
            'delivered' => 'تم التسليم',
            'failed_delivery' => 'فشل التوصيل',
            'returned' => 'مرتجع',
            'customs' => 'الجمارك',
            'at_facility' => 'في الفرع',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }
}
