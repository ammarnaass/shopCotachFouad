# إعادة تصميم بطاقة المنتج على شكل نموذج WooCommerce/Astra

## السياق
المشروع Laravel/Blade + Tailwind. البطاقة الحالية في `resources/views/frontend/partials/product-card.blade.php` تستخدم `<article>` بتصميم Material/Tailwind (خلفية بيضاء، شارات بألوان، زر "اشتري الآن" يوجه لصفحة المنتج، زر قلب للمفضلة).

المطلوب: إعادة تصميم البطاقة لتشبه بصرياً نموذج WooCommerce/Astra المرفق (الذي يعرض: صورة مربعة + شارة "تخفيضات" أو نسبة الخصم فوق الصورة + زر أيقونة سلة على الصورة + أسفلها عنوان المنتج + سعر مشطوب + زر "اضغط هنا للطلب").

**نطاق التغيير**: تعديل ملف واحد فقط (`product-card.blade.php`). لا AJAX للسلة؛ زر "اضغط هنا للطلب" يوجّه لصفحة المنتج (السلوك الحالي).

## نقطة فنية مهمة (تصحيح)
البطاقة الحالية تستخدم `$product->compare_price` للحساب، لكن نموذج `Product` يستخدم `sale_price` (`app/Models/Catalog/Product.php:128`) وله accessor جاهز:
- `final_price` → السعر النهائي بعد الخصم
- `discountPercent` → نسبة الخصم كعدد صحيح (Product.php:141)
- `primaryImage()` → الصورة الرئيسية (Product.php:70)

الخطة تستخدم هذه الـ accessors بدل الحساب اليدوي الخاطئ.

## القرارات
1. **التقنية**: Laravel/Blade + Tailwind (لا نسخ لـ PHP/CSS الخاص بـ Astra).
2. **الأزرار**: زر واحد فقط "اضغط هنا للطلب" يوجه لـ `route('shop.show')` (نفس السلوك الحالي). أيقونة سلة صغيرة على الصورة هي مجرد رابط لصفحة المنتج (ليست إضافة AJAX).
3. **الشارات**: عرض شارة `-X%` (نسبة الخصم) مستوحاة من اختيار المستخدم، مع الإبقاء على شارات New/نفاد/منخفض كاختيار بصري اختياري (انظر المهمة 3).
4. **المفضلة**: تُزال من البطاقة (النموذج الأصلي لا يحتويها) إلا إذا طلب المستخدم الإبقاء عليها — افتراضياً تُزال.
5. **نص الزر**: "اضغط هنا للطلب" (مطابق للنموذج) — يضاف كمفتاح ترجمة جديد.
6. **اتجاه RTL**: المشروع عربي RTL؛ الأيقونة/الشارة تُوضع على اليسار الأعلى منطقياً مع `start-2/end-2` الـ Tailwind المنطقي.

## المهام المرتبة

### 1) إضافة مفاتيح الترجمة الجديدة
إضافة مفاتيح لزر الطلب وشارة "تخفيضات" إلى ملف اللغة (تحقّق من موقع ملفات `lang/` أولاً). المفاتيح المقترحة:
- `shop.click_to_order` = "اضغط هنا للطلب"
- `shop.sale_badge` = "تخفيضات!" (مستوحاة من نموذج Astra، إن رغب المستخدم بالنص الحرفي)

> ملاحظة: تنفيذي المسؤول: ابحث عن ملف الترجمات العربي (مفتاح `shop.buy_now` يوجد بالفعل) وأضف المفاتيح بجانبه بنفس الصيغة.

### 2) إعادة كتابة `product-card.blade.php`
إعادة بناء البطاقة بأخذ/ترك العناصر من النموذج، مع الحفاظ على روابط التوجيه والبيانات الصحيحة:

