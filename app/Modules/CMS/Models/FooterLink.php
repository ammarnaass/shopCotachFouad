<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    protected $fillable = ['footer_section_id', 'title', 'url', 'target', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(FooterSection::class, 'footer_section_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
