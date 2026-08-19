# Plan: إصلاح جلب معلومات المتجر المخزّنة في طباعة الفواتير/الملصقات

## Status: Plan-ready — implementation-capable agent required

## Context / Problem (confirmed via code inspection)

عند طباعة فاتورة أو ملصق توجد مصدران للمعلومات:
- **(أ) الإعدادات العامة** المخزّنة في جدول `settings` (`SiteSettings::get('store_*')`, `invoice_*`, `printing_*`) — يُفترض أن تُجلب عبر `InvoiceService::getInvoiceData()` و `LabelService::getLabelData()`.
- **(ب) بيانات الطلبية** من `$order->shippingAddress` — تُجلب بشكل صحيح.

الخلل الحالي أن مصدر (أ) مكسور في عدة نقاط:

1. **`InvoiceService` snapshot ناقص:** `buildStoreSnapshot()` لا يلتقط `store_commune`, `store_postal_code`, `store_phone_secondary`, `invoice_phone`, `invoice_address`, `invoice_email` رغم وجودها في `SiteSettings::defaults()` و في `SettingsController::$defaults['store_extended']` و `$defaults['invoice_info']` (قابلة للتحرير من UI). النتيجة: القيمة تُحفظ في DB لكنها تُهمل عند الطباعة.

2. **`InvoiceService::getInvoiceData()::$storeInfo` ناقص:** لا حقول لـ `commune`, `postal_code`, `phone_secondary`, `invoice_phone`, `invoice_address`, `invoice_email`, `invoice_website` الغائبة من المصفوفة الممرّرة إلى blade.

3. **`invoice-header.blade.php` عرض ناقص:** السطر 23 يعرض فقط `العنوان - الولاية`، ولا يشمل البلدية/الرمز البريدي/الهاتف الثانوي/البريد الفاتورة/الهاتف الفاتورة/العنوان القانوني.

4. **عدم تطابق مفاتيح `UpdateSettingsRequest` مع `SiteSettings::defaults()`:** مجموعة `store` تتحقق من `store_country`, `store_city`, `store_whatsapp` — وهي **مفاتيح غير موجودة** في `SiteSettings::defaults()` (الذي يستخدم `store_wilaya`, `store_commune`). الحفظ يمر لكن عبر `str_ends_with('_file')`/القفز على القيم الفارغة؛ يكتب مفاتيح لا يقرؤها أحد، والمفاتيح الحقيقية (`store_wilaya`...) للأساس مدخولة ضمن `store_extended` (وليست في مجموعة `store`).

5. **`SettingsController::$defaults['store']` لا يتطابق مع `SiteSettings::defaults()` للقيم الافتراضية:** مثلاً `store_address = 'الخرطوم، السودان'` مقابل `SiteSettings::defaults()['store_address'] = 'الجزائر العاصمة'`، وغياب `store_wilaya`/`store_commune`/`store_postal_code`/`store_website`/`store_phone_secondary` من القسم `store` (موجودة في `store_extended` — منفصلة).

## Decisions (confirmed with user)

- **تنظيف السلسلة بالكامل** (snapshot + Service + blade + قواعد التحقق) — لا تغيير في منطق الطلبية.
- **مصدر الحقيقة الموحَّد = `SiteSettings::defaults()`**: تُزال المفاتيح المتخلفة من `UpdateSettingsRequest`، وتُحاذا `SettingsController::$defaults` مع `SiteSettings::defaults()` (نفس القيم الافتراضية).
- **للفواتير القديمة:** `fallback` ديناميكي عبر `??` على كل مفتاح جديد داخل `getInvoiceData()` — أي `$snapshot['store_commune'] ?? SiteSettings::get('store_commune')`. لا migration لإعادة backfill (المفاتيح الجديدة فقط).
- **لا تُمسّ** الفواتير القديمة الموجودة (`metadata`) — ستظهر بقيم `??` من الإعدادات الحية.

## Out of Scope

- إعادة تصميم معماري أعمق لفصل المصادر — المقصود إصلاح السلسلة الحالية فقط.
- migration backfill لفواتير قديمة.
- تعديل منطق الطلب أو `shippingAddress`.
- لمس القوالب القديمة المهجورة (`invoice-pdf.blade.php`, `customer-label-pdf.blade.php`...).
- إعادة seeding الـ DocumentPermissions/InvoiceTemplate/LabelTemplate.

