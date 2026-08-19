<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Wishlist\Models\Wishlist;
use App\Modules\Reviews\Models\Review;
use App\Modules\Shipping\Models\ShippingCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'name_en', 'name_fr',
        'slug', 'description', 'description_en', 'description_fr',
        'short_description', 'short_description_en', 'short_description_fr',
        'price', 'sale_price', 'sku', 'stock', 'type', 'weight', 'length', 'width', 'height',
        'low_stock_threshold', 'shipping_company_id',
        'status', 'featured', 'seo_title', 'seo_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'SKU-'.strtoupper(Str::random(8));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function shippingCompany(): BelongsTo
    {
        $companyClass = class_exists(ShippingCompany::class) ? ShippingCompany::class : \App\Models\Shipping\ShippingCompany::class;
        return $this->belongsTo($companyClass, 'shipping_company_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(ProductCustomField::class);
    }

    public function reviews(): HasMany
    {
        $reviewClass = class_exists(Review::class) ? Review::class : \App\Models\Content\Review::class;
        return $this->hasMany($reviewClass)->where('status', 'approved');
    }

    public function wishlists(): HasMany
    {
        $wishlistClass = class_exists(Wishlist::class) ? Wishlist::class : \App\Models\Cart\Wishlist::class;
        return $this->hasMany($wishlistClass);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function shippingRule(): HasOne
    {
        return $this->hasOne(ProductShippingRule::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('featured', true);
    }

    public function scopeInStock(Builder $q): Builder
    {
        return $q->where('stock', '>', 0);
    }

    public function getFinalPriceAttribute(): float
    {
        if ($this->sale_price !== null && (float) $this->sale_price > 0 && (float) $this->sale_price < (float) $this->price) {
            return (float) $this->sale_price;
        }

        return (float) $this->price;
    }

    public function getComparePriceAttribute(): ?float
    {
        if (isset($this->attributes['compare_price']) && $this->attributes['compare_price'] !== null) {
            return (float) $this->attributes['compare_price'];
        }

        if ($this->sale_price !== null && (float) $this->sale_price > 0 && (float) $this->sale_price < (float) $this->price) {
            return (float) $this->price;
        }

        return null;
    }

    public function getDiscountPercentAttribute(): int
    {
        return $this->getDiscountPercentageAttribute();
    }

    public function getDiscountPercentageAttribute(): int
    {
        $compare = $this->compare_price;
        $current = $this->final_price;

        if ($compare && $compare > $current && $compare > 0) {
            return (int) round((($compare - $current) / $compare) * 100);
        }

        if ($this->sale_price && (float) $this->price > 0 && (float) $this->sale_price < (float) $this->price) {
            return (int) round((((float) $this->price - (float) $this->sale_price) / (float) $this->price) * 100);
        }

        return 0;
    }

    public function getRatingAttribute(): float
    {
        if (isset($this->attributes['rating']) && $this->attributes['rating'] !== null) {
            return (float) $this->attributes['rating'];
        }

        return (float) ($this->reviews_avg_rating ?? $this->reviews()->avg('rating') ?? 0);
    }

    public function getReviewsCountAttribute(): int
    {
        if (isset($this->attributes['reviews_count']) && $this->attributes['reviews_count'] !== null) {
            return (int) $this->attributes['reviews_count'];
        }

        return (int) ($this->reviews_count ?? $this->reviews()->count());
    }

    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()->avg('rating') ?: 0;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'en' => !empty($this->attributes['name_en']) ? $this->attributes['name_en'] : ($this->attributes['name'] ?? ''),
            'fr' => !empty($this->attributes['name_fr']) ? $this->attributes['name_fr'] : ($this->attributes['name'] ?? ''),
            default => $this->attributes['name'] ?? '',
        };
    }

    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'en' => !empty($this->attributes['description_en']) ? $this->attributes['description_en'] : ($this->attributes['description'] ?? null),
            'fr' => !empty($this->attributes['description_fr']) ? $this->attributes['description_fr'] : ($this->attributes['description'] ?? null),
            default => $this->attributes['description'] ?? null,
        };
    }

    public function getShortDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'en' => !empty($this->attributes['short_description_en']) ? $this->attributes['short_description_en'] : ($this->attributes['short_description'] ?? null),
            'fr' => !empty($this->attributes['short_description_fr']) ? $this->attributes['short_description_fr'] : ($this->attributes['short_description'] ?? null),
            default => $this->attributes['short_description'] ?? null,
        };
    }

    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => !empty($this->attributes['name_en']) ? $this->attributes['name_en'] : ($this->attributes['name'] ?? ''),
            'fr' => !empty($this->attributes['name_fr']) ? $this->attributes['name_fr'] : ($this->attributes['name'] ?? ''),
            default => $this->attributes['name'] ?? '',
        };
    }
}
