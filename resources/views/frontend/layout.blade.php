<!DOCTYPE html>
<html lang="{{ current_locale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $siteSettings['primary_color'] ?? '#2563eb' }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', site('store_name', config('app.name'))) - {{ site('store_name', config('app.name')) }}</title>
    <meta name="description" content="@yield('description', site('seo_meta_description', site('store_description', __t('layout.default_description'))))">
    <meta name="keywords" content="{{ site('seo_meta_keywords', '') }}">
    <meta property="og:title" content="@yield('title', site('seo_meta_title', site('store_name')))">
    <meta property="og:description" content="@yield('description', site('seo_meta_description', site('store_description')))">
    <meta property="og:image" content="{{ site('seo_og_image', '') }}">
    <meta property="og:type" content="website">

    <link rel="icon" type="image/x-icon" href="{{ site('store_favicon', 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Crect width=%27100%27 height=%27100%27 rx=%2720%27 fill=%27%234f46e5%27/%3E%3Ctext x=%2750%27 y=%2765%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27sans-serif%27 font-size=%2750%27 font-weight=%27bold%27%3EA%3C/text%3E%3C/svg%3E') }}">

    {{-- Vite (Tailwind 4 + Alpine.js + custom JS) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Dynamic colors from settings (override Tailwind defaults) --}}
    <style>
        :root {
            --color-primary: {{ $siteSettings['primary_color'] ?? '#2563eb' }};
            --color-accent:  {{ $siteSettings['accent_color'] ?? '#f59e0b' }};
        }
        .bg-brand-500 { background-color: var(--color-primary) !important; }
        .bg-brand-600 { background-color: var(--color-primary) !important; }
        .bg-brand-700 { background-color: var(--color-primary) !important; filter: brightness(0.9); }
        .text-brand-500, .text-brand-600, .text-brand-700 { color: var(--color-primary) !important; }
        .border-brand-500, .border-brand-600 { border-color: var(--color-primary) !important; }
        .from-brand-500 { --tw-gradient-from: var(--color-primary) !important; }
        .to-brand-500   { --tw-gradient-to:   var(--color-primary) !important; }
        .from-brand-600 { --tw-gradient-from: var(--color-primary) !important; }
        .to-brand-600   { --tw-gradient-to:   var(--color-primary) !important; }
        .from-brand-700 { --tw-gradient-from: var(--color-primary) !important; }
        .to-brand-700   { --tw-gradient-to:   var(--color-primary) !important; }
        .bg-accent-500 { background-color: var(--color-accent) !important; }
        .text-accent-500, .text-accent-600 { color: var(--color-accent) !important; }
        .hover\:bg-brand-600:hover { background-color: var(--color-primary) !important; }
    </style>

    {{-- Material Symbols (Stitch design system) --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Arabic:wght@300;400;500;600;700&family=Barlow+Condensed:wght@600;700;800;900&family=Cairo:wght@500;600;700&display=swap" rel="stylesheet">
    {{-- Font Awesome (for legacy category icons stored as fa-xxx) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eecU/V7BUV/4hMa1cEQIFVQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Inline critical CSS (loaded before Vite) */
        body { font-family: 'IBM Plex Sans Arabic', 'Noto Sans Arabic', 'Inter', system-ui, sans-serif; }
        html[dir="ltr"] body { font-family: 'Inter', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }

        /* Page loading screen */
        .page-loader {
            position: fixed;
            inset: 0;
            background: white;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease;
        }
        .page-loader.fade-out {
            opacity: 0;
            pointer-events: none;
        }
        .page-loader-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e1e2ed;
            border-top-color: #004ac6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>

    @stack('styles')

    {{-- JSON-LD and other per-page head content --}}
    @stack('head')

    {{-- Smooth page transitions (avoids the "browser closes and reopens" feeling) --}}
    <style>
        /* Fade transition between page navigations */
        @view-transitions { navigation: auto; }
        ::view-transition-old(root),
        ::view-transition-new(root) { animation-duration: 0.18s; }
        ::view-transition-old(root) { animation: fade-out 0.18s ease-out both; }
        ::view-transition-new(root) { animation: fade-in 0.22s ease-out both; }
        @keyframes fade-out { to { opacity: 0; } }
        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }

        /* Loading indicator at top of page when navigating */
        html.is-loading { cursor: progress; }
        html.is-loading::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg,
                {{ $siteSettings['primary_color'] ?? '#2563eb' }},
                {{ $siteSettings['accent_color'] ?? '#f59e0b' }});
            z-index: 99999;
            animation: loading-bar 1s ease-in-out infinite;
        }
        @keyframes loading-bar {
            0%   { transform: translateX(-100%); }
            50%  { transform: translateX(0%); }
            100% { transform: translateX(100%); }
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col antialiased">

    {{-- Skip to content (accessibility) --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-[9999] focus:bg-brand-600 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:font-bold focus:outline-none focus:ring-2 focus:ring-white">
        {{ __t('layout.skip_to_content', [], 'انتقل إلى المحتوى الرئيسي') }}
    </a>

    {{-- Page loader --}}
    <div class="page-loader" id="pageLoader">
        <div class="page-loader-spinner"></div>
    </div>

    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                setTimeout(() => loader.classList.add('fade-out'), 100);
                setTimeout(() => loader.remove(), 500);
            }
        });
    </script>

    {{-- Header --}}
    @include('frontend.partials.header')

    {{-- Flash messages (legacy) --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
        @php
            $type = session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : 'info'));
            $msg = session('success') ?? session('error') ?? session('warning') ?? session('info');
        @endphp
        <div x-data x-cloak
             x-init="$store.toast.show(@js($msg), @js($type))">
        </div>
    @endif

    {{-- Main content --}}
    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('frontend.partials.footer')

    {{-- Global Alpine.js components (toasts, modals, back-to-top) --}}
    @include('frontend.partials.alpine-components')

    {{-- Global JavaScript --}}
    @php
        $sessionCountry = session('selected_country', config('ecommerce.store.default_country', 'DZ'));
        $sessionCurrency = $sessionCountry ? (config('ecommerce.countries.' . $sessionCountry . '.currency_symbol') ?: config('ecommerce.store.currency_symbol', __t('layout.currency_symbol'))) : config('ecommerce.store.currency_symbol', __t('layout.currency_symbol'));
        $sessionCurrencyCode = $sessionCountry ? (config('ecommerce.countries.' . $sessionCountry . '.currency') ?: config('ecommerce.store.currency', 'DZD')) : config('ecommerce.store.currency', 'DZD');
    @endphp
    <script>
        // CSRF token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        // Global cart defaults (overridden by per-page scripts)
        window.__CART_COUNT__ = window.__CART_COUNT__ || 0;
        window.__CART_SUBTOTAL__ = window.__CART_SUBTOTAL__ || 0;
        window.__CURRENCY_SYMBOL__ = @json($sessionCurrency);
        window.__CURRENCY__ = @json($sessionCurrencyCode);
        window.__COUNTRY__ = @json($sessionCountry);
        window.__CONVERSION_RATE__ = @json(conversionRate());

        // Format currency helper (converts from base currency using the current rate)
        window.formatCurrency = function(amount, symbol) {
            symbol = symbol || window.__CURRENCY_SYMBOL__;
            const rate = window.__CONVERSION_RATE__ || 1;
            var intlLocale = '{{ current_locale() }}' === 'ar' ? 'ar-SA' : 'en-US';
            return new Intl.NumberFormat(intlLocale, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount * rate) + ' ' + symbol;
        };
    </script>

    @stack('scripts')

    {{-- Smooth navigation: show loading bar on internal link click + prefetch on hover --}}
    <script>
        (function() {
            // Show loading bar on any internal link click
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('mailto:') ||
                    href.startsWith('tel:') || link.target === '_blank' ||
                    link.hasAttribute('download') || e.ctrlKey || e.metaKey || e.shiftKey) return;
                // Only flag internal same-origin links
                if (link.origin === window.location.origin) {
                    document.documentElement.classList.add('is-loading');
                }
            });
            // Also on form submit
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.method && form.method.toLowerCase() === 'get') return;
                document.documentElement.classList.add('is-loading');
            });
            // Reset on pageshow (back/forward navigation)
            window.addEventListener('pageshow', function() {
                document.documentElement.classList.remove('is-loading');
            });
            // Reset on load (in case of bfcache)
            window.addEventListener('load', function() {
                document.documentElement.classList.remove('is-loading');
            });
        })();
    </script>
    {{-- Floating WhatsApp Button --}}
    @if(site('whatsapp_btn_show', '0') === '1' && site('whatsapp_btn_phone'))
        @php
            $waPhone = preg_replace('/\D/', '', site('whatsapp_btn_phone'));
            $waText = site('whatsapp_btn_text', __t('page.whatsapp_default_text'));
            $waUrl = 'https://wa.me/' . $waPhone . '?text=' . urlencode($waText);
            $waPosition = site('whatsapp_btn_position', 'right') === 'left' ? 'left-6' : 'right-6';
        @endphp
        <div class="fixed bottom-6 {{ $waPosition }} z-40 group">
            {{-- Tooltip text --}}
            <span class="absolute bottom-1/2 translate-y-1/2 {{ site('whatsapp_btn_position', 'right') === 'left' ? 'left-16' : 'right-16' }} whitespace-nowrap bg-gray-900 text-white text-xs px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none shadow-md">
                {{ __t('layout.whatsapp_tooltip') }}
            </span>
            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" 
               class="w-14 h-14 bg-[#25D366] hover:bg-[#20ba5a] text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110 animate-bounce-subtle">
                <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.982L2 22l5.202-1.362a9.932 9.932 0 004.81 1.233h.005c5.507 0 9.99-4.479 9.99-9.986.002-2.67-1.037-5.18-2.929-7.072A9.914 9.914 0 0012.012 2zm5.799 14.143c-.252.712-1.253 1.3-1.724 1.386-.432.08-1.002.138-2.836-.615-2.35-.966-3.83-3.32-3.95-3.48-.11-.16-1.008-1.332-1.008-2.541 0-1.209.629-1.804.85-2.051.222-.246.49-.311.654-.311.164 0 .328.001.472.008.152.008.356-.057.556.425.2.485.69 1.68.749 1.803.06.122.1.266.019.426-.08.162-.122.26-.244.403-.122.143-.257.32-.367.43-.122.122-.25.255-.107.498.142.243.633 1.037 1.36 1.682.937.83 1.728 1.087 1.974 1.21.246.12.39.102.535-.067.144-.17.614-.712.78-1.03.16-.316.326-.264.55-.18.225.084 1.428.673 1.674.795.246.122.41.182.47.286.062.103.062.597-.19 1.31z"/>
                </svg>
            </a>
        </div>
        
        <style>
            @keyframes bounce-subtle {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-4px); }
            }
            .animate-bounce-subtle {
                animation: bounce-subtle 3s ease-in-out infinite;
            }
        </style>
    @endif
</body>
</html>
