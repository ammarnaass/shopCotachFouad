<?php

namespace App\Modules\Cart\Models;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'product_id', 'variant_id', 'quantity',
        'options', 'custom_text', 'custom_file', 'price',
    ];

    protected $casts = [
        'options' => 'array',
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        $productClass = class_exists(Product::class) ? Product::class : \App\Models\Catalog\Product::class;
        return $this->belongsTo($productClass);
    }

    public function variant(): BelongsTo
    {
        $variantClass = class_exists(ProductVariant::class) ? ProductVariant::class : \App\Models\Catalog\ProductVariant::class;
        return $this->belongsTo($variantClass, 'variant_id');
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->price * $this->quantity);
    }
}
