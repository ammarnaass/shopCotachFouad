<?php

namespace App\Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingOfficePickup extends Model
{
    protected $fillable = [
        'carrier_id', 'name', 'address',
        'city', 'state', 'country_code',
        'latitude', 'longitude',
        'working_hours', 'phone', 'email',
        'is_active',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'is_active' => 'boolean',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'carrier_id');
    }
}
