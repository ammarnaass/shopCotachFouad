@extends('frontend.layout')

@section('title', __t('home.title', ['store' => site('store_name', config('app.name'))]))
@section('description', site('store_description', __t('home.description')))

@section('content')

@php
    $sectionOrder = json_decode(site('home_section_order', '["hero","marquee","categories","featured","latest","banner_1","banner_2"]'), true);
    if (!is_array($sectionOrder)) {
        $sectionOrder = ["hero","marquee","categories","featured","latest","banner_1","banner_2"];
    }
@endphp

@foreach($sectionOrder as $section)

{{-- ==================== 1. HERO SECTION ==================== --}}
@if($section === 'hero' && site('show_hero', '1') === '1')
    @if(!empty($slides) && count($slides) > 0)
        @include('frontend.partials.hero-slider', ['slides' => $slides])
    @else
        {{-- Default Elevated Hero Banner when no custom slides are uploaded --}}
        <section class="relative overflow-hidden bg-gradient-to-l from-primary via-primary/95 to-primary-container text-white py-14 sm:py-20 lg:py-24">
            {{-- Background glowing blurs --}}
            <div class="absolute -top-32 -end-32 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -start-32 w-[30rem] h-[30rem] bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="container-app relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    {{-- Hero Tag Badge --}}
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/25 text-white text-xs sm:text-sm font-bold mb-6 shadow-xs">
                        <span class="material-symbols-outlined text-base animate-pulse">auto_awesome</span>
                        <span>{{ site('hero_badge', 'متجرك الأول للتسوق الموثوق في الجزائر 🇩🇿') }}</span>
                    </div>

                    {{-- Main Headline --}}
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-white mb-6 leading-tight font-sora">
                        {{ site('hero_title', 'أفضل المنتجات الأصلية بأعلى جودة وأفضل الأسعار') }}
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-white/90 text-sm sm:text-base md:text-lg max-w-2xl mx-auto mb-8 leading-relaxed">
                        {{ site('hero_subtitle', 'نوفر لك تشكيلة واسعة ومميزة مع خدمة شحن سريعة لجميع ولايات الوطن والدفع عند الاستلام بكل أمان وراحة.') }}
                    </p>

                    {{-- Action CTA Buttons --}}
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 sm:gap-4">
                        <a href="{{ route('shop.index') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white text-primary font-bold text-sm sm:text-base hover:bg-white/95 hover:scale-105 active:scale-95 transition-all shadow-lg">
                            <span class="material-symbols-outlined text-xl">shopping_bag</span>
                            <span>{{ __t('home.hero_shop_now') ?? 'تسوق الآن' }}</span>
                        </a>

                        @if(isset($categories) && $categories->count() > 0)
                            <a href="#categories"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-2xl bg-white/15 hover:bg-white/25 border border-white/30 text-white font-bold text-sm sm:text-base backdrop-blur-md transition-all shadow-xs">
                                <span class="material-symbols-outlined text-xl">grid_view</span>
                                <span>{{ __t('home.browse_categories') ?? 'تصفح التصنيفات' }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- Quick Trust Indicators --}}
                    <div class="mt-10 pt-8 border-t border-white/15 grid grid-cols-3 gap-2 sm:gap-4 text-center">
                        <div>
                            <p class="text-base sm:text-2xl font-black font-mono text-white">58</p>
                            <p class="text-[11px] sm:text-xs text-white/80 font-medium mt-0.5">ولاية مغطاة للشحن</p>
                        </div>
                        <div>
                            <p class="text-base sm:text-2xl font-black font-mono text-white">100%</p>
                            <p class="text-[11px] sm:text-xs text-white/80 font-medium mt-0.5">منتجات أصلية ومضمونة</p>
                        </div>
                        <div>
                            <p class="text-base sm:text-2xl font-black font-mono text-white">COD</p>
                            <p class="text-[11px] sm:text-xs text-white/80 font-medium mt-0.5">الدفع عند الاستلام</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endif

