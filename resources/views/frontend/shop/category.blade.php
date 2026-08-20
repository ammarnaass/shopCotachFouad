@extends('frontend.layout')

@section('title', $category->name . ' - ' . site('store_name'))
@section('description', $category->description ?? __t('shop.category.subtitle', ['name' => $category->name]))

@section('content')
@php
    $currentRating = request('rating');
    $currentMaxPrice = (int) request('max_price', 20000);
    $searchQuery = request('q');
    $currentSort = request('sort', 'created_at');
    $currentDir = request('dir', 'desc');
    $hasActiveFilters = !empty($searchQuery) || !empty($currentRating) || request()->has('max_price');
@endphp

{{-- ============ HERO / HEADER ============ --}}
<section class="relative overflow-hidden bg-gradient-to-l from-primary via-primary/90 to-primary-container text-white py-10 md:py-14">
    <div class="absolute -top-24 -end-24 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -start-24 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container-app relative z-10">
        <nav class="flex items-center gap-2 text-xs font-semibold text-white/80 mb-4" aria-label="breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>{{ __t('nav.breadcrumb_home', [], 'الرئيسية') }}</span>
            </a>
            <span class="material-symbols-outlined text-xs text-white/40">chevron_left</span>
            <a href="{{ route('shop.index') }}" class="hover:text-white transition-colors">{{ __t('nav.breadcrumb_shop', [], 'المتجر') }}</a>
            <span class="material-symbols-outlined text-xs text-white/40">chevron_left</span>
            <span class="text-white font-bold" aria-current="page">{{ $category->name }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-2xl sm:text-3xl border border-white/25 shadow-lg flex-shrink-0">
                    @if($category->icon)
                        <span class="material-symbols-outlined">{{ $category->icon }}</span>
                    @else
                        <span class="material-symbols-outlined">category</span>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white">{{ $category->name }}</h1>
                        <span class="bg-white/20 text-white text-xs font-bold font-mono px-2.5 py-0.5 rounded-full border border-white/30">{{ $products->total() }} منتج</span>
                    </div>
                    <p class="text-white/90 text-xs sm:text-sm">
                        {{ $category->description ?? __t('shop.category.results', ['count' => $products->total()], 'تصفح جميع المنتجات في تصنيف ' . $category->name) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ MAIN CONTAINER ============ --}}
<div class="container-app py-8 md:py-12" x-data="{ mobileFiltersOpen: false, maxPrice: {{ $currentMaxPrice }} }">

    {{-- Subcategories Quick Bar (if any) --}}
    @if($category->children && $category->children->count() > 0)
        <div class="mb-8 overflow-x-auto pb-2 scrollbar-none">
            <div class="flex items-center gap-2 min-w-max">
                <span class="text-xs font-bold text-on-surface-variant me-2 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-primary">account_tree</span>
                    <span>الفئات الفرعية:</span>
                </span>

                <a href="{{ route('shop.category', ['slug' => $category->slug]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-primary text-on-primary shadow-xs">
                    <span>{{ __t('shop.category.all', [], 'الكل') }}</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[11px] font-mono bg-white/20 text-white">
                        {{ $category->products_count ?? $category->products()->count() }}
                    </span>
                </a>

                @foreach($category->children as $child)
                    <a href="{{ route('shop.category', ['slug' => $child->slug]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-surface-container-lowest text-on-surface hover:bg-surface-container-low transition border border-outline-variant/50 shadow-2xs">
                        <span>{{ $child->name }}</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[11px] font-mono bg-surface-container text-on-surface-variant font-bold">
                            {{ $child->products()->count() }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Mobile Filter Drawer Button --}}
    <div class="lg:hidden mb-6">
        <button type="button" @click="mobileFiltersOpen = !mobileFiltersOpen"
                class="w-full flex items-center justify-between px-5 py-3.5 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 text-on-surface font-bold text-sm shadow-xs active:scale-[0.99] transition-all">
            <span class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-lg">filter_list</span>
                <span>تصفية وفلترة منتجات {{ $category->name }}</span>
            </span>
            <div class="flex items-center gap-2">
                @if($hasActiveFilters)
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                @endif
                <span class="material-symbols-outlined text-base transition-transform" :class="mobileFiltersOpen ? 'rotate-180' : ''">expand_more</span>
            </div>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- ============ SIDEBAR FILTERS ============ --}}
        <aside class="lg:col-span-1" :class="mobileFiltersOpen ? 'block' : 'hidden lg:block'">
            <div class="space-y-5 sticky top-24">
                <form method="GET" action="{{ route('shop.category', ['slug' => $category->slug]) }}" class="space-y-5">
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    @if(request('dir'))
                        <input type="hidden" name="dir" value="{{ request('dir') }}">
                    @endif

                    {{-- Search Card --}}
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-5 shadow-xs">
                        <h2 class="font-extrabold text-sm text-on-surface mb-3.5 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-base">search</span>
                            <span>{{ __t('shop.category.search_in', [], 'البحث في ' . $category->name) }}</span>
                        </h2>
                        <div class="relative">
                            <input type="text" name="q" value="{{ $searchQuery }}" placeholder="{{ __t('shop.category.search_placeholder', [], 'ابحث...') }}"
                                   class="w-full ps-10 pe-4 py-2.5 bg-surface-container-low/40 rounded-xl border border-outline-variant/60 text-on-surface text-xs sm:text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <span class="material-symbols-outlined absolute start-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">search</span>
                        </div>
                    </div>

                    {{-- Price Filter Card --}}
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-5 shadow-xs">
                        <h2 class="font-extrabold text-sm text-on-surface mb-3.5 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-base">attach_money</span>
                            <span>{{ __t('shop.price', [], 'نطاق السعر') }}</span>
                        </h2>
                        <div class="space-y-4">
                            <input type="range" name="max_price" min="500" max="30000" step="500" x-model="maxPrice"
                                   class="w-full h-2 bg-surface-container rounded-lg appearance-none cursor-pointer accent-primary">
                            <div class="flex items-center justify-between text-xs font-mono font-bold text-on-surface-variant">
                                <span>0 {{ currentCurrencySymbol() }}</span>
                                <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-md" x-text="Number(maxPrice).toLocaleString() + ' ' + @js(currentCurrencySymbol())"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Rating Filter Card --}}
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-5 shadow-xs">
                        <h2 class="font-extrabold text-sm text-on-surface mb-3.5 flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500 text-base">star</span>
                            <span>{{ __t('shop.rating', [], 'التقييم') }}</span>
                        </h2>
                        <div class="space-y-2">
                            @foreach([5, 4, 3] as $rating)
                                <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-surface-container-low cursor-pointer transition-colors group">
                                    <input type="radio" name="rating" value="{{ $rating }}" {{ $currentRating == $rating ? 'checked' : '' }}
                                           class="w-4 h-4 text-primary border-outline-variant focus:ring-primary/20">
                                    <div class="flex text-amber-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' {{ $i <= $rating ? 1 : 0 }}">star</span>
                                        @endfor
                                    </div>
                                    @if($rating < 5)
                                        <span class="text-xs text-on-surface-variant font-medium group-hover:text-primary transition-colors">فأعلى</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-2 pt-1">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary text-on-primary font-bold text-xs sm:text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
                            <span class="material-symbols-outlined text-base">check</span>
                            <span>تطبيق الفلترة</span>
                        </button>
                        @if($hasActiveFilters)
                            <a href="{{ route('shop.category', ['slug' => $category->slug]) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-semibold text-xs transition-colors border border-outline-variant/40">
                                <span class="material-symbols-outlined text-sm">restart_alt</span>
                                <span>إلغاء جميع الفلاتر</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </aside>

        {{-- ============ PRODUCTS MAIN COLUMN ============ --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Top Toolbar / Sorting Bar --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs sm:text-sm text-on-surface-variant font-medium">
                    <span>عرض</span>
                    <span class="font-bold font-mono text-on-surface">{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</span>
                    <span>من أصل</span>
                    <span class="font-bold font-mono text-primary">{{ $products->total() }}</span>
                    <span>منتج في <span class="font-bold text-on-surface">{{ $category->name }}</span></span>
                </div>

                <div class="flex items-center gap-2.5 self-end sm:self-center">
                    <label for="sort-select" class="text-xs font-bold text-on-surface-variant whitespace-nowrap">الترتيب:</label>
                    <div class="relative">
                        <select id="sort-select" onchange="window.location.href = this.value"
                                class="ps-3.5 pe-8 py-2 bg-surface-container-low/60 rounded-xl border border-outline-variant/60 text-xs sm:text-sm font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none cursor-pointer">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => 'desc']) }}" {{ $currentSort == 'created_at' ? 'selected' : '' }}>الأحدث أولاً</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => 'asc']) }}" {{ $currentSort == 'price' && $currentDir == 'asc' ? 'selected' : '' }}>السعر: من الأقل للأعلى</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => 'desc']) }}" {{ $currentSort == 'price' && $currentDir == 'desc' ? 'selected' : '' }}>السعر: من الأعلى للأقل</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'best_selling', 'dir' => 'desc']) }}" {{ $currentSort == 'best_selling' ? 'selected' : '' }}>الأكثر مبيعاً</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => 'asc']) }}" {{ $currentSort == 'name' ? 'selected' : '' }}>الاسم (أ - ي)</option>
                        </select>
                        <span class="material-symbols-outlined absolute end-2 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm pointer-events-none">expand_more</span>
                    </div>
                </div>
            </div>

            {{-- Active Filter Tags (if any) --}}
            @if($hasActiveFilters)
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-on-surface-variant font-semibold">الفلاتر المطبقة:</span>

                    @if(!empty($searchQuery))
                        <a href="{{ route('shop.category', array_merge(['slug' => $category->slug], request()->except(['q', 'page']))) }}"
                           class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 transition-colors">
                            <span>بحث: {{ $searchQuery }}</span>
                            <span class="material-symbols-outlined text-xs">close</span>
                        </a>
                    @endif

                    @if(!empty($currentRating))
                        <a href="{{ route('shop.category', array_merge(['slug' => $category->slug], request()->except(['rating', 'page']))) }}"
                           class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 transition-colors">
                            <span>تقييم: {{ $currentRating }}★+</span>
                            <span class="material-symbols-outlined text-xs">close</span>
                        </a>
                    @endif

                    <a href="{{ route('shop.category', ['slug' => $category->slug]) }}" class="text-xs text-error font-bold hover:underline ms-2">
                        مسح الكل
                    </a>
                </div>
            @endif

            {{-- Products Grid --}}
            @if($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-3.5 sm:gap-6">
                    @foreach($products as $product)
                        @include('frontend.partials.product-card', ['product' => $product, 'symbol' => currentCurrencySymbol()])
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($products->hasPages())
                    <div class="mt-10 flex justify-center">
                        {{ $products->links() }}
                    </div>
                @endif
            @else
                {{-- Empty Search / Filter Results --}}
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-3xl p-8 sm:p-14 text-center shadow-xs">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-5 rounded-3xl bg-primary/10 text-primary flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-4xl sm:text-5xl">search_off</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-on-surface mb-2">{{ __t('shop.no_products', [], 'لا توجد منتجات مطابقة') }}</h2>
                    <p class="text-xs sm:text-sm text-on-surface-variant mb-6 max-w-md mx-auto leading-relaxed">
                        {{ __t('shop.no_products_desc', [], 'لم نتمكن من العثور على منتجات تطابق خيارات التصفية الحالية في هذا التصنيف.') }}
                    </p>
                    <a href="{{ route('shop.category', ['slug' => $category->slug]) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-md hover:shadow-lg hover:brightness-105 active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined text-lg">restart_alt</span>
                        <span>إعادة ضبط التصفية</span>
                    </a>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
