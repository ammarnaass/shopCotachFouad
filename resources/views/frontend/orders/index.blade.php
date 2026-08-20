@extends('frontend.layout')

@section('title', __t('order.title') . ' - ' . site('store_name'))
@section('description', __t('orders.description') . ' ' . site('store_name'))

@section('content')
@php
    $statusMap = [
        'pending' => [
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-700',
            'border' => 'border-amber-200/80',
            'icon' => 'schedule',
            'label' => __t('order_status.pending') ?? 'قيد الانتظار'
        ],
        'confirmed' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-700',
            'border' => 'border-blue-200/80',
            'icon' => 'check_circle',
            'label' => __t('order_status.confirmed') ?? 'مؤكد'
        ],
        'processing' => [
            'bg' => 'bg-indigo-50',
            'text' => 'text-indigo-700',
            'border' => 'border-indigo-200/80',
            'icon' => 'inventory',
            'label' => __t('order_status.processing') ?? 'قيد التجهيز'
        ],
        'shipped' => [
            'bg' => 'bg-purple-50',
            'text' => 'text-purple-700',
            'border' => 'border-purple-200/80',
            'icon' => 'local_shipping',
            'label' => __t('order_status.shipped') ?? 'تم الشحن'
        ],
        'delivered' => [
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-700',
            'border' => 'border-emerald-200/80',
            'icon' => 'verified',
            'label' => __t('order_status.delivered') ?? 'تم التسليم'
        ],
        'cancelled' => [
            'bg' => 'bg-rose-50',
            'text' => 'text-rose-700',
            'border' => 'border-rose-200/80',
            'icon' => 'cancel',
            'label' => __t('order_status.cancelled') ?? 'ملغي'
        ],
    ];

    $totalOrdersCount = $orders->total();
    $pendingCount = $orders->getCollection()->whereIn('status', ['pending', 'processing', 'confirmed'])->count();
    $deliveredCount = $orders->getCollection()->where('status', 'delivered')->count();
@endphp

