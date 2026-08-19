<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCustomField extends Model
{
    protected $fillable = [
        'product_id', 'label', 'type', 'required', 'price_effect',
    ];

    protected $casts = [
        'required' => 'boolean',
        'price_effect' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
