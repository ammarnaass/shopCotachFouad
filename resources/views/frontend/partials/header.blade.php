{{--
    AN Shop Header — Modern, High-Performance, Mobile-First, RTL-Aware.
    Receives from AppServiceProvider view composer:
      $navItems, $navCategories, $navPages, $cartCount, $wishlistCount
--}}

@php
    $useAltHeaderBg = trim(site('top_bar_show', '0')) === '1';
    $topBarBg = site('top_bar_bg_color', '#004ac6');
    $topBarColor = site('top_bar_text_color', '#ffffff');
    $topBarText = site('top_bar_text', '');
    $topBarLink = site('top_bar_link');
    $topBarPhone = site('top_bar_phone');

    $storeLogo = site('store_logo');
    $storeName = site('store_name', config('app.name'));

    $isAuth = auth()->check();
    $user = auth()->user();

    $supportedLangs = [
        'ar' => ['name' => 'العربية', 'flag' => '🇩🇿', 'code' => 'AR'],
        'en' => ['name' => 'English', 'flag' => '🇬🇧', 'code' => 'EN'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'code' => 'FR'],
    ];
    $currentLoc = current_locale();
@endphp

{{-- ============ TOP ANNOUNCEMENT BAR ============ --}}
@if($useAltHeaderBg && !empty($topBarText))
    <div class="text-xs py-2.5 relative z-40 transition-colors shadow-2xs" style="background: linear-gradient(135deg, {{ $topBarBg }} 0%, {{ $topBarBg }}ee 100%); color: {{ $topBarColor }}">
        <div class="container-app flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-4 flex-wrap">
                @if($topBarLink)
                    <a href="{{ $topBarLink }}" class="flex items-center gap-1.5 hover:opacity-90 transition font-semibold">
                        <span class="material-symbols-outlined text-sm animate-pulse">campaign</span>
                        <span>{{ $topBarText }}</span>
                    </a>
                @else
                    <span class="flex items-center gap-1.5 font-semibold">
                        <span class="material-symbols-outlined text-sm animate-pulse">campaign</span>
                        <span>{{ $topBarText }}</span>
                    </span>
                @endif

                @if($topBarPhone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $topBarPhone) }}" class="hidden sm:flex items-center gap-1.5 hover:opacity-90 font-mono" dir="ltr">
                        <span class="material-symbols-outlined text-xs">call</span>
                        <span>{{ $topBarPhone }}</span>
                    </a>
                @endif

                @if(site('top_bar_show_cod', '1') === '1')
                    <span class="hidden md:flex items-center gap-1.5 opacity-90">
                        <span class="material-symbols-outlined text-xs">payments</span>
                        <span>{{ __t('topbar.cod') ?? 'الدفع عند الاستلام متاح لجميع الولايات' }}</span>
                    </span>
                @endif
            </div>

            <div class="hidden sm:flex items-center gap-3 text-[11px] opacity-90">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">local_shipping</span>
                    <span>توصيل سريع 58 ولاية</span>
                </span>
            </div>
        </div>
    </div>
@endif

