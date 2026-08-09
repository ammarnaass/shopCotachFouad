@extends('frontend.layout')

@section('title', __t('shop.page_title') . ' - ' . site('store_name'))
@section('description', __t('shop.browse_all_products') . ' ' . site('store_name'))

@section('content')

{{-- ========== BREADCRUMBS & BANNER ========== --}}
<section class="bg-gradient-to-l from-brand-600 via-brand-500 to-accent-500 text-white py-10 md:py-14 relative overflow-hidden">
    <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
    <div class="container-app relative z-10">
        <nav class="flex items-center gap-2 text-sm text-white/80 mb-4" aria-label="breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-white transition flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">home</span>
                {{ __t('shop.home') }}
            </a>
            <span class="material-symbols-outlined text-[10px] text-white/50">chevron_left</span>
            <span class="text-white font-medium" aria-current="page">{{ __t('shop.page_title') }}</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl border border-white/30">
                <span class="material-symbols-outlined">storefront</span>
            </div>
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-1">{{ __t('shop.page_title') }}</h1>
                <p class="text-white/90 text-base">{{ __t('shop.browse_all_products_desc') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ========== MAIN LAYOUT ========== --}}
<div class="container-app py-8 md:py-10" x-data="{ mobileFiltersOpen: false }">

    {{-- Mobile filter trigger --}}
    <div class="lg:hidden mb-4 flex items-center justify-between gap-3">
        <button type="button" @click="mobileFiltersOpen = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 min-h-[44px] bg-white border border-outline-variant rounded-lg text-sm font-semibold hover:bg-gray-50"
                aria-controls="filters-panel" :aria-expanded="mobileFiltersOpen">
            <span class="material-symbols-outlined text-base">filter_list</span>
            {{ __t('shop.filter') }}
        </button>
        <div class="flex items-center gap-2 bg-white border border-outline-variant rounded-lg px-3 py-1.5 text-sm">
            <span class="text-xs text-on-surface-variant">{{ __t('shop.sort_by') }}:</span>
            <label for="sort-mobile" class="sr-only">{{ __t('shop.sort_by') }}</label>
            <select id="sort-mobile" onchange="window.location.href = this.value" class="bg-transparent border-none focus:ring-0 text-xs font-bold cursor-pointer py-0 pe-7">
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => 'desc']) }}" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>{{ __t('shop.sort_newest') }}</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => 'asc']) }}" {{ request('sort') == 'price' && request('dir') == 'asc' ? 'selected' : '' }}>{{ __t('shop.sort_price_low') }}</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => 'desc']) }}" {{ request('sort') == 'price' && request('dir') == 'desc' ? 'selected' : '' }}>{{ __t('shop.sort_price_high') }}</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'best_selling', 'dir' => 'desc']) }}" {{ request('sort') == 'best_selling' ? 'selected' : '' }}>{{ __t('shop.sort_best_selling') }}</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => 'asc']) }}" {{ request('sort') == 'name' ? 'selected' : '' }}>{{ __t('shop.name') }}</option>
            </select>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-6">

        {{-- ============ SIDEBAR FILTERS ============ --}}
        <aside id="filters-panel"
               :class="mobileFiltersOpen ? 'fixed inset-0 z-50 bg-white overflow-y-auto p-6 lg:p-0 lg:relative lg:inset-auto lg:z-auto lg:bg-transparent lg:overflow-visible' : 'hidden lg:block'"
               class="lg:col-span-1"
               role="dialog"
               :aria-modal="mobileFiltersOpen ? 'true' : 'false'"
               aria-label="{{ __t('shop.filter') }}">

            {{-- Mobile close button --}}
            <div class="lg:hidden flex items-center justify-between mb-4 pb-4 border-b">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-600">filter_list</span>
                    {{ __t('shop.filter') }}
                </h2>
                <button type="button" @click="mobileFiltersOpen = false"
                        class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full hover:bg-gray-100"
                        aria-label="{{ __t('ui.close') }}">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="bg-white border border-outline-variant rounded-xl p-6 h-fit lg:sticky lg:top-24 shadow-sm">
                <form method="GET" action="{{ route('shop.index') }}" class="space-y-6">
                    <div class="hidden lg:flex items-center justify-between pb-4 border-b border-outline-variant">
                        <h2 class="font-bold text-lg text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-600">filter_list</span>
                            {{ __t('shop.filter') }}
                        </h2>
                    </div>

                    {{-- Search Input inside sidebar --}}
                    <div>
                        <h3 class="font-semibold text-sm mb-3">{{ __t('shop.quick_search') }}</h3>
                        <label for="filter-search" class="sr-only">{{ __t('shop.search_placeholder') }}</label>
                        <div class="relative">
                            <input id="filter-search" type="text" name="q" value="{{ request('q') }}" placeholder="{{ __t('shop.search_placeholder') }}"
                                   class="w-full text-sm pe-9 ps-3 py-2 rounded-lg border border-outline-variant focus:outline-none focus:ring-2 focus:ring-primary">
                            <span class="material-symbols-outlined absolute end-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
                        </div>
                    </div>

                    {{-- Categories list --}}
                    <div>
                        <h3 class="font-semibold text-sm mb-3">{{ __t('shop.categories') }}</h3>
                        <div class="space-y-1 pe-1">
                            <a href="{{ route('shop.index') }}"
                               class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition
                                      {{ !request('category_id') && !request('category') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                <span class="flex items-center"><span class="material-symbols-outlined text-xs me-1.5">grid_view</span>{{ __t('shop.all_products') }}</span>
                            </a>
                            @foreach($categories as $cat)
                                <a href="{{ route('shop.category', ['slug' => $cat->slug]) }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition
                                          {{ request('category') == $cat->slug ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <span>{{ $cat->name }}</span>
                                    <span class="text-xs px-1.5 py-0.5 rounded {{ request('category') == $cat->slug ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $cat->products_count ?? $cat->products()->count() }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Price range filter --}}
                    <div x-data="{ maxPrice: {{ (int) request('max_price', 1000) }} }">
                        <h3 class="font-semibold text-sm mb-3 flex items-center justify-between">
                            <span>{{ __t('shop.max_price') }}</span>
                            <span class="text-xs text-brand-600 font-bold" x-text="maxPrice + ' ' + @js(currentCurrencySymbol())"></span>
                        </h3>
                        <div class="px-1">
                            <input type="range" name="max_price" min="0" max="2000" step="50" x-model="maxPrice"
                                   class="w-full h-1.5 bg-surface-container rounded-lg appearance-none cursor-pointer accent-primary">
                            <div class="flex justify-between mt-2 text-[10px] text-on-surface-variant font-medium">
                                <span>0 {{ currentCurrencySymbol() }}</span>
                                <span>2000 {{ currentCurrencySymbol() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Rating filter --}}
                    <div>
                        <h3 class="font-semibold text-sm mb-3">{{ __t('shop.rating') }}</h3>
                        <div class="space-y-2.5">
                            @foreach([5, 4, 3] as $rating)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="rating" value="{{ $rating }}" {{ request('rating') == $rating ? 'checked' : '' }} class="form-radio text-primary border-outline-variant">
                                    <div class="flex text-yellow-500">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' {{ $i <= $rating ? 1 : 0 }}">star</span>
                                        @endfor
                                    </div>
                                    @if($rating < 5)
                                        <span class="text-xs text-on-surface-variant group-hover:text-on-surface transition-colors">{{ __t('shop.and_up') }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Submit & Reset Buttons --}}
                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit" class="w-full bg-primary text-white py-2.5 min-h-[44px] rounded-lg font-semibold text-sm hover:opacity-90 transition-all active:scale-[0.98] shadow-sm flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">check</span>
                            {{ __t('shop.apply_filters') }}
                        </button>
                        <a href="{{ route('shop.index') }}" class="w-full text-center border border-outline-variant text-on-surface-variant py-2.5 min-h-[44px] rounded-lg font-semibold text-sm hover:bg-surface-container transition-all block">
                            {{ __t('shop.reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </aside>

        {{-- ============ PRODUCTS GRID & HEADER ============ --}}
        <div class="lg:col-span-3">
            {{-- Header Bar --}}
            <div class="bg-white border border-outline-variant rounded-xl p-4 mb-6 flex flex-row justify-between items-center gap-4 shadow-sm">
                <div class="hidden sm:block">
                    <h2 class="font-bold text-base text-on-surface">{{ __t('shop.all_products') }} ({{ $products->total() }} {{ __t('shop.product') }})</h2>
                </div>
                <div class="flex items-center gap-2 ms-auto">
                    <label for="sort-desktop" class="sr-only">{{ __t('shop.sort_by') }}</label>
                    <div class="hidden lg:flex items-center gap-2 bg-surface-container-low px-3 py-1.5 rounded-lg border border-outline-variant text-sm">
                        <span class="text-xs text-on-surface-variant font-medium">{{ __t('shop.sort_by') }}:</span>
                        <select id="sort-desktop" onchange="window.location.href = this.value" class="bg-transparent border-none focus:ring-0 text-xs font-bold pe-8 cursor-pointer py-0">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => 'desc']) }}" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>{{ __t('shop.sort_newest') }}</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => 'asc']) }}" {{ request('sort') == 'price' && request('dir') == 'asc' ? 'selected' : '' }}>{{ __t('shop.sort_price_low') }}</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => 'desc']) }}" {{ request('sort') == 'price' && request('dir') == 'desc' ? 'selected' : '' }}>{{ __t('shop.sort_price_high') }}</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'best_selling', 'dir' => 'desc']) }}" {{ request('sort') == 'best_selling' ? 'selected' : '' }}>{{ __t('shop.sort_best_selling') }}</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => 'asc']) }}" {{ request('sort') == 'name' ? 'selected' : '' }}>{{ __t('shop.name') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            @if($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                    @foreach($products as $product)
                        @include('frontend.partials.product-card', ['product' => $product, 'symbol' => currentCurrencySymbol()])
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="bg-white border border-outline-variant rounded-xl">
                    <div class="p-16 text-center">
                        <div class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-gray-100 flex items-center justify-center" aria-hidden="true">
                            <span class="material-symbols-outlined text-5xl text-gray-300">inventory_2</span>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">{{ __t('shop.no_products') }}</h3>
                        <p class="text-gray-500 mb-6">{{ __t('shop.no_products_desc') }}</p>
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-3 min-h-[44px] bg-primary text-white rounded-lg font-semibold hover:opacity-90 transition">
                            <span class="material-symbols-outlined">grid_view</span>
                            {{ __t('shop.reset_filters') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
