<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductShippingRule extends Model
{
    protected $fillable = [
        'product_id',
        'allowed_methods', 'excluded_methods',
        'max_weight', 'max_dimensions',
        'allowed_zones', 'excluded_zones',
        'requires_signature', 'fragile', 'hazardous',
        'priority',
    ];

    protected $casts = [
        'allowed_methods' => 'array',
        'excluded_methods' => 'array',
        'max_dimensions' => 'array',
        'allowed_zones' => 'array',
        'excluded_zones' => 'array',
        'requires_signature' => 'boolean',
        'fragile' => 'boolean',
        'hazardous' => 'boolean',
        'priority' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
