# خطة إعادة هيكلة المعمارية — تطبيق Laravel

## المبدأ

تطبيق MVVM + Repository + Service من دليل Flutter على Laravel. كل مرحلة مستقلة وقابلة للتحقق.

| Flutter | Laravel |
|---|---|
| View | Blade Template |
| ViewModel | Controller |
| Repository | Repository (Interface + Eloquent) |
| Service | Service |
| Use Case | Action Class (`app()` helper) |
| Domain Model | Eloquent Model |

## قرارات تصميم

| القرار | الاختيار |
|---|---|
| Action resolution | `app(CreateOrder::class)->execute(...)` |
| Model namespacing | تضمين — نقل 40 model إلى مجلدات فرعية |
| Testing | كود جديد فقط (Services + Actions) |
| DI في Controllers | Constructor injection |
| Form Requests | لكل endpoint رئيسي |
| Route model binding | يعتمد على FQCN في type-hint — لا يتأثر إذا حدّثنا الـ imports |

## ملخص تأثير Model namespacing

| الفئة | ملفات متأثرة | ملاحظة |
|---|---|---|
| Controllers (Admin) | ~18 model type-hints | يعتمد على `use` statement — آمن إذا حدّثناه |
| Controllers (Web) | 0 | يستخدم `int` type-hints |
| Services | ~12 ملف | `OrderService`, `CartService`, etc. |
| Events/Listeners | 2 ملف | `OrderStatusChanged`, `SendOrderStatusNotification` |
| View Composers | 1 ملف (6 refs) | `AppServiceProvider` |
| Seeders | 6 ملف | 14 model refs |
| Factories | 1 ملف | `User` |
| Commands | 1 ملف | `Role`, `User` |
| Blade views | 7 ملف (11 refs) | inline `\App\Models\*` |
| Config | 1 ملف | `config/auth.php` → `User` |
| **المجموع** | **~55 ملف** | **لا توجد polymorphic refs** |

## الهيكل المستهدف

```
app/
├── Actions/
│   ├── Order/
│   │   ├── CreateOrder.php
│   │   ├── CreateInstantOrder.php
│   │   └── BulkOrderAction.php
│   ├── Product/
│   │   ├── CreateProduct.php
│   │   ├── UpdateProduct.php
│   │   ├── SyncProductOptions.php
│   │   └── SyncShippingRules.php
│   └── Settings/
│       └── UpdateSettings.php
├── Data/
│   ├── DTOs/
│   │   └── OrderPreview.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   ├── OrderRepositoryInterface.php
│   │   │   ├── ProductRepositoryInterface.php
│   │   │   ├── CouponRepositoryInterface.php
│   │   │   └── SettingsRepositoryInterface.php
│   │   └── Eloquent/
│   │       ├── BaseEloquentRepository.php
│   │       ├── EloquentOrderRepository.php
│   │       ├── EloquentProductRepository.php
│   │       ├── EloquentCouponRepository.php
│   │       └── EloquentSettingsRepository.php
│   └── Resources/
│       ├── OrderResource.php
│       └── ProductResource.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Api/
│   │   └── Web/
│   └── Requests/
│       ├── Admin/
│       └── Web/
├── Models/
│   ├── Catalog/       # Product, Category, Tag, Product*
│   ├── Order/         # Order, OrderItem, Payment, etc.
│   ├── Cart/          # Cart, CartItem, Wishlist
│   ├── Shipping/      # Shipping*
│   ├── User/          # User, Role, Permission
│   ├── Content/       # Page, Slide, Review, etc.
│   ├── Settings/      # Setting, Language, Translation
│   └── InstantBuy/    # InstantBuyOrder, InstantBuySetting
├── Services/
│   ├── CartService.php
│   ├── OrderService.php
│   ├── ProductService.php
│   ├── PricingService.php          # NEW
│   ├── CouponService.php           # NEW
│   ├── ImageUploadService.php      # NEW
│   ├── EnvironmentService.php      # NEW
│   ├── SettingsService.php         # NEW
│   ├── DynamicShippingService.php
│   ├── ShippingCalculator.php
│   └── TranslationService.php
└── Support/
    └── SiteSettings.php
```

## واجهات Repositories

### OrderRepositoryInterface
```php
interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;
    public function findWithRelations(int $id, array $relations = []): ?Order;
    public function query(): Builder;
    public function create(array $data): Order;
    public function getStats(): array;
    public function bulkUpdateStatus(array $ids, string $status): int;
}
```

