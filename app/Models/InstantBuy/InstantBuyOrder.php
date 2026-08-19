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
        $countries = config('ecommerce.countries', []);

        return $countries[$this->country_code]['name'] ?? $this->country_code;
    }

    public function getStateNameAttribute(): ?string
    {
        $countries = config('ecommerce.countries', []);

        return $countries[$this->country_code]['states'][$this->state_code] ?? $this->state_code;
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
