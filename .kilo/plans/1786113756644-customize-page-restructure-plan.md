# خطة إعادة هيكلة صفحة التخصيص (Customize)

## المشكلة

صفحة التخصيص (`admin.customize`) بها:
1. **تكرار حقول** مع صفحة الإعدادات — معلومات التواصل والاجتماعي تظهر في الصفحتين
2. **矛盾 في أسماء المفاتيح** — الإعدادات تحفظ بمفاتيح جديدة (`social_facebook`) لكن الواجهة الأمامية تقرأ القديمة (`facebook_url`) — البيانات المحفوظة من الإعدادات لا تظهر!
3. **صفحة ضخمة واحدة** بدون تبويبات — صعب التنقل
4. **Instant buy مكرر** — موجود في الإعدادات (checkout tab) والتخصيص أيضاً
5. **SiteSettings.php defaults** قديمة — لا تتضمن المفاتيح الجديدة
6. **`contact_whatsapp` مفقود** — الواجهة الأمامية تقرأه لكن لا يوجد حقل لحفظه

## خطة الإصلاح

### المنطقة المسؤولية (النتيجة النهائية)

| الصفحة | المسؤولية |
|---|---|
| **صفحة الإعدادات (Settings)** | معلومات المتجر، التواصل، التواصل الاجتماعي، SEO، العملة، Checkout |
| **صفحة التخصيص (Customize)** | المظهر فقط — ثيم، ألوان، بانرات، إظهار الأقسام، شريط الإعلانات، WhatsApp العائم، الفوتر (نص فقط) |

### الخطوة 1: تحديث SiteSettings.php defaults

**الملف:** `app/Support/SiteSettings.php`

إضافة المفاتيح الجديدة إلى `defaults()` (الحفاظ على القديمة كfallback):

```php
// social (مفاتيح جديدة — القيم القديمة تبقى كfallback)
'social_whatsapp' => '',
'social_facebook' => '',
'social_instagram' => '',
'social_tiktok' => '',
'social_youtube' => '',
'social_telegram' => '',
'social_snapchat' => '',

// contact (مفاتيح جديدة)
'contact_working_hours' => '',
'contact_support_hours' => '',
'contact_whatsapp' => '',

// seo (مفاتيح جديدة)
'seo_ga_id' => '',
'seo_fb_pixel' => '',
```

### الخطوة 2: إضافة `contact_whatsapp` إلى صفحة الإعدادات

**الملف:** `app/Http/Controllers/Admin/SettingsController.php`

إضافة `contact_whatsapp` إلى:
- `defaults['contact']`: `'contact_whatsapp' => ''`
- `rulesFor('contact')`: `'contact_whatsapp' => 'nullable|string|max:50'`

**الملف:** `resources/views/admin/settings/index.blade.php`

إضافة حقل `contact_whatsapp` في قسم Contact tab (بعد contact_phone):

```html
<div class="space-y-2">
    <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.whatsapp_number') }}</label>
    <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact']['contact_whatsapp'] ?? '') }}"
           class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md" placeholder="966500000000">
</div>
```

### الخطوة 3: تحديث الواجهة الأمامية لاستخدام المفاتيح الجديدة

**الملف:** `resources/views/frontend/partials/footer.blade.php`

تحديث كل `site()` call مع fallback للمفاتيح القديمة:

| السطر | من | إلى |
|---|---|---|
| 74 | `site('whatsapp_number')` | `site('social_whatsapp', site('whatsapp_number'))` |
| 79-80 | `site('facebook_url')` | `site('social_facebook', site('facebook_url'))` |
| 84-85 | `site('twitter_url')` | `site('social_twitter', site('twitter_url', site('x_url')))` |
| 89-90 | `site('instagram_url')` | `site('social_instagram', site('instagram_url'))` |
| 94-96 | `site('whatsapp_number')` | `site('social_whatsapp', site('whatsapp_number'))` |
| 105-106 | `site('youtube_url')` | `site('social_youtube', site('youtube_url'))` |
| 216,221 | `site('contact_hours')` | `site('contact_working_hours', site('contact_hours'))` |

ملاحظة: `site('social_facebook', site('facebook_url'))` = اقرأ المفتاح الجديد، إذا فارغ اقرأ القديم.

### الخطوة 4: تنظيف CustomizeController — حذف المكرر

**الملف:** `app/Http/Controllers/Admin/CustomizeController.php`

**إزالة من `index()` current array** ( handled by Settings ):
```php
// حذف هذه المفاتيح:
'contact_phone', 'contact_email', 'contact_whatsapp', 'contact_address',
'facebook_url', 'twitter_url', 'instagram_url', 'whatsapp_number', 'youtube_url',
'instant_enable_bank_transfer', 'instant_show_email', 'instant_req_email',
'instant_show_state', 'instant_req_state', 'instant_show_district', 'instant_req_district',
'instant_show_zip', 'instant_req_zip', 'instant_show_notes', 'instant_show_coupon'
```