### ProductRepositoryInterface
```php
interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function query(): Builder;
    public function create(array $data): Product;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(array $filters): Builder;
}
```

### CouponRepositoryInterface
```php
interface CouponRepositoryInterface
{
    public function findByCode(string $code): ?Coupon;
    public function incrementUsage(Coupon $coupon): void;
}
```

### SettingsRepositoryInterface
```php
interface SettingsRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, string $group): void;
    public function getGroup(string $group): array;
    public function setGroup(string $group, array $data): void;
}
```

## تحديث OrderService

**الحالي:** `OrderService` يحقن `CartService` ويستخدم 7 Eloquent models مباشرة.

**المستقبل:** `OrderService` يحقن `OrderRepositoryInterface` + `CouponRepositoryInterface`:

```php
class OrderService
{
    public function __construct(
        private CartService $cartService,
        private OrderRepositoryInterface $orders,
        private CouponRepositoryInterface $coupons,
    ) {}

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cart = $this->cartService->getCart();
            // ... pricing logic (يبقى في Service)
            $order = $this->orders->create($orderData);
            // ... stock decrement, coupon increment
            $this->coupons->incrementUsage($cart->coupon);
            return $order;
        });
    }
}
```

**ملاحظة:** `calculateShipping()` و `calculateCodFee()` يبقى في `OrderService` لأنهما pure business logic.

## Actions — التفاصيل

### CreateInstantOrder
```php
class CreateInstantOrder
{
    public function __construct(
        private PricingService $pricing,
        private CouponService $coupons,
        private OrderService $orderService,
    ) {}

    public function execute(array $data, Product $product, ?UploadedFile $customFile = null): Order
    {
        // 1. حساب السعر (base + options + custom fields)
        $price = $this->pricing->calculateProductPrice($product, $data['options'] ?? [], $data['custom_text'] ?? null);
        // 2. حساب الشحن
        $shipping = $this->orderService->calculateShipping(...);
        // 3. تطبيق الكوبون
        $discount = $this->coupons->apply($data['coupon_code'] ?? null, $price->subtotal);
        // 4. إنشاء الطلب في transaction
        // 5. return Order
    }
}
```

### CreateProduct
```php
class CreateProduct
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private ImageUploadService $images,
    ) {}

    public function execute(array $data, array $images, array $options, array $shippingRules): Product
    {
        $product = $this->products->create($data);
        $this->images->uploadMultiple($product, $images);
        // sync options, shipping rules
        return $product;
    }
}
```

## Form Requests

| Request | Controller | القواعد الرئيسية |
|---|---|---|
| `StoreProductRequest` | ProductController@store | 40+ قواعد (اسم، سعر، مخزون، صور، خيارات) |
| `UpdateProductRequest` | ProductController@update | مثل Store مع `sometimes` |
| `StoreOrderNoteRequest` | OrderController@addNote | `note: required|string|max:1000` |
| `UpdateOrderStatusRequest` | OrderController@updateStatus | `status: required|in:pending,processing,...` |
| `BulkOrderActionRequest` | OrderController@bulkAction | `order_ids: required|array`, `action: required|in:...` |
| `UpdateSettingsRequest` | SettingsController@update | dinamik — يعتمد على group |
| `InstantBuyRequest` | InstantBuyController@submit | conditional rules حسب instant_show_* settings |

## خطة التنفيذ

### المرحلة 1: البنية التحتية
- [ ] إنشاء `Data/Repositories/Contracts/` — 4 interfaces
- [ ] إنشاء `Data/Repositories/Eloquent/BaseEloquentRepository.php`
- [ ] إنشاء 4 Eloquent Repositories
- [ ] إنشاء `Providers/RepositoryServiceProvider.php`
- [ ] تسجيل في `config/app.php`
- **تحقق:** `php artisan test`

### المرحلة 2: Services جديدة
- [ ] `Services/ImageUploadService.php`
- [ ] `Services/PricingService.php`
- [ ] `Services/CouponService.php`
- [ ] `Services/EnvironmentService.php`
- [ ] `Services/SettingsService.php`
- [ ] تحديث `OrderService` → repository injection
- **تحقق:** `php artisan test`

