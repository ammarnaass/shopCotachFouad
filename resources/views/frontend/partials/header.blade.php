{{--
    AN Shop Header (mobile-first, sticky, RTL-aware).
    Receives the following from AppServiceProvider view composer:
      $navItems   — array of ['type' => home|products|category|page|contact, 'data' => Model|null, 'key' => string]
      $navCategories, $navPages
    Receives per-page from controllers / composer:
      $cartCount, $wishlistCount
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
@endphp

@if($useAltHeaderBg)
    <div class="text-sm py-3 relative z-50" style="background: linear-gradient(135deg, {{ $topBarBg }} 0%, {{ $topBarBg }}dd 100%); color: {{ $topBarColor }}">
        <div class="container-app flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-4 flex-wrap">
                @if($topBarLink)
                    <a href="{{ $topBarLink }}" class="flex items-center gap-1.5 hover:opacity-90 transition">
                        <span class="material-symbols-outlined text-base">campaign</span>
                        <span class="font-bold">{{ $topBarText }}</span>
                    </a>
                @else
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base animate-pulse">campaign</span>
                        <span class="font-bold">{{ $topBarText }}</span>
                    </span>
                @endif
                @if($topBarPhone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $topBarPhone) }}" class="hidden sm:flex items-center gap-1.5 hover:opacity-90">
                        <span class="material-symbols-outlined text-sm">call</span>
                        <span>{{ $topBarPhone }}</span>
                    </a>
                @endif
                @if(site('top_bar_show_cod', '1') === '1')
                    <span class="hidden sm:flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">payments</span>
                        <span>{{ __t('topbar.cod') }}</span>
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif

