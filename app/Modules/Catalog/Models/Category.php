<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'name_en', 'name_fr',
        'slug', 'description', 'description_en', 'description_fr',
        'image', 'icon', 'banner',
        'parent_id', 'order', 'status',
        'seo_title', 'seo_description',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'en' => $this->attributes['name_en'] ?? $this->attributes['name'],
            'fr' => $this->attributes['name_fr'] ?? $this->attributes['name'],
            default => $this->attributes['name'],
        };
    }

    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'en' => $this->attributes['description_en'] ?? $this->attributes['description'],
            'fr' => $this->attributes['description_fr'] ?? $this->attributes['description'],
            default => $this->attributes['description'],
        };
    }

    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->attributes['name_en'] ?? $this->attributes['name'],
            'fr' => $this->attributes['name_fr'] ?? $this->attributes['name'],
            default => $this->attributes['name'],
        };
    }

    public static function booted(): void
    {
        static::creating(function ($cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