{{-- ==================== 2. MARQUEE / TRUST FEATURES ==================== --}}
@if($section === 'marquee' && site('show_marquee', '1') === '1')
<section class="py-8 sm:py-10 border-b border-outline-variant/30 bg-surface-container-low/20">
    <div class="container-app">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-5">
            {{-- Feature 1 --}}
            <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-start p-4 sm:p-5 bg-surface-container-lowest rounded-2xl border border-outline-variant/40 hover:border-primary/40 hover:shadow-xs transition-all duration-300 gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-2xl">local_shipping</span>
                </div>
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-on-surface">{{ __t('home.free_shipping') ?? 'توصيل سريع وموثوق' }}</h2>
                    <p class="text-[11px] sm:text-xs text-on-surface-variant mt-1 leading-relaxed">تغطية 58 ولاية حتى باب منزلك</p>
                </div>
            </div>

            {{-- Feature 2 --}}
            <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-start p-4 sm:p-5 bg-surface-container-lowest rounded-2xl border border-outline-variant/40 hover:border-primary/40 hover:shadow-xs transition-all duration-300 gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-2xl">payments</span>
                </div>
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-on-surface">{{ __t('home.cod') ?? 'الدفع عند الاستلام' }}</h2>
                    <p class="text-[11px] sm:text-xs text-on-surface-variant mt-1 leading-relaxed">عاين طلبك وادفع نقداً بكل أمان</p>
                </div>
            </div>

            {{-- Feature 3 --}}
            <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-start p-4 sm:p-5 bg-surface-container-lowest rounded-2xl border border-outline-variant/40 hover:border-primary/40 hover:shadow-xs transition-all duration-300 gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-2xl">verified</span>
                </div>
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-on-surface">{{ __t('home.authentic') ?? 'جودة وضمان أصلي' }}</h2>
                    <p class="text-[11px] sm:text-xs text-on-surface-variant mt-1 leading-relaxed">منتجات مضمونة المصدر 100%</p>
                </div>
            </div>

            {{-- Feature 4 --}}
            <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-start p-4 sm:p-5 bg-surface-container-lowest rounded-2xl border border-outline-variant/40 hover:border-primary/40 hover:shadow-xs transition-all duration-300 gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-2xl">support_agent</span>
                </div>
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-on-surface">{{ __t('home.support') ?? 'خدمة عملاء ومتابعة' }}</h2>
                    <p class="text-[11px] sm:text-xs text-on-surface-variant mt-1 leading-relaxed">فريق جاهز لمساعدتك دائماً</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ==================== 3. CATEGORIES ==================== --}}
@if($section === 'categories' && site('show_categories', '1') === '1' && isset($categories) && $categories->count() > 0)
<section id="categories" class="py-12 sm:py-16">
    <div class="container-app">
        {{-- Section Header --}}
        <div class="flex justify-between items-end mb-8 sm:mb-10 flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary font-bold text-xs px-3 py-1 rounded-full mb-2 shadow-2xs">
                    <span class="material-symbols-outlined text-sm">category</span>
                    <span>{{ __t('home.browse_categories') ?? 'تصفح حسب التصنيف' }}</span>
                </span>
                <h2 class="font-sora text-2xl sm:text-3xl font-extrabold text-on-surface">{{ __t('home.all_categories') ?? 'أقسام المتجر' }}</h2>
            </div>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 text-xs sm:text-sm font-bold text-primary hover:underline group">
                <span>{{ __t('shop.view_all', [], 'عرض جميع المنتجات') }}</span>
                <span class="material-symbols-outlined text-base transition-transform group-hover:-translate-x-1">chevron_left</span>
            </a>
        </div>

        {{-- Categories Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($categories as $category)
                <a href="{{ route('shop.category', ['slug' => $category->slug]) }}"
                   class="group relative block h-48 sm:h-56 rounded-2xl overflow-hidden bg-surface-container-low border border-outline-variant/40 shadow-2xs hover:shadow-md hover:border-primary/40 transition-all duration-300">
                    @if($category->image)
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105"
                             style="background-image: url('{{ asset('storage/' . $category->image) }}')"></div>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center">
                            @categoryIcon($category->icon ?? 'category', 'text-5xl text-primary/40 group-hover:text-primary transition-colors')
                        </div>
                    @endif

                    {{-- Gradient Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

                    {{-- Category Content --}}
                    <div class="absolute bottom-0 inset-x-0 p-4 sm:p-5 flex flex-col justify-end">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-sora text-sm sm:text-base font-extrabold text-white group-hover:text-primary-container transition-colors truncate">
                                {{ $category->name }}
                            </h3>
                            <span class="px-2 py-0.5 rounded-md bg-white/20 backdrop-blur-xs text-white text-[11px] font-mono font-bold shrink-0">
                                {{ $category->products_count ?? $category->products()->count() }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ==================== 4. FEATURED PRODUCTS ==================== --}}
@if($section === 'featured' && site('show_featured', '1') === '1' && isset($featuredProducts) && $featuredProducts->count() > 0)
<section class="py-12 sm:py-16 bg-surface-container-low/20 border-y border-outline-variant/30">
    <div class="container-app">
        {{-- Section Header --}}
        <div class="flex items-end justify-between mb-8 sm:mb-10 flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 bg-tertiary/10 text-tertiary font-bold text-xs px-3 py-1 rounded-full mb-2 shadow-2xs">
                    <span class="material-symbols-outlined text-sm">local_fire_department</span>
                    <span>{{ __t('home.most_requested') ?? 'الأكثر طلباً' }}</span>
                </span>
                <h2 class="font-sora text-2xl sm:text-3xl font-extrabold text-on-surface">{{ __t('home.featured_products') ?? 'المنتجات المميزة' }}</h2>
                <p class="text-on-surface-variant text-xs sm:text-sm mt-1">{{ __t('home.featured_subtitle') ?? 'أفضل الاختيارات الموصى بها لأجلك' }}</p>
            </div>
            <a href="{{ route('shop.index') }}?featured=1" class="inline-flex items-center gap-1 text-xs sm:text-sm font-bold text-primary hover:underline group">
                <span>{{ __t('shop.view_all', [], 'عرض الكل') }}</span>
                <span class="material-symbols-outlined text-base transition-transform group-hover:-translate-x-1">chevron_left</span>
            </a>
        </div>

        {{-- Products Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6">
            @foreach($featuredProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product, 'symbol' => currentCurrencySymbol()])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ==================== 5. PROMOTIONAL BANNER 1 ==================== --}}
@if($section === 'banner_1' && (site('banner_1_title') || site('banner_1_image')))
<section class="py-10 sm:py-14">
    <div class="container-app">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-l from-primary via-primary/95 to-primary-container text-white p-8 sm:p-12 shadow-md">
            <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
                <div>
                    @if(site('banner_1_badge'))
                        <span class="inline-flex items-center gap-1 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-xs font-bold mb-4">
                            <span class="material-symbols-outlined text-sm">stars</span>
                            {{ site('banner_1_badge') }}
                        </span>
                    @endif
                    <h2 class="font-sora text-2xl sm:text-3xl md:text-4xl font-extrabold mb-3 text-white leading-tight">
                        {{ site('banner_1_title') }}
                    </h2>
                    <p class="text-white/90 text-sm sm:text-base mb-6 leading-relaxed">
                        {{ site('banner_1_subtitle') }}
                    </p>
                    @if(site('banner_1_link'))
                        <a href="{{ site('banner_1_link') }}"
                           class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-primary font-sora font-extrabold text-sm rounded-xl hover:bg-white/95 hover:scale-105 active:scale-95 transition-all shadow-md">
                            <span>{{ site('banner_1_btn_text', 'اكتشف العرض الآن') }}</span>
                            <span class="material-symbols-outlined text-base">chevron_left</span>
                        </a>
                    @endif
                </div>
                @if(site('banner_1_image'))
                    <div class="hidden md:flex justify-center">
                        <img src="{{ site('banner_1_image') }}" alt="{{ site('banner_1_title') }}" loading="lazy" decoding="async" class="rounded-2xl shadow-xl max-w-md w-full object-cover">
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- ==================== 6. LATEST PRODUCTS ==================== --}}
@if($section === 'latest' && site('show_latest', '1') === '1' && isset($latestProducts) && $latestProducts->count() > 0)
<section class="py-12 sm:py-16">
    <div class="container-app">
        {{-- Section Header --}}
        <div class="flex items-end justify-between mb-8 sm:mb-10 flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary font-bold text-xs px-3 py-1 rounded-full mb-2 shadow-2xs">
                    <span class="material-symbols-outlined text-sm">bolt</span>
                    <span>{{ __t('home.just_arrived') ?? 'وصل حديثاً' }}</span>
                </span>
                <h2 class="font-sora text-2xl sm:text-3xl font-extrabold text-on-surface">{{ __t('home.latest_products') ?? 'أحدث المنتجات' }}</h2>
                <p class="text-on-surface-variant text-xs sm:text-sm mt-1">{{ __t('home.latest_subtitle') ?? 'اطلع على أحدث ما تم إضافته لمتجرنا' }}</p>
            </div>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 text-xs sm:text-sm font-bold text-primary hover:underline group">
                <span>{{ __t('nav.browse_all', [], 'تصفح الكل') }}</span>
                <span class="material-symbols-outlined text-base transition-transform group-hover:-translate-x-1">chevron_left</span>
            </a>
        </div>

        {{-- Products Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6">
            @foreach($latestProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product, 'symbol' => currentCurrencySymbol()])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ==================== 7. PROMOTIONAL BANNER 2 ==================== --}}
