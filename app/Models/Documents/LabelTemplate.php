<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Model;

class LabelTemplate extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'paper_size',
        'custom_width', 'custom_height', 'is_default', 'status', 'settings',
    ];

    protected $casts = [
        'is_default'    => 'boolean',
        'status'        => 'boolean',
        'settings'      => 'array',
        'custom_width'  => 'integer',
        'custom_height' => 'integer',
    ];

    public const PAPER_SIZES = [
        '100x150' => '100 × 150 mm',
        '100x100' => '100 × 100 mm',
        '80x50'   => '80 × 50 mm',
        'a6'      => 'A6 (105×148mm)',
        'custom'  => 'مخصص',
    ];

    public const DEFAULT_SETTINGS = [
        'show_logo'           => true,
        'show_barcode'        => true,
        'show_qr'             => false,
        'show_product_count'  => true,
        'show_total'          => true,
        'show_payment_method' => true,
        'show_status'         => false,
    ];

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? self::DEFAULT_SETTINGS[$key] ?? $default;
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getPaperSizeLabelAttribute(): string
    {
        return self::PAPER_SIZES[$this->paper_size] ?? $this->paper_size;
    }

    public function getSizeMmAttribute(): array
    {
        return match ($this->paper_size) {
            '100x150' => ['width' => 100, 'height' => 150],
            '100x100' => ['width' => 100, 'height' => 100],
            '80x50'   => ['width' => 80,  'height' => 50],
            'a6'      => ['width' => 105, 'height' => 148],
            'custom'  => ['width' => $this->custom_width ?? 100, 'height' => $this->custom_height ?? 150],
            default   => ['width' => 100, 'height' => 150],
        };
    }
}
