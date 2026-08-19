@extends('frontend.layout')

@section('title', __t('shop.page_title') . ' - ' . site('store_name'))
@section('description', __t('shop.browse_all_products') . ' ' . site('store_name'))

@section('content')
<main class="flex-grow w-full max-w-container-max mx-auto px-4 md:px-6 py-8" x-data="{ mobileFiltersOpen: false }">
    <!-- Hero & Breadcrumb Section -->
    <section class="mb-8 border-b border-outline-variant pb-6">
        <nav class="text-sm text-secondary mb-4 flex items-center gap-2" aria-label="breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors font-body-md">{{ __t('shop.home', [], 'الرئيسية') }}</a>
            <span class="material-symbols-outlined text-sm">chevron_left</span>
            <span class="text-on-background font-medium font-body-md" aria-current="page">{{ __t('shop.page_title', [], 'كل المنتجات') }}</span>
        </nav>
        <h1 class="text-3xl md:text-4xl font-extrabold text-on-surface mb-2 font-display-lg">{{ __t('shop.page_title', [], 'كل المنتجات') }}</h1>
        <p class="text-base text-on-surface-variant font-body-lg">{{ __t('shop.browse_all_products_desc', [], 'تصفح مجموعتنا الواسعة من المكملات الغذائية لدعم أهدافك الرياضية.') }}</p>
    </section>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="w-full lg:w-1/4 shrink-0 space-y-8" id="filters-panel">
            <!-- Mobile Filter Toggle -->
            <button type="button" @click="mobileFiltersOpen = !mobileFiltersOpen"
                    class="lg:hidden w-full flex items-center justify-between bg-surface-container-high p-4 rounded-lg text-on-surface font-semibold">
                <span>تصفية النتائج</span>
                <span class="material-symbols-outlined">filter_list</span>
            </button>

            <!-- Filters Container -->
            <div :class="mobileFiltersOpen ? 'block' : 'hidden lg:block'" class="space-y-6">
                <form method="GET" action="{{ route('shop.index') }}" class="space-y-6">
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    @if(request('dir'))
                        <input type="hidden" name="dir" value="{{ request('dir') }}">
                    @endif

                    <!-- Search Card -->
                    <div class="bg-surface rounded-lg border border-outline-variant p-6">
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-4 pb-2 border-b border-outline-variant font-bold">البحث السريع</h3>
                        <div class="relative">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __t('shop.search_placeholder', [], 'ابحث...') }}"
                                   class="w-full bg-surface-container-low border-none rounded-lg py-2.5 px-4 pl-10 focus:ring-2 focus:ring-primary focus:outline-none text-on-surface font-body-md text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                        </div>
                    </div>

                    <!-- Categories Card -->
                    <div class="bg-surface rounded-lg border border-outline-variant p-6">
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-4 pb-2 border-b border-outline-variant font-bold">الفئات</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="category" value="" onchange="this.form.submit()" {{ !request('category') ? 'checked' : '' }}
                                       class="form-radio text-primary border-outline-variant focus:ring-primary">
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-primary transition-colors">كل الفئات</span>
                            </label>
                            @foreach($categories as $cat)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="category" value="{{ $cat->slug }}" onchange="this.form.submit()" {{ request('category') == $cat->slug ? 'checked' : '' }}
                                           class="form-radio text-primary border-outline-variant focus:ring-primary">
                                    <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-primary transition-colors flex-1 flex justify-between items-center">
                                        <span>{{ $cat->name }}</span>
                                        <span class="text-xs text-secondary font-semibold">({{ $cat->products_count ?? $cat->products()->count() }})</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range Card -->
                    <div class="bg-surface rounded-lg border border-outline-variant p-6" x-data="{ maxPrice: {{ (int) request('max_price', 10000) }} }">
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-4 pb-2 border-b border-outline-variant font-bold">السعر</h3>
                        <div class="space-y-4">
                            <input type="range" name="max_price" min="0" max="20000" step="100" x-model="maxPrice"
                                   class="w-full h-2 bg-surface-container-high rounded-lg appearance-none cursor-pointer accent-primary">
                            <div class="flex justify-between text-sm text-secondary font-body-md">
                                <span>0 {{ currentCurrencySymbol() }}</span>
                                <span x-text="maxPrice + ' ' + @js(currentCurrencySymbol())"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Filter Card -->
                    <div class="bg-surface rounded-lg border border-outline-variant p-6">
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-4 pb-2 border-b border-outline-variant font-bold">التقييم</h3>
                        <div class="space-y-3">
                            @foreach([5, 4, 3] as $rating)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="rating" value="{{ $rating }}" {{ request('rating') == $rating ? 'checked' : '' }}
                                           class="form-radio text-primary border-outline-variant focus:ring-primary">
                                    <div class="flex text-amber-500">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' {{ $i <= $rating ? 1 : 0 }}">star</span>
                                        @endfor
                                    </div>
                                    @if($rating < 5)
                                        <span class="text-xs text-on-surface-variant group-hover:text-primary transition-colors">فأعلى</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Submit & Reset Buttons -->
                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold hover:opacity-90 transition-all shadow-xs flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">check</span>
                            تطبيق الفلترة
                        </button>
                        <a href="{{ route('shop.index') }}" class="w-full text-center bg-surface border border-outline-variant text-on-surface py-2.5 rounded-lg font-bold hover:bg-surface-container-high transition-all block">
                            إعادة ضبط
                        </a>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Main Content: Products Grid -->
        <div class="w-full lg:w-3/4 flex flex-col gap-6">
            <!-- Sorting & Results Count Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-center bg-surface-container-lowest p-4 rounded-lg border border-outline-variant gap-4 shadow-xs">
                <span class="text-on-surface-variant font-body-md text-sm">
                    عرض {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} من أصل {{ $products->total() }} منتج
                </span>
                <div class="flex items-center gap-3">
                    <label class="text-on-surface font-medium font-body-md text-sm" for="sort">ترتيب حسب:</label>
                    <select id="sort" onchange="window.location.href = this.value" class="bg-surface-container-low border-none rounded-md py-2 px-4 focus:ring-2 focus:ring-primary text-on-surface font-body-md text-sm cursor-pointer">
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => 'desc']) }}" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>الأحدث</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => 'asc']) }}" {{ request('sort') == 'price' && request('dir') == 'asc' ? 'selected' : '' }}>السعر: من الأقل للأعلى</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price', 'dir' => 'desc']) }}" {{ request('sort') == 'price' && request('dir') == 'desc' ? 'selected' : '' }}>السعر: من الأعلى للأقل</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'best_selling', 'dir' => 'desc']) }}" {{ request('sort') == 'best_selling' ? 'selected' : '' }}>الأكثر مبيعاً</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => 'asc']) }}" {{ request('sort') == 'name' ? 'selected' : '' }}>الاسم</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        @include('frontend.partials.product-card', ['product' => $product, 'symbol' => currentCurrencySymbol()])
                    @endforeach
                </div>
                <div class="mt-8 flex justify-center">
                    {{ $products->links() }}
                </div>
            @else
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-16 text-center shadow-xs">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-surface-container-high flex items-center justify-center">
                        <span class="material-symbols-outlined text-4xl text-outline">inventory_2</span>
                    </div>
                    <h3 class="text-xl font-bold text-on-surface mb-2">{{ __t('shop.no_products', [], 'لا توجد منتجات') }}</h3>
                    <p class="text-on-surface-variant mb-6">{{ __t('shop.no_products_desc', [], 'لم نتمكن من العثور على منتجات تطابق خيارات التصفية الحالية.') }}</p>
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-lg font-bold hover:opacity-90 transition">
                        <span class="material-symbols-outlined">grid_view</span>
                        {{ __t('shop.reset_filters', [], 'إعادة ضبط التصفية') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</main>
@endsection

