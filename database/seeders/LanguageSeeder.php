<?php

namespace Database\Seeders;

use App\Models\Settings\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::create([
            'code' => 'ar',
            'name' => 'العربية',
            'name_en' => 'Arabic',
            'native_name' => 'العربية',
            'flag' => '🇸🇦',
            'locale' => 'ar_SA',
            'is_active' => true,
            'is_default' => true,
            'direction' => 'rtl',
            'sort_order' => 1,
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'currency_position' => 'after',
            'flag_icon' => 'sa',
        ]);

        Language::create([
            'code' => 'en',
            'name' => 'English',
            'name_en' => 'English',
            'native_name' => 'English',
            'flag' => '🇬🇧',
            'locale' => 'en_US',
            'is_active' => true,
            'is_default' => false,
            'direction' => 'ltr',
            'sort_order' => 2,
            'date_format' => 'm/d/Y',
            'time_format' => 'h:i A',
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'currency_position' => 'before',
            'flag_icon' => 'us',
        ]);

        Language::create([
            'code' => 'fr',
            'name' => 'Français',
            'name_en' => 'French',
            'native_name' => 'Français',
            'flag' => '🇫🇷',
            'locale' => 'fr_FR',
            'is_active' => true,
            'is_default' => false,
            'direction' => 'ltr',
            'sort_order' => 3,
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'decimal_separator' => ',',
            'thousands_separator' => ' ',
            'currency_position' => 'after',
            'flag_icon' => 'fr',
        ]);
    }
}