**البنية المستهدفة** (Tailwind + Blade):
```blade
@php
    $image = $product->primaryImage ?? $product->images->first();
    $finalPrice = $product->final_price;
    $hasDiscount = $product->sale_price && (float)$product->sale_price > 0 && (float)$product->sale_price < (float)$product->price;
    $discount = $hasDiscount ? $product->discountPercent : 0;
    $isNew = $product->created_at && $product->created_at->gt(now()->subDays(7));
    $isLowStock = $product->stock > 0 && $product->stock <= 5;
    $isOutOfStock = $product->stock <= 0;
    $symbol = $symbol ?? currentCurrencySymbol();
    $productUrl = route('shop.show', ['slug' => $product->slug ?? $product->id]);
@endphp

<article class="group relative bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition overflow-hidden flex flex-col">
    {{-- صورة المنتج (مربعة بنسبة 1:1) --}}
    <a href="{{ $productUrl }}" class="block relative pt-[100%] aspect-square overflow-hidden">
        <img src="..." alt="..." loading="lazy"
             class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
        @if(!$image) placeholder @endif

        {{-- شارة التخفيض (نسبة أو نص) أعلى الصورة --}}
        @if($hasDiscount)
            <span class="absolute top-2 start-2 bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-full shadow">
                -{{ $discount }}%
            </span>
        @endif

        {{-- شارات New/نفاد/منخفض (اختياري) --}}
        @if($isOutOfStock)
            <span class="badge ...">{{ __t('product.out_of_stock_label') }}</span>
        @elseif($isLowStock)
            <span class="badge ...">{{ __t('product.remaining', ['count' => $product->stock]) }}</span>
        @endif

        {{-- أيقونة سلة صغيرة (رابط فقط) --}}
        <span class="absolute bottom-2 end-2 w-9 h-9 rounded-full bg-white/90 shadow flex items-center justify-center ...">
            <span class="material-symbols-outlined text-base">shopping_cart</span>
        </span>
    </a>

    {{-- أسفل البطاقة: عنوان + سعر مشطوب + زر الطلب --}}
    <div class="p-3 flex flex-col flex-1">
        <h3 class="font-medium text-sm text-gray-800 line-clamp-2 min-h-[2.5rem] mb-2">
            {{ $product->name }}
        </h3>

        <div class="flex items-center gap-2 mb-3">
            @if($hasDiscount)
                <span class="text-lg text-gray-400 line-through">
                    {{ $symbol }}{{ number_format(convertPrice($product->price), 2) }}
                </span>
            @endif
            <span class="text-lg font-extrabold text-primary">
                {{ $symbol }}{{ number_format(convertPrice($finalPrice), 2) }}
            </span>
        </div>

        <a href="{{ $productUrl }}"
           class="w-full h-11 rounded-xl {{ $isOutOfStock ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-brand-50 text-brand-600 hover:bg-brand-500 hover:text-white' }} flex items-center justify-center gap-2 font-medium transition shadow-sm"
           @if($isOutOfStock) aria-disabled="true" @endif>
            @if($isOutOfStock)
                {{ __t('product.out_of_stock_label') }}
            @else
                <span class="material-symbols-outlined text-sm">shopping_cart_checkout</span>
                {{ __t('shop.click_to_order', [], 'اضغط هنا للطلب') }}
            @endif
        </a>
    </div>
</article>
```

ملاحظات مهمة أثناء التنفيذ:
- صورة placeholder عند غياب الصورة كما في البطاقة الحالية.
- استخدام `convertPrice()` و`currentCurrencySymbol()` الموجودين بالفعل (كما في البطاقة الحالية).
- إذا كان `$hasDiscount` يعرض السعر المشطوب هو `price` والسعر النهائي هو `finalPrice` (= `sale_price` إن وُجد).
- الـ badge classes (`badge-*`) معرّفة في المشروع؛ استخدمها للشارات الثانوية بمقياس أصغر لتوافق النموذج.
- نطق `__t(key, [], $fallback)` — تأكد من توقيع الدالة قبل الاستخدام (تحقق من `app/Services/TranslationService.php`).

### 3) الحفاظ على الشارات/الميزات الاختيارية
- البطاقة الحالية تعرض شارة "جديد" و"متبقي X قطع" و"نفد المخزون". الإبقاء عليها بأسلوب النموذج (شارة صغيرة على الصورة) بدل الإزالة التامة، لأنها قيمة وظيفية موجودة.
- شارة الخصم تظهر بنسبة `-X%` (مطابقة لاخيار المستخدم). لا تُعرض شارة "تخفيضات!" الإضافية لتجنّب التكرار.

### 4) التحقق/vndТак
- لا تعديل لأحداث JS أو Alpine (الزر مجرد `<a href>`).
- لا تغيير في شبكة العرض (`grid grid-cols-2 ...`) في الصفحات الأم — البطاقة يجب أن تت adapting لأي شبكة دون تكسير.
- تأكد من رمز `route('shop.show', ...)` سليم، و`convertPrice` و`number_format` لا تُسبّب أخطاء عند غياب `sale_price`.
- بعد التعديل: شغّل `php artisan view:clear` وافتح صفحة `/shop` أو الصفحة الرئيسية للتأكد بصرياً.

## التحقق والقبول
1. `php artisan view:clear` لتفريغ الكاش.
2. فتح `/shop` و`/` (المنتجات المميزة + الأحدث) والتأكد بصرياً من:
   - مربع الصورة 1:1، شارة خصم مثبتة على الصورة، أيقونة سلة على الصورة.
   - عنوان المنتج (سطرين كحد أقصى).
   - سعر مشطوب + سعر نهائي واضحان.
   - زر "اضغط هنا للطلب" موجه لصفحة المنتج.
3. فتح المنتجات بسعر بدون خصم: لا يظهر سعر مشطوب، الزر يعمل.
4. منتج نفد: الزر معطّل ونصه "نفد المخزون".
5. RTL: الشارات والأيقونة في الجهة الصحيحة (`start/end` Tailwind).

## خارج النطاق
- إضافة AJAX لزر السلة.
- إضافة زر مفضلة للبطاقة (أُزيل لمطابقة النموذج؛ يمكن إعادته بطلب منفصل).
- نسخ بنية `<li class="ast-article-single ...">` الحرفية أو كلاسات Astra.
- تعديل صفحات العرض الأم (`index.blade.php`, `category.blade.php`, `home.blade.php`).

## ملفات مفتوحة
- `resources/views/frontend/partials/product-card.blade.php` (إعادة كتابة كاملة).
- ملف اللغة العربي (إضافة مفتاح/مفتاحين) — يحدد موقعها المنفّذ عند قراءة `lang/`.
