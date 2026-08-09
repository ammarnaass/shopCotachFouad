@extends('frontend.layout')

@section('title', __t('order.details') . ' ' . $order->order_number . ' - ' . site('store_name'))
@section('description', __t('order.details') . ' #' . $order->order_number)

@section('content')
@php
    $statusColors = [
        'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'icon' => 'schedule', 'label' => __t('order_status.pending')],
        'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'settings', 'label' => __t('order_status.processing')],
        'shipped' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'icon' => 'local_shipping', 'label' => __t('order_status.shipped')],
        'delivered' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'check_circle', 'label' => __t('order_status.delivered')],
        'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'icon' => 'cancel', 'label' => __t('order_status.cancelled')],
    ];
    $st = $statusColors[$order->status] ?? $statusColors['pending'];
@endphp

{{-- ============ HERO ============ --}}
<section class="bg-gradient-to-l from-brand-600 via-brand-500 to-accent-500 text-white py-8 md:py-12">
    <div class="container-app">
        <nav class="flex items-center gap-2 text-sm text-white/80 mb-4 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" class="hover:text-white transition flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">home</span>
                {{ __t('nav.home') }}
            </a>
            <span class="material-symbols-outlined text-[10px] text-white/50">chevron_right</span>
            <a href="{{ route('orders.index') }}" class="hover:text-white transition">{{ __t('order.title') }}</a>
            <span class="material-symbols-outlined text-[10px] text-white/50">chevron_right</span>
            <span class="text-white font-medium">{{ $order->order_number }}</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold mb-1">{{ __t('order.heading') }} #{{ $order->order_number }}</h1>
                <p class="text-white/90 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-xs">calendar_month</span>
                    {{ $order->created_at->format('Y/m/d H:i') }}
                </p>
            </div>
            <span class="badge {{ $st['bg'] }} {{ $st['text'] }} border {{ $st['border'] }} text-sm py-1.5 px-3">
                <span class="material-symbols-outlined">{{ $st['icon'] }}</span>
                {{ $st['label'] }}
            </span>
        </div>
    </div>
</section>

