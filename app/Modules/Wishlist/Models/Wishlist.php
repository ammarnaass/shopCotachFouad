<?php

namespace App\Modules\Wishlist\Models;

use App\Modules\Catalog\Models\Product;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $fillable = ['user_id', 'product_id'];

    public function user(): BelongsTo
    {
        $userClass = class_exists(User::class) ? User::class : \App\Models\User\User::class;
        return $this->belongsTo($userClass);
    }

    public function product(): BelongsTo
    {
        $productClass = class_exists(Product::class) ? Product::class : \App\Models\Catalog\Product::class;
        return $this->belongsTo($productClass);
    }
}