---

## Tasks (ordered)

### 1. توسيع `buildStoreSnapshot()` لكل المفاتيح المخزّنة
**[MODIFY]** `app/Services/Documents/InvoiceService.php` — دالة `buildStoreSnapshot()` (السطور 113-129).

أضف المفاتيح الناقصة حتى يُلتقط snapshot كامل:

```
'store_commune'          => SiteSettings::get('store_commune'),
'store_postal_code'      => SiteSettings::get('store_postal_code'),
'store_phone_secondary'  => SiteSettings::get('store_phone_secondary'),
'invoice_phone'          => SiteSettings::get('invoice_phone'),
'invoice_email'           => SiteSettings::get('invoice_email'),
'invoice_address'        => SiteSettings::get('invoice_address'),
```

> ملاحظة: `store_website` موجودة في snapshot لكن يجب التأكد من بقائها.

---

### 2. توسيع `getInvoiceData()::$storeInfo` + fallback ديناميكي
**[MODIFY]** `app/Services/Documents/InvoiceService.php` — `$storeInfo` (السطور 65-80).

أضف للمصفوفة نفس المفاتيح الناقصة كلٌّ مع `?? SiteSettings::get('...')`:

```
'commune'         => $snapshot['store_commune']         ?? SiteSettings::get('store_commune'),
'postal_code'     => $snapshot['store_postal_code']    ?? SiteSettings::get('store_postal_code'),
'phone_secondary' => $snapshot['store_phone_secondary'] ?? SiteSettings::get('store_phone_secondary'),
'invoice_phone'   => $snapshot['invoice_phone']        ?? SiteSettings::get('invoice_phone'),
'invoice_email'   => $snapshot['invoice_email']        ?? SiteSettings::get('invoice_email'),
'invoice_address' => $snapshot['invoice_address']     ?? SiteSettings::get('invoice_address'),
```

> ملاحظة تسمية: استخدم `phone_secondary` (بلا بادئة `store_`) في `$storeInfo` لتطابق أسماء blade المستخدمة بشكل أوضح. أكِّد أن أي مفتاح جديد إضافي يستخدم نفس نمط `??`.

---

### 3. توسيع `LabelService::getLabelData()::$storeInfo`
**[MODIFY]** `app/Services/Documents/LabelService.php` — السطور 48-51.

أضف المفاتيح ليشمل الـ store للملصق (الحالي: name, logo فقط):

```
'phone'          => SiteSettings::get('store_phone'),
'phone_secondary'=> SiteSettings::get('store_phone_secondary'),
'address'        => SiteSettings::get('store_address'),
'wilaya'         => SiteSettings::get('store_wilaya'),
'commune'        => SiteSettings::get('store_commune'),
'website'        => SiteSettings::get('store_website'),
```

(الملصقات لا تحتاج صورة snapshot قانونية، تكتفي بـ live settings — لا يوجد `metadata` للـ labels.)

---

### 4. تحديث `invoice-header.blade.php` لإظهار المفاتيح الناقصة
**[MODIFY]** `resources/views/components/documents/invoice-header.blade.php` — السطور 21-28.

أضف أسطراً مع `@if` تحقق بوجود القيمة (نمط الـ `@if` الموجود) لكل من:
- `العنوان: address - wilaya - commune - postal_code` (نفس الصف، يمكن فصل `commune` و `postal_code` بشرطات داخل `@if`).
- `الهاتف الثانوي: phone_secondary` (سطر جديد بعد `الهاتف`).
- `البريد الإلكتروني للفاتورة: invoice_email` (سطر جديد بعد `website` أو تحته).
- `عنوان الفاتورة: invoice_address` (سطر جديد داخل قسم العنوان).
- `هاتف الفاتورة: invoice_phone` (سطر جديد ضمن store-details).

علّق `@props` (السطر 2) ليشمل المفاتيح الجديدة: `commune, postal_code, phone_secondary, invoice_phone, invoice_email, invoice_address`.

---

