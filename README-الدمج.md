# موديول Shipping — دليل الدمج

## البنية
```
app/Modules/Shipping/
├── Models/           ShippingCompany, ShippingZone, ShippingMethod
├── Services/         ShippingZoneMatcher, ShippingCalculationService, ShippingQuote
├── Exceptions/       NoShippingZoneException, InvalidShippingConfigurationException
├── Http/
│   ├── Controllers/Admin/ShippingZoneController.php
│   ├── Controllers/Api/ShippingCalculationController.php
│   └── Requests/     Store/UpdateShippingZoneRequest
└── routes/           admin.php, api.php
```

## أهم قرار تصميمي: قاعدة "لا صفر صامت"
كل مسار بالنظام يمر إجباريًا عبر `ShippingCalculationService::calculate()`، وهذه الخدمة:
- **لا تُرجع أبدًا** `0` أو `null` كنتيجة "لم نجد شيء"
- **تُرجع** `ShippingQuote` بتكلفة صحيحة، أو **تطلق استثناء صريح** (`NoShippingZoneException` / `InvalidShippingConfigurationException`)
- الصفر الوحيد المسموح هو شحن مجاني **معلوم السبب** (تجاوز `free_shipping_threshold` صراحة) ويظهر بـ `isFreeShipping: true`

## خطوات الدمج بمشروعك

### 1. الملفات
انسخ محتوى هذا الأرشيف مباشرة فوق نفس المسارات بمشروعك (البنية متطابقة مع `app/Modules/...` الموجودة عندك).

### 2. تسجيل المسارات
بملف `app/Providers/ModulesServiceProvider.php` تأكد يتم تحميل:
```php
Route::middleware('web')->group(base_path('app/Modules/Shipping/routes/admin.php'));
Route::middleware('api')->group(base_path('app/Modules/Shipping/routes/api.php'));
```

### 3. Migration
```bash
php artisan migrate
```
(لو عندك جداول شحن قديمة بأسماء مختلفة، اكتب migration إضافي لنقل البيانات — لا تحذف القديمة قبل التأكد من نجاح النقل)

### 4. **إلزامي:** احذف المسارات القديمة المكررة
بعد التأكد أن الفرونت-إند (صفحة السلة + أي API مستقبلي) يستدعي `/api/shipping/calculate` فقط:
- احذف `CartApiController::calculateShipping()` القديمة (كانت ترجع `0` صامت — هذا أصل المشكلة الأصلية)
- احذف `ShippingApiController::calculate()` القديمة (كانت تقارن `country_id` رقمي بمصفوفة نصية)

**لا تُبقِ المسارين القديمين شغّالين بالتوازي مع الجديد** — وجود مسارين لنفس الوظيفة بمنطقين مختلفين هو بالضبط سبب الباگ الأصلي.

### 5. صلاحية الإدارة
الكنترولرات تفترض وجود Gate باسم `manage-shipping`. أضفه بـ `AuthServiceProvider` أو عدّل حسب نظام الصلاحيات عندك.

### 6. شغّل الاختبارات
```bash
php artisan test --filter=ShippingCalculationTest
```
هذه الاختبارات تُثبّت السلوك الصارم وتفشل تلقائيًا لو رجع أحد لكتابة `return 0;` بالمستقبل.

## نقاط يجب تعديلها يدويًا حسب مشروعك الفعلي
- اسم موديل `User` ومسار الـ Factory بالاختبار الأخير (`App\Modules\Users\Models\User`) — عدّله حسب مسارك الحقيقي
- Gate الصلاحيات `manage-shipping`
- لو عندك حقل وزن/كمية بالسلة بمكان مختلف، تأكد `weightKg` بـ `ShippingCalculationController` تُقرأ من نفس المصدر
