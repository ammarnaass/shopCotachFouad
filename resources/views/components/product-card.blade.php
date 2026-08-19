@props([
    'product' => null,
    'symbol' => null,
])

@php
    if (!$product) {
        return;
    }

    // Convert object or array properties safely
    $id = is_array($product) ? ($product['id'] ?? 0) : ($product->id ?? 0);
    $name = is_array($product) ? ($product['name'] ?? '') : ($product->name ?? '');
    $slug = is_array($product) ? ($product['slug'] ?? '') : ($product->slug ?? '');
    $stock = (int) (is_array($product) ? ($product['stock'] ?? 0) : ($product->stock ?? 0));
    $isOutOfStock = $stock <= 0;

    // Price handling
    $currencySymbol = $symbol ?? currentCurrencySymbol();

    if (is_array($product)) {
        $comparePrice = isset($product['compare_price']) ? (float) $product['compare_price'] : null;
        $currentPrice = (float) ($product['price'] ?? 0);
        $discount = (int) ($product['discount_percentage'] ?? $product['discount_percent'] ?? 0);
    } else {
        $comparePrice = $product->compare_price !== null ? (float) $product->compare_price : null;
        $currentPrice = (float) ($product->final_price ?? $product->price ?? 0);
        $discount = (int) ($product->discount_percentage ?? $product->discountPercent ?? 0);
    }

    $hasDiscount = $comparePrice !== null && $comparePrice > $currentPrice;
    if ($hasDiscount && $discount <= 0 && $comparePrice > 0) {
        $discount = (int) round((($comparePrice - $currentPrice) / $comparePrice) * 100);
    }

    // Image handling
    $imagePath = null;
    if (is_array($product)) {
        $imagePath = $product['image'] ?? null;
    } else {
        $primaryImg = $product->primaryImage ?? ($product->images ? $product->images->first() : null);
        $imagePath = $primaryImg->image ?? ($product->image ?? null);
    }

    $imageUrl = null;
    if ($imagePath) {
        $imageUrl = (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://'))
            ? $imagePath
            : asset('storage/' . $imagePath);
    }

    // Product URL
    $url = is_array($product)
        ? ($product['url'] ?? route('shop.show', ['slug' => $slug ?: $id]))
        : (isset($product->slug) ? route('shop.show', ['slug' => $product->slug]) : route('shop.show', ['slug' => $id]));

    // Badges
    $isNew = is_array($product)
        ? ($product['is_new'] ?? false)
        : ($product->created_at && $product->created_at->gt(now()->subDays(7)));

    $isFeatured = is_array($product)
        ? ($product['is_featured'] ?? $product['featured'] ?? false)
        : ($product->featured ?? false);

    // Subtitle (Brand or Category)
    $subtitle = is_array($product)
        ? ($product['brand'] ?? $product['category_name'] ?? '')
        : ($product->brand ?? ($product->category->name ?? ''));

    // Rating
    $rating = (float) (is_array($product)
        ? ($product['rating'] ?? 0)
        : ($product->rating ?? $product->reviews_avg_rating ?? 0));

    $fullStars = (int) floor($rating);
    $hasHalf = ($rating - $fullStars) >= 0.25 && ($rating - $fullStars) < 0.75;
    $roundedUp = ($rating - $fullStars) >= 0.75 ? 1 : 0;
    $filledStars = min(5, $fullStars + $roundedUp);
    $halfIndex = $hasHalf ? $fullStars + 1 : 0;
@endphp

<article class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden group hover:shadow-lg transition-all duration-300 relative flex flex-col h-full"
         id="product-card-{{ $id }}">

    {{-- Badges --}}
    @if($hasDiscount && $discount > 0)
        <div class="absolute top-3 right-3 bg-primary-container text-on-primary-container px-3 py-1 rounded-full text-sm font-bold z-10 shadow-xs">
            {{ $discount }}% خصم
        </div>
    @elseif($isOutOfStock)
        <div class="absolute top-3 right-3 bg-surface-dim text-secondary px-3 py-1 rounded-full text-sm font-bold z-10">
            نفذت الكمية
        </div>
    @elseif($isNew)
        <div class="absolute top-3 right-3 bg-tertiary text-on-tertiary px-3 py-1 rounded-full text-sm font-bold z-10 shadow-xs">
            جديد
        </div>
    @elseif($isFeatured)
        <div class="absolute top-3 right-3 bg-primary-container text-on-primary-container px-3 py-1 rounded-full text-sm font-bold z-10 shadow-xs">
            مميز
        </div>
    @endif

    {{-- Product Image --}}
    <a href="{{ $url }}" class="relative w-full aspect-square bg-surface-container-high overflow-hidden p-6 flex items-center justify-center border-b border-outline-variant/30 {{ $isOutOfStock ? 'opacity-60 grayscale' : '' }}" aria-label="{{ $name }}">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $name }}" loading="lazy" decoding="async" class="object-contain w-full h-full group-hover:scale-105 transition-transform duration-500">
        @else
            <div class="w-full h-full flex items-center justify-center text-outline">
                <span class="material-symbols-outlined text-4xl">inventory_2</span>
            </div>
        @endif
    </a>

    {{-- Card Body --}}
    <div class="p-5 flex flex-col flex-grow">

        {{-- Rating Stars --}}
        <div class="flex items-center gap-1 mb-2 text-primary-container {{ $isOutOfStock ? 'opacity-60' : '' }}" dir="ltr">
            @for($i = 1; $i <= 5; $i++)
                @if($i === $halfIndex)
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star_half</span>
                @elseif($i <= $filledStars)
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                @else
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 0;">star</span>
                @endif
            @endfor
            @if($rating > 0)
                <span class="text-xs text-secondary ml-1 font-body-md">({{ number_format($rating, 1) }})</span>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="font-headline-md text-lg font-bold text-on-surface mb-1 line-clamp-2 {{ $isOutOfStock ? 'opacity-60' : '' }}">
            <a href="{{ $url }}" class="hover:text-primary transition-colors">
                {{ $name }}
            </a>
        </h3>

        {{-- Brand / Category Subtitle --}}
        @if($subtitle)
            <p class="text-sm text-secondary mb-4 font-body-md {{ $isOutOfStock ? 'opacity-60' : '' }}">{{ $subtitle }}</p>
        @endif

        {{-- Price & CTA --}}
        <div class="mt-auto pt-2">
            <div class="flex items-center gap-3 mb-4 {{ $isOutOfStock ? 'opacity-60' : '' }}">
                <span class="font-sora text-xl font-bold text-on-surface">
                    {{ number_format(convertPrice($currentPrice), 0) }} {{ $currencySymbol }}
                </span>
                @if($hasDiscount && $comparePrice)
                    <span class="text-sm text-secondary line-through">
                        {{ number_format(convertPrice($comparePrice), 0) }} {{ $currencySymbol }}
                    </span>
                @endif
            </div>

            @if($isOutOfStock)
                <button class="w-full bg-surface-dim text-secondary py-3 rounded-lg font-bold cursor-not-allowed text-center" disabled>
                    غير متوفر حالياً
                </button>
            @else
                <a href="{{ $url }}" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 block text-center">
                    اضغط هنا للطلب
                </a>
            @endif
        </div>

    </div>
</article>

