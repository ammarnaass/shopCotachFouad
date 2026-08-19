<?php

namespace App\Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = [
        'company_id', 'name', 'description', 'regions', 'countries', 'states', 'cities',
        'delivery_type',
        'cost', 'express_cost',
        'home_cost', 'home_express_cost',
        'office_cost', 'office_express_cost',
        'cost_per_kg', 'free_threshold',
        'estimated_days_standard', 'estimated_days_express',
        'is_default', 'priority', 'sort_order', 'status',
    ];

    protected $casts = [
        'regions' => 'array',
        'countries' => 'array',
        'states' => 'array',
        'cities' => 'array',
        'cost' => 'decimal:2',
        'express_cost' => 'decimal:2',
        'home_cost' => 'decimal:2',
        'home_express_cost' => 'decimal:2',
        'office_cost' => 'decimal:2',
        'office_express_cost' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'free_threshold' => 'decimal:2',
        'is_default' => 'boolean',
        'priority' => 'integer',
        'sort_order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'company_id');
    }

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id');
    }

    public function activeMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id')->where('status', true);
    }

    public function supportsDelivery(string $type): bool
    {
        return $this->delivery_type === 'both' || $this->delivery_type === $type;
    }

    /**
     * Find the best matching zone for a given address.
     */
    public static function findMatchingZone(?int $countryId, ?int $stateId = null, ?int $cityId = null): ?self
    {
        return static::where('status', 'active')
            ->where(function ($q) use ($countryId) {
                $q->whereNull('countries')
                    ->orWhereJsonContains('countries', $countryId)
                    ->orWhereJsonContains('countries', '*');
            })
            ->where(function ($q) use ($stateId) {
                $q->whereNull('states')
                    ->orWhereJsonContains('states', $stateId)
                    ->orWhereJsonContains('states', '*');
            })
            ->where(function ($q) use ($cityId) {
                $q->whereNull('cities')
                    ->orWhereJsonContains('cities', $cityId)
                    ->orWhereJsonContains('cities', '*');
            })
            ->orderBy('priority')
            ->first();
    }

    /**
     * Calculate shipping cost given a city, country, delivery type and method.
     */
    public function calculateCost(string $city, string $countryCode = '', string $method = 'standard', string $deliveryType = 'home', float $subtotal = 0, float $weight = 0, ?string $stateName = null): float
    {
        if (! $this->isCityInZone($city, $countryCode ?: null, $stateName)) {
            return 0;
        }
        if (! $this->supportsDelivery($deliveryType)) {
            return 0;
        }

        // Per-delivery-type specific price
        $costField = match (true) {
            $deliveryType === 'office' && $method === 'express' => 'office_express_cost',
            $deliveryType === 'office' => 'office_cost',
            $deliveryType === 'home' && $method === 'express' => 'home_express_cost',
            default => 'home_cost',
        };
        $baseCost = $this->{$costField};

        // Fallback to general cost/express_cost if specific field is null
        if ($baseCost === null) {
            $baseCost = $method === 'express' ? (float) $this->express_cost : (float) $this->cost;
        }

        // Add weight-based cost
        if ($this->cost_per_kg && $weight > 0) {
            $baseCost += (float) $this->cost_per_kg * $weight;
        }

        // Free shipping if subtotal exceeds threshold
        if ($this->free_threshold && $subtotal >= $this->free_threshold) {
            return 0;
        }

        return (float) $baseCost;
    }

    public function isCityInZone(string $city, ?string $countryCode = null, ?string $stateName = null): bool
    {
        // Country filter
        if (! empty($countryCode) && ! empty($this->countries) && is_array($this->countries)) {
            if (! in_array('*', $this->countries) && ! in_array($countryCode, $this->countries)) {
                return false;
            }
        }

        // City/State filter
        $citiesData = $this->cities ?? $this->regions ?? [];
        if (empty($citiesData)) {
            return true;
        } // empty = all cities match

        $cities = [];
        if (is_array($citiesData)) {
            if ($countryCode && isset($citiesData[$countryCode]) && is_array($citiesData[$countryCode])) {
                $cities = $citiesData[$countryCode];
            } elseif (isset($citiesData['*']) && is_array($citiesData['*'])) {
                $cities = $citiesData['*'];
            } else {
                foreach ($citiesData as $k => $v) {
                    if (is_array($v)) {
                        $cities = array_merge($cities, $v);
                    } else {
                        $cities[] = $v;
                    }
                }
            }
        } elseif (is_string($citiesData)) {
            $cities = array_map('trim', explode(',', $citiesData));
        }

        if (empty($cities) || in_array('*', $cities)) {
            return true;
        }

        // Check if city matches directly
        if (in_array($city, $cities)) {
            return true;
        }

        // Check if state name matches
        if ($stateName && in_array($stateName, $cities)) {
            return true;
        }

        // Resolve code to name or name to code for supported countries
        if ($countryCode && config("ecommerce.countries.{$countryCode}.states")) {
            $states = config("ecommerce.countries.{$countryCode}.states", []);
            $nameFromCode = $states[$city] ?? null;
            if ($nameFromCode && in_array($nameFromCode, $cities)) {
                return true;
            }
            $codeFromName = array_search($city, $states);
            if ($codeFromName !== false && in_array($codeFromName, $cities)) {
                return true;
            }
        }

        return false;
    }

    public function estimatedDays(string $method = 'standard'): ?string
    {
        $field = $method === 'express' ? 'estimated_days_express' : 'estimated_days_standard';

        return $this->{$field};
    }

    /**
     * Get list of carrier names from attached methods.
     */
    public function getCarrierNames(): string
    {
        $carriers = $this->methods()->with('carrier')->get()
            ->pluck('carrier.name')
            ->filter()
            ->unique()
            ->implode('، ');

        return $carriers ?: ($this->company?->name ?? 'متجر');
    }

    public function getFormattedCities(): string
    {
        if (empty($this->cities)) {
            return '';
        }
        if (is_string($this->cities)) {
            return $this->cities;
        }
        $list = [];
        if (is_array($this->cities)) {
            foreach ($this->cities as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $city) {
                        $list[] = $city;
                    }
                } else {
                    $list[] = $value;
                }
            }
        }

        return implode('، ', $list);
    }

    public function getFormattedCountries(): string
    {
        if (empty($this->countries)) {
            return '';
        }
        if (is_string($this->countries)) {
            return $this->countries;
        }
        $list = [];
        if (is_array($this->countries)) {
            foreach ($this->countries as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $country) {
                        $list[] = $country;
                    }
                } else {
                    $list[] = $value;
                }
            }
        }

        return implode('، ', $list);
    }

    public function getFormattedStates(): string
    {
        if (empty($this->states)) {
            return '';
        }
        if (is_string($this->states)) {
            return $this->states;
        }
        $list = [];
        if (is_array($this->states)) {
            foreach ($this->states as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $state) {
                        $list[] = $state;
                    }
                } else {
                    $list[] = $value;
                }
            }
        }

        return implode('، ', $list);
    }
}