<header class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm"
        x-data="{
            mobileMenu: false,
            searchOpen: false,
            userMenu: false,
        }"
        @keydown.escape.window="mobileMenu = false; searchOpen = false; userMenu = false">

    <div class="container-app flex items-center justify-between h-16 gap-2">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0 min-w-0" aria-label="{{ $storeName }}">
            @if($storeLogo)
                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-9 h-9 rounded-full object-cover" loading="eager">
            @else
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-accent-500 flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-base">storefront</span>
                </div>
            @endif
            <span class="hidden sm:flex flex-col leading-none">
                <span class="font-extrabold text-[13px] tracking-wide text-on-surface truncate max-w-[160px]">{{ strtoupper($storeName) }}</span>
                <span class="text-[10px] text-accent-500 font-semibold">{{ __t('nav.subtitle') }}</span>
            </span>
        </a>

        {{-- Desktop navigation --}}
        <nav class="hidden lg:flex items-center gap-1" aria-label="{{ __t('nav.primary') }}">
            @foreach($navItems as $item)
                @if(($item['type'] ?? '') === 'home' && site('nav_show_home', '1') === '1')
                    @php $active = request()->routeIs('home'); @endphp
                    <a href="{{ route('home') }}"
                       class="px-3 py-1.5 text-sm rounded-full transition {{ $active ? 'bg-primary text-white font-semibold' : 'text-secondary hover:bg-surface-container-low hover:text-primary font-medium' }}"
                       {{ $active ? 'aria-current=page' : '' }}>
                        {{ __t('nav.home') }}
                    </a>
                @elseif(($item['type'] ?? '') === 'products' && site('nav_show_products', '1') === '1')
                    @php $active = request()->routeIs('shop.index') && !request('featured'); @endphp
                    <a href="{{ route('shop.index') }}"
                       class="px-3 py-1.5 text-sm rounded-full transition {{ $active ? 'bg-primary text-white font-semibold' : 'text-secondary hover:bg-surface-container-low hover:text-primary font-medium' }}"
                       {{ $active ? 'aria-current=page' : '' }}>
                        {{ __t('nav.products') }}
                    </a>
                @elseif(($item['type'] ?? '') === 'category' && site('nav_show_categories', '1') === '1' && !empty($item['data']))
                    @php
                        $cat = $item['data'];
                        $active = request()->is('category/'.$cat->slug) || request()->fullUrlIs('*category/'.$cat->slug.'*');
                    @endphp
                    <a href="{{ route('shop.category', ['slug' => $cat->slug]) }}"
                       class="px-3 py-1.5 text-sm rounded-full transition {{ $active ? 'bg-primary text-white font-semibold' : 'text-secondary hover:bg-surface-container-low hover:text-primary font-medium' }}"
                       {{ $active ? 'aria-current=page' : '' }}>
                        {{ $cat->name }}
                    </a>
                @elseif(($item['type'] ?? '') === 'page' && !empty($item['data']))
                    @php $active = request()->is('page/'.$item['data']->slug); @endphp
                    <a href="{{ route('page.show', ['slug' => $item['data']->slug]) }}"
                       class="px-3 py-1.5 text-sm rounded-full transition {{ $active ? 'bg-primary text-white font-semibold' : 'text-secondary hover:bg-surface-container-low hover:text-primary font-medium' }}">
                        {{ $item['data']->title }}
                    </a>
                @elseif(($item['type'] ?? '') === 'contact' && site('nav_show_contact', '1') === '1')
                    @php $active = request()->is('page/contact') || request()->is('contact'); @endphp
                    <a href="{{ route('page.show', ['slug' => 'contact']) }}"
                       class="px-3 py-1.5 text-sm rounded-full transition {{ $active ? 'bg-primary text-white font-semibold' : 'text-secondary hover:bg-surface-container-low hover:text-primary font-medium' }}">
                        {{ __t('nav.contact') }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Desktop search --}}
        <div class="hidden lg:flex flex-1 max-w-md mx-4 relative"
             x-data="liveSearch('{{ route('shop.index') }}', 2)"
             @click.outside="close()">
            <form action="{{ route('shop.index') }}" method="GET" class="relative w-full" role="search">
                <label for="header-search" class="sr-only">{{ __t('nav.search') }}</label>
                <span class="material-symbols-outlined absolute top-1/2 -translate-y-1/2 end-3 text-outline pointer-events-none">search</span>
                <input id="header-search"
                       type="search"
                       name="q"
                       x-model="query"
                       @input.debounce.300ms="_search(); show()"
                       @focus="show()"
                       @keydown.escape="close()"
                       placeholder="{{ __t('nav.search_placeholder') }}"
                       class="w-full ps-10 pe-4 py-2 bg-surface-container-low border border-outline-variant rounded-full focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                       autocomplete="off">
            </form>

            <div x-show="open && (results.length > 0 || loading)"
                 x-transition.opacity
                 class="absolute top-full inset-x-0 mt-2 bg-white rounded-2xl shadow-xl border border-outline-variant overflow-hidden z-50 max-h-96 overflow-y-auto">
                <div x-show="loading" class="p-4 text-center text-gray-500">
                    <span class="material-symbols-outlined text-2xl text-brand-500 animate-spin">progress_activity</span>
                </div>
                <template x-for="item in results" :key="item.id">
                    <a :href="item.url" class="flex items-center gap-3 p-3 hover:bg-brand-50 transition border-b border-outline-variant last:border-0">
                        <img :src="item.image" :alt="item.name" class="w-10 h-10 rounded-lg object-cover" loading="lazy">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.name"></p>
                            <p class="text-xs text-brand-600 font-bold" x-text="item.price"></p>
                        </div>
                        <span class="material-symbols-outlined text-gray-300">chevron_left</span>
                    </a>
                </template>
            </div>
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-1 sm:gap-2">

            {{-- Mobile search trigger --}}
            <button type="button" @click="searchOpen = !searchOpen"
                    class="lg:hidden min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full hover:bg-gray-100"
                    aria-label="{{ __t('nav.search') }}">
                <span class="material-symbols-outlined text-primary">search</span>
            </button>

            {{-- Wishlist --}}
            <a href="{{ route('wishlist.index') }}"
               class="relative min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full hover:bg-gray-100"
               aria-label="{{ __t('nav.wishlist') }}">
                <span class="material-symbols-outlined text-primary">favorite</span>
                @if(($wishlistCount ?? 0) > 0)
                    <span class="absolute top-1 end-1 bg-accent-500 text-white text-[10px] min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center font-bold shadow-md">
                        {{ $wishlistCount }}
                    </span>
                @endif
            </a>

            {{-- Cart --}}
            <a href="{{ route('cart.index') }}"
               class="relative min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full hover:bg-gray-100"
               aria-label="{{ __t('nav.cart') }}">
                <span class="material-symbols-outlined text-primary">shopping_cart</span>
                @if(($cartCount ?? 0) > 0)
                    <span class="absolute top-1 end-1 bg-accent-500 text-white text-[10px] min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center font-bold shadow-md">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>

            {{-- User menu / Login --}}
            @auth
                <div class="relative" @click.outside="userMenu = false">
                    <button type="button" @click="userMenu = !userMenu"
                            class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full hover:bg-gray-100"
                            aria-label="{{ __t('nav.my_account') }}" aria-haspopup="true" :aria-expanded="userMenu">
                        <span class="material-symbols-outlined text-primary">person</span>
                    </button>
                    <div x-show="userMenu" x-transition x-cloak
                         class="absolute end-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-outline-variant py-2 z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="font-semibold text-sm text-gray-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('account.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                                <span class="material-symbols-outlined text-base">person</span> {{ __t('nav.my_account') }}
                            </a>
                            <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                                <span class="material-symbols-outlined text-base">inventory_2</span> {{ __t('nav.my_orders') }}
                            </a>
                            <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                                <span class="material-symbols-outlined text-base">favorite</span> {{ __t('nav.wishlist') }}
                            </a>
                            @if(method_exists(auth()->user(), 'isAdmin') && (auth()->user()->isAdmin() ?? false))
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand-700">
                                    <span class="material-symbols-outlined text-base">dashboard</span> {{ __t('nav.dashboard') }}
                                </a>
                            @endif
                        </div>
                        <div class="border-t border-gray-100 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full text-start px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                    <span class="material-symbols-outlined text-base">logout</span> {{ __t('nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}"
                   class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full hover:bg-gray-100"
                   aria-label="{{ __t('nav.login') }}">
                    <span class="material-symbols-outlined text-primary">person</span>
                </a>
            @endauth

            {{-- Mobile menu trigger --}}
            <button type="button" @click="mobileMenu = !mobileMenu"
                    class="lg:hidden min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full hover:bg-gray-100"
                    :aria-expanded="mobileMenu" aria-controls="mobile-drawer" aria-label="{{ __t('nav.menu') }}">
                <span class="material-symbols-outlined text-primary" x-text="mobileMenu ? 'close' : 'menu'"></span>
            </button>
        </div>
    </div>

    {{-- Mobile search bar (collapsible) --}}
    <div x-show="searchOpen" x-collapse x-cloak class="lg:hidden border-t border-outline-variant bg-white">
        <div class="container-app py-3">
            <form action="{{ route('shop.index') }}" method="GET" class="relative" role="search">
                <label for="mobile-search" class="sr-only">{{ __t('nav.search') }}</label>
                <input id="mobile-search" type="search" name="q" placeholder="{{ __t('nav.search_placeholder') }}"
                       class="w-full ps-10 pe-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                <span class="material-symbols-outlined absolute top-1/2 -translate-y-1/2 start-3 text-gray-400">search</span>
                <button type="submit"
                        class="absolute end-1 top-1 bottom-1 px-4 bg-primary text-white rounded-full text-xs font-semibold min-h-[36px]">
                    {{ __t('nav.search_submit') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div id="mobile-drawer" x-show="mobileMenu" x-cloak class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-black/40" @click="mobileMenu = false" x-transition.opacity></div>
        <div class="absolute inset-y-0 {{ is_rtl() ? 'start-0' : 'end-0' }} w-80 max-w-[85vw] bg-white shadow-2xl flex flex-col"
             x-show="mobileMenu"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="{{ is_rtl() ? '-translate-x-full' : 'translate-x-full' }}"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="{{ is_rtl() ? '-translate-x-full' : 'translate-x-full' }}">

            <div class="flex items-center justify-between p-4 border-b border-outline-variant">
                <div class="flex items-center gap-2 min-w-0">
                    @if($storeLogo)
                        <img src="{{ $storeLogo }}" alt="{{ $storeName }}" loading="eager" class="w-8 h-8 rounded-full object-cover">
                    @endif
                    <span class="font-bold text-base truncate">{{ $storeName }}</span>
                </div>
                <button type="button" @click="mobileMenu = false"
                        class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full hover:bg-gray-100"
                        aria-label="{{ __t('ui.close') }}">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-2" aria-label="{{ __t('nav.mobile') }}">
                @foreach($navItems as $item)
                    @if(($item['type'] ?? '') === 'home' && site('nav_show_home', '1') === '1')
                        @php $active = request()->routeIs('home'); @endphp
                        <a href="{{ route('home') }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] {{ $active ? 'bg-primary text-white font-semibold' : 'text-on-surface hover:bg-surface-container-low' }}">
                            <span class="material-symbols-outlined text-lg">home</span> {{ __t('nav.home') }}
                        </a>
                    @elseif(($item['type'] ?? '') === 'products' && site('nav_show_products', '1') === '1')
                        @php $active = request()->routeIs('shop.index'); @endphp
                        <a href="{{ route('shop.index') }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] {{ $active ? 'bg-primary text-white font-semibold' : 'text-on-surface hover:bg-surface-container-low' }}">
                            <span class="material-symbols-outlined text-lg">shopping_bag</span> {{ __t('nav.products') }}
                        </a>
                    @elseif(($item['type'] ?? '') === 'category' && site('nav_show_categories', '1') === '1' && !empty($item['data']))
                        @php $active = request()->is('category/'.$item['data']->slug); @endphp
                        <a href="{{ route('shop.category', ['slug' => $item['data']->slug]) }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] {{ $active ? 'bg-primary text-white font-semibold' : 'text-on-surface hover:bg-surface-container-low' }}">
                            <span class="material-symbols-outlined text-lg">category</span> {{ $item['data']->name }}
                        </a>
                    @elseif(($item['type'] ?? '') === 'page' && !empty($item['data']))
                        @php $active = request()->is('page/'.$item['data']->slug); @endphp
                        <a href="{{ route('page.show', ['slug' => $item['data']->slug]) }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] {{ $active ? 'bg-primary text-white font-semibold' : 'text-on-surface hover:bg-surface-container-low' }}">
                            <span class="material-symbols-outlined text-lg">description</span> {{ $item['data']->title }}
                        </a>
                    @elseif(($item['type'] ?? '') === 'contact' && site('nav_show_contact', '1') === '1')
                        <a href="{{ route('page.show', ['slug' => 'contact']) }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] text-on-surface hover:bg-surface-container-low">
                            <span class="material-symbols-outlined text-lg">mail</span> {{ __t('nav.contact') }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="border-t border-outline-variant py-2">
                @auth
                    <a href="{{ route('account.index') }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] text-on-surface hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-lg">person</span> {{ __t('nav.my_account') }}
                    </a>
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] text-on-surface hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-lg">inventory_2</span> {{ __t('nav.my_orders') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] text-primary font-semibold hover:bg-primary/5">
                        <span class="material-symbols-outlined text-lg">login</span> {{ __t('nav.login') }}
                    </a>
                @endauth
                <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] text-on-surface hover:bg-surface-container-low">
                    <span class="material-symbols-outlined text-lg">favorite</span> {{ __t('nav.wishlist') }}
                </a>
                <a href="{{ route('cart.index') }}" class="flex items-center gap-3 px-5 py-3 text-sm rounded-lg min-h-[44px] text-on-surface hover:bg-surface-container-low">
                    <span class="material-symbols-outlined text-lg">shopping_cart</span> {{ __t('nav.cart') }}
                </a>
            </div>
        </div>
    </div>
</header>
