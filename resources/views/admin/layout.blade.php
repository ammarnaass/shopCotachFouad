<!DOCTYPE html>
<html lang="ar" dir="rtl" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __t('admin.title_prefix')) - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ── Dynamic admin colors from لوحة الإدارة ← التخصيص ← المظهر ── --}}
    {{-- Same source of truth as the frontend — primary_color & accent_color --}}
    <style>
        :root {
            --color-primary:              {{ $siteSettings['primary_color'] ?? '#2563eb' }};
            --color-accent:               {{ $siteSettings['accent_color']  ?? '#f59e0b' }};
            --color-primary-container:    color-mix(in srgb, var(--color-primary) 25%, white);
            --color-on-primary:           #ffffff;
            --color-on-primary-container: color-mix(in srgb, var(--color-primary) 85%, black);
            --color-primary-fixed:        color-mix(in srgb, var(--color-primary) 35%, white);
            --color-primary-fixed-dim:    color-mix(in srgb, var(--color-primary) 55%, white);
            --color-inverse-primary:      color-mix(in srgb, var(--color-primary) 70%, white);
            --color-surface-tint:         var(--color-primary);
            --color-tertiary:             {{ $siteSettings['accent_color'] ?? '#f59e0b' }};
            --color-tertiary-container:   color-mix(in srgb, var(--color-accent) 25%, white);
            --color-on-tertiary:          #ffffff;
        }

        /* Tailwind utility overrides for admin panel */
        .bg-primary          { background-color: var(--color-primary) !important; }
        .text-primary        { color: var(--color-primary) !important; }
        .border-primary      { border-color: var(--color-primary) !important; }
        .ring-primary, .focus\:ring-primary:focus { --tw-ring-color: var(--color-primary) !important; }
        .focus\:border-primary:focus { border-color: var(--color-primary) !important; }
        .bg-primary-container { background-color: var(--color-primary-container) !important; }
        .bg-primary\/5        { background-color: color-mix(in srgb, var(--color-primary)  5%, transparent) !important; }
        .bg-primary\/10       { background-color: color-mix(in srgb, var(--color-primary) 10%, white) !important; }
        .bg-primary\/20       { background-color: color-mix(in srgb, var(--color-primary) 20%, white) !important; }
        .bg-primary-fixed\/30 { background-color: color-mix(in srgb, var(--color-primary) 30%, white) !important; }
        .hover\:text-primary:hover   { color: var(--color-primary) !important; }
        .hover\:bg-primary:hover     { background-color: var(--color-primary) !important; }
        .hover\:border-primary:hover { border-color: var(--color-primary) !important; }
    </style>

    @stack('styles')
