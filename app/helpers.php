<?php

use App\Models\Settings\Language;
use App\Models\Settings\Setting;
use App\Services\TranslationService;
use App\Support\SiteSettings;

if (! function_exists('site')) {
    /**
     * Get a site setting value.
     *
     * @param  mixed  $default
     * @return mixed
     */
    function site(string $key, $default = null)
    {
        return SiteSettings::get($key, $default);
    }
}

if (! function_exists('site_settings')) {
    /**
     * Get all site settings as an array.
     */
    function site_settings(): array
    {
        return SiteSettings::all();
    }
}

if (! function_exists('site_flush')) {
    /**
     * Flush the settings cache. Called automatically when a Setting is written.
     */
    function site_flush(): void
    {
        SiteSettings::flush();
    }
}

if (! function_exists('countryCurrency')) {
    /**
     * Get currency symbol for a country code (e.g. SD -> ج.س, EG -> ج.م).
     * Reads from config('ecommerce.countries.{CODE}.currency_symbol').
     */
    function countryCurrency(string $countryCode): string
    {
        $countries = config('ecommerce.countries', []);

        return $countries[$countryCode]['currency_symbol'] ?? 'د.ج';
    }
}

if (! function_exists('countryDialCode')) {
    /**
     * Get dial code for a country (e.g. SD -> 249, EG -> 20).
     */
    function countryDialCode(string $countryCode): string
    {
        $countries = config('ecommerce.countries', []);

        return (string) ($countries[$countryCode]['dial_code'] ?? '213');
    }
}

if (! function_exists('currentCountry')) {
    /**
     * Get the user's currently selected country (from session) or the store default.
     */
    function currentCountry(): string
    {
        return session('selected_country', config('ecommerce.store.default_country', 'DZ'));
    }
}

if (! function_exists('currentCurrency')) {
    /**
     * Get the current currency code (e.g. DZD) for the user's selected country.
     */
    function currentCurrency(): string
    {
        $country = currentCountry();
        $countries = config('ecommerce.countries', []);

        return $countries[$country]['currency'] ?? config('ecommerce.store.currency', 'DZD');
    }
}

if (! function_exists('currentCurrencySymbol')) {
    /**
     * Get the current currency symbol (e.g. ج.س, ج.م) for the user's selected country.
     */
    function currentCurrencySymbol(): string
    {
        $country = currentCountry();
        $countries = config('ecommerce.countries', []);

        return $countries[$country]['currency_symbol'] ?? config('ecommerce.store.currency_symbol', 'د.ج');
    }
}

if (! function_exists('rateForCountry')) {
    /**
     * Get the rate_to_usd for a country, checking admin-saved settings first,
     * then falling back to config/ecommerce.php.
     */
    function rateForCountry(string $countryCode): ?float
    {
        // Check settings table for admin-edited rates
        $saved = Setting::get('exchange_rates');
        if ($saved) {
            $rates = json_decode($saved, true);
            if (isset($rates[$countryCode]) && is_numeric($rates[$countryCode]) && (float) $rates[$countryCode] > 0) {
                return (float) $rates[$countryCode];
            }
        }

        // Fall back to config
        $countries = config('ecommerce.countries', []);
        $rate = $countries[$countryCode]['rate_to_usd'] ?? null;

        return $rate !== null ? (float) $rate : null;
    }
}

if (! function_exists('convertPrice')) {
    /**
     * Convert a price from the store's base currency to the user's selected currency
     * using rate_to_usd exchange rates from config or admin settings.
     *
     * Returns the converted amount rounded to 2 decimal places.
     * If rates are unavailable or currencies match, returns the original amount.
     */
    function convertPrice(float $amount): float
    {
        $defaultCountry = config('ecommerce.store.default_country', 'DZ');
        $targetCountry = currentCountry();

        // Same country, no conversion needed
        if ($defaultCountry === $targetCountry) {
            return $amount;
        }

        $baseRate = rateForCountry($defaultCountry);
        $targetRate = rateForCountry($targetCountry);

        if (! $baseRate || ! $targetRate || $baseRate <= 0 || $targetRate <= 0) {
            return $amount;
        }

        return round($amount * ($baseRate / $targetRate), 2);
    }
}

