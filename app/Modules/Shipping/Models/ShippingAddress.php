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
        $countryName = $this->country_name ?? $this->country_code;
        $stateName = $this->state_name;
        $stateNum = $this->state_number ? "(ولاية: {$this->state_number})" : null;

        return collect([
            $this->address,
            $this->district,
            $this->city,
            $stateName ? "{$stateName} {$stateNum}" : null,
            $countryName,
        ])->filter()->implode(' - ');
    }

    public function getCountryNameAttribute(): ?string
    {
        $cc = strtoupper($this->country_code ?: 'DZ');
        return config("ecommerce.countries.{$cc}.name") ?: ($cc === 'DZ' ? 'الجزائر' : $cc);
    }

    public function getStateNameAttribute(): ?string
    {
        $cc = strtoupper($this->country_code ?: 'DZ');
        $states = config("ecommerce.countries.{$cc}.states", []);

        if (!empty($this->state_code)) {
            // Direct key match (e.g. '47')
            if (isset($states[$this->state_code])) {
                return $states[$this->state_code];
            }
            // Padded 2-digit key match (e.g. '1' -> '01')
            $padded = str_pad($this->state_code, 2, '0', STR_PAD_LEFT);
            if (isset($states[$padded])) {
                return $states[$padded];
            }
            // In values match
            if (in_array($this->state_code, $states, true)) {
                return $this->state_code;
            }
        }

        // Fallback: match by city
        if (!empty($this->city)) {
            foreach ($states as $name) {
                if (trim($this->city) === trim($name)) {
                    return $name;
                }
            }
        }

        return $this->state_code;
    }

    public function getStateNumberAttribute(): ?string
    {
        $cc = strtoupper($this->country_code ?: 'DZ');
        $states = config("ecommerce.countries.{$cc}.states", []);

        if (!empty($this->state_code)) {
            if (isset($states[$this->state_code])) {
                return (string)$this->state_code;
            }
            $padded = str_pad($this->state_code, 2, '0', STR_PAD_LEFT);
            if (isset($states[$padded])) {
                return $padded;
            }
            // Search key by value
            foreach ($states as $code => $name) {
                if ($name === $this->state_code) {
                    return (string)$code;
                }
            }
            if (is_numeric($this->state_code)) {
                return $padded;
            }
        }

        if (!empty($this->city)) {
            foreach ($states as $code => $name) {
                if (trim($this->city) === trim($name)) {
                    return (string)$code;
                }
            }
        }

        return null;
    }
}
