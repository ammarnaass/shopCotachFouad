<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_en',
        'native_name',
        'flag',
        'locale',
        'is_active',
        'is_default',
        'direction',
        'sort_order',
        'date_format',
        'time_format',
        'decimal_separator',
        'thousands_separator',
        'currency_position',
        'flag_icon',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(LanguageSetting::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    public function setting(string $key, $default = null)
    {
        $setting = $this->settings()->where('setting_key', $key)->first();

        return $setting ? $setting->setting_value : $default;
    }
}
