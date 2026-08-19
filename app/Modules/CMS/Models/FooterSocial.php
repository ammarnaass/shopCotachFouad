<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSocial extends Model
{
    protected $fillable = ['platform', 'url', 'icon', 'sort_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const PLATFORMS = [
        'facebook'  => ['label' => 'Facebook',  'icon' => 'facebook',  'color' => '#1877F2'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'instagram', 'color' => '#E1306C'],
        'tiktok'    => ['label' => 'TikTok',    'icon' => 'tiktok',    'color' => '#000000'],
        'youtube'   => ['label' => 'YouTube',   'icon' => 'youtube',   'color' => '#FF0000'],
        'whatsapp'  => ['label' => 'WhatsApp',  'icon' => 'whatsapp',  'color' => '#25D366'],
        'telegram'  => ['label' => 'Telegram',  'icon' => 'telegram',  'color' => '#2CA5E0'],
        'snapchat'  => ['label' => 'Snapchat',  'icon' => 'snapchat',  'color' => '#FFFC00'],
        'twitter'   => ['label' => 'Twitter/X', 'icon' => 'twitter',   'color' => '#000000'],
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform]['label'] ?? $this->platform;
    }

    public function getPlatformColorAttribute(): string
    {
        return self::PLATFORMS[$this->platform]['color'] ?? '#666666';
    }
}
