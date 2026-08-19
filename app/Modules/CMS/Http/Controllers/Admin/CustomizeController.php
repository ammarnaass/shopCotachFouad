<?php

namespace App\Modules\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Category;
use App\Modules\CMS\Models\Page;
use App\Services\ImageUploadService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomizeController extends Controller
{
    public const HERO_IMAGE_MAX_KB = 2048;

    public const HERO_IMAGE_MIMES = 'jpeg,jpg,png,webp';

    public function __construct(
        private SettingsService $settings,
        private ImageUploadService $images,
    ) {}

    public function index(): View
    {
        $themes = $this->themes();
        $banners = $this->banners();
        $categories = Category::whereNull('parent_id')->orderBy('order')->get();
        $pages = Page::where('is_active', true)->orderBy('sort_order')->get();

        $navItemsOrderRaw = $this->settings->get('nav_items_order', '');
        if ($navItemsOrderRaw) {
            $navItemsOrder = json_decode($navItemsOrderRaw, true) ?: [];
        } else {
            $navItemsOrder = [];
        }
        foreach (['home', 'products', 'contact'] as $builtin) {
            if (! in_array($builtin, $navItemsOrder)) {
                $navItemsOrder[] = $builtin;
            }
        }

        $current = [
            'theme' => $this->settings->get('site_theme', 'light'),
            'primary_color' => $this->settings->get('primary_color', '#2563eb'),
            'accent_color' => $this->settings->get('accent_color', '#f59e0b'),
            'hero_title' => $this->settings->get('hero_title', 'تسوق بذكاء، عش تجربة فريدة'),
            'hero_subtitle' => $this->settings->get('hero_subtitle', 'اكتشف أحدث المنتجات بأسعار مميزة مع شحن سريع لجميع الدول العربية'),
            'hero_badge' => $this->settings->get('hero_badge', 'جديد'),
            'hero_image' => $this->settings->get('hero_image', ''),
            'banner_1_title' => $this->settings->get('banner_1_title', ''),
            'banner_1_subtitle' => $this->settings->get('banner_1_subtitle', ''),
            'banner_1_image' => $this->settings->get('banner_1_image', ''),
            'banner_1_link' => $this->settings->get('banner_1_link', ''),
            'banner_2_title' => $this->settings->get('banner_2_title', ''),
            'banner_2_subtitle' => $this->settings->get('banner_2_subtitle', ''),
            'banner_2_image' => $this->settings->get('banner_2_image', ''),
            'banner_2_link' => $this->settings->get('banner_2_link', ''),
            'show_newsletter' => $this->settings->get('show_newsletter', '1'),
            'show_featured' => $this->settings->get('show_featured', '1'),
            'show_latest' => $this->settings->get('show_latest', '1'),
            'show_categories' => $this->settings->get('show_categories', '1'),
            'show_hero' => $this->settings->get('show_hero', '1'),
            'show_marquee' => $this->settings->get('show_marquee', '1'),
            'show_banner_1' => $this->settings->get('show_banner_1', '1'),
            'slider_animation_duration' => $this->settings->get('slider_animation_duration', 500),
            'slider_entrance_stagger' => $this->settings->get('slider_entrance_stagger', 80),
            'home_section_order' => $this->settings->get('home_section_order', '["hero","marquee","categories","featured","latest","banner_1","banner_2"]'),
            'nav_show_home' => $this->settings->get('nav_show_home', '1'),
            'nav_show_products' => $this->settings->get('nav_show_products', '1'),
            'nav_show_categories' => $this->settings->get('nav_show_categories', '1'),
            'nav_show_contact' => $this->settings->get('nav_show_contact', '1'),
            'nav_categories_limit' => $this->settings->get('nav_categories_limit', '3'),
            'nav_categories_list' => $this->settings->get('nav_categories_list', ''),
            'nav_pages_list' => $this->settings->get('nav_pages_list', ''),
            'nav_items_order' => $this->settings->get('nav_items_order', ''),
            'footer_about' => $this->settings->get('footer_about', 'متجر إلكتروني متكامل يوفر لك تجربة تسوق فريدة مع شحن سريع ودفع آمن عند الاستلام.'),
            'footer_copyright' => $this->settings->get('footer_copyright', 'جميع الحقوق محفوظة'),
            'top_bar_show' => $this->settings->get('top_bar_show', '1'),
            'top_bar_text' => $this->settings->get('top_bar_text', ''),
            'top_bar_bg_color' => $this->settings->get('top_bar_bg_color', '#004ac6'),
            'top_bar_text_color' => $this->settings->get('top_bar_text_color', '#ffffff'),
            'top_bar_link' => $this->settings->get('top_bar_link', ''),
            'top_bar_phone' => $this->settings->get('top_bar_phone', ''),
            'top_bar_show_cod' => $this->settings->get('top_bar_show_cod', '1'),
            'top_bar_show_track' => $this->settings->get('top_bar_show_track', '1'),
            'top_bar_show_help' => $this->settings->get('top_bar_show_help', '1'),
            'top_bar_btn_text' => $this->settings->get('top_bar_btn_text', ''),
            'top_bar_btn_url' => $this->settings->get('top_bar_btn_url', ''),
            'whatsapp_btn_show' => $this->settings->get('whatsapp_btn_show', '0'),
            'whatsapp_btn_phone' => $this->settings->get('whatsapp_btn_phone', ''),
            'whatsapp_btn_text' => $this->settings->get('whatsapp_btn_text', 'مرحباً، أود الاستفسار عن المنتجات'),
            'whatsapp_btn_position' => $this->settings->get('whatsapp_btn_position', 'right'),
        ];

        return view('admin.customize.index', compact('themes', 'banners', 'categories', 'pages', 'current', 'navItemsOrder'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme' => 'required|in:light,colorful,minimal',
            'primary_color' => ['required', 'string', function ($attr, $value, $fail) {
                $v = strtoupper(trim($value));
                if (! preg_match('/^#[0-9A-F]{6}$/', $v)) {
                    $fail('اللون الأساسي يجب أن يكون بصيغة #RRGGBB');
                }
            }],
            'accent_color' => ['required', 'string', function ($attr, $value, $fail) {
                $v = strtoupper(trim($value));
                if (! preg_match('/^#[0-9A-F]{6}$/', $v)) {
                    $fail('اللون الثانوي يجب أن يكون بصيغة #RRGGBB');
                }
            }],
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_badge' => 'nullable|string|max:50',
            'hero_image' => 'nullable|string|max:500',
            'hero_image_file' => 'nullable|file|mimes:'.self::HERO_IMAGE_MIMES.'|max:'.self::HERO_IMAGE_MAX_KB,
            'banner_1_title' => 'nullable|string|max:255',
            'banner_1_subtitle' => 'nullable|string|max:500',
            'banner_1_image' => 'nullable|string|max:500',
            'banner_1_image_file' => 'nullable|file|mimes:'.self::HERO_IMAGE_MIMES.'|max:'.self::HERO_IMAGE_MAX_KB,
            'banner_1_link' => 'nullable|url',
            'banner_2_title' => 'nullable|string|max:255',
            'banner_2_subtitle' => 'nullable|string|max:500',
            'banner_2_image' => 'nullable|string|max:500',
            'banner_2_image_file' => 'nullable|file|mimes:'.self::HERO_IMAGE_MIMES.'|max:'.self::HERO_IMAGE_MAX_KB,
            'banner_2_link' => 'nullable|url',
            'show_newsletter' => 'boolean',
            'show_featured' => 'boolean',
            'show_latest' => 'boolean',
            'show_categories' => 'boolean',
            'show_hero' => 'boolean',
            'show_marquee' => 'boolean',
            'show_banner_1' => 'boolean',
            'slider_animation_duration' => 'nullable|integer|min:100|max:2000',
            'slider_entrance_stagger' => 'nullable|integer|min:10|max:300',
            'home_section_order' => 'nullable|string|max:500',
            'nav_show_home' => 'boolean',
            'nav_show_products' => 'boolean',
            'nav_show_categories' => 'boolean',
            'nav_show_contact' => 'boolean',
            'nav_categories_limit' => 'nullable|integer|min:1|max:10',
            'nav_categories_list' => 'nullable|array',
            'nav_pages_list' => 'nullable|array',
            'nav_items_order' => 'nullable|string|max:2000',
            'footer_about' => 'nullable|string|max:1000',
            'footer_copyright' => 'nullable|string|max:255',
            'top_bar_show' => 'boolean',
            'top_bar_text' => 'nullable|string|max:255',
            'top_bar_bg_color' => 'nullable|string|max:20',
            'top_bar_text_color' => 'nullable|string|max:20',
            'top_bar_link' => 'nullable|string|max:500',
            'top_bar_phone' => 'nullable|string|max:50',
            'top_bar_show_cod' => 'boolean',
            'top_bar_show_track' => 'boolean',
            'top_bar_show_help' => 'boolean',
            'top_bar_btn_text' => 'nullable|string|max:100',
            'top_bar_btn_url' => 'nullable|string|max:500',
            'whatsapp_btn_show' => 'boolean',
            'whatsapp_btn_phone' => 'nullable|string|max:50',
            'whatsapp_btn_text' => 'nullable|string|max:500',
            'whatsapp_btn_position' => 'nullable|in:right,left',
        ]);

        $checkboxKeys = [
            'show_newsletter', 'show_featured', 'show_latest', 'show_categories',
            'show_hero', 'show_marquee', 'show_banner_1',
            'nav_show_home', 'nav_show_products', 'nav_show_categories', 'nav_show_contact',
            'top_bar_show', 'top_bar_show_cod', 'top_bar_show_track', 'top_bar_show_help',
            'whatsapp_btn_show',
        ];

        if ($request->has('nav_categories_list')) {
            $this->settings->set('nav_categories_list', json_encode($request->input('nav_categories_list', [])), 'customize');
        }
        if ($request->has('nav_pages_list')) {
            $this->settings->set('nav_pages_list', json_encode($request->input('nav_pages_list', [])), 'customize');
        }

        foreach ($checkboxKeys as $key) {
            if ($request->has($key)) {
                $this->settings->set($key, $request->boolean($key) ? '1' : '0', 'customize');
            }
        }

        $sectionOrder = $request->input('home_section_order');
        if ($sectionOrder) {
            $decoded = is_string($sectionOrder) ? json_decode($sectionOrder, true) : $sectionOrder;
            if (! is_array($decoded)) {
                $decoded = ['hero', 'marquee', 'categories', 'featured', 'latest', 'banner_1', 'banner_2'];
            }
            $this->settings->set('home_section_order', json_encode($decoded), 'customize');
        }

        $navItemsOrder = $request->input('nav_items_order');
        if ($navItemsOrder) {
            $decodedNav = is_string($navItemsOrder) ? json_decode($navItemsOrder, true) : $navItemsOrder;
            if (is_array($decodedNav)) {
                $this->settings->set('nav_items_order', json_encode($decodedNav), 'customize');

                $syncedCatIds = [];
                $syncedPageIds = [];
                foreach ($decodedNav as $item) {
                    if (preg_match('/^cat-(\d+)$/', $item, $m)) {
                        $syncedCatIds[] = (int) $m[1];
                    } elseif (preg_match('/^page-(\d+)$/', $item, $m)) {
                        $syncedPageIds[] = (int) $m[1];
                    }
                }
                $this->settings->set('nav_categories_list', json_encode($syncedCatIds), 'customize');
                $this->settings->set('nav_pages_list', json_encode($syncedPageIds), 'customize');
            }
        }

        foreach ($data as $key => $value) {
            if (str_ends_with($key, '_file')) {
                continue;
            }
            if ($key === 'nav_categories_list' || $key === 'nav_pages_list' || $key === 'nav_items_order') {
                continue;
            }
            if (in_array($key, $checkboxKeys, true)) {
                continue;
            }

            if (in_array($key, ['hero_image', 'banner_1_image', 'banner_2_image'], true) && empty($value)) {
                $existing = $this->settings->get($key);
                if ($existing && ! preg_match('#^https?://#i', $existing)) {
                    continue;
                }
            }

            if (in_array($key, ['primary_color', 'accent_color', 'top_bar_bg_color', 'top_bar_text_color'], true)) {
                $value = strtoupper(trim($value));
            }
            $this->settings->set($key, (string) $value, 'customize');
            if ($key === 'theme') {
                $this->settings->set('site_theme', (string) $value, 'customize');
            }
        }

        $imageMap = [
            'hero_image_file' => ['key' => 'hero_image',     'folder' => 'hero'],
            'banner_1_image_file' => ['key' => 'banner_1_image', 'folder' => 'banners'],
            'banner_2_image_file' => ['key' => 'banner_2_image', 'folder' => 'banners'],
        ];
        foreach ($imageMap as $input => $info) {
            if ($request->hasFile($input)) {
                $path = $this->images->upload(
                    $request->file($input), $info['folder'], self::HERO_IMAGE_MAX_KB, self::HERO_IMAGE_MIMES,
                    $this->settings->get($info['key'])
                );
                if ($path) {
                    $this->settings->set($info['key'], $path, 'customize');
                }
            }
        }

        $this->settings->flush();

        return redirect()->route('admin.customize.index')->with('success', 'تم حفظ التخصيصات');
    }

    public function removeImage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => 'required|in:hero_image,banner_1_image,banner_2_image',
        ]);
        $current = $this->settings->get($data['key']);
        if ($current && ! preg_match('#^https?://#i', $current)) {
            $this->images->delete($current);
        }
        $this->settings->set($data['key'], '', 'customize');
        $this->settings->flush();

        return back()->with('success', 'تم حذف الصورة');
    }

    public function reset(): RedirectResponse
    {
        $defaults = [
            'site_theme' => 'light',
            'primary_color' => '#2563eb',
            'accent_color' => '#f59e0b',
            'hero_title' => 'تسوق بذكاء، عش تجربة فريدة',
            'hero_subtitle' => 'اكتشف أحدث المنتجات بأسعار مميزة',
            'show_newsletter' => '1',
            'show_featured' => '1',
            'show_latest' => '1',
            'show_categories' => '1',
            'slider_animation_duration' => 500,
            'slider_entrance_stagger' => 80,
        ];
        foreach ($defaults as $k => $v) {
            $this->settings->set($k, $v, 'customize');
        }
        $this->settings->flush();

        return redirect()->route('admin.customize.index')->with('success', 'تم استعادة الإعدادات الافتراضية');
    }

    private function themes(): array
    {
        return [
            'light' => ['name' => 'فاتح', 'icon' => 'light_mode', 'description' => 'تصميم مشرق وألوان هادئة', 'colors' => ['#ffffff', '#f3f4f6', '#2563eb', '#f59e0b']],
            'colorful' => ['name' => 'ملون', 'icon' => 'palette', 'description' => 'ألوان زاهية وجريئة', 'colors' => ['#fef3c7', '#fce7f3', '#ec4899', '#8b5cf6']],
            'minimal' => ['name' => 'بسيط', 'icon' => 'crop_square', 'description' => 'تصميم نظيف وأبسط', 'colors' => ['#ffffff', '#f5f5f5', '#18181b', '#71717a']],
        ];
    }

    private function banners(): array
    {
        return [
            ['id' => 1, 'name' => 'بانر الشحن المجاني'],
            ['id' => 2, 'name' => 'بانر العروض الخاصة'],
        ];
    }
}
