<?php

namespace App\Services;

use App\Models\Settings\Language;
use App\Models\Settings\Translation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class TranslationService
{
    protected string $currentLocale;
    protected string $defaultLocale;
    protected array $supportedLocales;

    public function __construct()
    {
        $this->defaultLocale = config('ecommerce.languages.default', 'ar');
        $this->supportedLocales = config('ecommerce.languages.supported', ['ar', 'en', 'fr']);
        $this->currentLocale = $this->defaultLocale;
    }

    public function get(string $key, ?string $locale = null, array $replace = []): string
    {
        $locale = $locale ?: $this->currentLocale;

        $value = $this->getFromCache($key, $locale);

        if ($value === null && $locale !== $this->defaultLocale) {
            $value = $this->getFromCache($key, $this->defaultLocale);
        }

        if ($value === null) {
            $value = $this->getFromJsonFiles($key, $locale);
        }

        if ($value === null && $locale !== $this->defaultLocale) {
            $value = $this->getFromJsonFiles($key, $this->defaultLocale);
        }

        if ($value === null) {
            $value = $this->getFromJsonFiles($key, 'ar');
        }

        if ($value === null) {
            $value = $key;
        }

        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, (string) $v, $value);
        }

        return $value;
    }

    public function setLocale(string $locale): void
    {
        if (!in_array($locale, $this->supportedLocales)) {
            $locale = $this->defaultLocale;
        }

        $language = $this->getLanguageModel($locale);
        if (!$language || !$language->is_active) {
            $locale = $this->defaultLocale;
            $language = $this->getLanguageModel($locale);
        }

        $this->currentLocale = $locale;
        App::setLocale($locale);
        session(['locale' => $locale]);

        config(['app.direction' => $language?->direction ?? 'ltr']);
        config(['app.language_id' => $language?->id ?? 1]);

        $cookieName = config('ecommerce.languages.cookie_name', 'locale');
        cookie()->queue($cookieName, $locale, config('ecommerce.languages.cookie_minutes', 43200));
    }

    public function detectLocale(): string
    {
        // 1. Query parameter ?lang=
        $queryLocale = request()->get('lang');
        if ($queryLocale && in_array($queryLocale, $this->supportedLocales)) {
            return $queryLocale;
        }

        // 2. Session
        if (session()->has('locale') && in_array(session('locale'), $this->supportedLocales)) {
            return session('locale');
        }

        // 3. Cookie
        $cookieName = config('ecommerce.languages.cookie_name', 'locale');
        $cookieLocale = request()->cookie($cookieName);
        if ($cookieLocale && in_array($cookieLocale, $this->supportedLocales)) {
            return $cookieLocale;
        }

        // 4. Browser Accept-Language header
        $headerLocale = $this->detectFromHeader();
        if ($headerLocale && in_array($headerLocale, $this->supportedLocales)) {
            return $headerLocale;
        }

        // 5. Default
        return $this->defaultLocale;
    }

    public function getLanguageModel(?string $locale = null): ?Language
    {
        $locale = $locale ?: $this->currentLocale;

        return Language::where('code', $locale)->first();
    }

    public function isRtl(?string $locale = null): bool
    {
        $locale = $locale ?: $this->currentLocale;

        $language = $this->getLanguageModel($locale);
        if ($language) {
            return $language->direction === 'rtl';
        }

        $rtlLocales = ['ar', 'he', 'fa', 'ur'];
        return in_array($locale, $rtlLocales);
    }

    public function getLanguages(): \Illuminate\Database\Eloquent\Collection
    {
        return Language::active()->ordered()->get();
    }

    public function getGroup(string $group, ?string $locale = null): array
    {
        $locale = $locale ?: $this->currentLocale;

        return Cache::remember("translations_{$locale}_{$group}", 3600, function () use ($locale, $group) {
            $dbTranslations = Translation::whereHas('language', fn($q) => $q->where('code', $locale))
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();

            $jsonTranslations = $this->loadJsonGroup($group, $locale);

            return array_merge($jsonTranslations, $dbTranslations);
        });
    }

    public function cacheFlush(?string $locale = null): void
    {
        if ($locale) {
            Cache::forget("trans_all_{$locale}");
            $groups = Translation::whereHas('language', fn($q) => $q->where('code', $locale))->pluck('group')->unique();
            foreach ($groups as $group) {
                Cache::forget("translations_{$locale}_{$group}");
            }
        } else {
            foreach ($this->supportedLocales as $loc) {
                Cache::forget("trans_all_{$loc}");
                $groups = Translation::whereHas('language', fn($q) => $q->where('code', $loc))->pluck('group')->unique();
                foreach ($groups as $group) {
                    Cache::forget("translations_{$loc}_{$group}");
                }
            }
        }
    }

    protected function getFromCache(string $key, string $locale): ?string
    {
        static $allTranslations = [];

        if (!isset($allTranslations[$locale])) {
            $cached = Cache::remember("trans_all_{$locale}", 3600, function () use ($locale) {
                $translations = Translation::whereHas('language', fn($q) => $q->where('code', $locale))->get();
                $map = [];
                foreach ($translations as $t) {
                    if ($t->group) {
                        $map[$t->group . '.' . $t->key] = $t->value;
                    }
                    $map[$t->key] = $t->value;
                }
                return $map;
            });
            $allTranslations[$locale] = $cached;
        }

        return $allTranslations[$locale][$key] ?? null;
    }

    protected function getFromJsonFiles(string $key, string $locale): ?string
    {
        static $loaded = [];

        if (!isset($loaded[$locale])) {
            $path = lang_path("{$locale}.json");
            $loaded[$locale] = File::exists($path)
                ? json_decode(File::get($path), true) ?? []
                : [];
        }

        return $loaded[$locale][$key] ?? null;
    }

    protected function loadJsonGroup(string $group, string $locale): array
    {
        $all = $this->getFromJsonFiles('*', $locale);
        $json = $this->getAllFromJson($locale);
        $prefix = $group . '.';

        $result = [];
        foreach ($json as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $shortKey = substr($key, strlen($prefix));
                $result[$shortKey] = $value;
            }
        }

        return $result;
    }

    protected function getAllFromJson(string $locale): array
    {
        static $loaded = [];

        if (!isset($loaded[$locale])) {
            $path = lang_path("{$locale}.json");
            $loaded[$locale] = File::exists($path)
                ? json_decode(File::get($path), true) ?? []
                : [];
        }

        return $loaded[$locale];
    }

    protected function detectFromHeader(): ?string
    {
        $acceptLanguage = request()->header('Accept-Language');
        if (!$acceptLanguage) {
            return null;
        }

        $preferred = substr($acceptLanguage, 0, 2);
        if (in_array($preferred, $this->supportedLocales)) {
            return $preferred;
        }

        return null;
    }
}
