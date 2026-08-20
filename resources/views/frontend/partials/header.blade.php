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
    <div class="text-xs py-2.5 relative z-50 transition-colors shadow-2xs" style="background: linear-gradient(135deg, {{ $topBarBg }} 0%, {{ $topBarBg }}ee 100%); color: {{ $topBarColor }}">
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
<header class="sticky top-0 z-40 bg-surface-container-lowest/90 backdrop-blur-xl border-b border-outline-variant/40 shadow-xs transition-all duration-200"
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
                        @php $active = request()->is('page/'.$item['data']->slug); @endphp
                        <a href="{{ route('page.show', ['slug' => $item['data']->slug]) }}"
                           class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-1.5 {{ $active ? 'bg-primary/10 text-primary shadow-2xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}"
                           {{ $active ? 'aria-current=page' : '' }}>
                            <span>{{ $item['data']->title }}</span>
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
                <a href="{{ route('home') }}" class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all {{ request()->routeIs('home') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    {{ __t('nav.home') ?? 'الرئيسية' }}
                </a>
                <a href="{{ route('shop.index') }}" class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all {{ request()->routeIs('shop.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    {{ __t('nav.products') ?? 'المنتجات' }}
                </a>
            @endif
        </nav>

        {{-- 3. Desktop Live Search --}}
        <div class="hidden lg:flex flex-1 max-w-sm xl:max-w-md mx-2 relative"
             x-data="liveSearch('{{ route('shop.index') }}', 2)"
             @click.outside="close()">
            <form action="{{ route('shop.index') }}" method="GET" class="relative w-full" role="search">
                <label for="header-search" class="sr-only">{{ __t('nav.search') ?? 'بحث' }}</label>
                <input id="header-search"
                       type="search"
                       name="q"
                       x-model="query"
                       @input.debounce.300ms="_search(); show()"
                       @focus="show()"
                       @keydown.escape="close()"
                       placeholder="{{ __t('nav.search_placeholder') ?? 'ابحث عن منتجات...' }}"
                       class="w-full ps-10 pe-4 py-2.5 bg-surface-container-low/70 border border-outline-variant/60 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs sm:text-sm transition-all shadow-2xs font-medium placeholder:text-on-surface-variant/60"
                       autocomplete="off">
                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant/70 text-lg pointer-events-none">search</span>
            </form>

            {{-- Live Search Results Dropdown --}}
            <div x-show="open && (results.length > 0 || loading)"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 x-cloak
                 class="absolute top-full inset-x-0 mt-2 bg-surface-container-lowest rounded-2xl shadow-xl border border-outline-variant/60 overflow-hidden z-50 max-h-96 overflow-y-auto">
                <div x-show="loading" class="p-5 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-2xl text-primary animate-spin">progress_activity</span>
                </div>
                <template x-for="item in results" :key="item.id">
                    <a :href="item.url" class="flex items-center gap-3.5 p-3.5 hover:bg-surface-container-low transition-colors border-b border-outline-variant/30 last:border-0 group">
                        <img :src="item.image" :alt="item.name" class="w-11 h-11 rounded-xl object-cover border border-outline-variant/40 bg-surface-container flex-shrink-0" loading="lazy">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-bold text-on-surface truncate group-hover:text-primary transition-colors" x-text="item.name"></p>
                            <p class="text-xs text-primary font-bold font-mono mt-0.5" x-text="item.price"></p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant/40 group-hover:text-primary group-hover:-translate-x-1 transition-all text-base">chevron_left</span>
                    </a>
                </template>
            </div>
        </div>

        {{-- 4. Right Quick Actions Group --}}
        <div class="flex items-center gap-1.5 sm:gap-2">

            {{-- Mobile Search Trigger Button --}}
            <button type="button" @click="searchOpen = !searchOpen"
                    class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-surface-container-low text-on-surface-variant transition-colors"
                    aria-label="{{ __t('nav.search') ?? 'البحث' }}">
                <span class="material-symbols-outlined text-xl">search</span>
            </button>

            {{-- Wishlist Button with Counter Badge --}}
            <a href="{{ route('wishlist.index') }}"
               class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-surface-container-low/40 hover:bg-primary/10 hover:text-primary text-on-surface-variant transition-all group"
               aria-label="{{ __t('nav.wishlist') ?? 'المفضلة' }}" title="{{ __t('nav.wishlist') ?? 'المفضلة' }}">
                <span class="material-symbols-outlined text-xl transition-transform group-hover:scale-110">favorite</span>
                @if(($wishlistCount ?? 0) > 0)
                    <span class="absolute -top-1 -end-1 bg-tertiary text-white text-[10px] min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center font-bold font-mono shadow-sm animate-fade-in">
                        {{ $wishlistCount }}
                    </span>
                @endif
            </a>

            {{-- Cart Button with Counter Badge --}}
            <a href="{{ route('cart.index') }}"
               class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-surface-container-low/40 hover:bg-primary/10 hover:text-primary text-on-surface-variant transition-all group"
               aria-label="{{ __t('nav.cart') ?? 'سلة المشتريات' }}" title="{{ __t('nav.cart') ?? 'سلة المشتريات' }}">
                <span class="material-symbols-outlined text-xl transition-transform group-hover:scale-110">shopping_bag</span>
                @if(($cartCount ?? 0) > 0)
                    <span class="absolute -top-1 -end-1 bg-primary text-white text-[10px] min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center font-bold font-mono shadow-sm animate-fade-in">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>

            {{-- Language Switcher Dropdown --}}
            <div class="relative" @click.outside="langMenu = false">
                <button type="button" @click="langMenu = !langMenu"
                        class="w-10 sm:w-auto h-10 px-2.5 rounded-xl bg-surface-container-low/40 hover:bg-surface-container text-on-surface-variant flex items-center justify-center gap-1.5 transition-colors"
                        aria-label="{{ __t('nav.language', [], 'اللغة') }}" aria-haspopup="true" :aria-expanded="langMenu">
                    <span class="material-symbols-outlined text-xl text-primary">translate</span>
                    <span class="hidden sm:inline text-xs font-bold text-on-surface font-mono uppercase">{{ $currentLoc }}</span>
                    <span class="material-symbols-outlined text-xs hidden sm:inline text-on-surface-variant/60">expand_more</span>
                </button>

                <div x-show="langMenu"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     x-cloak
                     class="absolute end-0 mt-2 w-48 bg-surface-container-lowest rounded-2xl shadow-xl border border-outline-variant/60 py-2 z-50 overflow-hidden">
                    <div class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60 border-b border-outline-variant/30">
                        {{ __t('nav.select_language') ?? 'اختر اللغة' }}
                    </div>
                    @foreach($supportedLangs as $code => $lang)
                        @php
                            $currentPath = request()->path();
                            $pathWithoutLocale = preg_replace('#^(' . implode('|', array_keys($supportedLangs)) . ')(/|$)#', '', $currentPath);
                            $switchUrl = url($code . '/' . ltrim($pathWithoutLocale, '/'));
                        @endphp
                        <a href="{{ $switchUrl }}"
                           class="flex items-center justify-between px-3.5 py-2.5 text-xs sm:text-sm font-semibold transition-colors {{ $code === $currentLoc ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}"
                           @if($code === $currentLoc) aria-current="true" @endif>
                            <span class="flex items-center gap-2.5">
                                <span class="text-base leading-none">{{ $lang['flag'] }}</span>
                                <span>{{ $lang['name'] }}</span>
                            </span>
                            @if($code === $currentLoc)
                                <span class="material-symbols-outlined text-primary text-base">check</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- User Account Menu / Login Button --}}
            @auth
                <div class="relative" @click.outside="userMenu = false">
                    <button type="button" @click="userMenu = !userMenu"
                            class="h-10 px-2 sm:px-3 rounded-xl bg-primary/10 hover:bg-primary/20 text-primary flex items-center gap-2 transition-all group"
                            aria-label="{{ __t('nav.my_account') }}" aria-haspopup="true" :aria-expanded="userMenu">
                        <div class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold font-mono">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <span class="hidden md:inline text-xs font-bold max-w-[100px] truncate text-on-surface">{{ $user->name }}</span>
                        <span class="material-symbols-outlined text-xs text-primary hidden sm:inline">expand_more</span>
                    </button>

                    {{-- User Dropdown Menu --}}
                    <div x-show="userMenu"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
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

    {{-- ============ MOBILE NAVIGATION DRAWER ============ --}}
    <div id="mobile-drawer" x-show="mobileMenu" x-cloak class="fixed inset-0 z-50 lg:hidden">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-xs" @click="mobileMenu = false" x-transition.opacity></div>

        {{-- Off-Canvas Container --}}
        <div class="absolute inset-y-0 {{ is_rtl() ? 'start-0' : 'end-0' }} w-80 max-w-[85vw] bg-surface-container-lowest shadow-2xl flex flex-col"
             x-show="mobileMenu"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="{{ is_rtl() ? '-translate-x-full' : 'translate-x-full' }}"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="{{ is_rtl() ? '-translate-x-full' : 'translate-x-full' }}">

            {{-- Drawer Header --}}
            <div class="flex items-center justify-between p-4 border-b border-outline-variant/40 bg-surface-container-low/30">
                <div class="flex items-center gap-2.5 min-w-0">
                    @if($storeLogo)
                        <img src="{{ $storeLogo }}" alt="{{ $storeName }}" loading="eager" class="w-8 h-8 rounded-xl object-cover">
                    @else
                        <div class="w-8 h-8 rounded-xl bg-primary text-white flex items-center justify-center">
                            <span class="material-symbols-outlined text-base">storefront</span>
                        </div>
                    @endif
                    <span class="font-extrabold text-sm sm:text-base text-on-surface truncate">{{ $storeName }}</span>
                </div>
                <button type="button" @click="mobileMenu = false"
                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-surface-container text-on-surface-variant"
                        aria-label="{{ __t('ui.close') ?? 'إغلاق' }}">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            {{-- Drawer Navigation --}}
            <nav class="flex-1 overflow-y-auto p-3 space-y-1" aria-label="{{ __t('nav.mobile') }}">
                @if(isset($navItems) && is_iterable($navItems))
                    @foreach($navItems as $item)
                        @if(($item['type'] ?? '') === 'home' && site('nav_show_home', '1') === '1')
                            @php $active = request()->routeIs('home'); @endphp
                            <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold transition-colors {{ $active ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                                <span class="material-symbols-outlined text-lg">home</span>
                                <span>{{ __t('nav.home') ?? 'الرئيسية' }}</span>
                            </a>
                        @elseif(($item['type'] ?? '') === 'products' && site('nav_show_products', '1') === '1')
                            @php $active = request()->routeIs('shop.index'); @endphp
                            <a href="{{ route('shop.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold transition-colors {{ $active ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                                <span class="material-symbols-outlined text-lg">shopping_bag</span>
                                <span>{{ __t('nav.products') ?? 'المنتجات' }}</span>
                            </a>
                        @elseif(($item['type'] ?? '') === 'category' && site('nav_show_categories', '1') === '1' && !empty($item['data']))
                            @php $active = request()->is('category/'.$item['data']->slug); @endphp
                            <a href="{{ route('shop.category', ['slug' => $item['data']->slug]) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold transition-colors {{ $active ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                                <span class="material-symbols-outlined text-lg">category</span>
                                <span>{{ $item['data']->name }}</span>
                            </a>
                        @elseif(($item['type'] ?? '') === 'page' && !empty($item['data']))
                            @php $active = request()->is('page/'.$item['data']->slug); @endphp
                            <a href="{{ route('page.show', ['slug' => $item['data']->slug]) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold transition-colors {{ $active ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface hover:bg-surface-container-low' }}">
                                <span class="material-symbols-outlined text-lg">description</span>
                                <span>{{ $item['data']->title }}</span>
                            </a>
                        @elseif(($item['type'] ?? '') === 'contact' && site('nav_show_contact', '1') === '1')
                            <a href="{{ route('page.show', ['slug' => 'contact']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low">
                                <span class="material-symbols-outlined text-lg">mail</span>
                                <span>{{ __t('nav.contact') ?? 'اتصل بنا' }}</span>
                            </a>
                        @endif
                    @endforeach
                @endif
            </nav>

            {{-- Drawer Footer Actions --}}
            <div class="border-t border-outline-variant/40 p-3 space-y-1 bg-surface-container-low/20">
                @auth
                    <a href="{{ route('account.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-lg text-primary">person</span>
                        <span>{{ __t('nav.my_account') ?? 'حسابي' }}</span>
                    </a>
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-lg text-primary">inventory_2</span>
                        <span>{{ __t('nav.my_orders') ?? 'طلباتي' }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-primary bg-primary/10">
                        <span class="material-symbols-outlined text-lg">login</span>
                        <span>{{ __t('nav.login') ?? 'تسجيل الدخول' }}</span>
                    </a>
                @endauth
                <a href="{{ route('wishlist.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-on-surface hover:bg-surface-container-low">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg text-tertiary">favorite</span>
                        <span>{{ __t('nav.wishlist') ?? 'المفضلة' }}</span>
                    </span>
                    @if(($wishlistCount ?? 0) > 0)
                        <span class="text-xs font-mono font-bold bg-tertiary/10 text-tertiary px-2 py-0.5 rounded-md">{{ $wishlistCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</header>
