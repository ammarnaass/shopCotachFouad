<?php

namespace App\Helpers;

use App\Models\Settings\Language;
use App\Models\Settings\Translation;
use Illuminate\Support\Facades\Cache;

class Trans
{
    private static $translations = [];

    public static function get($key, $group = 'instant_buy', $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $langId = self::getLanguageId($locale);

        $cacheKey = "translations_{$locale}_{$group}";

        if (! isset(self::$translations[$cacheKey])) {
            self::$translations[$cacheKey] = Cache::remember($cacheKey, 3600, function () use ($langId, $group) {
                return Translation::where('language_id', $langId)
                    ->where('group', $group)
                    ->pluck('value', 'key')
                    ->toArray();
            });
        }

        return self::$translations[$cacheKey][$key] ?? $key;
    }

    private static function getLanguageId($locale)
    {
        return Cache::remember("lang_id_{$locale}", 3600, function () use ($locale) {
            return Language::where('code', $locale)->value('id');
        });
    }

    public static function flushCache()
    {
        foreach (['ar', 'en', 'fr'] as $locale) {
            foreach (['instant_buy', 'products', 'general', 'shipping'] as $group) {
                Cache::forget("translations_{$locale}_{$group}");
                Cache::forget("lang_id_{$locale}");
            }
        }
        self::$translations = [];
    }
}
