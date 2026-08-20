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
            langMenu: false
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
            <button type="button" @click="searchOpen = !searchOpen"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-surface-container-low/50 hover:bg-surface-container text-on-surface transition-colors"
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
                        <a href="{{ route('lang.switch', ['lang' => $loc]) }}"
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

    {{-- ============ COLLAPSIBLE MOBILE SEARCH BAR ============ --}}
    <div x-show="searchOpen" x-collapse x-cloak class="lg:hidden border-t border-outline-variant/40 bg-surface-container-lowest">
        <div class="container-app py-3">
            <form action="{{ route('shop.index') }}" method="GET" class="relative" role="search">
                <label for="mobile-search" class="sr-only">{{ __t('nav.search') }}</label>
                <input id="mobile-search" type="search" name="q" placeholder="{{ __t('nav.search_placeholder') ?? 'ابحث عن منتج...' }}"
                       class="w-full ps-10 pe-20 py-2.5 bg-surface-container-low rounded-xl border border-outline-variant/60 text-xs sm:text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <span class="material-symbols-outlined absolute top-1/2 -translate-y-1/2 start-3 text-on-surface-variant text-base">search</span>
                <button type="submit"
                        class="absolute end-1.5 top-1.5 bottom-1.5 px-3 bg-primary text-on-primary rounded-lg text-xs font-bold">
                    {{ __t('nav.search_submit') ?? 'بحث' }}
                </button>
            </form>
        </div>
    </div>

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

                {{-- Drawer Header --}}
                <div class="flex items-center justify-between p-4 border-b border-outline-variant/40 bg-surface-container-low/40">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if($storeLogo)
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" loading="eager" class="w-8 h-8 rounded-xl object-cover ring-1 ring-primary/20">
                        @else
                            <div class="w-8 h-8 rounded-xl bg-primary text-white flex items-center justify-center shadow-xs">
                                <span class="material-symbols-outlined text-base">storefront</span>
                            </div>
                        @endif
                        <span class="font-black text-sm sm:text-base text-primary tracking-tight truncate">{{ $storeName }}</span>
                    </div>
                    <button type="button" @click="mobileMenu = false"
                            class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-surface-container text-on-surface-variant"
                            aria-label="{{ __t('ui.close') ?? 'إغلاق' }}">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                {{-- Drawer Body / Navigation --}}
                <div class="flex-1 overflow-y-auto p-3 space-y-4">
                    {{-- User Profile Card / Login Prompt --}}
                    @auth
                        <div class="p-3 rounded-2xl bg-surface-container-low/50 border border-outline-variant/50 flex items-center gap-3">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-primary/30 shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-xs sm:text-sm text-on-surface truncate">{{ $user->name }}</p>
                                <p class="text-[11px] text-on-surface-variant font-mono truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-2xl bg-primary/5 border border-primary/20 space-y-2">
                            <p class="text-xs font-bold text-on-surface">مرحباً بك في {{ $storeName }}</p>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('login') }}" class="flex-1 text-center py-2 rounded-xl bg-primary text-white text-xs font-bold shadow-2xs">
                                    {{ __t('nav.login') ?? 'تسجيل الدخول' }}
                                </a>
                                <a href="{{ route('register') }}" class="flex-1 text-center py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/60 text-on-surface text-xs font-bold">
                                    إنشاء حساب
                                </a>
                            </div>
                        </div>
                    @endauth

                    {{-- Main Navigation Links --}}
                    <nav class="space-y-1" aria-label="{{ __t('nav.mobile') }}">
                        @if(isset($navItems) && is_iterable($navItems))
                            @foreach($navItems as $item)
                                @if(($item['type'] ?? '') === 'home' && site('nav_show_home', '1') === '1')
                                    @php $active = request()->routeIs('home'); @endphp
                                    <a href="{{ route('home') }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">home</span>
                                        <span>{{ __t('nav.home') ?? 'الرئيسية' }}</span>
                                    </a>
                                @elseif(($item['type'] ?? '') === 'products' && site('nav_show_products', '1') === '1')
                                    @php $active = request()->routeIs('shop.index'); @endphp
                                    <a href="{{ route('shop.index') }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">shopping_bag</span>
                                        <span>{{ __t('nav.products') ?? 'المنتجات' }}</span>
                                    </a>
                                @elseif(($item['type'] ?? '') === 'category' && site('nav_show_categories', '1') === '1' && !empty($item['data']))
                                    @php $active = request()->is('category/'.$item['data']->slug); @endphp
                                    <a href="{{ route('shop.category', ['slug' => $item['data']->slug]) }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">category</span>
                                        <span>{{ $item['data']->name }}</span>
                                    </a>
                                @elseif(($item['type'] ?? '') === 'page' && !empty($item['data']))
                                    @php $active = request()->is('page/'.$item['data']->slug); @endphp
                                    <a href="{{ route('page.show', ['slug' => $item['data']->slug]) }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-bold transition-colors {{ $active ? 'bg-primary text-white shadow-2xs' : 'text-on-surface hover:bg-surface-container-low' }}">
                                        <span class="material-symbols-outlined text-lg">description</span>
                                        <span>{{ $item['data']->title }}</span>
                                    </a>
                                @elseif(($item['type'] ?? '') === 'contact' && site('nav_show_contact', '1') === '1')
                                    <a href="{{ route('page.show', ['slug' => 'contact']) }}" @click="mobileMenu = false" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-bold text-on-surface hover:bg-surface-container-low">
                                        <span class="material-symbols-outlined text-lg">mail</span>
                                        <span>{{ __t('nav.contact') ?? 'اتصل بنا' }}</span>
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    </nav>

                    {{-- Customer Account Quick Links --}}
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
                                <a href="{{ route('lang.switch', ['lang' => $loc]) }}"
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