{{-- ============ HERO / HEADER ============ --}}
<section class="relative overflow-hidden bg-gradient-to-l from-primary via-primary/90 to-primary-container text-white py-10 md:py-14">
    <div class="absolute -top-24 -end-24 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -start-24 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container-app relative z-10">
        <nav class="flex items-center gap-2 text-xs font-semibold text-white/80 mb-4">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>{{ __t('nav.home') }}</span>
            </a>
            <span class="material-symbols-outlined text-xs text-white/40">chevron_left</span>
            <span class="text-white font-bold">{{ __t('order.title') }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-2xl sm:text-3xl border border-white/25 shadow-lg flex-shrink-0">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white">{{ __t('order.title') }}</h1>
                        <span class="bg-white/20 text-white text-xs font-bold font-mono px-2.5 py-0.5 rounded-full border border-white/30">{{ $totalOrdersCount }}</span>
                    </div>
                    <p class="text-white/90 text-xs sm:text-sm">{{ __t('orders.description') ?? 'تتبع وإدارة جميع طلباتك ومشترياتك السابقة' }}</p>
                </div>
            </div>

            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/30 text-white text-xs sm:text-sm font-bold backdrop-blur-sm transition-all shadow-xs self-start sm:self-center">
                <span class="material-symbols-outlined text-base">shopping_bag</span>
                <span>{{ __t('orders.start_shopping') ?? 'تسوق المزيد' }}</span>
            </a>
        </div>
    </div>
</section>

{{-- ============ MAIN CONTENT ============ --}}
<div class="container-app py-8 md:py-12">
    @if($orders->count() > 0)
        {{-- KPI Quick Counters --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-4 sm:p-5 flex items-center gap-3.5 shadow-2xs">
                <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl">receipt_long</span>
                </div>
                <div>
                    <p class="text-xs text-on-surface-variant font-semibold">إجمالي الطلبات</p>
                    <p class="text-lg sm:text-xl font-extrabold text-on-surface font-mono mt-0.5">{{ $totalOrdersCount }}</p>
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-4 sm:p-5 flex items-center gap-3.5 shadow-2xs">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl">hourglass_top</span>
                </div>
                <div>
                    <p class="text-xs text-on-surface-variant font-semibold">قيد المعالجة</p>
                    <p class="text-lg sm:text-xl font-extrabold text-amber-700 font-mono mt-0.5">{{ $pendingCount }}</p>
                </div>
            </div>

            <div class="col-span-2 sm:col-span-1 bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-4 sm:p-5 flex items-center gap-3.5 shadow-2xs">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl">task_alt</span>
                </div>
                <div>
                    <p class="text-xs text-on-surface-variant font-semibold">المكتملة</p>
                    <p class="text-lg sm:text-xl font-extrabold text-emerald-700 font-mono mt-0.5">{{ $deliveredCount }}</p>
                </div>
            </div>
        </div>

        {{-- Orders List --}}
        <div class="space-y-4">
            @foreach($orders as $order)
                @php
                    $st = $statusMap[$order->status] ?? $statusMap['pending'];
                @endphp
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs hover:shadow-md hover:border-primary/40 transition-all duration-200 overflow-hidden group">
                    <div class="p-5 sm:p-6">
                        {{-- Top Header Row --}}
                        <div class="flex items-start sm:items-center justify-between flex-wrap gap-3 pb-4 border-b border-outline-variant/30">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="font-mono font-black text-sm sm:text-base text-on-surface bg-surface-container-low px-3 py-1 rounded-xl border border-outline-variant/50">
                                    #{{ $order->order_number }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $st['bg'] }} {{ $st['text'] }} {{ $st['border'] }}">
                                    <span class="material-symbols-outlined text-sm">{{ $st['icon'] }}</span>
                                    <span>{{ $st['label'] }}</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-2 text-xs text-on-surface-variant font-medium">
                                <span class="material-symbols-outlined text-sm">calendar_today</span>
                                <span>{{ $order->created_at->format('Y/m/d - H:i') }}</span>
                            </div>
                        </div>

                        {{-- Middle Row: Items preview + Shipping summary --}}
                        <div class="py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-3 flex-wrap">
                                {{-- Product Thumbnails preview --}}
                                <div class="flex items-center -space-x-2 rtl:space-x-reverse overflow-hidden py-1">
                                    @foreach($order->items->take(4) as $item)
                                        <div class="w-12 h-12 rounded-xl bg-surface-container-low border-2 border-surface-container-lowest overflow-hidden flex-shrink-0 shadow-2xs" title="{{ $item->product_name }}">
                                            @if($item->product && $item->product->primaryImage)
                                                <img src="{{ asset('storage/' . $item->product->primaryImage->image) }}"
                                                     alt="{{ $item->product_name }}"
                                                     class="w-full h-full object-cover" loading="lazy">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-on-surface-variant/40 bg-surface-container">
                                                    <span class="material-symbols-outlined text-base">image</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($order->items->count() > 4)
                                        <div class="w-12 h-12 rounded-xl bg-surface-container text-on-surface-variant font-bold text-xs flex items-center justify-center border-2 border-surface-container-lowest shadow-2xs font-mono">
                                            +{{ $order->items->count() - 4 }}
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-0.5">
                                    <p class="text-sm font-bold text-on-surface">
                                        {{ $order->items->count() }} {{ $order->items->count() > 1 ? 'منتجات' : 'منتج' }}
                                    </p>
                                    @if($order->shippingAddress)
                                        <p class="text-xs text-on-surface-variant flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs">location_on</span>
                                            <span>{{ $order->shippingAddress->city ?? $order->shippingAddress->state_code ?? 'الجزائر' }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Price & CTA Button --}}
                            <div class="flex items-center justify-between md:justify-end gap-5 pt-3 md:pt-0 border-t md:border-t-0 border-outline-variant/20">
                                <div class="text-start md:text-end">
                                    <p class="text-[11px] uppercase tracking-wider font-semibold text-on-surface-variant mb-0.5">{{ __t('order.total') }}</p>
                                    <p class="text-lg sm:text-xl font-black text-primary font-mono">
                                        {{ number_format(convertPrice($order->grand_total), 0) }}
                                        <span class="text-xs font-bold text-on-surface-variant font-sans">{{ currentCurrencySymbol() }}</span>
                                    </p>
                                </div>

                                <a href="{{ route('orders.show', $order->id) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-surface-container-low hover:bg-primary hover:text-on-primary text-on-surface font-bold text-xs sm:text-sm transition-all duration-200 group-hover:bg-primary group-hover:text-on-primary shadow-2xs">
                                    <span>{{ __t('order.details') ?? 'تفاصيل الطلب' }}</span>
                                    <span class="material-symbols-outlined text-base transition-transform group-hover:-translate-x-1">chevron_left</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $orders->links() }}
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-3xl p-8 sm:p-14 text-center max-w-lg mx-auto shadow-xs">
            <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-5 rounded-3xl bg-primary/10 text-primary flex items-center justify-center shadow-xs">
                <span class="material-symbols-outlined text-4xl sm:text-5xl">inventory_2</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-on-surface mb-2">{{ __t('order.no_orders') ?? 'لا توجد طلبات بعد' }}</h2>
            <p class="text-xs sm:text-sm text-on-surface-variant mb-6 leading-relaxed">{{ __t('orders.empty') ?? 'لم تقم بإنشاء أي طلبات حتى الآن. استكشف منتجاتنا المميزة وابدأ طلبك الأول الآن!' }}</p>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-md hover:shadow-lg hover:brightness-105 active:scale-[0.98] transition-all">
                <span class="material-symbols-outlined text-lg">shopping_cart</span>
                <span>{{ __t('orders.start_shopping') ?? 'ابدأ التسوق الآن' }}</span>
            </a>
        </div>
    @endif
</div>
@endsection
