# Plan: إصلاح مشكلة طباعة الفواتير والملصقات

## المشكلة

صفحة معاينة الطباعة (print preview) تظهر **فارغة** — لا تعرض محتوى الفاتورة ولا الملصق، ولا يظهر زر تحميل PDF، والعنوان يظهر "معاينة الطباعة" بدلاً من عنوان المستند.

**ملف واحد فقط يحتاج تعديل**: `resources/views/documents/layouts/print.blade.php`

## السبب الجذري

الملف `print.blade.php` يستخدم Blade `@yield()` (sections) في 4 أماكن:
- `@yield('title', 'مستند')` — سطر 6
- `@yield('doc_title', 'معاينة الطباعة')` — سطر 97
- `@yield('pdf_link')` — سطر 103
- `@yield('content')` — سطر 109

لكن **لا يوجد أي قالب فاتورة أو ملصق** يستخدم `@extends()` أو `@section()`. القوالب كلها standalone views تoutput HTML مباشرة:
- `documents/invoices/classic.blade.php`
- `documents/invoices/thermal.blade.php`
- `documents/invoices/modern.blade.php`
- `documents/invoices/minimal.blade.php`
- `documents/labels/classic.blade.php`
- `documents/labels/thermal.blade.php`
- `documents/labels/compact.blade.php`

الـ Controller يمرر البيانات كمتغيرات عادية:
```php
return view('documents.layouts.print', array_merge($data, [
    'doc_title' => 'فاتورة - ' . $invoice->invoice_number,
    'pdf_link'  => '<a href="..." class="btn-pdf">📥 تحميل PDF</a>',
]))->with('view', $view);
```

لكن `@yield()` لا تقرأ المتغيرات — تقرأ `@section` blocks فقط.

## الحل

تعديل 4 أسطر في `print.blade.php`:

| السطر | القديم | الجديد |
|-------|--------|--------|
| 6 | `@yield('title', 'مستند')` | `{{ $doc_title ?? 'مستند' }}` |
| 97 | `@yield('doc_title', 'معاينة الطباعة')` | `{{ $doc_title ?? 'معاينة الطباعة' }}` |
| 103 | `@yield('pdf_link')` | `{!! $pdf_link ?? '' !!}` |
| 109 | `@yield('content')` | `@if(!empty($view)) @include($view) @endif` |

### لماذا يعمل:
1. `@include($view)` يستدعي القالب ويرث جميع المتغيرات (order, invoice, template, store, customer, items, payment, currencySymbol)
2. `{{ $doc_title }}` يستخدم المتغير الممرّ من Controller مباشرة
3. `{!! $pdf_link !!}` يعرض رابط HTML كـ raw output

## لا يوجد تغيير آخر مطلوب

- `bulk-invoices.blade.php` و `bulk-labels.blade.php` تستخدم `@include($view, $data)` بشكل صحيح بالفعل
- `PdfService.php` يعمل بشكل صحيح لتحميل PDF
- القوالب المكونة (components) تعمل بشكل صحيح
- Routes والـ Controllers تعمل بشكل صحيح

## التحقق بعد الإصلاح

1. فتح صفحة تفاصيل طلب → الضغط على "معاينة / طباعة" للفاتورة → يجب أن تظهر الفاتورة مع شريط التحكم
2. الضغط على "تحميل PDF" → يجب تحميل ملف PDF
3. الضغط على "طباعة" → يجب فتح نافذة الطباعة
4. تكرار نفس الاختبار للملصق
5. اختبار الطباعة المجمعة (bulk) من صفحة قائمة الطلبات
