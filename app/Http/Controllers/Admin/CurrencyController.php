<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        $supported = config('ecommerce.countries');
        $currencies = [];
        $storeCurrency = config('ecommerce.store.currency');
        $storeSymbol = config('ecommerce.store.currency_symbol');
        $defaultCountry = config('ecommerce.store.default_country');

        // Build list of unique currencies from countries config
        $seen = [];
        foreach ($supported as $code => $info) {
            $cur = $info['currency'] ?? null;
            $sym = $info['currency_symbol'] ?? null;
            if ($cur && ! isset($seen[$cur])) {
                $currencies[] = [
                    'code' => $cur,
                    'symbol' => $sym,
                    'country' => $code,
                    'country_name' => $info['name'],
                    'dial_code' => $info['dial_code'],
                    'rate_to_usd' => $info['rate_to_usd'] ?? 1,
                ];
                $seen[$cur] = true;
            }
        }

        return view('admin.currencies.index', compact('currencies', 'storeCurrency', 'storeSymbol', 'defaultCountry'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency' => 'required|string|size:3',
            'currency_symbol' => 'required|string|max:10',
            'default_country' => 'required|string|size:2',
        ], [
            'currency.required' => 'كود العملة مطلوب',
            'currency_symbol.required' => 'رمز العملة مطلوب',
            'default_country.required' => 'الدولة الافتراضية مطلوبة',
        ]);

        // Update .env file
        $this->updateEnv([
            'STORE_CURRENCY' => $data['currency'],
            'STORE_CURRENCY_SYMBOL' => $data['currency_symbol'],
            'STORE_DEFAULT_COUNTRY' => $data['default_country'],
        ]);

        // Update config in-memory
        Config::set('ecommerce.store.currency', $data['currency']);
        Config::set('ecommerce.store.currency_symbol', $data['currency_symbol']);
        Config::set('ecommerce.store.default_country', $data['default_country']);

        // Sync the admin's session to the new default country so changes reflect immediately
        session(['selected_country' => $data['default_country']]);

        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return redirect()->route('admin.currencies.index')
            ->with('success', 'تم تحديث إعدادات العملة بنجاح ('.$data['currency'].' '.$data['currency_symbol'].'). تم تحديث ملف .env ومسح الكاش.');
    }

    public function updateRates(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rates' => 'required|array',
            'rates.*' => 'nullable|numeric|min:0|max:999999',
        ]);

        // Store all rates as a single JSON setting
        Setting::set('exchange_rates', json_encode($data['rates'], JSON_UNESCAPED_UNICODE), 'currencies');

        // Also update config in-memory so the current request sees the changes
        $rates = [];
        foreach ($data['rates'] as $country => $rate) {
            if (is_numeric($rate) && (float) $rate > 0) {
                $rates[$country] = (float) $rate;
            }
        }
        Config::set('ecommerce.exchange_rates', $rates);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'تم تحديث أسعار الصرف بنجاح لـ '.count($rates).' دولة.');
    }

    /**
     * Update or add values in the .env file (preserves comments and other keys).
     */
    private function updateEnv(array $values): void
    {
        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            return;
        }

        $env = File::get($envPath);

        foreach ($values as $key => $value) {
            // Escape any double quotes in the value
            $escapedValue = '"'.str_replace('"', '\"', $value).'"';

            if (preg_match("/^{$key}=.*$/m", $env)) {
                $env = preg_replace("/^{$key}=.*$/m", "{$key}={$escapedValue}", $env);
            } else {
                $env .= "\n{$key}={$escapedValue}\n";
            }
        }

        File::put($envPath, $env);
    }
}
