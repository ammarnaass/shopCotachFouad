<?php

namespace App\Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCompany extends Model
{
    protected $fillable = ['name', 'code', 'logo', 'status', 'sort_order'];

    public function zones(): HasMany
    {
        return $this->hasMany(ShippingZone::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
