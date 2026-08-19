<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FooterSection extends Model
{
    protected $fillable = ['title', 'type', 'custom_html', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const TYPES = [
        'links'       => 'روابط',
        'categories'  => 'أقسام',
        'custom_html' => 'محتوى مخصص',
        'contact'     => 'التواصل',
        'social'      => 'التواصل الاجتماعي',
        'store_info'  => 'معلومات المتجر',
    ];

    public function links(): HasMany
    {
        return $this->hasMany(FooterLink::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
