@extends('frontend.layout')

@section('title', __t('home.title', ['store' => config('app.name')]))
@section('description', __t('home.description'))

@section('content')

@php
    $sectionOrder = json_decode(site('home_section_order', '["hero","marquee","categories","featured","latest","banner_1","banner_2"]'), true);
    if (!is_array($sectionOrder)) {
        $sectionOrder = ["hero","marquee","categories","featured","latest","banner_1","banner_2"];
    }
@endphp

@foreach($sectionOrder as $section)

{{-- ========== HERO SECTION ========== --}}
@if($section === 'hero' && site('show_hero', '1') === '1')
    @include('frontend.partials.hero-slider', ['slides' => $slides])
@endif

{{-- ========== MARQUEE FEATURES ========== --}}
@if($section === 'marquee' && site('show_marquee', '1') === '1')
<section class="py-10 border-y border-outline-variant/30 bg-surface">
    <div class="container-app">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div class="flex flex-col sm:flex-row items-center text-center sm:text-start p-4 md:p-5 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:shadow-sm transition-all gap-3">
                <div class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-xl">local_shipping</span>
                </div>
                <div>
                    <h3 class="font-sora font-bold text-sm text-on-surface">{{ __t('home.free_shipping') }}</h3>
                    <p class="text-xs text-secondary mt-0.5">{{ __t('home.free_shipping_desc', ['amount' => config('ecommerce.shipping.free_threshold', 500)]) }}</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center text-center sm:text-start p-4 md:p-5 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:shadow-sm transition-all gap-3">
                <div class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-xl">payments</span>
                </div>
                <div>
                    <h3 class="font-sora font-bold text-sm text-on-surface">{{ __t('home.cod') }}</h3>
                    <p class="text-xs text-secondary mt-0.5">{{ __t('home.cod_desc') }}</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center text-center sm:text-start p-4 md:p-5 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:shadow-sm transition-all gap-3">
                <div class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-xl">headphones</span>
                </div>
                <div>
                    <h3 class="font-sora font-bold text-sm text-on-surface">{{ __t('home.support') }}</h3>
                    <p class="text-xs text-secondary mt-0.5">{{ __t('home.support_desc') }}</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center text-center sm:text-start p-4 md:p-5 bg-surface-container-lowest rounded-xl border border-outline-variant/30 hover:shadow-sm transition-all gap-3">
                <div class="w-12 h-12 rounded-full bg-surface-container-low flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-xl">verified</span>
                </div>
                <div>
                    <h3 class="font-sora font-bold text-sm text-on-surface">{{ __t('home.authentic') }}</h3>
                    <p class="text-xs text-secondary mt-0.5">{{ __t('home.authentic_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ========== CATEGORIES ========== --}}
@if($section === 'categories' && site('show_categories', '1') === '1' && $categories->count() > 0)
<section class="section py-14 bg-surface">
    <div class="container-app">
        <div class="flex justify-between items-end mb-8 flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 bg-primary-container text-on-primary-container font-label-caps text-xs px-3 py-1 rounded-full font-bold mb-2">
                    <span class="material-symbols-outlined text-sm">grid_view</span> {{ __t('home.browse_categories') }}
                </span>
                <h2 class="font-sora text-2xl sm:text-3xl font-extrabold text-on-surface">{{ __t('home.all_categories') }}</h2>
            </div>
            <a href="{{ route('shop.index') }}" class="font-sora text-sm font-bold text-primary hover:underline flex items-center gap-1">
                {{ __t('shop.view_all', [], 'عرض الكل') }}
                <span class="material-symbols-outlined text-sm">arrow_back</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
            @foreach($categories as $category)
                <a href="{{ route('shop.category', ['slug' => $category->slug]) }}"
                   class="group relative block h-56 rounded-2xl overflow-hidden bg-surface-container border border-outline-variant/30 hover:shadow-xl transition-all duration-300">
                    @if($category->image)
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105"
                             style="background-image: url('{{ asset('storage/' . $category->image) }}')"></div>
                    @else
                        <div class="absolute inset-0 bg-surface-container-high flex items-center justify-center">
                            @categoryIcon($category->icon ?? 'fitness_center', 'text-4xl text-primary')
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-on-background/85 via-on-background/30 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-5">
                        <h3 class="font-sora text-base font-bold text-on-primary mb-0.5 group-hover:text-primary-container transition-colors">{{ $category->name }}</h3>
                        <p class="font-body-md text-xs text-surface-dim">{{ __t('home.products_count', ['count' => $category->products_count ?? $category->products()->count()]) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ========== FEATURED PRODUCTS ========== --}}
@if($section === 'featured' && site('show_featured', '1') === '1' && $featuredProducts->count() > 0)
<section class="section py-14">
    <div class="container-app">
        <div class="flex items-end justify-between mb-8 flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center gap-1 bg-tertiary text-on-tertiary font-label-caps text-xs px-3 py-1 rounded-full font-bold mb-2">
                    <span class="material-symbols-outlined text-sm">local_fire_department</span> {{ __t('home.most_requested') }}
                </span>
                <h2 class="font-sora text-2xl sm:text-3xl font-extrabold text-on-surface">{{ __t('home.featured_products') }}</h2>
                <p class="text-secondary text-sm mt-1">{{ __t('home.featured_subtitle') }}</p>
            </div>
            <a href="{{ route('shop.index') }}?featured=1" class="font-sora text-sm font-bold text-primary hover:underline flex items-center gap-1">
                {{ __t('shop.view_all') }}
                <span class="material-symbols-outlined text-sm">arrow_back</span>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach($featuredProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product, 'symbol' => currentCurrencySymbol()])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ========== LATEST PRODUCTS ========== --}}
