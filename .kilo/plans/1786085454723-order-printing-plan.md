# Plan: طباعة فاتورة الشراء وملصق معلومات العميل

## الهدف
إضافة إمكانية طباعة فاتورة شراء PDF وملصق عميل PDF لكل طلب، من لوحة الإدارة.

## القرارات المُحدّدة

| القرار | القيمة |
|--------|--------|
| أنواع المستندات | فاتورة شراء (A4) + ملصق عميل (A5) |
| مكان الأزرار | صفحة تفاصيل الطلب + قائمة الطلبات (زر لكل طلب + bulk action) |
| محتوى الفاتورة | بيانات المتجر + رقم الطلب والتاريخ + جدول المنتجات + الإجمالي + بيانات العميل + عنوان الشحن + طريقة الدفع |
| حجم الملصق | A5 متوسط (بيانات العميل + ملخص الطلب) |
| مكتبة PDF | `barryvdh/laravel-dompdf` (مثبتة مسبقاً) |
| نمط التصميم | عصري بألوان المتجر (Material 3 / Stitch design system) |
| اتجاه | RTL كامل مع دعم العربية الصحيح |
| خط PDF | `IBM Plex Sans Arabic` (محمّل مسبقاً في app.css) مع `Arial` كاحتياطي |
| بنية التخطيط | جداول HTML عادية بدلاً من flexbox/grid لضمان توافق DomPDF |

## الملفات المتأثرة

### ملفات جديدة
1. `app/Http/Controllers/Admin/OrderPrintController.php` - جديد
   - `invoice(Order $order)` → يولد PDF الفاتورة (A4)
   - `customerLabel(Order $order)` → يولد PDF ملصق العميل (A5)
   - `bulkInvoice(Request $request)` → طباعة مجموعة فواتير
   - `bulkLabel(Request $request)` → طباعة مجموعة ملصقات

2. `resources/views/admin/orders/invoice-pdf.blade.php` - قالب HTML للفاتورة (A4) - **مطلوب إعادة تصميم**
3. `resources/views/admin/orders/customer-label-pdf.blade.php` - قالب HTML لملصق العميل (A5) - **مطلوب إعادة تصميم**
4. `resources/views/admin/orders/bulk-invoice-pdf.blade.php` - قالب فواتير مجمعة
5. `resources/views/admin/orders/bulk-label-pdf.blade.php` - قالب ملصقات مجمعة

### ملفات معدّلة
6. `app/Http/Controllers/Admin/OrderController.php`
   - تعديل `bulkAction`: إضافة منطق حقيقي لـ `print_labels` بدلاً من العداد فقط

7. `routes/web.php`
   - `GET /admin/orders/{order}/invoice` → `OrderPrintController@invoice`
   - `GET /admin/orders/{order}/label` → `OrderPrintController@customerLabel`
   - `POST /admin/orders/bulk-invoice` → `OrderPrintController@bulkInvoice`
   - `POST /admin/orders/bulk-label` → `OrderPrintController@bulkLabel`

8. `resources/views/admin/orders/show.blade.php`
   - إضافة أزرار "طباعة الفاتورة" و"طباعة ملصق العميل"

9. `resources/views/admin/orders/index.blade.php`
   - إضافة خيارات bulk action للطباعة الجماعية
   - إضافة أزرار صغيرة في actions لكل طلب

## متطلبات التصميم الجديدة

### مشاكل الحالية التي يجب إصلاحها
1. **RTL غير صحيح**: استخدام `text-align: left` و `margin-right: auto` في قالب RTL
2. **تخطيط غير متسق**: خلط بين inline styles و CSS classes
3. **خطوط عربية**: `DejaVu Sans` له دعم محدود للعربية - يجب استخدام `IBM Plex Sans Arabic`
4. **ألوان**: لا يستخدم ألوان المتجر - يجب استخدام ألوان Stitch/M3

### ألوان التصميم (من app.css)
```
--color-primary: #004ac6
--color-primary-container: #2563eb
--color-primary-fixed: #dbe1ff
--color-on-primary: #ffffff
--color-on-primary-fixed-variant: #003ea8
--color-on-surface: #191b23
--color-on-surface-variant: #434655
--color-outline-variant: #c3c6d7
--color-surface-container-lowest: #ffffff
--color-surface-container-low: #f3f3fe
--color-error: #ba1a1a
```