### المرحلة 3: Actions
- [ ] `Actions/Order/CreateOrder.php`
- [ ] `Actions/Order/CreateInstantOrder.php`
- [ ] `Actions/Order/BulkOrderAction.php`
- [ ] `Actions/Product/CreateProduct.php`
- [ ] `Actions/Product/UpdateProduct.php`
- [ ] `Actions/Product/SyncProductOptions.php`
- [ ] `Actions/Settings/UpdateSettings.php`
- **تحقق:** `php artisan test`

### المرحلة 4: Form Requests
- [ ] `Requests/Admin/StoreProductRequest.php`
- [ ] `Requests/Admin/UpdateProductRequest.php`
- [ ] `Requests/Admin/StoreOrderNoteRequest.php`
- [ ] `Requests/Admin/UpdateOrderStatusRequest.php`
- [ ] `Requests/Admin/BulkOrderActionRequest.php`
- [ ] `Requests/Admin/UpdateSettingsRequest.php`
- [ ] `Requests/Web/InstantBuyRequest.php`
- **تحقق:** `php artisan test`

### المرحلة 5: Controllers
- [ ] ProductController (488 → ~100)
- [ ] InstantBuyController (577 → ~80)
- [ ] SettingsController (302 → ~120)
- [ ] CustomizeController (264 → ~100)
- [ ] Admin\OrderController (183 → ~80)
- [ ] CheckoutController (109 → ~60)
- **تحقق:** `php artisan test` + اختبار في المتصفح

### المرحلة 6: Model Namespacing
**التنفيذ حسب تأثير الملفات (الأقل أولاً):**

- [ ] 6.1 Cart (4 ملفات → ~7 imports)
  - Cart, CartItem, Wishlist → `Models/Cart/`
- [ ] 6.2 InstantBuy (3 ملفات → ~5 imports)
  - InstantBuyOrder, InstantBuySetting → `Models/InstantBuy/`
- [ ] 6.3 Content (5 ملفات → ~15 imports)
  - Page, Slide, Review, NewsletterSubscriber, Notification → `Models/Content/`
- [ ] 6.4 Settings (4 ملفات → ~10 imports)
  - Setting, Language, LanguageSetting, Translation → `Models/Settings/`
- [ ] 6.5 Shipping (7 ملفات → ~20 imports)
  - Shipping* → `Models/Shipping/`
- [ ] 6.6 Order (7 ملفات → ~30 imports + 7 `::class` refs)
  - Order, OrderItem, OrderNote, OrderStatusHistory, Payment, PaymentMethod → `Models/Order/`
- [ ] 6.7 User (3 ملفات → ~35 imports + 11 `::class` refs)
  - User, Role, Permission → `Models/User/`
- [ ] 6.8 Catalog (11 ملفات → ~40 imports + 11 `::class` refs)
  - Product, Category, Tag, Product* → `Models/Catalog/`
- [ ] تحديث `config/auth.php` → `User::class`
- [ ] تحديث `AppServiceProvider` view composers (6 refs)
- [ ] تحديث `Events/OrderStatusChanged.php` + `Listeners/SendOrderStatusNotification.php`
- [ ] تحديث `CreateAdminCommand.php`
- [ ] تحديث `UserFactory.php`
- [ ] تحديث 6 seeders
- [ ] تحديث 7 blade views (11 inline refs)
- **تحقق:** `php artisan test` + `php artisan route:list` + اختبار كامل

### المرحلة 7: Tests
- [ ] Unit: `PricingServiceTest`
- [ ] Unit: `CouponServiceTest`
- [ ] Unit: `ImageUploadServiceTest`
- [ ] Feature: `CreateInstantOrderTest`
- [ ] Feature: `CreateProductTest`
- **تحقق:** `php artisan test`

## المخاطر

| المخاطر | التخفيف |
|---|---|
| Route model binding يكسر | يعتمد على `use` statement في Controller — آمن إذا حدّثناه |
| `config/auth.php` → `User` | تحديث واحد + `php artisan config:clear` |
| Blade inline refs (11) | فحص شامل لكل blade بعد كل نقل |
| View composers (6 refs) | تحديث `AppServiceProvider` بعد كل نقل |
| `::class` refs في relationships | ~40 ref — فحص grep شامل |
| Existing tests تكسر | لا توجد tests实质ية — فقط `PageTest` (27 methods) |
| `services.php` cache | لا يوجد model refs — آمن |