@if($section === 'latest' && site('show_latest', '1') === '1' && $latestProducts->count() > 0)
<section class="section py-14 bg-surface-container-low/40">
    <div class="container-app">
        <div class="flex items-end justify-between mb-8 flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center gap-1 bg-primary-container text-on-primary-container font-label-caps text-xs px-3 py-1 rounded-full font-bold mb-2">
                    <span class="material-symbols-outlined text-sm">bolt</span> {{ __t('home.just_arrived') }}
                </span>
                <h2 class="font-sora text-2xl sm:text-3xl font-extrabold text-on-surface">{{ __t('home.latest_products') }}</h2>
                <p class="text-secondary text-sm mt-1">{{ __t('home.latest_subtitle') }}</p>
            </div>
            <a href="{{ route('shop.index') }}" class="font-sora text-sm font-bold text-primary hover:underline flex items-center gap-1">
                {{ __t('nav.browse_all') }}
                <span class="material-symbols-outlined text-sm">arrow_back</span>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach($latestProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product, 'symbol' => currentCurrencySymbol()])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ========== CTA BANNER 1 ========== --}}
@if($section === 'banner_1' && site('show_banner_1', '1') === '1')
<section class="py-12">
    <div class="container-app">
        <div class="relative bg-secondary text-on-primary rounded-3xl p-8 md:p-14 overflow-hidden shadow-xl border border-outline-variant/20">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-container/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-24 -right-24 w-[30rem] h-[30rem] bg-primary/20 rounded-full blur-3xl"></div>

            <div class="relative z-10 grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 bg-primary-container text-on-primary-container px-4 py-1.5 rounded-full text-xs font-bold mb-4 shadow-sm">
                        <span class="material-symbols-outlined text-base">local_shipping</span> {{ __t('home.fast_shipping') }}
                    </span>
                    <h2 class="font-sora text-2xl sm:text-3xl md:text-4xl font-extrabold mb-4 leading-tight text-on-primary">
                        @if(site('banner_1_title'))
                            {{ site('banner_1_title') }}
                        @else
                            {{ __t('home.banner_1_title') }}
                        @endif
                    </h2>
                    <p class="text-surface-dim text-base md:text-lg mb-8 leading-relaxed">
                        {{ site('banner_1_subtitle', __t('home.banner_1_subtitle')) }}
                    </p>
                    <a href="{{ site('banner_1_link') ?: route('shop.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary-container text-on-primary-container font-sora font-extrabold text-base rounded-2xl shadow-lg hover:bg-inverse-primary hover:scale-105 active:scale-95 transition-all duration-300">
                        <span class="material-symbols-outlined">shopping_bag</span>
                        {{ __t('nav.shop_now') }}
                    </a>
                </div>
                <div class="hidden md:flex justify-center">
                    @if(site('banner_1_image'))
                        <img src="{{ site('banner_1_image') }}" alt="" loading="lazy" decoding="async" class="rounded-2xl shadow-2xl max-w-md w-full object-cover hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="relative">
                            <div class="w-48 h-48 rounded-full bg-surface-container-high/20 flex items-center justify-center border border-outline-variant/30">
                                <span class="material-symbols-outlined text-8xl text-primary-container">fitness_center</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ========== BANNER 2 ========== --}}
@if($section === 'banner_2' && (site('banner_2_title') || site('banner_2_image')))
<section class="py-12">
    <div class="container-app">
        <div class="relative overflow-hidden rounded-3xl bg-surface-container-high border border-outline-variant/30 text-on-surface p-8 md:p-12">
            <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="font-sora text-3xl md:text-4xl font-extrabold mb-3 text-on-surface">{{ site('banner_2_title') }}</h2>
                    <p class="text-secondary text-lg mb-6">{{ site('banner_2_subtitle') }}</p>
                    @if(site('banner_2_link'))
                        <a href="{{ site('banner_2_link') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-on-primary font-sora font-bold text-sm rounded-xl hover:bg-primary-container hover:text-on-primary-container transition-colors">
                            <span class="material-symbols-outlined">arrow_back</span> {{ __t('nav.discover_more') }}
                        </a>
                    @endif
                </div>
                @if(site('banner_2_image'))
                    <div class="hidden md:flex justify-center">
                        <img src="{{ site('banner_2_image') }}" alt="" loading="lazy" decoding="async" class="rounded-2xl shadow-xl max-w-md w-full object-cover">
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

@endforeach

@push('scripts')
<script defer>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    });
</script>
@endpush

@endsection