### 5. توحيد `SettingsController::$defaults` مع `SiteSettings::defaults()`
**[MODIFY]** `app/Http/Controllers/Admin/SettingsController.php` — خاصية `$defaults`.

- **قسم `store`:** عدّل القيم الافتراضية لتطابق `SiteSettings::defaults()`:
  - `store_name` → `'AN SHOP'`
  - `store_email` → `'contact@anshop.dz'`
  - `store_phone` → `'+213 550 00 00 00'`
  - `store_address` → `'الجزائر العاصمة'`
  - `store_description` → `'متجر إلكتروني متكامل يوفر لك تجربة تسوق فريدة'`
  - `store_logo`, `store_favicon` → `''` (تبقى كما هي).
  - **أزل** المفاتيح المتخلفة غير المستخدمة (`store_country`, `store_city`, `store_whatsapp`) — ليست ضمن Request للقسم `store` في المهمة 6.

- اترك `store_extended` و `invoice_info` و `printing` كما هي — هذه المجموعات صحيحة.

> توحيد القيم الافتراضية يمنع تعارض القيم عند `old($key, $settings['store'][$key])` في blade.

---

### 6. تصحيح قواعد `UpdateSettingsRequest` للقسم `store`
**[MODIFY]** `app/Http/Requests/Admin/UpdateSettingsRequest.php` — match case `'store'` (السطور 23-34).

- **أزل** المفاتيح المتخلفة: `store_country`, `store_city`, `store_whatsapp`.
- **أضف** (إن لزم لتفادي تجاهلها) المفاتيح الحقيقية المستعملة في `SiteSettings` ولا تظهر ضمن `store_extended`:
  - جميع الحقول الأساسية في قسم `store` (store_name/email/phone/address/description) موجودة — جيد.
- الطلب الحالي فعلياً لا يحتاج `store_wilaya` ضمن `store` لأنها في `store_extended` — اتركها هناك.
- أكِّد أن كل مفتاح في `SettingsController::$defaults['store']` مطابق لمفتاح في `rules()` بـ `nullable|string|max:...`.
  - `store_name` `nullable|string|max:255`
  - `store_email` `nullable|email|max:255`
  - `store_phone` `nullable|string|max:50`
  - `store_address` `nullable|string|max:500`
  - `store_description` `nullable|string|max:1000`
  - `store_logo_file` `nullable|image|mimes:jpeg,jpg,png,webp,svg|max:2048`
  - `store_favicon_file` `nullable|image|mimes:jpeg,jpg,png,webp,svg|max:2048`

> ملاحظة: لا تُضِف مفاتيح قسم `store_extended` هنا — هي مفصولة بـ match case `store_extended` (موجود وصحيح).

---

### 7. التحقق من `invoice-footer.blade.php` (سياق كامل)
**[INSPECT]** `resources/views/components/documents/invoice-footer.blade.php` — السطر 18.

يستخدم `$store['name']`, `$store['phone']` فقط. إذا احتجت معلومات قانونية في الـ footer (مثل `invoice_address`/`invoice_email`) إضافتها هنا بنفس نمط `@if`. إن كان الـ footer يكتفي بما فيه، اعتبره **out of scope**.

---

## Validation Plan

