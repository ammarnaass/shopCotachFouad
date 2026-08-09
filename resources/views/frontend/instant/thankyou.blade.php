@extends('frontend.layout')

@section('title', __t('instant.thankyou_title') . ' - ' . site('store_name'))
@section('description', __t('instant.thankyou_meta_description'))

@section('content')
@php
    $countrySymbol = currentCurrencySymbol();
@endphp

<section class="bg-gradient-to-bl from-emerald-500 via-emerald-400 to-teal-500 text-white py-12 md:py-16 relative overflow-hidden">
    {{-- Confetti / success pattern --}}
    <div class="absolute inset-0 opacity-20">
        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
            <defs>
                <pattern id="success-pattern" x="0" y="0" width="80" height="80" patternUnits="userSpaceOnUse">
                    <circle cx="20" cy="20" r="2" fill="white"/>
                    <circle cx="60" cy="40" r="1.5" fill="white"/>
                    <circle cx="40" cy="70" r="1" fill="white"/>
                    <path d="M10 50 L15 55 L20 50" stroke="white" stroke-width="1.5" fill="none"/>
                    <path d="M50 10 L55 15 L60 10" stroke="white" stroke-width="1.5" fill="none"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#success-pattern)"/>
        </svg>
    </div>
    <div class="absolute top-10 right-20 w-72 h-72 bg-white/20 rounded-full blur-3xl animate-bounce-slow"></div>
    <div class="absolute bottom-10 left-20 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>

    <div class="container-app relative z-10 text-center py-8">
        <div class="w-24 h-24 mx-auto mb-5 rounded-3xl bg-white/20 backdrop-blur-md flex items-center justify-center border-2 border-white/30 shadow-2xl animate-bounce-slow">
            <span class="material-symbols-outlined text-5xl text-white">check</span>
        </div>
        <h1 class="text-3xl md:text-5xl font-extrabold mb-3 text-balance">{{ __t('instant.thankyou_title') }}</h1>
        <p class="text-white/90 text-lg max-w-xl mx-auto text-pretty">{{ __t('instant.thankyou_description') }}</p>
    </div>
</section>

<div class="container-app py-10 max-w-2xl">
    <div class="space-y-5">
        {{-- Order number --}}
        <div class="card overflow-hidden animate-fade-up">
            <div class="bg-gradient-to-l from-emerald-50 to-teal-50 p-6 text-center">
                <p class="text-sm text-gray-600 mb-1">{{ __t('instant.order_number') }}</p>
                <p class="font-mono font-extrabold text-2xl text-gray-800 mb-2">{{ $order->order_number }}</p>
                <a href="{{ route('track') }}" class="text-sm text-emerald-700 hover:underline inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">location_on</span>
                    {{ __t('track.title') }}
                </a>
            </div>
        </div>

        {{-- Order details --}}
        <div class="card animate-fade-up">
            <div class="card-header">
                <h2 class="font-bold text-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-600">category</span>
                    {{ __t('instant.order_details') }}
                </h2>
            </div>
            <div class="card-body p-5">
                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="py-3 first:pt-0 last:pb-0 flex justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800">{{ $item->product_name }}</div>
                                @if($item->options_summary)
                                    <div class="text-xs text-gray-500 mt-1 flex flex-wrap gap-1">
                                        @foreach($item->options_summary as $opt)
                                            <span class="badge badge-gray text-[10px]">{{ $opt['option'] }}: {{ $opt['value'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($item->custom_text)
                                    <div class="text-xs text-gray-500 mt-1">
                                        <span class="material-symbols-outlined text-[10px] ms-1">edit</span>{{ Str::limit($item->custom_text, 50) }}
                                    </div>
                                @endif
                            </div>
<div class="text-start flex-shrink-0">
                                <div class="text-sm text-gray-600">{{ $item->quantity }} × {{ number_format(convertPrice($item->price), 0) }}</div>
                                <div class="font-bold text-gray-800">{{ number_format(convertPrice($item->total), 0) }} {{ $countrySymbol }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-4 mt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __t('instant.subtotal') }}</span>
                        <span class="font-semibold">{{ number_format(convertPrice($order->subtotal), 0) }} {{ $countrySymbol }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __t('instant.shipping') }}</span>
                        <span class="font-semibold">{{ number_format(convertPrice($order->shipping_cost), 0) }} {{ $countrySymbol }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span><span class="material-symbols-outlined text-xs ms-1">local_offer</span>{{ __t('instant.discount') }}</span>
                            <span class="font-semibold">-{{ number_format(convertPrice($order->discount), 0) }} {{ $countrySymbol }}</span>
                        </div>
                    @endif
                    <div class="border-t border-gray-100 pt-3 mt-3 flex justify-between items-baseline">
                        <span class="font-bold text-gray-800 text-base">{{ __t('instant.total') }}</span>
                        <span class="font-extrabold text-2xl bg-gradient-to-l from-emerald-600 to-teal-500 bg-clip-text text-transparent">
                            {{ number_format(convertPrice($order->grand_total), 0) }} {{ $countrySymbol }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shipping address --}}
        @if($order->shippingAddress)
            <div class="card animate-fade-up">
                <div class="card-body p-5">
                    <h3 class="font-bold text-lg mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-600">location_on</span>
                        {{ __t('instant.delivery_address') }}
                    </h3>
                    <div class="bg-gradient-to-l from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-4">
                        <div class="font-bold text-gray-800">{{ $order->shippingAddress->name }}</div>
                        <div class="text-sm text-gray-600 mt-1">
<span class="material-symbols-outlined text-xs ms-1 text-gray-400">phone</span>
                            {{ $order->shippingAddress->phone }}
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            <span class="material-symbols-outlined text-xs ms-1 text-gray-400">location_on</span>
                            {{ $order->shippingAddress->full_address }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Next steps --}}
        <div class="card animate-fade-up">
            <div class="card-body p-5">
                <h3 class="font-bold text-base mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-600">info</span>
                    {{ __t('instant.next_steps') }}
                </h3>
                <ol class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold flex-shrink-0">1</span>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ __t('instant.step_confirm_title') }}</p>
                            <p class="text-xs text-gray-500">{{ __t('instant.step_confirm_desc') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold flex-shrink-0">2</span>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ __t('instant.step_process_title') }}</p>
                            <p class="text-xs text-gray-500">{{ __t('instant.step_process_desc') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold flex-shrink-0">3</span>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ __t('instant.step_delivery_title') }}</p>
                            <p class="text-xs text-gray-500">{{ __t('instant.step_delivery_desc') }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-col sm:flex-row gap-3 pt-2 animate-fade-up">
            <a href="{{ route('home') }}" class="btn btn-secondary btn-lg flex-1">
                <span class="material-symbols-outlined">home</span>
                {{ __t('instant.back_home') }}
            </a>
            <a href="{{ route('shop.index') }}" class="btn-primary btn-lg flex-1">
                <span class="material-symbols-outlined">shopping_bag</span>
                {{ __t('instant.continue_shopping') }}
            </a>
        </div>
    </div>
</div>
@endsection