{{-- ============ MAIN HEADER ============ --}}
<header class="sticky top-0 z-40 bg-surface-container-lowest/95 backdrop-blur-md border-b border-outline-variant/40 shadow-xs transition-all duration-200"
        x-data="{
            mobileMenu: false,
            searchOpen: false,
            userMenu: false,
            langMenu: false,
            searchQuery: '',
            searchResults: [],
            isSearching: false,
            hasSearched: false,
            searchTimeout: null,
            init() {
                this.$watch('searchOpen', (val) => {
                    if (val) {
                        this.$nextTick(() => {
                            const el = document.getElementById('live-search-modal-input');
                            if (el) el.focus();
                        });
                    } else {
                        this.searchQuery = '';
                        this.searchResults = [];
                        this.hasSearched = false;
                        this.isSearching = false;
                    }
                });
            },
            doLiveSearch() {
                clearTimeout(this.searchTimeout);
                const q = (this.searchQuery || '').trim();
                if (q.length < 1) {
                    this.searchResults = [];
                    this.isSearching = false;
                    this.hasSearched = false;
                    return;
                }
                this.isSearching = true;
                this.searchTimeout = setTimeout(() => {
                    fetch('/api/products?q=' + encodeURIComponent(q) + '&per_page=6')
                        .then(r => r.json())
                        .then(res => {
                            this.searchResults = res.data || [];
                            this.isSearching = false;
                            this.hasSearched = true;
                        })
                        .catch(() => {
                            this.isSearching = false;
                            this.hasSearched = true;
                        });
                }, 200);
            }
        }"
        @keydown.escape.window="mobileMenu = false; searchOpen = false; userMenu = false; langMenu = false">

    <div class="container-app flex items-center justify-between h-18 sm:h-20 gap-3 sm:gap-6">

        {{-- 1. Brand Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0 group focus:outline-none" aria-label="{{ $storeName }}">
            @if($storeLogo)
                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl object-cover ring-2 ring-primary/20 shadow-2xs group-hover:scale-105 transition-transform" loading="eager">
            @else
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-gradient-to-br from-primary to-primary-container text-white flex items-center justify-center shadow-xs ring-2 ring-primary/20 group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">storefront</span>
                </div>
            @endif
            <div class="flex flex-col leading-none">
                <span class="font-sora font-black tracking-tight text-primary uppercase text-base sm:text-lg group-hover:brightness-110 transition-all">{{ $storeName }}</span>
            </div>
        </a>

        {{-- 2. Desktop Navigation Menu --}}
        <nav class="hidden lg:flex items-center gap-1.5" aria-label="{{ __t('nav.primary') ?? 'القائمة الرئيسية' }}">
            @if(isset($navItems) && is_iterable($navItems))
                @foreach($navItems as $item)
                    @if(($item['type'] ?? '') === 'home' && site('nav_show_home', '1') === '1')
                        @php $active = request()->routeIs('home'); @endphp
                        <a href="{{ route('home') }}"
                           class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-1.5 {{ $active ? 'bg-primary/10 text-primary shadow-2xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}"
                           {{ $active ? 'aria-current=page' : '' }}>
                            <span>{{ __t('nav.home') ?? 'الرئيسية' }}</span>
                        </a>
                    @elseif(($item['type'] ?? '') === 'products' && site('nav_show_products', '1') === '1')
                        @php $active = request()->routeIs('shop.index') && !request('featured'); @endphp
                        <a href="{{ route('shop.index') }}"
                           class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-1.5 {{ $active ? 'bg-primary/10 text-primary shadow-2xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}"
                           {{ $active ? 'aria-current=page' : '' }}>
                            <span>{{ __t('nav.products') ?? 'المنتجات' }}</span>
                        </a>
                    @elseif(($item['type'] ?? '') === 'category' && site('nav_show_categories', '1') === '1' && !empty($item['data']))
                        @php
                            $cat = $item['data'];
                            $active = request()->is('category/'.$cat->slug) || request()->fullUrlIs('*category/'.$cat->slug.'*');
                        @endphp
                        <a href="{{ route('shop.category', ['slug' => $cat->slug]) }}"
                           class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-1.5 {{ $active ? 'bg-primary/10 text-primary shadow-2xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}"
                           {{ $active ? 'aria-current=page' : '' }}>
                            <span>{{ $cat->name }}</span>
                        </a>
                    @elseif(($item['type'] ?? '') === 'page' && !empty($item['data']))
                        @php
                            $p = $item['data'];
                            $active = request()->is('page/'.$p->slug);
                        @endphp
                        <a href="{{ route('page.show', ['slug' => $p->slug]) }}"
                           class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-1.5 {{ $active ? 'bg-primary/10 text-primary shadow-2xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}"
                           {{ $active ? 'aria-current=page' : '' }}>
                            <span>{{ $p->title }}</span>
                        </a>
                    @elseif(($item['type'] ?? '') === 'contact' && site('nav_show_contact', '1') === '1')
                        @php $active = request()->is('page/contact') || request()->is('contact'); @endphp
                        <a href="{{ route('page.show', ['slug' => 'contact']) }}"
                           class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-1.5 {{ $active ? 'bg-primary/10 text-primary shadow-2xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}"
                           {{ $active ? 'aria-current=page' : '' }}>
                            <span>{{ __t('nav.contact') ?? 'اتصل بنا' }}</span>
                        </a>
                    @endif
                @endforeach
            @else
                <a href="{{ route('home') }}" class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-primary bg-primary/10">{{ __t('nav.home') ?? 'الرئيسية' }}</a>
                <a href="{{ route('shop.index') }}" class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low">{{ __t('nav.products') ?? 'المنتجات' }}</a>
                <a href="{{ route('page.show', ['slug' => 'contact']) }}" class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low">{{ __t('nav.contact') ?? 'اتصل بنا' }}</a>
            @endif
        </nav>

        {{-- 3. Header Action Icons (Search, Wishlist, Language, Account/Login, Mobile Menu) --}}
        <div class="flex items-center gap-1.5 sm:gap-2.5">

            {{-- Live Search Trigger Button --}}
            <button type="button" @click="searchOpen = true"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface-container-low/50 hover:bg-surface-container text-on-surface hover:text-primary transition-colors cursor-pointer"
                    aria-label="{{ __t('nav.search') ?? 'البحث' }}">
                <span class="material-symbols-outlined text-xl">search</span>
            </button>

            {{-- Wishlist Button with Animated Counter --}}
            <a href="{{ route('wishlist.index') }}"
               class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface-container-low/50 hover:bg-surface-container text-on-surface hover:text-tertiary transition-colors relative"
               aria-label="{{ __t('nav.wishlist') ?? 'المفضلة' }}">
                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 0;">favorite</span>
                @if(($wishlistCount ?? 0) > 0)
                    <span class="absolute -top-1 -end-1 bg-tertiary text-on-tertiary text-[10px] font-mono font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-xs animate-scale-in">
                        {{ $wishlistCount }}
                    </span>
                @endif
            </a>

            {{-- Language Selector Dropdown --}}
            <div class="relative" @click.outside="langMenu = false">
                <button type="button" @click="langMenu = !langMenu"
                        class="h-10 px-2.5 sm:px-3 flex items-center gap-1.5 rounded-xl bg-surface-container-low/50 hover:bg-surface-container text-on-surface font-bold text-xs transition-colors"
                        aria-label="تغيير اللغة">
                    <span class="text-base">{{ $supportedLangs[$currentLoc]['flag'] ?? '🌐' }}</span>
                    <span class="hidden sm:inline font-mono uppercase">{{ $supportedLangs[$currentLoc]['code'] ?? 'AR' }}</span>
                    <span class="material-symbols-outlined text-xs text-on-surface-variant">expand_more</span>
                </button>

                <div x-show="langMenu"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     x-cloak
                     class="absolute end-0 mt-2 w-36 bg-surface-container-lowest rounded-2xl shadow-xl border border-outline-variant/60 py-1.5 z-50 overflow-hidden">
                    @foreach($supportedLangs as $loc => $info)
                        <a href="{{ route('lang.switch', ['locale' => $loc]) }}"
                           class="flex items-center justify-between px-3.5 py-2 text-xs font-semibold text-on-surface hover:bg-surface-container-low transition-colors {{ $currentLoc === $loc ? 'text-primary bg-primary/5 font-bold' : '' }}">
                            <span class="flex items-center gap-2">
                                <span>{{ $info['flag'] }}</span>
                                <span>{{ $info['name'] }}</span>
                            </span>
                            @if($currentLoc === $loc)
                                <span class="material-symbols-outlined text-sm text-primary">check</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- User Account / Auth Dropdown --}}
            @auth
                <div class="relative" @click.outside="userMenu = false">
                    <button type="button" @click="userMenu = !userMenu"
                            class="h-10 px-2 sm:px-3 flex items-center gap-2 rounded-xl bg-surface-container-low/50 hover:bg-surface-container text-on-surface font-bold text-xs transition-colors"
                            aria-label="قائمة الحساب">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-6 h-6 rounded-full object-cover ring-1 ring-primary/30">
                        @else
                            <div class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold font-mono">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        <span class="hidden md:inline max-w-[100px] truncate">{{ $user->name }}</span>
                        <span class="material-symbols-outlined text-xs text-on-surface-variant">expand_more</span>
                    </button>

                    <div x-show="userMenu"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         x-cloak
                         class="absolute end-0 mt-2 w-64 bg-surface-container-lowest rounded-2xl shadow-xl border border-outline-variant/60 py-2 z-50 overflow-hidden">
                        {{-- User Header --}}
                        <div class="px-4 py-3 border-b border-outline-variant/30 bg-surface-container-low/30">
                            <p class="font-extrabold text-sm text-on-surface truncate">{{ $user->name }}</p>
                            <p class="text-xs text-on-surface-variant font-mono truncate mt-0.5">{{ $user->email }}</p>
                        </div>

                        {{-- Links --}}
                        <div class="py-1.5 space-y-0.5">
                            <a href="{{ route('account.index') }}" class="flex items-center gap-3 px-4 py-2 text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-lg text-primary">person</span>
                                <span>{{ __t('account.profile') ?? 'الملف الشخصي' }}</span>
                            </a>
                            <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-2 text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-lg text-primary">inventory_2</span>
                                <span>{{ __t('nav.my_orders') ?? 'طلباتي' }}</span>
                            </a>
                            <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-4 py-2 text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-lg text-primary">favorite</span>
                                <span>{{ __t('nav.wishlist') ?? 'المفضلة' }}</span>
                            </a>
                            @if(method_exists($user, 'isAdmin') && ($user->isAdmin() ?? false))
                                <div class="my-1 border-t border-outline-variant/30"></div>
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-xs sm:text-sm font-bold text-primary hover:bg-primary/10 transition-colors">
                                    <span class="material-symbols-outlined text-lg">dashboard</span>
                                    <span>لوحة التحكم (Admin)</span>
                                </a>
                            @endif
                        </div>

                        {{-- Logout --}}
                        <div class="border-t border-outline-variant/30 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full text-start px-4 py-2 text-xs sm:text-sm font-semibold text-error hover:bg-red-50 transition-colors">
                                    <span class="material-symbols-outlined text-lg">logout</span>
                                    <span>{{ __t('nav.logout') ?? 'تسجيل الخروج' }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl bg-primary text-on-primary font-bold text-xs sm:text-sm hover:brightness-105 active:scale-95 transition-all shadow-2xs">
                    <span class="material-symbols-outlined text-base">login</span>
                    <span class="hidden sm:inline">{{ __t('nav.login') ?? 'تسجيل الدخول' }}</span>
                </a>
            @endauth

            {{-- Mobile Drawer Hamburger Button --}}
            <button type="button" @click="mobileMenu = !mobileMenu"
                    class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-surface-container-low/50 hover:bg-surface-container text-on-surface transition-colors"
                    :aria-expanded="mobileMenu" aria-controls="mobile-drawer" aria-label="{{ __t('nav.menu') ?? 'القائمة' }}">
                <span class="material-symbols-outlined text-2xl" x-text="mobileMenu ? 'close' : 'menu'"></span>
            </button>
        </div>
    </div>

    {{-- ============ FULL-SCREEN LIVE SEARCH MODAL (TELEPORTED TO BODY) ============ --}}
    <template x-teleport="body">
        <div x-show="searchOpen" x-cloak class="fixed inset-0 z-[100] flex flex-col items-center justify-start p-4 sm:p-6 md:p-12 overflow-y-auto">
            {{-- Backdrop Blur Overlay --}}
            <div class="fixed inset-0 bg-black/60 backdrop-blur-md transition-opacity duration-200"
                 x-show="searchOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="searchOpen = false"></div>

            {{-- Floating Search Modal Container --}}
            <div class="relative w-full max-w-2xl bg-surface-container-lowest border border-outline-variant/60 rounded-3xl shadow-2xl overflow-hidden z-10 transition-all duration-300 transform"
                 x-show="searchOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
                 @click.outside="searchOpen = false">

                {{-- Search Header Form --}}
                <form action="{{ route('shop.index') }}" method="GET" class="relative border-b border-outline-variant/50 p-4 sm:p-5 flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-2xl shrink-0">search</span>

                    <input id="live-search-modal-input"
                           type="search"
                           name="q"
                           x-model="searchQuery"
                           @input="doLiveSearch()"
                           placeholder="{{ __t('nav.search_placeholder') ?? 'ابحث عن اسم منتج، مكمل، كرياتين، بروتين...' }}"
                           class="w-full bg-transparent text-sm sm:text-base font-bold text-on-surface placeholder:text-on-surface-variant/60 focus:outline-none"
                           autocomplete="off">

                    {{-- Loading Spinner --}}
                    <span x-show="isSearching" class="material-symbols-outlined text-primary text-xl animate-spin shrink-0">progress_activity</span>

                    {{-- Clear Button --}}
                    <button type="button" x-show="searchQuery.length > 0 && !isSearching" @click="searchQuery = ''; searchResults = []; hasSearched = false"
                            class="p-1 rounded-lg text-on-surface-variant hover:text-on-surface transition-colors"
                            aria-label="مسح">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>

                    {{-- Close Modal Button --}}
                    <button type="button" @click="searchOpen = false"
                            class="px-2.5 py-1 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface text-xs font-bold font-mono transition-colors shrink-0 flex items-center gap-1">
                        <span>ESC</span>
                    </button>
                </form>

                {{-- Live Results Container --}}
                <div class="max-h-[60vh] overflow-y-auto p-4 sm:p-5">

                    {{-- State 1: Results Found --}}
                    <template x-if="searchResults.length > 0">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between text-xs font-bold text-on-surface-variant px-1">
                                <span x-text="'النتائج المقترحة (' + searchResults.length + ')'"></span>
                                <a :href="'{{ route('shop.index') }}?q=' + encodeURIComponent(searchQuery)" class="text-primary hover:underline">عرض الكل في المتجر</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="product in searchResults" :key="product.id">
                                    <a :href="product.url"
                                       class="flex items-center gap-3 p-3 rounded-2xl bg-surface-container-low/40 hover:bg-surface-container border border-outline-variant/40 hover:border-primary/40 transition-all group">
                                        <div class="w-14 h-14 rounded-xl bg-surface-container-lowest p-1.5 flex items-center justify-center shrink-0 border border-outline-variant/30 overflow-hidden">
                                            <template x-if="product.image_url">
                                                <img :src="product.image_url" :alt="product.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform">
                                            </template>
                                            <template x-if="!product.image_url">
                                                <span class="material-symbols-outlined text-primary/40 text-2xl">inventory_2</span>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[11px] text-on-surface-variant truncate font-semibold" x-text="product.category_name || 'منتج'"></p>
                                            <h4 class="text-xs sm:text-sm font-extrabold text-on-surface truncate group-hover:text-primary transition-colors" x-text="product.name"></h4>
                                            <p class="text-xs font-black text-primary font-mono mt-0.5" x-text="product.formatted_price"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>

                            {{-- Full Search CTA Button --}}
                            <div class="pt-2">
                                <a :href="'{{ route('shop.index') }}?q=' + encodeURIComponent(searchQuery)"
                                   class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary text-white font-bold text-xs sm:text-sm shadow-md hover:brightness-105 transition-all">
                                    <span class="material-symbols-outlined text-lg">storefront</span>
                                    <span>عرض كافة النتائج لـ "<span x-text="searchQuery"></span>" في صفحة المتجر</span>
                                </a>
                            </div>
                        </div>
                    </template>

                    {{-- State 2: No Results Found --}}
                    <template x-if="hasSearched && searchResults.length === 0 && !isSearching">
                        <div class="py-8 text-center space-y-3">
                            <div class="w-14 h-14 rounded-2xl bg-surface-container-low text-on-surface-variant mx-auto flex items-center justify-center">
                                <span class="material-symbols-outlined text-3xl">search_off</span>
                            </div>
                            <h3 class="text-sm sm:text-base font-extrabold text-on-surface">لم يتم العثور على منتجات مطابقة لـ "<span x-text="searchQuery"></span>"</h3>
                            <p class="text-xs text-on-surface-variant max-w-sm mx-auto">تأكد من كتابة الكلمات بشكل صحيح أو تصفح كافة أقسام المتجر.</p>
                            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs shadow-2xs hover:brightness-105 transition-all">
                                <span>تصفح جميع المنتجات</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </template>

                    {{-- State 3: Empty State (Quick Search Suggestions) --}}
                    <template x-if="!hasSearched && searchResults.length === 0 && !isSearching">
                        <div class="space-y-4 py-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-on-surface-variant">
                                <span class="material-symbols-outlined text-base text-primary">local_fire_department</span>
                                <span>عمليات البحث الشائعة والتصنيفات</span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if(isset($navCategories) && is_iterable($navCategories))
                                    @foreach($navCategories as $cat)
                                        <a href="{{ route('shop.category', ['slug' => $cat->slug]) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-surface-container-low/70 hover:bg-primary hover:text-white border border-outline-variant/40 text-xs font-bold text-on-surface transition-all">
                                            @if($cat->icon)
                                                <span class="material-symbols-outlined text-sm">{{ $cat->icon }}</span>
                                            @endif
                                            <span>{{ $cat->name }}</span>
                                        </a>
                                    @endforeach
                                @else
                                    <a href="{{ route('shop.index', ['q' => 'بروتين']) }}" class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-primary hover:text-white text-xs font-bold text-on-surface transition-all">بروتين</a>
                                    <a href="{{ route('shop.index', ['q' => 'كرياتين']) }}" class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-primary hover:text-white text-xs font-bold text-on-surface transition-all">كرياتين</a>
                                    <a href="{{ route('shop.index', ['q' => 'فيتامينات']) }}" class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-primary hover:text-white text-xs font-bold text-on-surface transition-all">فيتامينات</a>
                                    <a href="{{ route('shop.index', ['q' => 'شيكر']) }}" class="px-3 py-2 rounded-xl bg-surface-container-low hover:bg-primary hover:text-white text-xs font-bold text-on-surface transition-all">شيكر / خلاط</a>
                                @endif
                            </div>
                        </div>
                    </template>

                </div>

            </div>
        </div>
    </template>

    {{-- ============ MOBILE NAVIGATION DRAWER (TELEPORTED TO BODY) ============ --}}
    <template x-teleport="body">
        <div id="mobile-drawer" x-show="mobileMenu" x-cloak class="fixed inset-0 z-[100] lg:hidden">
            {{-- Backdrop Overlay --}}
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity duration-300"
                 x-show="mobileMenu"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenu = false"></div>

            {{-- Off-Canvas Sidebar Container --}}
            <div class="fixed inset-y-0 {{ is_rtl() ? 'right-0' : 'left-0' }} w-80 max-w-[85vw] bg-surface-container-lowest shadow-2xl flex flex-col z-10 transition-transform duration-300 ease-in-out"
                 x-show="mobileMenu"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="{{ is_rtl() ? 'translate-x-full' : '-translate-x-full' }}"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="{{ is_rtl() ? 'translate-x-full' : '-translate-x-full' }}">

                {{-- Drawer Header with Brand and Close Button --}}
                <div class="p-4 sm:p-5 flex items-center justify-between border-b border-outline-variant/40 bg-surface-container-low/40">
                    <a href="{{ route('home') }}" @click="mobileMenu = false" class="flex items-center gap-2.5">
                        @if($storeLogo)
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-8 h-8 rounded-xl object-cover ring-1 ring-primary/20">
                        @else
                            <div class="w-8 h-8 rounded-xl bg-primary text-white flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg">storefront</span>
                            </div>
                        @endif
                        <span class="font-sora font-black text-primary text-base uppercase">{{ $storeName }}</span>
                    </a>
                    <button type="button" @click="mobileMenu = false"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-container-low text-on-surface-variant hover:text-on-surface"
                            aria-label="إغلاق القائمة">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                {{-- Drawer Search Input Trigger --}}
                <div class="p-3 border-b border-outline-variant/30">
                    <button type="button" @click="mobileMenu = false; searchOpen = true"
                            class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-surface-container-low text-on-surface-variant text-xs font-semibold hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-base text-primary">search</span>
                        <span>ابحث عن منتج...</span>
                    </button>
                </div>

                {{-- Drawer Scrollable Content --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4">

                    {{-- Navigation Links --}}
                    <div class="space-y-1">
                        <span class="px-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1">التصفح</span>
                        @if(isset($navItems) && is_iterable($navItems))
                            @foreach($navItems as $item)
                                @if(($item['type'] ?? '') === 'home' && site('nav_show_home', '1') === '1')
                                    @php $active = request()->routeIs('home'); @endphp
                                    <a href="{{ route('home') }}" @click="mobileMenu = false"
                                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">home</span>
                                        <span>{{ __t('nav.home') ?? 'الرئيسية' }}</span>
                                    </a>
                                @elseif(($item['type'] ?? '') === 'products' && site('nav_show_products', '1') === '1')
                                    @php $active = request()->routeIs('shop.index') && !request('featured'); @endphp
                                    <a href="{{ route('shop.index') }}" @click="mobileMenu = false"
                                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">inventory_2</span>
                                        <span>{{ __t('nav.products') ?? 'المنتجات' }}</span>
                                    </a>
                                @elseif(($item['type'] ?? '') === 'category' && site('nav_show_categories', '1') === '1' && !empty($item['data']))
                                    @php
                                        $cat = $item['data'];
                                        $active = request()->is('category/'.$cat->slug) || request()->fullUrlIs('*category/'.$cat->slug.'*');
                                    @endphp
                                    <a href="{{ route('shop.category', ['slug' => $cat->slug]) }}" @click="mobileMenu = false"
                                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">{{ $cat->icon ?? 'category' }}</span>
                                        <span>{{ $cat->name }}</span>
                                    </a>
                                @elseif(($item['type'] ?? '') === 'page' && !empty($item['data']))
                                    @php
                                        $p = $item['data'];
                                        $active = request()->is('page/'.$p->slug);
                                    @endphp
                                    <a href="{{ route('page.show', ['slug' => $p->slug]) }}" @click="mobileMenu = false"
                                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">{{ $p->icon ?? 'description' }}</span>
                                        <span>{{ $p->title }}</span>
                                    </a>
                                @elseif(($item['type'] ?? '') === 'contact' && site('nav_show_contact', '1') === '1')
                                    @php $active = request()->is('page/contact') || request()->is('contact'); @endphp
                                    <a href="{{ route('page.show', ['slug' => 'contact']) }}" @click="mobileMenu = false"
                                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">headset_mic</span>
                                        <span>{{ __t('nav.contact') ?? 'اتصل بنا' }}</span>
                                    </a>
                                @endif
                            @endforeach
                        @else
                            <a href="{{ route('home') }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-primary text-white">
                                <span class="material-symbols-outlined text-lg">home</span>
                                <span>{{ __t('nav.home') ?? 'الرئيسية' }}</span>
                            </a>
                            <a href="{{ route('shop.index') }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-on-surface hover:bg-surface-container-low">
                                <span class="material-symbols-outlined text-lg">inventory_2</span>
                                <span>{{ __t('nav.products') ?? 'المنتجات' }}</span>
                            </a>
                            <a href="{{ route('page.show', ['slug' => 'contact']) }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-on-surface hover:bg-surface-container-low">
                                <span class="material-symbols-outlined text-lg">headset_mic</span>
                                <span>{{ __t('nav.contact') ?? 'اتصل بنا' }}</span>
                            </a>
                        @endif
                    </div>

                    {{-- User Account or Login in Drawer --}}
                    @auth
                        <div class="pt-3 border-t border-outline-variant/40 space-y-1">
                            <span class="px-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block mb-1">إدارة الحساب</span>
                            <a href="{{ route('account.index') }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low">
                                <span class="material-symbols-outlined text-lg text-primary">person</span>
                                <span>{{ __t('nav.my_account') ?? 'الملف الشخصي' }}</span>
                            </a>
                            <a href="{{ route('orders.index') }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low">
                                <span class="material-symbols-outlined text-lg text-primary">inventory_2</span>
                                <span>{{ __t('nav.my_orders') ?? 'طلباتي' }}</span>
                            </a>
                            <a href="{{ route('wishlist.index') }}" @click="mobileMenu = false" class="flex items-center justify-between px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low">
                                <span class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-lg text-tertiary">favorite</span>
                                    <span>{{ __t('nav.wishlist') ?? 'المفضلة' }}</span>
                                </span>
                                @if(($wishlistCount ?? 0) > 0)
                                    <span class="text-xs font-mono font-bold bg-tertiary/10 text-tertiary px-2 py-0.5 rounded-md">{{ $wishlistCount }}</span>
                                @endif
                            </a>
                            @if(method_exists($user, 'isAdmin') && ($user->isAdmin() ?? false))
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-primary bg-primary/10">
                                    <span class="material-symbols-outlined text-lg">dashboard</span>
                                    <span>لوحة التحكم (Admin)</span>
                                </a>
                            @endif
                        </div>
                    @endauth

                    {{-- Language Switcher in Drawer --}}
                    <div class="pt-3 border-t border-outline-variant/40">
                        <span class="px-4 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider block mb-2">اللغة / Language</span>
                        <div class="grid grid-cols-3 gap-1.5 px-1">
                            @foreach($supportedLangs as $loc => $info)
                                <a href="{{ route('lang.switch', ['locale' => $loc]) }}"
                                   class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl text-xs font-bold transition-all {{ $currentLoc === $loc ? 'bg-primary text-white shadow-2xs' : 'bg-surface-container-low text-on-surface hover:bg-surface-container' }}">
                                    <span>{{ $info['flag'] }}</span>
                                    <span>{{ $info['code'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Drawer Bottom Action (WhatsApp or Logout) --}}
                <div class="p-3 border-t border-outline-variant/40 bg-surface-container-low/30 space-y-2">
                    @if(site('whatsapp_btn_show', '0') === '1' && site('whatsapp_btn_phone'))
                        @php
                            $waPhone = preg_replace('/\D/', '', site('whatsapp_btn_phone'));
                            $waText = site('whatsapp_btn_text', 'مرحباً، أود الاستفسار');
                            $waUrl = 'https://wa.me/' . $waPhone . '?text=' . urlencode($waText);
                        @endphp
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                           class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20ba5a] text-white text-xs font-bold shadow-2xs transition-all">
                            <span class="material-symbols-outlined text-lg">chat</span>
                            <span>تواصل معنا عبر واتساب</span>
                        </a>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 rounded-xl text-xs font-semibold text-error hover:bg-red-50 transition-colors">
                                <span class="material-symbols-outlined text-base">logout</span>
                                <span>{{ __t('nav.logout') ?? 'تسجيل الخروج' }}</span>
                            </button>
                        </form>
                    @endauth
                </div>

            </div>
        </div>
    </template>
</header>
