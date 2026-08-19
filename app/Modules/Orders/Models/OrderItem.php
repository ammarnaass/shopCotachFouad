<?php

namespace App\Modules\Orders\Models;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'product_name',
        'product_sku', 'quantity', 'price', 'total',
        'options', 'options_summary', 'custom_text', 'custom_file',
    ];

    protected $casts = [
        'options' => 'array',
        'options_summary' => 'array',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
}
