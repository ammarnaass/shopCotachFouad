@extends('frontend.layout')

@section('title', __t('order.details') . ' #' . $order->order_number . ' - ' . site('store_name'))
@section('description', __t('order.details') . ' #' . $order->order_number)

@section('content')
@php
    $statusColors = [
        'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200/80', 'icon' => 'schedule', 'label' => __t('order_status.pending') ?? 'قيد الانتظار'],
        'confirmed' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200/80', 'icon' => 'check_circle', 'label' => __t('order_status.confirmed') ?? 'مؤكد'],
        'processing' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200/80', 'icon' => 'inventory', 'label' => __t('order_status.processing') ?? 'قيد التجهيز'],
        'shipped' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200/80', 'icon' => 'local_shipping', 'label' => __t('order_status.shipped') ?? 'تم الشحن'],
        'delivered' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200/80', 'icon' => 'verified', 'label' => __t('order_status.delivered') ?? 'تم التسليم'],
        'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200/80', 'icon' => 'cancel', 'label' => __t('order_status.cancelled') ?? 'ملغي'],
    ];
    $st = $statusColors[$order->status] ?? $statusColors['pending'];
@endphp

{{-- ============ HERO / HEADER ============ --}}
<section class="relative overflow-hidden bg-gradient-to-l from-primary via-primary/90 to-primary-container text-white py-10 md:py-14">
    <div class="absolute -top-24 -end-24 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -start-24 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container-app relative z-10">
        <nav class="flex items-center gap-2 text-xs font-semibold text-white/80 mb-4 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>{{ __t('nav.home') }}</span>
            </a>
            <span class="material-symbols-outlined text-xs text-white/40">chevron_left</span>
            <a href="{{ route('orders.index') }}" class="hover:text-white transition-colors">{{ __t('order.title') }}</a>
            <span class="material-symbols-outlined text-xs text-white/40">chevron_left</span>
            <span class="text-white font-bold font-mono">#{{ $order->order_number }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 flex-wrap mb-1.5">
                    <h1 class="text-2xl sm:text-3xl font-black text-white font-mono">#{{ $order->order_number }}</h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $st['bg'] }} {{ $st['text'] }} {{ $st['border'] }} shadow-xs">
                        <span class="material-symbols-outlined text-sm">{{ $st['icon'] }}</span>
                        <span>{{ $st['label'] }}</span>
                    </span>
                </div>
                <p class="text-white/90 text-xs sm:text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">calendar_month</span>
                    <span>تم الإنشاء في {{ $order->created_at->format('Y/m/d - H:i') }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/30 text-white text-xs sm:text-sm font-bold backdrop-blur-sm transition-all shadow-xs">
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                    <span>{{ __t('order.back_to_orders') ?? 'العودة للطلبات' }}</span>
                </a>
            </div>
        </div>
    </div>
</section>

<div class="container-app py-8 md:py-12">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 flex items-center gap-3 shadow-xs animate-fade-in">
            <span class="material-symbols-outlined text-emerald-600 text-xl flex-shrink-0">check_circle</span>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200/80 text-red-800 flex items-center gap-3 shadow-xs animate-fade-in">
            <span class="material-symbols-outlined text-red-600 text-xl flex-shrink-0">error</span>
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Status Timeline / Journey (Only when not cancelled) --}}
    @if($order->status !== 'cancelled')
        @php
            $steps = [
                'pending' => ['icon' => 'schedule', 'label' => __t('track.status.received') ?? 'تم الاستلام'],
                'confirmed' => ['icon' => 'check_circle', 'label' => __t('order_status.confirmed') ?? 'مؤكد'],
                'processing' => ['icon' => 'inventory', 'label' => __t('order_status.processing') ?? 'قيد التجهيز'],
                'shipped' => ['icon' => 'local_shipping', 'label' => __t('order_status.shipped') ?? 'تم الشحن'],
                'delivered' => ['icon' => 'home', 'label' => __t('order_status.delivered') ?? 'تم التوصيل'],
            ];
            $orderStatusKeys = array_keys($steps);
            $currentIndex = array_search($order->status, $orderStatusKeys);
            if ($currentIndex === false) $currentIndex = 0;
        @endphp

        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-6 md:p-8 shadow-xs mb-8 overflow-hidden">
            <div class="flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-primary text-xl">route</span>
                <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('order.journey') ?? 'مسار تقدم الطلب' }}</h2>
            </div>

            <div class="flex items-center justify-between overflow-x-auto pb-4 pt-2 relative">
                @foreach($steps as $key => $step)
                    @php $stepIndex = $loop->index; @endphp
                    <div class="flex flex-col items-center min-w-[76px] relative z-10 flex-shrink-0">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-2xs
                            {{ $stepIndex < $currentIndex ? 'bg-emerald-500 text-white shadow-emerald-500/20' :
                               ($stepIndex === $currentIndex ? 'bg-primary text-on-primary ring-4 ring-primary/20 scale-110 shadow-primary/30' :
                               'bg-surface-container-low text-on-surface-variant/50 border border-outline-variant/40') }}">
                            <span class="material-symbols-outlined text-lg">{{ $stepIndex < $currentIndex ? 'check' : $step['icon'] }}</span>
                        </div>
                        <p class="text-xs mt-2.5 text-center font-bold
                            {{ $stepIndex <= $currentIndex ? 'text-on-surface' : 'text-on-surface-variant/50' }}">
                            {{ $step['label'] }}
                        </p>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-1 h-1 -mt-6 mx-2 sm:mx-3 rounded-full transition-all duration-300
                            {{ $stepIndex < $currentIndex ? 'bg-emerald-500' : 'bg-surface-container-high' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- ============ LEFT / MAIN COLUMN ============ --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Ordered Items Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
                <div class="px-6 py-4.5 border-b border-outline-variant/30 bg-surface-container-low/30 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-xl">shopping_cart</span>
                        <h2 class="font-extrabold text-base text-on-surface">{{ __t('order.items') ?? 'المنتجات المطلوبة' }}</h2>
                    </div>
                    <span class="text-xs font-mono font-bold bg-surface-container-lowest px-2.5 py-1 rounded-full border border-outline-variant/40 text-on-surface-variant">
                        {{ $order->items->count() }} {{ $order->items->count() > 1 ? 'عناصر' : 'عنصر' }}
                    </span>
                </div>

                <div class="divide-y divide-outline-variant/25">
                    @foreach($order->items as $item)
                        <div class="p-5 sm:p-6 flex items-start sm:items-center gap-4 group">
                            <a href="{{ $item->product ? route('shop.show', ['slug' => $item->product->slug]) : '#' }}"
                               class="w-16 h-16 sm:w-20 sm:h-20 bg-surface-container-low rounded-2xl overflow-hidden flex-shrink-0 border border-outline-variant/40 block">
                                @if($item->product && $item->product->primaryImage)
                                    <img src="{{ asset('storage/' . $item->product->primaryImage->image) }}"
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-on-surface-variant/40 bg-surface-container">
                                        <span class="material-symbols-outlined text-2xl">image</span>
                                    </div>
                                @endif
                            </a>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-sm sm:text-base text-on-surface group-hover:text-primary transition-colors line-clamp-2">
                                    {{ $item->product_name }}
                                </h3>

                                <div class="flex items-center gap-2 text-xs text-on-surface-variant mt-1.5 flex-wrap">
                                    <span class="font-semibold text-on-surface">
                                        {{ __t('order.quantity') }}: <span class="font-mono font-bold">{{ $item->quantity }}</span>
                                    </span>
                                    <span class="opacity-30">•</span>
                                    <span class="font-mono">{{ number_format(convertPrice($item->price), 0) }} {{ currentCurrencySymbol() }} للقطعة</span>
                                </div>

                                @if($item->options && count((array) $item->options) > 0)
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach((array) $item->options as $k => $v)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-surface-container text-on-surface-variant text-[11px] font-semibold">
                                                {{ $k }}: {{ $v }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="text-start sm:text-end flex-shrink-0">
                                <p class="text-sm sm:text-base font-black text-primary font-mono">
                                    {{ number_format(convertPrice($item->total), 0) }}
                                    <span class="text-xs font-sans font-bold text-on-surface-variant">{{ currentCurrencySymbol() }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Shipping Address & Customer Details --}}
            @if($order->shippingAddress)
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
                    <div class="px-6 py-4.5 border-b border-outline-variant/30 bg-surface-container-low/30 flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-xl">location_on</span>
                        <h2 class="font-extrabold text-base text-on-surface">{{ __t('order.shipping_address') ?? 'بيانات الشحن والتوصيل' }}</h2>
                    </div>

                    <div class="p-6">
                        <div class="bg-surface-container-low/40 rounded-2xl p-5 border border-outline-variant/30 flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0 shadow-2xs">
                                <span class="material-symbols-outlined text-xl">person</span>
                            </div>
                            <div class="flex-1 space-y-1.5">
                                <h3 class="font-extrabold text-sm text-on-surface">{{ $order->shippingAddress->name }}</h3>
                                <p class="text-xs text-on-surface-variant font-mono flex items-center gap-1.5" dir="ltr">
                                    <span class="material-symbols-outlined text-xs">call</span>
                                    <span>{{ $order->shippingAddress->phone }}</span>
                                </p>
                                <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed flex items-start gap-1.5 pt-1">
                                    <span class="material-symbols-outlined text-xs mt-0.5 flex-shrink-0">pin_drop</span>
                                    <span>{{ $order->shippingAddress->full_address }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ============ RIGHT / SIDEBAR COLUMN ============ --}}
        <div class="space-y-6">

            {{-- Order Financial Summary --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden sticky top-24">
                <div class="px-6 py-4.5 border-b border-outline-variant/30 bg-surface-container-low/30 flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                    <h2 class="font-extrabold text-base text-on-surface">{{ __t('order.summary') ?? 'ملخص الحساب' }}</h2>
                </div>

                <div class="p-6 space-y-3.5 text-xs sm:text-sm">
                    <div class="flex justify-between text-on-surface-variant">
                        <span>{{ __t('order.subtotal') ?? 'المجموع الفرعي' }}</span>
                        <span class="font-bold font-mono text-on-surface">{{ number_format(convertPrice($order->subtotal), 0) }} {{ currentCurrencySymbol() }}</span>
                    </div>

                    <div class="flex justify-between text-on-surface-variant">
                        <span>{{ __t('order.shipping') ?? 'تكلفة الشحن' }}</span>
                        <span class="font-bold font-mono">
                            @if($order->shipping_cost > 0)
                                <span class="text-on-surface">{{ number_format(convertPrice($order->shipping_cost), 0) }} {{ currentCurrencySymbol() }}</span>
                            @else
                                <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md text-xs font-bold">{{ __t('order.free') ?? 'مجاناً' }}</span>
                            @endif
                        </span>
                    </div>

                    @if($order->discount > 0)
                        <div class="flex justify-between text-emerald-700">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">local_offer</span>
                                <span>{{ __t('order.discount') ?? 'الخصم' }}</span>
                            </span>
                            <span class="font-bold font-mono">-{{ number_format(convertPrice($order->discount), 0) }} {{ currentCurrencySymbol() }}</span>
                        </div>
                    @endif

                    @if($order->cod_fee > 0)
                        <div class="flex justify-between text-on-surface-variant">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">payments</span>
                                <span>{{ __t('order.cod_fee') ?? 'رسوم الدفع عند الاستلام' }}</span>
                            </span>
                            <span class="font-bold font-mono text-on-surface">{{ number_format(convertPrice($order->cod_fee), 0) }} {{ currentCurrencySymbol() }}</span>
                        </div>
                    @endif

                    <div class="border-t border-outline-variant/30 pt-4 mt-4 flex justify-between items-baseline">
                        <span class="font-extrabold text-sm sm:text-base text-on-surface">{{ __t('order.total') ?? 'المجموع الإجمالي' }}</span>
                        <span class="font-black text-xl sm:text-2xl text-primary font-mono">
                            {{ number_format(convertPrice($order->grand_total), 0) }}
                            <span class="text-xs font-sans font-bold text-on-surface-variant">{{ currentCurrencySymbol() }}</span>
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 border-t border-outline-variant/30 space-y-2.5">
                        @if($order->canBeCancelled())
                            <form method="POST" action="{{ route('orders.cancel', $order->id) }}"
                                  onsubmit="return confirm('{{ __t('order.cancel_confirm') ?? 'هل أنت متأكد من رغبتك في إلغاء هذا الطلب؟' }}')">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs sm:text-sm transition-all shadow-2xs">
                                    <span class="material-symbols-outlined text-base">close</span>
                                    <span>{{ __t('order.cancel') ?? 'إلغاء الطلب' }}</span>
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('orders.index') }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-bold text-xs sm:text-sm transition-all border border-outline-variant/40 shadow-2xs">
                            <span class="material-symbols-outlined text-base">arrow_forward</span>
                            <span>{{ __t('order.back_to_orders') ?? 'العودة إلى طلباتي' }}</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tracking Code Card (if available) --}}
            @if($order->tracking_number)
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center gap-2 mb-2 text-on-surface">
                        <span class="material-symbols-outlined text-primary text-lg">local_shipping</span>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-on-surface-variant">{{ __t('order.tracking_number') ?? 'رقم التتبع' }}</h3>
                    </div>
                    <div class="bg-surface-container-low/60 rounded-xl p-3 text-center font-mono font-bold text-sm text-primary border border-outline-variant/30 select-all">
                        {{ $order->tracking_number }}
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
