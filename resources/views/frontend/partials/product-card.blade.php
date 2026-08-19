{{-- ============================================================
    Product Card Partial — Wrapper delegating to <x-product-card />
    Usage: @include('frontend.partials.product-card', ['product' => $product])
    ============================================================ --}}
<x-product-card :product="$product" :symbol="$symbol ?? null" />
