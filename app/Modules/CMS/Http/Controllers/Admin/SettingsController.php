<?php

namespace App\Modules\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\EnvironmentService;
use App\Services\ImageUploadService;
use App\Services\SettingsService;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public const LOGO_MAX_KB = 1024;

    public const FAVICON_MAX_KB = 256;

    public const LOGO_MIMES = 'jpeg,jpg,png,webp,svg';

    public const FAVICON_MIMES = 'ico,png,svg';

    public function __construct(
        private SettingsService $settings,
        private ImageUploadService $images,
        private EnvironmentService $environment,
    ) {}

    private array $defaults = [
        'store' => [
            'store_name' => 'AN SHOP',
            'store_email' => 'contact@anshop.dz',
            'store_phone' => '+213 550 00 00 00',
            'store_address' => 'الجزائر العاصمة',
            'store_description' => 'متجر إلكتروني متكامل يوفر لك تجربة تسوق فريدة',
            'store_logo' => '',
            'store_favicon' => '',
        ],
        'social' => [
            'social_whatsapp' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_tiktok' => '',
            'social_youtube' => '',
            'social_telegram' => '',
            'social_snapchat' => '',
        ],
        'contact' => [
            'contact_email' => 'info@amarstore.com',
            'contact_phone' => '+249 90 000 0000',
            'contact_whatsapp' => '',
            'contact_address' => 'الخرطوم، السودان',
            'contact_working_hours' => '',
            'contact_support_hours' => '',
        ],
        'seo' => [
            'seo_meta_title' => '',
            'seo_meta_description' => '',
            'seo_meta_keywords' => '',
            'seo_og_image' => '',
            'seo_ga_id' => '',
            'seo_fb_pixel' => '',
        ],
        'store_extended' => [
            'store_wilaya' => '',
            'store_commune' => '',
            'store_postal_code' => '',
            'store_website' => '',
            'store_phone_secondary' => '',
        ],
        'invoice_info' => [
            'invoice_business_name' => '',
            'invoice_legal_name' => '',
            'invoice_rc' => '',
            'invoice_nif' => '',
            'invoice_nis' => '',
            'invoice_phone' => '',
            'invoice_address' => '',
            'invoice_email' => '',
            'invoice_notes' => '',
        ],
    ];

    public function index(): View
    {
        $settings = [];
        foreach ($this->defaults as $group => $fields) {
            $settings[$group] = [];
            foreach ($fields as $key => $default) {
                $settings[$group][$key] = $this->settings->get($key, $default);
            }
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $group = $request->input('group');

        $validated = $request->validated();
        foreach ($validated as $key => $value) {
            if (in_array($key, ['group', '_token'])) {
                continue;
            }
            if (str_ends_with($key, '_file')) {
                continue;
            }
            if (in_array($key, ['store_logo', 'store_favicon', 'seo_og_image'], true) && empty($value)) {
                continue;
            }
            $this->settings->set($key, (string) $value, $group);
        }

        if ($group === 'currency' && $request->filled('default_country')) {
            $this->environment->update('APP_DEFAULT_COUNTRY', strtoupper($request->input('default_country')));
            SiteSettings::flush();
        }

        if ($group === 'store') {
            if ($request->hasFile('store_logo_file')) {
                $path = $this->images->upload(
                    $request->file('store_logo_file'), 'logos', self::LOGO_MAX_KB, self::LOGO_MIMES,
                    $this->settings->get('store_logo')
                );
                if ($path) {
                    $this->settings->set('store_logo', $path, 'store');
                }
            }
            if ($request->hasFile('store_favicon_file')) {
                $path = $this->images->upload(
                    $request->file('store_favicon_file'), 'favicons', self::FAVICON_MAX_KB, self::FAVICON_MIMES,
                    $this->settings->get('store_favicon')
                );
                if ($path) {
                    $this->settings->set('store_favicon', $path, 'store');
                }
            }
        }

        if ($group === 'seo' && $request->hasFile('seo_og_image_file')) {
            $path = $this->images->upload(
                $request->file('seo_og_image_file'), 'seo', self::LOGO_MAX_KB, self::LOGO_MIMES,
                $this->settings->get('seo_og_image')
            );
            if ($path) {
                $this->settings->set('seo_og_image', $path, 'seo');
            }
        }

        $this->settings->flush();

        return redirect()->route('admin.settings.index', ['#'.$group])
            ->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    public function removeImage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => 'required|in:store_logo,store_favicon,seo_og_image',
        ]);
        $current = $this->settings->get($data['key']);
        if ($current && ! preg_match('#^https?://#i', $current)) {
            $this->images->delete($current);
        }
        $this->settings->set($data['key'], '', 'store');
        $this->settings->flush();

        return back()->with('success', 'تم حذف الصورة');
    }
}
