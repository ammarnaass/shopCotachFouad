# خطة إصلاح إعدادات المتجر (Admin Settings)

## المشكلة

صفحة الإعدادات العامة (`admin.settings.index`) بها عدم تطابق خطير بين حقول النموذج (View) والController مما يؤدي إلى:
- عدم حفظ بيانات التواصل الاجتماعي
- عدم حفظ ساعات العمل
- عدم حفظ أكواد التتبع (GA, FB Pixel)
- تبويب Checkout غير قابل للوصول من واجهة المستخدم

## المشاكل المحددة

### 1. Social Tab - عدم تطابق أسماء الحقول (حرج)
- **View يستخدم:** `social_whatsapp`, `social_facebook`, `social_instagram`, `social_tiktok`, `social_youtube`, `social_telegram`, `social_snapchat`
- **Controller يستخدم:** `facebook_url`, `twitter_url`, `instagram_url`, `whatsapp_number`, `youtube_url`
- **النتيجة:** لا يتم حفظ أي بيانات اجتماعية

### 2. Contact Tab - حقول مفقودة (حرج)
- **View يستخدم:** `contact_working_hours`, `contact_support_hours`
- **Controller يستخدم:** `contact_hours` فقط
- **النتيجة:** لا يتم حفظ ساعات العمل/الدعم

### 3. Checkout Tab - غير قابل للوصول (متوسط)
- تبويب `checkout` غير موجود في مصفوفة `$tabs` في الـ View
- يوجد كود كامل للتبويب (سطور 301-387) لكن لا يمكن الوصول إليه

### 4. SEO Tab - حقول إضافية مفقودة (منخفض)
- View يحتوي `seo_ga_id` و `seo_fb_pixel` لكن Controller لا يعرفها

## خطة الإصلاح

### الخطوة 1: تعديل Controller لتطابق View

**الملف:** `app/Http/Controllers/Admin/SettingsController.php`

#### أ) تحديث `defaults['social']`:
```php
'social' => [
    'social_whatsapp' => '',
    'social_facebook' => '',
    'social_instagram' => '',
    'social_tiktok' => '',
    'social_youtube' => '',
    'social_telegram' => '',
    'social_snapchat' => '',
],
```

#### ب) تحديث `defaults['contact']`:
```php
'contact' => [
    'contact_email' => 'info@amarstore.com',
    'contact_phone' => '+249 90 000 0000',
    'contact_address' => 'الخرطوم، السودان',
    'contact_working_hours' => '',
    'contact_support_hours' => '',
],
```

#### ج) تحديث `rulesFor('social')`:
```php
'social' => [
    'social_whatsapp' => 'nullable|string|max:50',
    'social_facebook' => 'nullable|url',
    'social_instagram' => 'nullable|url',
    'social_tiktok' => 'nullable|url',
    'social_youtube' => 'nullable|url',
    'social_telegram' => 'nullable|url',
    'social_snapchat' => 'nullable|url',
],
```

#### د) تحديث `rulesFor('contact')`:
```php
'contact' => [
    'contact_email' => 'required|email',
    'contact_phone' => 'required|string|max:50',
    'contact_address' => 'nullable|string|max:500',
    'contact_working_hours' => 'nullable|string|max:100',
    'contact_support_hours' => 'nullable|string|max:100',
],
```

#### هـ) تحديث `defaults['seo']`:
```php
'seo' => [
    'seo_meta_title' => '',
    'seo_meta_description' => '',
    'seo_meta_keywords' => '',
    'seo_og_image' => '',
    'seo_ga_id' => '',
    'seo_fb_pixel' => '',
],
```

#### و) تحديث `rulesFor('seo')`:
```php
'seo' => [
    'seo_meta_title' => 'nullable|string|max:255',
    'seo_meta_description' => 'nullable|string|max:500',
    'seo_meta_keywords' => 'nullable|string|max:500',
    'seo_og_image' => 'nullable|string|max:500',
    'seo_og_image_file' => 'nullable|file|mimes:' . self::LOGO_MIMES . '|max:' . self::LOGO_MAX_KB,
    'seo_ga_id' => 'nullable|string|max:50',
    'seo_fb_pixel' => 'nullable|string|max:50',
],
```

### الخطوة 2: إضافة تبويب Checkout إلى الـ View

**الملف:** `resources/views/admin/settings/index.blade.php`

إضافة `checkout` إلى مصفوفة `$tabs` (سطر 7-13):
```php
$tabs = [
    'store' => ['icon' => 'store', 'title' => __t('admin.settings.store_tab')],
    'currency' => ['icon' => 'payments', 'title' => __t('admin.settings.currency_tab')],
    'checkout' => ['icon' => 'bolt', 'title' => __t('admin.settings.checkout_tab')],
    'social' => ['icon' => 'share', 'title' => __t('admin.settings.social_tab')],
    'contact' => ['icon' => 'headset_mic', 'title' => __t('admin.settings.contact_tab')],
    'seo' => ['icon' => 'search', 'title' => __t('admin.settings.seo_tab')],
];
```

### الخطوة 3: التحقق والاختبار

1. تشغيل `php artisan serve`
2. فتح صفحة الإعدادات في المتصفح
3. اختبار كل تبويب: store, currency, checkout, social, contact, seo
4. التحقق من حفظ البيانات في كل تبويب
5. اختبار حذف الصور (removeImage)
6. تشغيل `php artisan route:list --name=admin.settings` للتأكد من صحة المسارات

## المخاطر

- **低:** تغيير أسماء الحقول قد يؤثر على أي كود آخر يستخدم `Setting::get('facebook_url')` — لكن بما أن النموذج القديم لم يكن يحفظ البيانات أصلاً، لا يوجد بيانات قديمة تخسرها.

## التأكيد

بعد التنفيذ:
- صفحة الإعدادات العامة تعمل بدون أخطاء
- جميع التبويبات قابلة للوصول
- جميع البيانات تُحفظ وتُعرض بشكل صحيح
