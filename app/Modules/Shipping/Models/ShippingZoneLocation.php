<?php

namespace App\Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingZoneLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_zone_id',
        'type',
        'value',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShippingZoneLocation $location) {
            $location->value = trim((string) $location->value);

            if ($location->type === 'country') {
                $location->value = strtoupper($location->value);
            } elseif ($location->type === 'state') {
                $location->value = strtoupper($location->value);
            }
        });
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    /**
     * هل يطابق هذا الموقع عنوان الزبون؟
     */
    public function matches(string $countryCode, ?string $stateCode = null, ?string $postcode = null): bool
    {
        $countryCode = strtoupper(trim($countryCode));
        $val = strtoupper(trim($this->value));

        if ($this->type === 'country') {
            return $val === '*' || $val === $countryCode;
        }

        if ($this->type === 'state') {
            if (empty($stateCode)) {
                return false;
            }
            $stateCode = strtoupper(trim($stateCode));

            // يقبل صيغة "DZ:16" أو "16" أو اسم الولاية
            return $val === "{$countryCode}:{$stateCode}"
                || $val === $stateCode
                || str_ends_with($val, ":{$stateCode}");
        }

        if ($this->type === 'postcode') {
            if (empty($postcode)) {
                return false;
            }
            $postcode = trim($postcode);

            // 1. Wildcard match (e.g. "16*")
            if (str_contains($val, '*')) {
                $prefix = rtrim($val, '*');
                return str_starts_with($postcode, $prefix);
            }

            // 2. Range match (e.g. "16000-16999" or "16000...16999")
            if (str_contains($val, '-') || str_contains($val, '...')) {
                $delimiter = str_contains($val, '-') ? '-' : '...';
                [$from, $to] = array_map('trim', explode($delimiter, $val, 2));

                if (is_numeric($from) && is_numeric($to) && is_numeric($postcode)) {
                    $numPostcode = (float) $postcode;
                    return $numPostcode >= (float) $from && $numPostcode <= (float) $to;
                }

                return strcmp($postcode, $from) >= 0 && strcmp($postcode, $to) <= 0;
            }

            // 3. Exact match
            return $val === strtoupper($postcode);
        }

        return false;
    }
}
