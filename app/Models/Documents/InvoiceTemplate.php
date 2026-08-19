<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'paper_size',
        'is_default', 'status', 'settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status'     => 'boolean',
        'settings'   => 'array',
    ];

    public const PAPER_SIZES = [
        'a4'         => 'A4 (210×297mm)',
        'a5'         => 'A5 (148×210mm)',
        'thermal_80' => 'حراري 80mm',
        'thermal_58' => 'حراري 58mm',
    ];

    public const DEFAULT_SETTINGS = [
        'primary_color'      => '#004ac6',
        'font'               => 'IBM Plex Sans Arabic',
        'show_logo'          => true,
        'show_sku'           => true,
        'show_product_image' => false,
        'show_discount'      => true,
        'show_shipping'      => true,
        'show_payment_method'=> true,
        'show_notes'         => true,
        'show_customer_info' => true,
        'header_text'        => '',
        'footer_text'        => '',
        'thank_you_message'  => 'شكراً لتسوقكم من متجرنا',
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

    public function isThermal(): bool
    {
        return in_array($this->paper_size, ['thermal_80', 'thermal_58']);
    }

    public function getDompdfPaper(): string
    {
        return match ($this->paper_size) {
            'a4'         => 'a4',
            'a5'         => 'a5',
            'thermal_80' => 'a4',  // closest approximation for dompdf
            'thermal_58' => 'a4',
            default      => 'a4',
        };
    }
}
