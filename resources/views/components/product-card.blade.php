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

<article class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl overflow-hidden group hover:shadow-md hover:border-primary/40 transition-all duration-300 relative flex flex-col h-full shadow-2xs"
         id="product-card-{{ $id }}">

    {{-- Badges --}}
    @if($hasDiscount && $discount > 0)
        <div class="absolute top-2.5 end-2.5 bg-primary text-on-primary px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-bold z-10 shadow-xs">
            -{{ $discount }}%
        </div>
    @elseif($isOutOfStock)
        <div class="absolute top-2.5 end-2.5 bg-surface-dim text-on-surface-variant px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-bold z-10">
            نفذت الكمية
        </div>
    @elseif($isNew)
        <div class="absolute top-2.5 end-2.5 bg-tertiary text-on-tertiary px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-bold z-10 shadow-xs">
            جديد
        </div>
    @elseif($isFeatured)
        <div class="absolute top-2.5 end-2.5 bg-primary-container text-on-primary-container px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-bold z-10 shadow-xs">
            مميز
        </div>
    @endif

    {{-- Product Image Container --}}
    <a href="{{ $url }}" class="relative w-full aspect-square bg-surface-container-low/40 overflow-hidden p-3 sm:p-5 flex items-center justify-center border-b border-outline-variant/30 {{ $isOutOfStock ? 'opacity-60 grayscale' : '' }}" aria-label="{{ $name }}">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $name }}" loading="lazy" decoding="async" class="object-contain w-full h-full group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-outline">
                <span class="material-symbols-outlined text-4xl text-primary/40">inventory_2</span>
            </div>
        @endif
    </a>

    {{-- Card Body --}}
    <div class="p-3.5 sm:p-5 flex flex-col flex-grow">

        {{-- Rating Stars --}}
        <div class="flex items-center gap-0.5 mb-1.5 text-amber-500 {{ $isOutOfStock ? 'opacity-60' : '' }}" dir="ltr">
            @for($i = 1; $i <= 5; $i++)
                @if($i === $halfIndex)
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star_half</span>
                @elseif($i <= $filledStars)
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                @else
                    <span class="material-symbols-outlined text-sm text-gray-300" style="font-variation-settings: 'FILL' 0;">star</span>
                @endif
            @endfor
            @if($rating > 0)
                <span class="text-[10px] sm:text-xs text-on-surface-variant font-mono ms-1">({{ number_format($rating, 1) }})</span>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="text-xs sm:text-sm md:text-base font-extrabold text-on-surface mb-1 line-clamp-2 leading-snug group-hover:text-primary transition-colors {{ $isOutOfStock ? 'opacity-60' : '' }}">
            <a href="{{ $url }}">
                {{ $name }}
            </a>
        </h3>

        {{-- Brand / Category Subtitle --}}
        @if($subtitle)
            <p class="text-[11px] sm:text-xs text-on-surface-variant mb-3 truncate {{ $isOutOfStock ? 'opacity-60' : '' }}">{{ $subtitle }}</p>
        @endif

        {{-- Price & CTA Button --}}
        <div class="mt-auto pt-2 space-y-2.5">
            <div class="flex items-baseline gap-2 flex-wrap {{ $isOutOfStock ? 'opacity-60' : '' }}">
                <span class="font-sora text-sm sm:text-lg font-black text-primary font-mono">
                    {{ number_format(convertPrice($currentPrice), 0) }} <span class="text-xs font-bold">{{ $currencySymbol }}</span>
                </span>
                @if($hasDiscount && $comparePrice)
                    <span class="text-[11px] sm:text-xs text-on-surface-variant line-through font-mono">
                        {{ number_format(convertPrice($comparePrice), 0) }} {{ $currencySymbol }}
                    </span>
                @endif
            </div>

            @if($isOutOfStock)
                <button class="w-full bg-surface-container-high text-on-surface-variant py-2.5 rounded-xl text-xs sm:text-sm font-bold cursor-not-allowed text-center" disabled>
                    غير متوفر حالياً
                </button>
            @else
                <a href="{{ $url }}" class="w-full bg-primary text-on-primary py-2.5 rounded-xl text-xs sm:text-sm font-bold hover:brightness-105 active:scale-95 transition-all block text-center shadow-2xs">
                    اضغط هنا للطلب
                </a>
            @endif
        </div>

    </div>
</article>
