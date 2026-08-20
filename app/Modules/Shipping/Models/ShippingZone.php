<?php

namespace App\Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use HasFactory;

    protected static function newFactory(): \Database\Factories\Shipping\ShippingZoneFactory
    {
        return \Database\Factories\Shipping\ShippingZoneFactory::new();
    }
    protected $fillable = [
        'shipping_company_id',
        'company_id',
        'name',
        'zone_type', // 'standard' | 'everywhere'
        'description',
        'countries',
        'states',
        'cities',
        'regions',
        'delivery_type',
        'cost',
        'express_cost',
        'home_cost',
        'home_express_cost',
        'office_cost',
        'office_express_cost',
        'cost_per_kg',
        'free_threshold',
        'estimated_days_standard',
        'estimated_days_express',
        'is_default',
        'status',
        'priority',
        'sort_order',
    ];

    protected $casts = [
        'countries' => 'array',
        'states'    => 'array',
        'cities'    => 'array',
        'regions'   => 'array',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'priority'   => 'integer',
        'cost'       => 'decimal:2',
        'express_cost' => 'decimal:2',
        'home_cost'  => 'decimal:2',
        'home_express_cost' => 'decimal:2',
        'office_cost' => 'decimal:2',
        'office_express_cost' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'free_threshold' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShippingZone $zone) {
            if (is_array($zone->countries)) {
                $zone->countries = collect($zone->countries)
                    ->flatten()
                    ->filter()
                    ->map(fn ($c) => strtoupper(trim((string) $c)))
                    ->unique()
                    ->values()
                    ->all();
            }

            if (is_array($zone->states)) {
                $zone->states = collect($zone->states)
                    ->flatten()
                    ->filter()
                    ->map(fn ($s) => trim((string) $s))
                    ->unique()
                    ->values()
                    ->all();
            }

            if (is_array($zone->cities)) {
                $zone->cities = collect($zone->cities)
                    ->flatten()
                    ->filter()
                    ->map(fn ($c) => mb_strtolower(trim((string) $c)))
                    ->unique()
                    ->values()
                    ->all();
            }

            if (is_array($zone->regions)) {
                $zone->regions = collect($zone->regions)
                    ->flatten()
                    ->filter()
                    ->map(fn ($r) => trim((string) $r))
                    ->unique()
                    ->values()
                    ->all();
            }
        });

        static::saved(function (ShippingZone $zone) {
            $zone->syncMethodsAndLocations();
        });
    }

    /**
     * مزامنة طرق الشحن والمواقع الجغرافية للمنطقة تلقائيًا
     */
    public function syncMethodsAndLocations(): void
    {
        // 1. مزامنة المواقع الجغرافية (جدول shipping_zone_locations) فقط عند تمرير مصفوفات مواقع صريحة
        if ($this->zone_type === 'standard' && (is_array($this->countries) || is_array($this->states) || is_array($this->cities))) {
            $this->locations()->delete();

            $hasSpecificStatesOrCities = (!empty($this->states) && is_array($this->states) && !in_array('*', $this->states))
                || (!empty($this->cities) && is_array($this->cities) && !in_array('*', $this->cities));

            // فقط إذا كانت المنطقة عامة لكل الدولة دون تخصيص ولايات
            if (!$hasSpecificStatesOrCities && is_array($this->countries)) {
                foreach ($this->countries as $c) {
                    $code = strtoupper(trim((string)$c));
                    if ($code !== '' && $code !== '*') {
                        $this->locations()->create([
                            'type'  => 'country',
                            'value' => $code,
                        ]);
                    }
                }
            }

            if (is_array($this->states)) {
                $flatStates = collect($this->states)->flatten()->filter()->all();
                foreach ($flatStates as $s) {
                    $st = trim((string)$s);
                    if ($st !== '' && $st !== '*') {
                        $this->locations()->create([
                            'type'  => 'state',
                            'value' => $st,
                        ]);
                    }
                }
            }

            if (is_array($this->cities)) {
                $flatCities = collect($this->cities)->flatten()->filter()->all();
                foreach ($flatCities as $city) {
                    $c = trim((string)$city);
                    if ($c !== '' && $c !== '*') {
                        $type = (is_numeric($c) && (int)$c >= 1 && (int)$c <= 58) ? 'state' : 'postcode';
                        $this->locations()->create([
                            'type'  => $type,
                            'value' => $c,
                        ]);
                    }
                }
            }
        }

        // 2. مزامنة طرق الشحن الشاملة (توصيل منزلي، استلام من المكتب، وشحن سريع)
        $hasExplicitCosts = isset($this->attributes['cost'])
            || isset($this->attributes['home_cost'])
            || isset($this->attributes['express_cost'])
            || isset($this->attributes['home_express_cost'])
            || isset($this->attributes['office_cost'])
            || isset($this->attributes['office_express_cost'])
            || isset($this->attributes['free_threshold']);

        if ($hasExplicitCosts) {
            $deliveryType = $this->delivery_type ?? 'both';
            $freeThreshold = isset($this->free_threshold) && (float)$this->free_threshold > 0
                ? (float)$this->free_threshold
                : null;
            $costPerKg = (float)($this->cost_per_kg ?? 0);
            $standardDays = $this->estimated_days_standard ?: '3-5 أيام';
            $expressDays = $this->estimated_days_express ?: '1-2 يوم';
            $isActive = $this->status === 'active' ? 1 : 0;

            // ----------------------------------------------------
            // 1. طريقة التوصيل المنزلي العادي (Home Standard)
            // ----------------------------------------------------
            $supportsHome = in_array($deliveryType, ['home', 'both']) && isset($this->home_cost) && (float)$this->home_cost > 0;
            $homeStandardCost = $supportsHome ? (float)$this->home_cost : 0.0;

            $homeStandardMethod = $this->methods()
                ->where(function ($q) {
                    $q->where('code', 'home-' . $this->id)
                      ->orWhere('code', 'standard-' . $this->id)
                      ->orWhere('name', 'LIKE', '%منزل%')
                      ->orWhere('name', 'توصيل قياسي');
                })
                ->first();

            if ($supportsHome && $homeStandardCost > 0) {
                $methodData = [
                    'shipping_zone_id'        => $this->id,
                    'zone_id'                 => $this->id,
                    'name'                    => 'توصيل للمنزل',
                    'code'                    => 'home-' . $this->id,
                    'calculation_type'        => 'flat',
                    'type'                    => 'flat_rate',
                    'base_cost'               => $homeStandardCost,
                    'flat_rate_amount'        => $homeStandardCost,
                    'free_shipping_threshold' => $freeThreshold,
                    'free_shipping_min'       => $freeThreshold,
                    'cost_per_kg'             => $costPerKg,
                    'estimated_days'          => $standardDays,
                    'status'                  => $isActive,
                    'sort_order'              => 1,
                    'method_order'            => 1,
                ];

                if ($homeStandardMethod) {
                    $homeStandardMethod->update($methodData);
                } else {
                    $this->methods()->create($methodData);
                }
            } elseif ($homeStandardMethod) {
                $homeStandardMethod->delete();
            }

            // ----------------------------------------------------
            // 2. طريقة التوصيل المنزلي السريع (Home Express)
            // ----------------------------------------------------
            $homeExpressCost = isset($this->home_express_cost) && (float)$this->home_express_cost > 0
                ? (float)$this->home_express_cost
                : 0.0;

            $hasExpressHome = $supportsHome && $homeExpressCost > 0;
            $homeExpressMethod = $this->methods()
                ->where(function ($q) {
                    $q->where('code', 'home-express-' . $this->id)
                      ->orWhere('code', 'express-' . $this->id)
                      ->orWhere('name', 'توصيل سريع للمنزل')
                      ->orWhere('name', 'توصيل سريع');
                })
                ->first();

            if ($hasExpressHome) {
                $methodData = [
                    'shipping_zone_id' => $this->id,
                    'zone_id'          => $this->id,
                    'name'             => 'توصيل سريع للمنزل',
                    'code'             => 'home-express-' . $this->id,
                    'calculation_type' => 'flat',
                    'type'             => 'flat_rate',
                    'base_cost'        => $homeExpressCost,
                    'flat_rate_amount' => $homeExpressCost,
                    'cost_per_kg'      => $costPerKg,
                    'estimated_days'   => $expressDays,
                    'status'           => $isActive,
                    'sort_order'       => 2,
                    'method_order'     => 2,
                ];

                if ($homeExpressMethod) {
                    $homeExpressMethod->update($methodData);
                } else {
                    $this->methods()->create($methodData);
                }
            } elseif ($homeExpressMethod) {
                $homeExpressMethod->delete();
            }

            // ----------------------------------------------------
            // 3. طريقة استلام من مكتب الشركة (Office Pickup / Stop Desk)
            // ----------------------------------------------------
            $supportsOffice = in_array($deliveryType, ['office', 'both']);
            $officeCost = isset($this->office_cost) && (float)$this->office_cost > 0
                ? (float)$this->office_cost
                : (isset($this->cost) && (float)$this->cost > 0 ? (float)$this->cost : 0.0);

            $officeMethod = $this->methods()
                ->where(function ($q) {
                    $q->where('code', 'office-' . $this->id)
                      ->orWhere('name', 'LIKE', '%مكتب%')
                      ->orWhere('name', 'LIKE', '%Stop Desk%');
                })
                ->first();

            if ($supportsOffice && ($officeCost > 0 || $this->zone_type === 'everywhere' || isset($this->attributes['cost']) || isset($this->attributes['office_cost']))) {
                $methodData = [
                    'shipping_zone_id'        => $this->id,
                    'zone_id'                 => $this->id,
                    'name'                    => 'استلام من مكتب الشركة (Stop Desk)',
                    'code'                    => 'office-' . $this->id,
                    'calculation_type'        => 'flat',
                    'type'                    => 'flat_rate',
                    'base_cost'               => $officeCost,
                    'flat_rate_amount'        => $officeCost,
                    'free_shipping_threshold' => $freeThreshold,
                    'free_shipping_min'       => $freeThreshold,
                    'cost_per_kg'             => $costPerKg,
                    'estimated_days'          => $standardDays,
                    'status'                  => $isActive,
                    'sort_order'              => 3,
                    'method_order'            => 3,
                ];

                if ($officeMethod) {
                    $officeMethod->update($methodData);
                } else {
                    $this->methods()->create($methodData);
                }
            } elseif ($officeMethod && !$supportsOffice) {
                $officeMethod->update(['status' => 0]);
            }

            // ----------------------------------------------------
            // 4. طريقة استلام سريع من المكتب (Office Express Pickup)
            // ----------------------------------------------------
            $officeExpressCost = isset($this->office_express_cost) && (float)$this->office_express_cost > 0
                ? (float)$this->office_express_cost
                : (isset($this->express_cost) && (float)$this->express_cost > 0 ? (float)$this->express_cost : 0.0);

            $hasExpressOffice = $supportsOffice && $officeExpressCost > 0;
            $officeExpressMethod = $this->methods()
                ->where(function ($q) {
                    $q->where('code', 'office-express-' . $this->id)
                      ->orWhere('name', 'استلام سريع من المكتب');
                })
                ->first();

            if ($hasExpressOffice) {
                $methodData = [
                    'shipping_zone_id' => $this->id,
                    'zone_id'          => $this->id,
                    'name'             => 'استلام سريع من المكتب',
                    'code'             => 'office-express-' . $this->id,
                    'calculation_type' => 'flat',
                    'type'             => 'flat_rate',
                    'base_cost'        => $officeExpressCost,
                    'flat_rate_amount' => $officeExpressCost,
                    'cost_per_kg'      => $costPerKg,
                    'estimated_days'   => $expressDays,
                    'status'           => $isActive,
                    'sort_order'       => 4,
                    'method_order'     => 4,
                ];

                if ($officeExpressMethod) {
                    $officeExpressMethod->update($methodData);
                } else {
                    $this->methods()->create($methodData);
                }
            } elseif ($officeExpressMethod && !$hasExpressOffice) {
                $officeExpressMethod->update(['status' => 0]);
            }
        }
    }

    public static function getOrCreateEverywhereZone(): self
    {
        return static::firstOrCreate(
            ['zone_type' => 'everywhere'],
            [
                'name'       => 'مواقع غير مغطاة بمناطقك الأخرى',
                'zone_type'  => 'everywhere',
                'sort_order' => 2147483647,
                'status'     => 'active',
                'regions'    => [],
            ]
        );
    }

    public function isEverywhere(): bool
    {
        return $this->zone_type === 'everywhere';
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ShippingZoneLocation::class, 'shipping_zone_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'shipping_company_id');
    }

    public function methods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'shipping_zone_id')->orderBy('method_order')->orderBy('sort_order');
    }

    public function activeMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'shipping_zone_id')
            ->where('status', 'active')
            ->orderBy('method_order')
            ->orderBy('sort_order');
    }

    public function scopeStandard($query)
    {
        return $query->where('zone_type', 'standard');
    }

    public function scopeEverywhere($query)
    {
        return $query->where('zone_type', 'everywhere');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true)->orWhere('zone_type', 'everywhere');
    }

    /**
     * هل تطابق هذه المنطقة عنوان الزبون؟
     * منطق ووكومرس: تطابق أي موقع واحد (منطق OR) كافٍ.
     */
    public function matchesAddress(string $countryCode, ?string $stateCode = null, ?string $postcode = null, ?string $city = null): bool
    {
        if ($this->isEverywhere()) {
            return true;
        }

        // 1. فحص مواقع الجدول الجديد (shipping_zone_locations)
        if ($this->locations->isNotEmpty()) {
            foreach ($this->locations as $loc) {
                if ($loc->matches($countryCode, $stateCode, $postcode)) {
                    return true;
                }
            }
        }

        // 2. فحص التوافقية العكسية مع مصفوفات JSON إن وجدت
        if (!empty($this->countries) && is_array($this->countries)) {
            $normalizedCountry = strtoupper(trim($countryCode));
            if (in_array('*', $this->countries) || in_array($normalizedCountry, $this->countries)) {
                // إذا لم توجد قيود على المدن/الولايات
                if (empty($this->cities) && empty($this->states)) {
                    return true;
                }

                if (!empty($city) && $this->isCityInZone($city, $countryCode, $stateCode)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * مطابقة دقيقة بدولة (كود ISO-alpha2 نصي فقط — لا IDs رقمية أبدًا).
     */
    public function scopeForCountry($query, string $countryCode)
    {
        return $query->whereJsonContains('countries', strtoupper(trim($countryCode)));
    }

    public function getFormattedCountries(): string
    {
        if (empty($this->countries)) {
            return '';
        }
        if (is_string($this->countries)) {
            return $this->countries;
        }

        return collect($this->countries)->flatten()->filter()->implode('، ');
    }

    public function getFormattedStates(): string
    {
        if (empty($this->states)) {
            return '';
        }
        if (is_string($this->states)) {
            return $this->states;
        }

        return collect($this->states)->flatten()->filter()->implode('، ');
    }

    public function getFormattedCities(): string
    {
        $cities = $this->cities ?? $this->regions ?? [];
        if (empty($cities)) {
            return '';
        }
        if (is_string($cities)) {
            return $cities;
        }

        return collect($cities)->flatten()->filter()->implode('، ');
    }

    public function getCarrierNames(): string
    {
        $carriers = $this->methods()->with('carrier')->get()
            ->pluck('carrier.name')
            ->filter()
            ->unique()
            ->implode('، ');

        return $carriers ?: ($this->company?->name ?? 'متجر');
    }

    public function supportsDelivery(string $type): bool
    {
        return true;
    }

    public function isCityInZone(string $city, ?string $countryCode = null, ?string $stateName = null, ?string $stateCode = null): bool
    {
        if ($this->isEverywhere()) {
            return false;
        }

        if (!empty($countryCode)) {
            if (empty($this->countries) || !is_array($this->countries)) {
                return false;
            }
            if (!in_array('*', $this->countries, true) && !in_array(strtoupper(trim($countryCode)), $this->countries, true)) {
                return false;
            }
        }

        $rawCities = $this->cities ?? $this->regions ?? [];
        if (empty($rawCities) || in_array('*', (array)$rawCities, true)) {
            return true;
        }

        // تحويل أي مصفوفات متداخلة إلى مصفوفة نصوص مسطحة
        $flatZoneCities = collect($rawCities)
            ->flatten()
            ->filter()
            ->map(fn($c) => mb_strtolower(trim((string)$c)))
            ->all();

        if (in_array('*', $flatZoneCities, true)) {
            return true;
        }

        // جمع كافة المصطلحات المراد فحصها
        $testCandidates = [];
        foreach ([$city, $stateName, $stateCode] as $param) {
            if (!empty($param)) {
                $val = mb_strtolower(trim((string)$param));
                $testCandidates[] = $val;
            }
        }

        // مطابقة ولايات الجزائر (تحويل الاسم لكود والكود لاسم)
        $algerianStates = config('ecommerce.countries.DZ.states', []);
        foreach (array_values($testCandidates) as $cand) {
            if (isset($algerianStates[$cand])) {
                $testCandidates[] = mb_strtolower(trim((string)$algerianStates[$cand]));
            }
            foreach ($algerianStates as $code => $name) {
                if (mb_strtolower(trim($name)) === $cand) {
                    $testCandidates[] = (string)$code;
                    $testCandidates[] = mb_strtolower(trim($name));
                }
            }
        }

        $testCandidates = array_unique(array_filter($testCandidates));

        // 1. فحص مدن المنطقة
        foreach ($testCandidates as $cand) {
            if (in_array($cand, $flatZoneCities, true)) {
                return true;
            }
        }

        // 2. فحص ولايات المنطقة
        if (!empty($this->states) && is_array($this->states)) {
            $flatZoneStates = collect($this->states)
                ->flatten()
                ->filter()
                ->map(fn($s) => mb_strtolower(trim((string)$s)))
                ->all();

            foreach ($testCandidates as $cand) {
                if (in_array($cand, $flatZoneStates, true)) {
                    return true;
                }
            }
        }

        // 3. فحص اسم المنطقة (إذا كان اسم المنطقة "غرداية" والعميل في "غرداية")
        $zoneName = mb_strtolower(trim((string)$this->name));
        foreach ($testCandidates as $cand) {
            if ($cand !== '' && ($zoneName === $cand || str_contains($zoneName, $cand) || str_contains($cand, $zoneName))) {
                return true;
            }
        }

        return false;
    }

    public function calculateCost(string $city = '', string $countryCode = '', string $method = 'standard', string $deliveryType = 'home', float $subtotal = 0, float $weight = 0, ?string $stateName = null): float
    {
        $matchedMethod = $this->methods()->active()->where('code', $method)->first()
            ?? $this->methods()->active()->first();

        if ($matchedMethod) {
            return $matchedMethod->calculateCost($subtotal, $weight);
        }

        if (isset($this->attributes['cost'])) {
            return (float) $this->attributes['cost'];
        }

        return (float) ($this->home_cost ?? 0);
    }

    public function estimatedDays(string $method = 'standard'): ?string
    {
        $m = $this->methods()->where('code', $method)->first();
        if ($m && $m->min_delivery_days && $m->max_delivery_days) {
            return "{$m->min_delivery_days}-{$m->max_delivery_days}";
        }
        return null;
    }
}