if (! function_exists('format_number')) {
    /**
     * Format a number using the current language's formatting settings.
     * Arabic: 1,234.56 | English: 1,234.56 | French: 1 234,56
     */
    function format_number(float $amount, int $decimals = 2): string
    {
        $locale = current_locale();
        $language = Language::where('code', $locale)->first();

        if ($language) {
            $decSep = $language->decimal_separator ?? '.';
            $thouSep = $language->thousands_separator ?? ',';
            $formatted = number_format($amount, $decimals, $decSep, $thouSep);
            $formatted = str_replace(['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $formatted);

            return $formatted;
        }

        return number_format($amount, $decimals, '.', ',');
    }
}

if (! function_exists('format_currency')) {
    /**
     * Format an amount as currency using the current language's settings.
     * Arabic: 150.00 ر.س | English: SAR 150.00 | French: 150,00 SAR
     */
    function format_currency(float $amount, ?string $symbol = null): string
    {
        $symbol = $symbol ?: currentCurrencySymbol();
        $locale = current_locale();
        $language = Language::where('code', $locale)->first();

        $formatted = format_number($amount);
        $position = $language->currency_position ?? 'after';

        if ($position === 'before') {
            return $symbol.' '.$formatted;
        }

        return $formatted.' '.$symbol;
    }
}

if (! function_exists('__t')) {
    function __t(string $key, array $replace = [], ?string $locale = null): string
    {
        return app(TranslationService::class)->get($key, $locale, $replace);
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        return app()->getLocale() ?: config('ecommerce.languages.default', 'ar');
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl(?string $locale = null): bool
    {
        if ($locale) {
            $lang = Language::where('code', $locale)->first();

            return $lang ? $lang->direction === 'rtl' : in_array($locale, ['ar', 'he', 'fa', 'ur']);
        }

        return config('app.direction') === 'rtl';
    }
}

if (! function_exists('current_direction')) {
    function current_direction(): string
    {
        return is_rtl() ? 'rtl' : 'ltr';
    }
}

if (! function_exists('lang_url')) {
    function lang_url(string $path = '/', ?string $locale = null): string
    {
        $locale = $locale ?: current_locale();
        $defaultLocale = config('ecommerce.languages.default', 'ar');
        $hideDefault = config('ecommerce.languages.hide_default_prefix', true);

        if ($hideDefault && $locale === $defaultLocale) {
            return url($path);
        }

        return url($locale.'/'.ltrim($path, '/'));
    }
}

if (! function_exists('lang_route')) {
    function lang_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $locale = $parameters['locale'] ?? current_locale();
        $defaultLocale = config('ecommerce.languages.default', 'ar');
        $hideDefault = config('ecommerce.languages.hide_default_prefix', true);

        if (! isset($parameters['locale'])) {
            if ($hideDefault && $locale === $defaultLocale) {
                unset($parameters['locale']);
            } else {
                $parameters['locale'] = $locale;
            }
        }

        return route($name, $parameters, $absolute);
    }
}

if (! function_exists('conversionRate')) {
    /**
     * Get the multiplier to convert from the store's base currency to the user's selected currency.
     * Useful for Alpine.js / client-side calculations.
     */
    function conversionRate(): float
    {
        $defaultCountry = config('ecommerce.store.default_country', 'DZ');
        $targetCountry = currentCountry();

        if ($defaultCountry === $targetCountry) {
            return 1.0;
        }

        $baseRate = rateForCountry($defaultCountry);
        $targetRate = rateForCountry($targetCountry);

        if (! $baseRate || ! $targetRate || $baseRate <= 0 || $targetRate <= 0) {
            return 1.0;
        }

        return $baseRate / $targetRate;
    }
}

if (! function_exists('pdf_image_src')) {
    /**
     * Resolve image sources for PDF rendering without making HTTP requests back to the same app.
     */
    function pdf_image_src(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (preg_match('#^(data:|file://)#i', $value)) {
            return $value;
        }

        $toFileUri = static function (string $path): string {
            $normalized = str_replace('\\', '/', $path);

            if (preg_match('/^[A-Za-z]:\//', $normalized)) {
                return 'file:///' . $normalized;
            }

            return 'file://' . $normalized;
        };

        if (is_file($value)) {
            return $toFileUri($value);
        }

        if (! preg_match('#^https?://#i', $value)) {
            $relativePath = ltrim($value, '/');
            $candidates = [
                public_path($relativePath),
                public_path('storage/' . $relativePath),
            ];

            foreach ($candidates as $candidate) {
                if (is_file($candidate)) {
                    return $toFileUri($candidate);
                }
            }

            return $value;
        }

        $assetHost = parse_url(config('app.url'), PHP_URL_HOST);
        $sourceHost = parse_url($value, PHP_URL_HOST);
        $sourcePath = parse_url($value, PHP_URL_PATH);

        if ($assetHost && $sourceHost && strcasecmp($assetHost, $sourceHost) === 0 && $sourcePath) {
            $candidate = public_path(ltrim(urldecode($sourcePath), '/'));

            if (is_file($candidate)) {
                return $toFileUri($candidate);
            }
        }

        return $value;
    }
}
