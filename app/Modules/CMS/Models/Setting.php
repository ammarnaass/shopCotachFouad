<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, $default = null)
    {
        if (Schema::hasTable('settings')) {
            return Cache::remember('site_setting_'.$key, 600, function () use ($key, $default) {
                return static::where('key', $key)->value('value') ?? $default;
            });
        }

        return $default;
    }

    public static function set(string $key, $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget('site_setting_'.$key);
        Cache::forget('site_settings');
    }
}
