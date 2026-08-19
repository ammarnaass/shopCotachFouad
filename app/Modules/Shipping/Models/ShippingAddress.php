<?php

namespace App\Modules\Shipping\Models;

use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'name', 'phone', 'email',
        'country_code', 'state_code', 'city', 'district',
        'address', 'zip', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        $userClass = class_exists(User::class) ? User::class : \App\Models\User\User::class;
        return $this->belongsTo($userClass);
    }

    public function getFullAddressAttribute(): string
    {
        $countries = config('ecommerce.countries', []);
        $countryName = $countries[$this->country_code]['name'] ?? $this->country_code;

        $stateName = null;
        if ($this->country_code && $this->state_code) {
            $stateName = $countries[$this->country_code]['states'][$this->state_code] ?? null;
        }

        return collect([
            $this->address,
            $this->district,
            $this->city,
            $stateName,
            $countryName,
        ])->filter()->implode(' - ');
    }

    public function getCountryNameAttribute(): ?string
    {
        return config("ecommerce.countries.{$this->country_code}.name");
    }

    public function getStateNameAttribute(): ?string
    {
        return config("ecommerce.countries.{$this->country_code}.states.{$this->state_code}");
    }
}
