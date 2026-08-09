@extends('frontend.layout')

@section('title', __t('order.title') . ' - ' . site('store_name'))

@section('content')
@php
    $statusColors = [
        'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'icon' => 'schedule', 'label' => __t('order_status.pending')],
        'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'settings', 'label' => __t('order_status.processing')],
        'shipped' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'icon' => 'local_shipping', 'label' => __t('order_status.shipped')],
        'delivered' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'check_circle', 'label' => __t('order_status.delivered')],
        'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'icon' => 'cancel', 'label' => __t('order_status.cancelled')],
    ];
@endphp

<div class="bg-gradient-to-l from-brand-600 via-brand-500 to-accent-500 text-white py-10 md:py-14">
    <div class="container-app">
        <nav class="flex items-center gap-2 text-sm text-white/80 mb-4">
            <a href="{{ route('home') }}" class="hover:text-white transition flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">home</span>
                {{ __t('nav.home') }}
            </a>
            <span class="material-symbols-outlined text-[10px] text-white/50">chevron_right</span>
            <span class="text-white font-medium">{{ __t('order.title') }}</span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl border border-white/30">
                <span class="material-symbols-outlined">inventory_2</span>
            </div>
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-1">{{ __t('order.title') }}</h1>
                <p class="text-white/90">{{ __t('orders.description') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="container-app py-10">
    @if($orders->count() > 0)
        <div class="grid gap-4">
            @foreach($orders as $order)
                @php
                    $st = $statusColors[$order->status] ?? $statusColors['pending'];
                @endphp
                <a href="{{ route('orders.show', $order->id) }}"
                   class="card card-hover block group">
                    <div class="card-body p-5">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2 flex-wrap">
                                    <h3 class="font-bold text-lg text-gray-800 group-hover:text-brand-600 transition">
                                        {{ $order->order_number }}
                                    </h3>
                                    <span class="badge {{ $st['bg'] }} {{ $st['text'] }} border {{ $st['border'] }}">
                                        <span class="material-symbols-outlined">{{ $st['icon'] }}</span>
                                        {{ $st['label'] }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-xs">calendar_month</span>
                                    {{ $order->created_at->format('Y/m/d H:i') }}
                                    <span class="text-gray-300">•</span>
                                    <span class="material-symbols-outlined text-xs">inventory_2</span>
                                    {{ $order->items->count() }} {{ __t('orders.items_count') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-start">
                                    <div class="text-xs text-gray-500 mb-0.5">{{ __t('order.total') }}</div>
                                    <div class="font-extrabold text-xl bg-gradient-to-l from-brand-600 to-accent-500 bg-clip-text text-transparent">
                                        {{ number_format(convertPrice($order->grand_total), 0) }} {{ currentCurrencySymbol() }}
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-gray-300 group-hover:text-brand-600 group-hover:-translate-x-1 transition">chevron_right</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $orders->links() }}</div>
    @else
        <div class="card max-w-2xl mx-auto animate-fade-up">
            <div class="card-body p-6 sm:p-12 text-center">
                <div class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-brand-100 to-accent-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-5xl text-brand-500">inventory_2</span>
                </div>
                <h2 class="text-2xl font-bold mb-2">{{ __t('order.no_orders') }}</h2>
                <p class="text-gray-500 mb-6">{{ __t('orders.empty') }}</p>
                <a href="{{ route('shop.index') }}" class="btn-primary btn-lg inline-flex">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    {{ __t('orders.start_shopping') }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
