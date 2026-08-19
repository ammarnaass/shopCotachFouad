# 🏗️ المعمارية — Modular Architecture

## نظرة عامة

المشروع يستخدم **Modular Architecture** (معمارية الموديولات)، حيث كل ميزة رئيسية هي موديول مستقل يحتوي على طبقاته الخاصة (Controllers، Models، Services).

## الخريطة الكاملة للموديولات

```
app/Modules/
├── Catalog/           # المنتجات والأقسام والعلامات
├── Users/             # المستخدمون والأدوار والصلاحيات
├── Cart/              # سلة التسوق
├── Checkout/          # إتمام الطلب
├── Orders/            # إدارة الطلبات وتتبعها
├── Payments/          # المدفوعات وبوابات الدفع
├── Shipping/          # الشحن (شركات، مناطق، طرق، بوالص)
├── Coupons/           # كوبونات الخصم والعروض
├── Wishlist/          # قائمة الأمنيات
├── Reviews/           # التقييمات والمراجعات
├── InstantBuy/        # الشراء الفوري بنقرة واحدة
├── CMS/               # الصفحات، السلايدر، الإعدادات، الفوتر، اللغات
└── Dashboard/         # لوحة الإدارة، التقارير، الإحصاءات
```

## بنية كل موديول

```php
app/Modules/OrdersModule/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   └── OrderController.php   // إدارة الطلبات (Admin Panel)
│   │   └── Api/
│   │       └── OrderController.php   // API endpoints
│   └── Requests/                     // Form Requests (Validation)
├── Models/
│   └── Order.php                     // Eloquent Model
├── Services/
│   └── OrderService.php              // Business Logic
└── routes/
    ├── web.php                       // مسارات الويب
    └── api.php                       // مسارات الـ API
```

## نقطة التحكم: ModulesServiceProvider

```php
// app/Providers/ModulesServiceProvider.php
// يُسجَّل في bootstrap/providers.php
// يقوم تلقائياً بـ:
//  - تحميل routes لكل موديول
//  - تحميل views لكل موديول
//  - تحميل migrations لكل موديول (اختياري)
```

## توافق عكسي 100% (Backward Compatibility)

للحفاظ على عمل الكود القديم، كل موديول لديه **Wrapper** في:

```php
// app/Models/Order.php → يمتد من App\Modules\Orders\Models\Order
// app/Services/OrderService.php → يمتد من App\Modules\Orders\Services\OrderService
```

بذلك أي كود قديم يستخدم `App\Models\Order` يظل يعمل بدون تغيير.

## تدفق الطلب (Request Flow)

```
Browser Request
       ↓
routes/web.php (أو routes/api.php)
       ↓
App\Modules\[Module]\Http\Controllers\[Controller]
       ↓
App\Modules\[Module]\Services\[Service]  ← Business Logic
       ↓
App\Modules\[Module]\Models\[Model]       ← Database Layer
       ↓
MySQL Database
```

## قواعد البناء

1. **لا تضع Business Logic في Controllers** — استخدم Services
2. **كل موديول مستقل** — لا تستدعِ Service موديول من داخل موديول آخر مباشرة، استخدم Events أو Interfaces
3. **الـ Models لها Wrappers** في `app/Models/` للتوافق العكسي
4. **الـ API Controllers** تُرجع JSON فقط باستخدام Resources

## إضافة موديول جديد

1. أنشئ المجلد: `app/Modules/NewModule/`
2. أنشئ الهيكل: `Http/Controllers/`, `Models/`, `Services/`
3. أضف مسارات في `routes/web.php` أو `routes/api.php`
4. أنشئ Wrapper في `app/Models/` (اختياري)
5. سجّل أي Provider في `bootstrap/providers.php`

## نمط الـ Service

```php
// مثال على استخدام Service داخل Controller
class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function add(Request $request): JsonResponse
    {
        $result = $this->cartService->addItem(
            productId: $request->product_id,
            quantity: $request->quantity,
            userId: auth()->id(),
        );
        
        return response()->json($result);
    }
}
```
