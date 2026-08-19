<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    public const ANIMATION_EFFECTS = [
        'fade' => 'Fade',
        'slide-left' => 'Slide Left',
        'slide-right' => 'Slide Right',
        'zoom' => 'Zoom',
        'flip' => 'Flip',
    ];

    public const ENTRANCE_EFFECTS = [
        'none' => 'None',
        'fade-up' => 'Fade Up',
        'fade-down' => 'Fade Down',
        'fade-left' => 'Fade Left',
        'fade-right' => 'Fade Right',
        'zoom' => 'Zoom',
    ];

    public const ANIMATION_EFFECT_DESCRIPTIONS = [
        'fade' => 'Cross-fade between slides',
        'slide-left' => 'Slide to the left (next moves left)',
        'slide-right' => 'Slide to the right (next moves right)',
        'zoom' => 'Zoom in/out between slides',
        'flip' => 'Flip like a card',
    ];

    public const ENTRANCE_EFFECT_DESCRIPTIONS = [
        'none' => 'Content appears instantly',
        'fade-up' => 'Fade in from bottom',
        'fade-down' => 'Fade in from top',
        'fade-left' => 'Fade in from left',
        'fade-right' => 'Fade in from right',
        'zoom' => 'Zoom in from center',
    ];

    protected $fillable = [
        'title', 'subtitle', 'description', 'badge', 'image', 'mobile_image',
        'link', 'btn_text', 'button_target', 'sort_order', 'is_active',
        'starts_at', 'ends_at', 'animation_effect', 'entrance_effect',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }
        if (preg_match('#^(https?://|data:)#i', $this->image)) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        if (! $this->mobile_image) {
            return null;
        }
        if (preg_match('#^(https?://|data:)#i', $this->mobile_image)) {
            return $this->mobile_image;
        }

        return asset('storage/'.$this->mobile_image);
    }

    public function getEffectiveMobileImageUrlAttribute(): ?string
    {
        return $this->mobile_image_url ?? $this->image_url;
    }

    public function getAnimationEffectAttribute(?string $value): string
    {
        return $value ?: site('slider_default_animation', 'fade');
    }

    public function getEntranceEffectAttribute(?string $value): string
    {
        return $value ?: site('slider_default_entrance', 'fade-up');
    }
}
