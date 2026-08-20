<?php

namespace App\Models\InstantBuy;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Shipping\ShippingCompany;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstantBuyOrder extends Model
{
    use SoftDeletes;

    protected $table = 'instant_buy_orders';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'options' => 'json',
            'quantity' => 'integer',
            'product_price' => 'decimal:2',
            'options_price' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'notified_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function shippingCompany(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'shipping_company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCountryNameAttribute(): string
    {
        $cc = strtoupper($this->country_code ?: 'DZ');
        return config("ecommerce.countries.{$cc}.name") ?: ($cc === 'DZ' ? 'الجزائر' : $cc);
    }

    public function getStateNameAttribute(): ?string
    {
        $cc = strtoupper($this->country_code ?: 'DZ');
        $states = config("ecommerce.countries.{$cc}.states", []);

        if (!empty($this->state_code)) {
            if (isset($states[$this->state_code])) {
                return $states[$this->state_code];
            }
            $padded = str_pad($this->state_code, 2, '0', STR_PAD_LEFT);
            if (isset($states[$padded])) {
                return $states[$padded];
            }
            if (in_array($this->state_code, $states, true)) {
                return $this->state_code;
            }
        }

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

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }
}