@if($section === 'banner_2' && (site('banner_2_title') || site('banner_2_image')))
<section class="py-10 sm:py-14 bg-surface-container-low/20 border-t border-outline-variant/30">
    <div class="container-app">
        <div class="relative overflow-hidden rounded-3xl bg-surface-container-lowest border border-outline-variant/40 text-on-surface p-8 sm:p-12 shadow-xs">
            <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="font-sora text-2xl sm:text-3xl md:text-4xl font-extrabold mb-3 text-on-surface leading-tight">
                        {{ site('banner_2_title') }}
                    </h2>
                    <p class="text-on-surface-variant text-sm sm:text-base mb-6 leading-relaxed">
                        {{ site('banner_2_subtitle') }}
                    </p>
                    @if(site('banner_2_link'))
                        <a href="{{ site('banner_2_link') }}"
                           class="inline-flex items-center gap-2 px-6 py-3.5 bg-primary text-on-primary font-sora font-extrabold text-sm rounded-xl hover:brightness-105 active:scale-95 transition-all shadow-sm">
                            <span>{{ site('banner_2_btn_text', 'اكتشف المزيد') }}</span>
                            <span class="material-symbols-outlined text-base">chevron_left</span>
                        </a>
                    @endif
                </div>
                @if(site('banner_2_image'))
                    <div class="hidden md:flex justify-center">
                        <img src="{{ site('banner_2_image') }}" alt="{{ site('banner_2_title') }}" loading="lazy" decoding="async" class="rounded-2xl shadow-xl max-w-md w-full object-cover">
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