### قيود DomPDF
- لا يدعم `flexbox` أو `grid` بشكل موثوق - استخدم `table` للتخطيط
- لا يدعم CSS variables - استخدم قيم hex مباشرة
- لا يدعم `@import` أو `@font-face` معقد - استخدم خطوط النظام
- `isHtml5ParserEnabled: true` لدعم CSS أفضل
- `isRemoteEnabled: true` للصور الخارجية (إن وُجدت)

### مواصفات الفاتورة (A4, portrait)

#### الهيدر
- خلفية: `#004ac6` مع نص أبيض
- اليمين: اسم المتجر بخط كبير وعريض
- اليسار: رقم الفاتورة، التاريخ، الحالة، طريقة الدفع
- حدود سفلية: `#004ac6` بسماكة 3px

#### قسم العميل والشحن
- جدول بعمودين متساويين
- عمود العميل: الاسم، الهاتف، البريد
- عمود الشحن: الاسم، الهاتف، العنوان، المدينة، الولاية، الدولة
- حدود بين العمودين: `1px solid #c3c6d7`

#### جدول المنتجات
- رؤوس: خلفية `#dbe1ff` مع نص `#003ea8` عريض
- حدود: `1px solid #c3c6d7`
- الأعمدة: المنتج، SKU، الخيارات، الكمية، السعر، الإجمالي
- `text-align: right` للعربية، `text-align: left` للأرقام

#### ملخص التكاليف
- محاذاة لليسار (لأنه RTL، الإجمالي يظهر على اليسار)
- خط فاصل علوي للإجمالي النهائي: `2px solid #004ac6`
- خلفية الإجمالي: `#f3f3fe`
- نص الإجمالي: `#004ac6` عريض بحجم أكبر

#### الفوتر
- نص صغير `10px` لون `#737686`
- خط علوي متقطع `1px dashed #c3c6d7`

### مواصفات الملصق (A5, landscape)

#### الهيدر
- خلفية: `#004ac6` مع نص أبيض
- اليمين: اسم المتجر
- اليسار: رقم الطلب في框框 رمادي

#### بيانات العميل
- خلفية: `#f3f3fe`
- حدود: `1px solid #c3c6d7`
- الاسم الكامل، الهاتف، العنوان، المدينة، الولاية، الدولة

#### ملخص الطلب
- جدول صغير: المنتج | الكمية
- الإجمالي النهائي بخط عريض

#### الفوتر
- خط متقطع علوي
- تاريخ الطباعة | اسم المتجر

### قواعد CSS للـ RTL (PDF)
- `text-align: right` للعناوين والنصوص
- `text-align: left` فقط للأرقام
- الجداول: `border-collapse: collapse`
- لا تستخدم `margin-right: auto` - استخدم `margin-left: auto` في RTL
- الألوان: استخدم قيم hex مباشرة (لا CSS variables لأن DomPDF لا يدعمها)

### معالجة البيانات المفقودة
- `phone`: `$order->user?->phone ?? $order->guest_phone ?? $order->shippingAddress?->phone ?? '-'`
- `email`: `$order->user?->email ?? $order->guest_email ?? '-'`
- `name`: `$order->user?->name ?? $order->shippingAddress?->name ?? 'ضيف'`
- `address`: `$order->shippingAddress?->full_address ?? '-'`

## الأمان
- جميع routes في `admin` middleware group
- لا حاجة لصلاحيات إضافية

## Validation Plan
1. فتح الفاتورة → النص العربي محاذي لليمين، الألوان صحيحة
2. فتح الملصق → التخطيط صحيح في A5 landscape
3. طباعة PDF → الألوان تظهر بشكل صحيح
4. اختبار مع بيانات طويلة → لا انكسار التخطيط
5. اختبار bulk printing → صفحات متعددة صحيحة
6. اختبار مع ضيف (guest) → عرض البيانات البديلة

## ملاحظات تنفيذية
- DomPDF خيارات:
  - `setPaper('a4', 'portrait')` للفاتورة
  - `setPaper('a5', 'landscape')` للملصق
  - `setOption('isHtml5ParserEnabled', true)`
  - `setOption('isRemoteEnabled', true)`
  - `setOption('isFontSubsettingEnabled', true)`
  - `setOption('defaultFont', 'Arial')` كاحتياطي
- استخدم جداول HTML للتخطيط (ليس flexbox/grid)
- تجنب CSS variables - استخدم hex values مباشرة
- الخط: `font-family: 'IBM Plex Sans Arabic', Arial, sans-serif;`
- كل الـ styles في `<style>` داخل القالب (لا ملفات خارجية)
