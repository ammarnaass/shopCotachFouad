<?php

namespace App\Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCompany extends Model
{
    protected $fillable = [
        'name', 'logo', 'website', 'tracking_url',
        'api_endpoint', 'api_key', 'api_secret',
        'is_active', 'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(ShippingZone::class, 'company_id');
    }

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'carrier_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(ShippingLabel::class, 'carrier_id');
    }

    public function officePickups(): HasMany
    {
        return $this->hasMany(ShippingOfficePickup::class, 'carrier_id');
    }

    public function getTrackingLink(string $trackingNumber): string
    {
        return str_replace('{TRACKING}', $trackingNumber, $this->tracking_url ?? '');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }
}
