<?php

namespace App\Support;

use App\Models\Settings\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Loads all settings from DB into a static cache so views can call
 *   site('key', 'default')
 * without hammering the DB.
 *
 * Auto-invalidates whenever Setting::set() writes a row.
 */
class SiteSettings
{
    private const CACHE_KEY = 'site_settings';
    private const CACHE_TTL = 600; // 10 minutes

    /**
     * Build the settings array from DB (with hard-coded defaults as fallback).
     */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            // If the table doesn't exist yet (e.g. during install), return defaults
            if (!Schema::hasTable('settings')) {
                return self::defaults();
            }

            $db = [];
            try {
                foreach (Setting::all() as $row) {
                    $db[$row->key] = $row->value;
                }
            } catch (\Throwable $e) {
                $db = [];
            }

            $merged = array_merge(self::defaults(), $db);
            return $merged;
        });
    }

    public static function get(string $key, $default = null)
    {
        $all = self::all();
        $val = $all[$key] ?? $default;

        // Auto-resolve image-like keys to public URL
        $imageKeys = ['store_logo', 'store_favicon', 'seo_og_image', 'hero_image', 'banner_1_image', 'banner_2_image'];
        if (in_array($key, $imageKeys, true) && $val) {
            if (!preg_match('#^(https?://|data:)#i', $val)) {
                $val = asset('storage/' . $val);
            }
        }

        return $val;
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function defaults(): array
    {
        return [
            // store
            'store_name' => 'AN SHOP',
            'store_email' => 'contact@anshop.dz',
            'store_phone' => '+213 550 00 00 00',
            'store_phone_secondary' => '',
            'store_address' => 'الجزائر العاصمة',
            'store_wilaya' => 'الجزائر',
            'store_commune' => 'باب الزوار',
            'store_postal_code' => '16000',
            'store_website' => 'https://anshop.dz',
            'store_description' => 'متجر إلكتروني متكامل يوفر لك تجربة تسوق فريدة',

            // invoice info
            'invoice_business_name' => 'AN SHOP',
            'invoice_legal_name' => 'AN SHOP SARL',
            'invoice_rc' => '',
            'invoice_nif' => '',
            'invoice_nis' => '',
            'invoice_phone' => '+213 550 00 00 00',
            'invoice_address' => 'الجزائر العاصمة',
            'invoice_email' => 'contact@anshop.dz',
            'invoice_notes' => 'شكراً لتسوقكم من AN SHOP',

            // printing settings
            'printing_default_invoice_template' => '',
            'printing_default_label_template' => '',
            'printing_default_invoice_size' => 'a4',
            'printing_default_label_size' => '100x150',
            'printing_show_logo' => '1',
            'printing_show_barcode' => '1',
            'printing_show_qr' => '0',
            'printing_auto_generate_invoice' => '1',

            // social (old keys — kept as fallback for frontend)
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'whatsapp_number' => '',
            'youtube_url' => '',
            // social (new keys — canonical)
            'social_whatsapp' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_tiktok' => '',
            'social_youtube' => '',
            'social_telegram' => '',
            'social_snapchat' => '',

            // contact
            'contact_email' => 'info@amarstore.com',
            'contact_phone' => '+249 90 000 0000',
            'contact_whatsapp' => '',
            'contact_address' => 'الخرطوم، السودان',
            'contact_hours' => '24/7 - متاحون دائماً',
            'contact_working_hours' => '',
            'contact_support_hours' => '',

            // seo
            'seo_meta_title' => '',
            'seo_meta_description' => '',
            'seo_meta_keywords' => '',
            'seo_og_image' => '',
            'seo_ga_id' => '',
            'seo_fb_pixel' => '',

            // customize
            'site_theme' => 'light',
            'primary_color' => '#2563eb',
            'accent_color' => '#f59e0b',
            'hero_title' => 'تسوق بذكاء، عش تجربة فريدة',
            'hero_subtitle' => 'اكتشف أحدث المنتجات بأسعار مميزة مع شحن سريع لجميع الدول العربية',
            'hero_badge' => 'جديد',
            'hero_image' => '',
            'banner_1_title' => '',
            'banner_1_subtitle' => '',
            'banner_1_image' => '',
            'banner_1_link' => '',
            'banner_2_title' => '',
            'banner_2_subtitle' => '',
            'banner_2_image' => '',
            'banner_2_link' => '',
            'show_newsletter' => '1',
            'show_featured' => '1',
            'show_latest' => '1',
            'show_categories' => '1',
            'show_hero' => '1',
            'show_marquee' => '1',
            'show_banner_1' => '1',
            'home_section_order' => '["hero","marquee","categories","featured","latest","banner_1","banner_2"]',
            'slider_animation_duration' => 500,
            'slider_entrance_stagger' => 80,
            'slider_default_animation' => 'fade',
            'slider_default_entrance' => 'fade-up',
            'footer_about' => 'متجر إلكتروني متكامل يوفر لك تجربة تسوق فريدة مع شحن سريع ودفع آمن عند الاستلام.',
            'footer_copyright' => 'جميع الحقوق محفوظة',
        ];
    }
}
