# خطة إصلاح مشكلة تحميل صفحات الموقع

## المشكلة

صفحات الموقع لا تُحمّل (timeout/توقف) بسبب عدة مشاكل متسلسلة.

## الأسباب المحددة

### P0 — السبب الرئيسي: `Cache::store('redis')` مع Redis متوقف

| الملف | السطر | الكود الحالي |
|-------|-------|-------------|
| `app\Http\Controllers\HomeController.php` | 37 | `Cache::store('redis')->remember(...)` |
| `app\Http\Controllers\Admin\SliderController.php` | 180 | `Cache::store('redis')->forget(...)` |

**لماذا يسبب التوقف:** الكود يستخدم `Cache::store('redis')` بشكل صريح، لكن خادم Redis غير شغال على المنفذ 6379. مكتبة `predis` تحاول الاتصال مع محاولات إعادة (3 محاولات بتأخير 100-1000ms). الصفحة الرئيسية (`HomeController::index`) تستدعي هذا في كل تحميل، مما يسبب توقف 3-10 ثوانٍ لكل زيارة.

**الملف `.env`** يحدد `CACHE_STORE=database` بشكل صحيح، لكن الكود يتجاوز هذا الإعداد.

**الإصلاح:** استبدال `Cache::store('redis')` بـ `Cache::remember()` و `Cache::forget()` (تستخدم cache driver الافتراضي من `.env`).

---

### P1 — خطأ `callAction` في Controller الأساسي يكسر Route Model Binding

**الملف:** `app\Http\Controllers\Controller.php` (سطر 7-11)

```php
public function callAction($method, $parameters)
{
    unset($parameters['locale']);
    return $this->{$method}(...array_values($parameters));
}
```

**المشكلة:** هذه الطريقة المخصصة تزيل `locale` من مصفوفة المعاملات ثم تستدعي الدالة بـ `array_values()`. هذا يكسر Route Model Binding في بعض الحالات حيث يتم تمرير `Illuminate\Http\Request` بدلاً من النموذج المتوقع.

**الأخطاء المسجلة في `laravel.log`:**
- `LabelTemplateController::preview()`: Argument #1 must be `LabelTemplate`, `Illuminate\Http\Request` given
- `OrderPrintController::invoice()`: Argument #1 must be `Order`, `Illuminate\Http\Request` given

**الحل:** إزالة طريقة `callAction` المخصصة. Laravel 11 يتعامل مع إزالة `{locale?}` من المعاملات تلقائياً عبر Route Model Binding.

---

### P1 — وسم `</a>` مفقود في admin layout

**الملف:** `resources\views\admin\layout.blade.php` (سطر 126-132)

وسم `<a>` المفتوح في سطر 126 لـ `admin.instant-buy.settings` لا يُغلق قبل `<div>` التالي في سطر 132. هذا يكسر هيكل HTML في الشريط الجانبي للإدارة.

**الإصلاح:** إضافة `</a>` قبل سطر 132.

---

## خطة التنفيذ

### الخطوة 1: إصلاح HomeController.php

**الملف:** `app\Http\Controllers\HomeController.php`

**السطر 37** — استبدال:
```php
$slides = Cache::store('redis')->remember('home.active_sliders', now()->addMinutes(10), function () {
```
بـ:
```php
$slides = Cache::remember('home.active_sliders', now()->addMinutes(10), function () {
```

---

### الخطوة 2: إصلاح SliderController.php

**الملف:** `app\Http\Controllers\Admin\SliderController.php`

**السطر 180** — استبدال:
```php
Cache::store('redis')->forget(self::CACHE_KEY);
```
بـ:
```php
Cache::forget(self::CACHE_KEY);
```

---

### الخطوة 3: إصلاح Controller.php الأساسي

**الملف:** `app\Http\Controllers\Controller.php`

إزالة طريقة `callAction` المخصصة بالكامل. الاستبدال:

```php
<?php

namespace App\Http\Controllers;

abstract class Controller
{
}
```

> **ملاحظة:** Laravel 11 لا يحتاج `callAction` override. الإعداد `{locale?}` في `routes/web.php` يُعالج تلقائياً عبر `SetLocale` middleware و `app('url')->defaults(['locale' => $currentLocale])`.

---

### الخطوة 4: إصلاح وسم `</a>` المفقود

**الملف:** `resources\views\admin\layout.blade.php`

**بعد سطر 131** (بعد `@endif`) — إضافة `</a>`:

```blade
                @endif
            </a>  {{-- أضف هذا السطر --}}
            <div class="pt-4 pb-1 px-4 text-xs text-surface-variant/50 font-semibold">المستندات والطباعة</div>
```

---

## التحقق

بعد تطبيق التغييرات:

1. أعد تشغيل خادم التطوير:
   ```bash
   C:\php83\php.exe artisan serve --host=127.0.0.1 --port=8000
   ```

2. افتح المتصفح على `http://127.0.0.1:8000/ar`
   - يجب أن تحمل الصفحة الرئيسية في أقل من ثانية واحدة
   - يجب أن تظهر المنتجات والتصنيفات والـ slides

3. افتح لوحة الإدارة على `http://127.0.0.1:8000/ar/admin`
   - يجب أن يعمل الشريط الجانبي بشكل صحيح
   - يجب أن تعمل روابط القوالب والمعاينة

4. تحقق من السجلات:
   ```bash
   # امسح السجلات القديمة
   echo "" > storage\logs\laravel.log
   ```
   ثم تصفّح الصفحات وتأكد من عدم ظهور أخطاء جديدة.

---

## المخاطر

| المخاطر | التوضيح |
|---------|---------|
| `callAction` removal | إذا كانت أي controller أخرى تعتمد على هذا الـ override، فقد تحتاج تعديلاً. لكن المراجعة تظهر أن Laravel 11 يتعامل مع `{locale?}` بشكل أصلي. |
| Cache driver | بعد الإصلاح، سيُستخدم `CACHE_STORE=database` (SQLite/MySQL). إذا كان هناك ضغط كبير على قاعدة البيانات، قد تحتاج لاحقاً تثبيت Redis. |
| `$stats` variable | View Composer في `AppServiceProvider` (سطر 115) يشارك `$stats` مع `admin.layout`، لذا لا مشكلة هنا. |