**تلقائية (فقط لـ semantics — لا تشغيل）：** تشغيل `php -l` على الملفات المعدّلة للتأكد من خلو syntax:
```bash
php -l app/Services/Documents/InvoiceService.php
php -l app/Services/Documents/LabelService.php
php -l app/Http/Controllers/Admin/SettingsController.php
php -l app/Http/Requests/Admin/UpdateSettingsRequest.php
```

**يدوية (browser):**
1. `/ar/admin/settings#store` → اضبط `store_name`, `store_address`, `store_phone` → احفظ.
2. `/ar/admin/settings#store_extended` → اضبط `store_wilaya=:الجزائر`, `store_commune=:باب الزوار`, `store_postal_code=:16000`, `store_website`, `store_phone_secondary` → احفظ.
3. `/ar/admin/settings#invoice_info` → اضبط `invoice_business_name`, `invoice_rc`, `invoice_nif`, `invoice_nis`, `invoice_phone`, `invoice_address`, `invoice_email`, `invoice_notes` → احفظ.
4. افتح طلبية → معاينة فاتورة (Print Preview) لقالب `classic`:
   - تظهر البلدية (`باب الزوار`) والرمز البريدي (`16000`) بجانب الولاية.
   - يظهر الهاتف الثانوي بجانب الهاتف الأساسي (سطر منفصل).
   - يظهر `invoice_address`, `invoice_email`, `invoice_phone` داخل store-details.
   - تظهر مفاتيح قانونية (rc/nif/nis) في نفس القسم.
5. اطلب ملف PDF — نفس العرض.
6. عاهد فاتورة قديمة في DB (موجودة قبل المهمة): افتح معاينتها → يجب أن تُجلب البلدية/الرمز البريدي من Live Settings (fallback) لا أن تكون فارغة.
7. كرّر لقالب `thermal` و `minimal` و `modern`:
   - `thermal` (فاتورة) حالياً يعرض `name/phone/wilaya` فقط — بعد المهمة 4 يجب أن يطابق `invoice-customer` وليس `invoice-header`. **تحقق:** العنوان سيتعامل معه `invoice-customer` (الذي تم تحديثه لعرض commune/wilaya/address). موافق.
8. افتح معاينة ملصق `classic` و `thermal`:
   - تظهر الولاية والدوّارة والعنوان بشكل واضح (Meal 3 من المهام السابقة مُطبقة على labels).
9. `/ar/admin/settings/printing` → اضبط القالب الافتراضي → احفظ → معاينة فاتورة بدون `template_id` في URL → يستخدم القالب الافتراضي.

**DB:**
```bash
php artisan tinker --execute="echo App\Support\SiteSettings::get('store_wilaya').PHP_EOL.App\Support\SiteSettings::get('invoice_nif');"
```
يجب أن تُرجع القيم المدخلة حديثاً (بعد `SiteSettings::flush()` الذي يستدعيه `SettingsController::update()`).

---

## Risks / Notes

- **أسماء مفاتيح `$storeInfo`** في blade يجب أن تتطابق بين `InvoiceService` و جميع المكونات. استعمل دائماً `?? SiteSettings::get('key')` مع نفس اسم العمود في snapshot (`store_*`/`invoice_*`).
- **القيم `?` (null) على Address** لا تغيير فيها — تشخيص المستخدم يؤكد أن الطلبية تُجلب من الطلبية (صحيح).
- **لا تُضِف migration** للفواتير القديمة — fallback الـ `??` كافٍ للـ validation (قرر المستخدم).
- بعد إصلاح `SettingsController::$defaults['store']`، تأكد أن لا blade آخر في `admin/settings/index.blade.php` يعتمد على قيمة `الخرطوم` (افتراضي قديم) ليظهر — استبدلته المهمة 5 بـ `الجزائر العاصمة`.
- لا تُعدِّل `PrintingSettingsController::update` (هو صحيح ويكتب بادئة `printing_`).
- **لا تُنشئ تعليقات** في الكود (طبقاً لقواعد المشروع).

---

## Files to Modify (final list)

| # | File | Action |
|---|---|---|
| 1 | `app/Services/Documents/InvoiceService.php` | توسيع `buildStoreSnapshot()` و `$storeInfo` |
| 2 | `app/Services/Documents/LabelService.php` | توسيع `$storeInfo` |
| 3 | `resources/views/components/documents/invoice-header.blade.php` | عرض المفاتيح الناقصة |
| 4 | `app/Http/Controllers/Admin/SettingsController.php` | توحيد `$defaults['store']` مع SiteSettings |
| 5 | `app/Http/Requests/Admin/UpdateSettingsRequest.php` | إزالة مفاتيح `store_country`/`store_city`/`store_whatsapp`، توحيد البقية |

Inspect-only (لا تعديل):
- `resources/views/components/documents/invoice-customer.blade.php` (محدّث مسبقاً)
- `resources/views/components/documents/label-customer.blade.php` (محدّث مسبقاً)
- `resources/views/documents/labels/{thermal,compact}.blade.php` (محدّثة مسبقاً)
- `resources/views/documents/invoices/thermal.blade.php` (محدّثة مسبقاً)

---

## Open Questions

لا (جميع القرارات الرئيسية تم تأكيدها مع المستخدم).