**إزالة من `update()` validation rules** (سطور 143-151):
```php
// حذف قواعد التحقق لهذه المفاتيح:
'contact_phone', 'contact_email', 'contact_whatsapp', 'contact_address',
'facebook_url', 'twitter_url', 'instagram_url', 'whatsapp_number', 'youtube_url'
```

**إزالة من `update()` persistence logic** — التأكد من عدم حفظها (التحقق من سلوك loop بعد الحذف).

### الخطوة 5: تحويل صفحة التخصيص إلى تبويبات

**الملف:** `resources/views/admin/customize/index.blade.php`

إعادة هيكلة الصفحة لتستخدم تبويبات (مثل صفحة الإعدادات):

```php
@php
$activeTab = request('tab', 'theme');
$tabs = [
    'theme' => ['icon' => 'palette', 'title' => __t('admin.customize.tab_theme')],
    'banners' => ['icon' => 'campaign', 'title' => __t('admin.customize.tab_banners')],
    'sections' => ['icon' => 'visibility', 'title' => __t('admin.customize.tab_sections')],
    'header' => ['icon' => 'link', 'title' => __t('admin.customize.tab_header')],
    'announcement' => ['icon' => 'ad_units', 'title' => __t('admin.customize.tab_announcement')],
    'whatsapp' => ['icon' => 'chat', 'title' => __t('admin.customize.tab_whatsapp')],
    'footer' => ['icon' => 'directions_walk', 'title' => __t('admin.customize.tab_footer')],
];
@endphp
```

**بنية كل تبويب:**

| التبويب | المحتوى |
|---|---|
| **theme** | ثيم (4 radio cards) + ألوان (primary + accent) |
| **banners** | بانر 1 + بانر 2 (title, subtitle, image, link) — بدون hero redirect |
| **sections** | checkboxes: show_featured, show_latest, show_categories, show_newsletter |
| **header** | nav_show_* checkboxes + nav_categories_limit + category/page pickers |
| **announcement** | top_bar_show + text + bg_color + text_color + link |
| **whatsapp** | whatsapp_btn_show + phone + position + text |
| **footer** | footer_about + footer_copyright فقط |

**إزالة من الـ view:**
- قسم Hero (redirect) — يُحذف أو يصبح link بسيط
- قسم Instant Buy (redirect) — يُحذف تماماً
- حقول contact_* من Footer (معلومات التواصل في صفحة الإعدادات)
- حقول social_* من Footer (روابط التواصل في صفحة الإعدادات)

**إضافة hidden input للـ group:**
```html
<input type="hidden" name="group" value="{{ $activeTab }}">
```

**تحديث `update()` method** للتعامل مع `group` parameter (مثل SettingsController):
```php
public function update(Request $request): RedirectResponse
{
    $group = $request->input('group', 'theme');
    // ... handle per-group validation and save
}
```

### الخطوة 6: لا تغيير في المسارات

**الملف:** `routes/web.php`

لا تغيير مطلوب — المسارات الحالية كافية. التبويبات تعمل عبر `request('tab')`.

## الملفات المتأثرة

| الملف | التعديل |
|---|---|
| `app/Support/SiteSettings.php` | إضافة مفاتيح جديدة إلى `defaults()` |
| `app/Http/Controllers/Admin/SettingsController.php` | إضافة `contact_whatsapp` إلى defaults و rules |
| `resources/views/admin/settings/index.blade.php` | إضافة حقل `contact_whatsapp` في contact tab |
| `app/Http/Controllers/Admin/CustomizeController.php` | حذف contact/social/instant من defaults, validation, persistence |
| `resources/views/admin/customize/index.blade.php` | تحويل إلى تبويبات، حذف الحقول المكررة |
| `resources/views/frontend/partials/footer.blade.php` | تحديث `site()` calls للمفاتيح الجديدة مع fallback |

## ملاحظات مهمة

1. **لا نحذف المفاتيح القديمة** من SiteSettings defaults — نحتفظ بها كfallback
2. **الواجهة الأمامية** تقرأ المفاتيح الجديدة مع fallback تلقائي
3. **صفحة الإعدادات** تبقى المصدر الوحيد للمعلومات والتواصل والـ SEO والـ Checkout
4. **صفحة التخصيص** تتعامل فقط مع المظهر والواجهة
5. **`contact_whatsapp`** يُضاف إلى صفحة الإعدادات contact tab (مفقود حالياً)

## التحقق

1. `php artisan serve` — فتح صفحة التخصيص، التأكد من عمل التبويبات
2. اختبار كل تبويب: theme, banners, sections, header, announcement, whatsapp, footer
3. التأكد من عدم وجود حقول contact/social/instant في صفحة التخصيص
4. اختبار حفظ كل تبويب بشكل منفصل
5. اختبار صفحة الإعدادات — التأكد من حفظ `contact_whatsapp` في contact tab
6. التأكد من ظهور معلومات التواصل في الواجهة الأمامية
7. `php artisan route:list --name=admin.customize` للتأكد من صحة المسارات
