<?php

namespace App\Modules\Reviews\Models;

use App\Modules\Catalog\Models\Product;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'rating', 'comment', 'images', 'status',
    ];

    protected $casts = [
        'images' => 'array',
        'rating' => 'integer',
    ];

    public function product(): BelongsTo
    {
        $productClass = class_exists(Product::class) ? Product::class : \App\Models\Catalog\Product::class;
        return $this->belongsTo($productClass);
    }

    public function user(): BelongsTo
    {
        $userClass = class_exists(User::class) ? User::class : \App\Models\User\User::class;
        return $this->belongsTo($userClass);
    }
}