<div class="container-app py-8 md:py-10">
    {{-- Status Timeline --}}
    <div class="card mb-6 animate-fade-up">
        <div class="card-body p-6 md:p-8">
            <h2 class="font-bold text-lg mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-600">route</span>
                {{ __t('order.journey') }}
            </h2>
            @php
                $steps = [
                    'pending' => ['icon' => 'inbox', 'label' => __t('track.status.received')],
                    'confirmed' => ['icon' => 'check', 'label' => __t('order_status.confirmed')],
                    'processing' => ['icon' => 'inventory_2', 'label' => __t('order_status.processing')],
                    'shipped' => ['icon' => 'local_shipping', 'label' => __t('order_status.shipped')],
                    'delivered' => ['icon' => 'house', 'label' => __t('order_status.delivered')],
                ];
                $currentIndex = array_search($order->status, array_keys($steps));
                if ($currentIndex === false) $currentIndex = 0;
            @endphp
            <div class="flex items-center justify-between overflow-x-auto pb-2 relative">
                <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white to-transparent pointer-events-none z-10"></div>
                @foreach($steps as $key => $step)
                    @php $stepIndex = $loop->index; @endphp
                    <div class="flex flex-col items-center min-w-[80px] relative z-10">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition
                            {{ $stepIndex < $currentIndex ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' :
                               ($stepIndex === $currentIndex ? 'bg-gradient-to-br from-brand-500 to-accent-500 text-white shadow-lg shadow-brand-200 ring-4 ring-brand-100' :
                               'bg-gray-100 text-gray-400') }}">
                            <span class="material-symbols-outlined">{{ $stepIndex < $currentIndex ? 'check' : $step['icon'] }}</span>
                        </div>
                        <p class="text-xs mt-2 text-center font-medium
                            {{ $stepIndex <= $currentIndex ? 'text-gray-800' : 'text-gray-400' }}">
                            {{ $step['label'] }}
                        </p>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-1 h-1 -mt-6 mx-2 rounded-full
                            {{ $stepIndex < $currentIndex ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- ============ LEFT COLUMN ============ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Items --}}
            <div class="card animate-fade-up">
                <div class="card-header flex items-center justify-between">
                    <h2 class="font-bold text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-600">category</span>
                        {{ __t('order.items') }} ({{ $order->items->count() }})
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="p-5 flex gap-4">
                            <a href="{{ $item->product ? route('shop.show', ['slug' => $item->product->slug]) : '#' }}"
                               class="w-20 h-20 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0 group">
                                @if($item->product && $item->product->primaryImage)
                                    <img src="{{ asset('storage/' . $item->product->primaryImage->image) }}"
                                         alt="{{ $item->product->primaryImage->alt ?? $item->product_name }}"
                                         loading="lazy"
                                         decoding="async"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <span class="material-symbols-outlined text-2xl">image</span>
                                    </div>
                                @endif
                            </a>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-800 mb-1 line-clamp-2">{{ $item->product_name }}</h4>
                                <p class="text-sm text-gray-500 mb-1">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">inventory_2</span>
                                         {{ __t('order.quantity') }}: {{ $item->quantity }}
                                    </span>
                                    <span class="text-gray-300 mx-1">•</span>
                                    <span>{{ number_format(convertPrice($item->price), 0) }} {{ currentCurrencySymbol() }}</span>
                                </p>
                                @if($item->options && count((array) $item->options) > 0)
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach((array) $item->options as $k => $v)
                                            <span class="badge badge-gray text-[10px]">{{ $k }}: {{ $v }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="text-start">
                                <div class="font-extrabold text-lg bg-gradient-to-l from-brand-600 to-accent-500 bg-clip-text text-transparent">
                                    {{ number_format(convertPrice($item->total), 0) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ currentCurrencySymbol() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Address --}}
            @if($order->shippingAddress)
                <div class="card animate-fade-up">
                    <div class="card-body p-5">
                        <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-600">location_on</span>
                            {{ __t('order.shipping_address') }}
                        </h2>
                        <div class="bg-gray-50 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-lg">person</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-800">{{ $order->shippingAddress->name }}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <span class="material-symbols-outlined text-xs me-1 text-gray-400">phone</span>
                                    {{ $order->shippingAddress->phone }}
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <span class="material-symbols-outlined text-xs me-1 text-gray-400">location_on</span>
                                    {{ $order->shippingAddress->full_address }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ============ RIGHT COLUMN ============ --}}
        <div class="space-y-5">
            {{-- Summary --}}
            <div class="card sticky top-4 animate-fade-up">
                <div class="card-header bg-gradient-to-l from-brand-50 to-accent-50">
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-600">receipt</span>
                        {{ __t('order.summary') }}
                    </h3>
                </div>
                <div class="card-body p-5 space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __t('order.subtotal') }}</span>
                        <span class="font-semibold">{{ number_format(convertPrice($order->subtotal), 0) }} {{ currentCurrencySymbol() }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __t('order.shipping') }}</span>
                        <span class="font-semibold">
                            @if($order->shipping_cost > 0)
                                {{ number_format(convertPrice($order->shipping_cost), 0) }} {{ currentCurrencySymbol() }}
                            @else
                                <span class="text-emerald-600 font-bold">{{ __t('order.free') }}</span>
                            @endif
                        </span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span><span class="material-symbols-outlined text-xs me-1">local_offer</span>{{ __t('order.discount') }}</span>
                            <span class="font-semibold">-{{ number_format(convertPrice($order->discount), 0) }} {{ currentCurrencySymbol() }}</span>
                        </div>
                    @endif
                    @if($order->cod_fee > 0)
                        <div class="flex justify-between text-gray-600">
                            <span><span class="material-symbols-outlined text-xs me-1">payments</span>{{ __t('order.cod_fee') }}</span>
                            <span class="font-semibold">{{ number_format(convertPrice($order->cod_fee), 0) }} {{ currentCurrencySymbol() }}</span>
                        </div>
                    @endif
                    <div class="border-t border-gray-200 pt-3 mt-3 flex justify-between items-baseline">
                        <span class="font-bold text-gray-800 text-base">{{ __t('order.total') }}</span>
                        <span class="font-extrabold text-2xl bg-gradient-to-l from-brand-600 to-accent-500 bg-clip-text text-transparent">
                            {{ number_format(convertPrice($order->grand_total), 0) }} {{ currentCurrencySymbol() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tracking --}}
            @if($order->tracking_number)
                <div class="card animate-fade-up">
                    <div class="card-body p-5">
                        <h3 class="font-bold text-base mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-brand-600">my_location</span>
                            {{ __t('order.tracking_number') }}
                        </h3>
                        <code class="block bg-gray-50 p-3 rounded-xl text-center font-mono text-sm border border-gray-200">
                            {{ $order->tracking_number }}
                        </code>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            @if($order->canBeCancelled())
                <form method="POST" action="{{ route('orders.cancel', $order->id) }}"
                      onsubmit="return confirm('{{ __t('order.cancel_confirm') }}')"
                      class="animate-fade-up">
                    @csrf
                    <button type="submit" class="btn btn-block bg-white border-2 border-rose-500 text-rose-600 hover:bg-rose-50">
                        <span class="material-symbols-outlined">close</span>
                        {{ __t('order.cancel') }}
                    </button>
                </form>
            @endif

            <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-block animate-fade-up">
                <span class="material-symbols-outlined">arrow_forward</span>
                {{ __t('order.back_to_orders') }}
            </a>
        </div>
    </div>
</div>
@endsection