</head>
<body class="bg-surface text-on-surface min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed right-0 top-0 h-full w-[260px] bg-on-background z-50 flex flex-col py-6 overflow-y-auto">
        <div class="px-6 mb-8 flex items-center gap-3">
            @if(site('store_logo'))
                <img src="{{ site('store_logo') }}" alt="{{ site('store_name') }}" class="w-10 h-10 object-contain rounded-lg bg-white p-0.5 flex-shrink-0">
            @else
                <div class="w-10 h-10 rounded-lg bg-primary-container flex items-center justify-center text-on-primary flex-shrink-0">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">storefront</span>
                </div>
            @endif
            <div class="overflow-hidden">
                <h1 class="text-sm font-bold text-surface-container-lowest leading-tight truncate" title="{{ site('store_name', config('app.name')) }}">
                    {{ site('store_name', config('app.name')) }}
                </h1>
                <p class="text-[10px] text-surface-variant/60">{{ __t('admin.system_management') }}</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 px-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.dashboard') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.dashboard') }}</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-xs text-surface-variant/50 font-semibold">{{ __t('admin.sidebar.sales') }}</div>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.orders*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">shopping_cart</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.orders') }}</span>
                @if(($stats['pending_orders'] ?? 0) > 0)
                    <span class="mr-auto bg-error text-on-error text-xs px-1.5 py-0.5 rounded-full">{{ $stats['pending_orders'] }}</span>
                @endif
            </a>

            <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.coupons*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">confirmation_number</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.coupons') }}</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-xs text-surface-variant/50 font-semibold">{{ __t('admin.sidebar.catalog') }}</div>
            <a href="{{ route('admin.products.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.products*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.products') }}</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.categories*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">category</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.categories') }}</span>
            </a>
            <a href="{{ route('admin.tags.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.tags*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">label</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.tags') }}</span>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.reviews*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">star</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.reviews') }}</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-xs text-surface-variant/50 font-semibold">{{ __t('admin.sidebar.customers') }}</div>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.users*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">group</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.users') }}</span>
            </a>
            <a href="{{ route('admin.newsletter.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.newsletter*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">mail</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.newsletter') }}</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-xs text-surface-variant/50 font-semibold">{{ __t('admin.sidebar.operations') }}</div>
            <a href="{{ route('admin.shipping.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.shipping*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">local_shipping</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.shipping') }}</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.payments*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">receipt_long</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.payments') }}</span>
            </a>
            <a href="{{ route('admin.payment-methods.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.payment-methods*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">credit_card</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.payment_methods') }}</span>
            </a>

            <div class="pt-4 pb-1 px-4 text-xs text-surface-variant/50 font-semibold">{{ __t('admin.sidebar.system') }}</div>
            <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.pages*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">description</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.pages') }}</span>
            </a>
            <a href="{{ route('admin.currencies.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.currencies*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">payments</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.currencies') }}</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.reports*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">bar_chart</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.reports') }}</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.settings*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">settings</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.settings') }}</span>
            </a>
            <a href="{{ route('admin.customize.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.customize*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">palette</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.customize') }}</span>
            </a>
            <a href="{{ route('admin.slider.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.slider*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">slideshow</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.slider') }}</span>
            </a>
            <a href="{{ route('admin.languages.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.languages*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">translate</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.languages') }}</span>
            </a>
            <a href="{{ route('admin.instant-buy.settings') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.instant-buy*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">bolt</span>
                <span class="font-medium text-sm">{{ __t('admin.sidebar.instant_orders') }}</span>
                @if(($stats['pending_instant_orders'] ?? 0) > 0)
                    <span class="mr-auto bg-error text-on-error text-xs px-1.5 py-0.5 rounded-full">{{ $stats['pending_instant_orders'] }}</span>
                @endif
            </a>
            <div class="pt-4 pb-1 px-4 text-xs text-surface-variant/50 font-semibold">المستندات والطباعة</div>
            <a href="{{ route('admin.footer.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.footer*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">bottom_sheets</span>
                <span class="font-medium text-sm">إدارة الفوتر</span>
            </a>
            <a href="{{ route('admin.invoices.templates.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.invoices*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">receipt_long</span>
                <span class="font-medium text-sm">قوالب الفواتير</span>
            </a>
            <a href="{{ route('admin.order-labels.templates.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.order-labels*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">label</span>
                <span class="font-medium text-sm">ملصقات الطلبات</span>
            </a>
            <a href="{{ route('admin.settings.printing') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150 active:scale-95 {{ request()->routeIs('admin.settings.printing*') ? 'active' : 'sidebar-link' }}">
                <span class="material-symbols-outlined">print</span>
                <span class="font-medium text-sm">إعدادات الطباعة</span>
            </a>
        </nav>

        <div class="px-6 py-4 mt-auto border-t border-outline/20">
            <div class="flex items-center gap-3">
                @if(site('store_logo'))
                    <img src="{{ site('store_logo') }}" alt="logo" class="w-10 h-10 rounded-full object-contain bg-white p-0.5 flex-shrink-0">
                @else
                    <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary flex-shrink-0">
                        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">person</span>
                    </div>
                @endif
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-medium text-surface-bright truncate">{{ auth()->user()->name ?? __t('admin.common.default_admin') }}</p>
                    <p class="text-xs text-surface-variant/70 truncate">{{ __t('admin.common.store_manager') }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="pr-[260px] min-h-screen flex flex-col">
        {{-- Top Nav Bar --}}
        <header class="h-16 flex items-center justify-between px-8 bg-surface border-b border-outline-variant shadow-sm z-40">
            <div class="flex items-center gap-6">
                <h2 class="text-xl font-bold text-primary">@yield('page_title', __t('admin.title'))</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative group">
                    <span class="absolute inset-y-0 right-3 flex items-center text-outline group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-outlined">search</span>
                    </span>
                    <input type="text" placeholder="{{ __t('admin.common.quick_search') }}"
                           class="bg-surface-container-low border border-outline-variant rounded-lg pr-10 pl-4 py-1.5 w-64 text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                </div>
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="p-2 text-on-surface-variant hover:bg-surface-container-low rounded-lg transition-all relative">
                        <span class="material-symbols-outlined">notifications</span>
                        @if(($stats['pending_orders'] ?? 0) > 0)
                            <span class="notif-dot"></span>
                        @endif
                    </button>
                    {{-- Notification Dropdown --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 top-full mt-2 w-72 bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant z-50 overflow-hidden"
                         x-cloak>
                        <div class="px-4 py-3 border-b border-outline-variant flex items-center justify-between">
                            <span class="font-semibold text-sm text-on-surface">{{ __t('admin.common.notifications') }}</span>
                            @if(($stats['pending_orders'] ?? 0) > 0)
                                <span class="text-xs bg-error text-on-error px-2 py-0.5 rounded-full">{{ $stats['pending_orders'] }} {{ __t('admin.common.new') }}</span>
                            @endif
                        </div>
                        @if(($stats['pending_orders'] ?? 0) > 0)
                            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-surface-container-low transition-colors">
                                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-amber-600" style="font-size:16px">pending</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-on-surface">{{ __t('admin.common.pending_orders_review') }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $stats['pending_orders'] }} {{ __t('admin.common.new_order') }}</p>
                                </div>
                            </a>
                        @else
                            <div class="px-4 py-6 text-center">
                                <span class="material-symbols-outlined text-outline text-3xl block mb-2">notifications_none</span>
                                <p class="text-sm text-on-surface-variant">{{ __t('admin.common.no_new_notifications') }}</p>
                            </div>
                        @endif
                        <div class="px-4 py-2 border-t border-outline-variant">
                            <a href="{{ route('admin.orders.index') }}" class="text-xs text-primary hover:underline block text-center">{{ __t('admin.common.view_all_orders') }}</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mr-2 pr-2 border-r border-outline-variant">
                    <a href="{{ route('home') }}" target="_blank" class="p-2 text-on-surface-variant hover:bg-surface-container-low rounded-lg transition-all" title="{{ __t('admin.common.view_store') }}">
                        <span class="material-symbols-outlined">open_in_new</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-error hover:bg-error-container/30 rounded-lg transition-all" title="{{ __t('admin.logout') }}">
                            <span class="material-symbols-outlined">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Alerts --}}
        <div class="px-8 pt-6">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.opacity
                     class="alert alert-success mb-4">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <span class="flex-1">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-700 hover:text-emerald-900"><span class="material-symbols-outlined">close</span></button>
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)" x-transition.opacity
                     class="alert alert-danger mb-4">
                    <span class="material-symbols-outlined">error</span>
                    <span class="flex-1">{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-700 hover:text-red-900"><span class="material-symbols-outlined">close</span></button>
                </div>
            @endif
            @if(session('warning'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.opacity
                     class="alert alert-warning mb-4">
                    <span class="material-symbols-outlined">warning</span>
                    <span class="flex-1">{{ session('warning') }}</span>
                    <button @click="show = false" class="text-amber-700 hover:text-amber-900"><span class="material-symbols-outlined">close</span></button>
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        <div class="p-8 space-y-6">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>