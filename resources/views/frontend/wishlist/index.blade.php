@extends('frontend.layout')

@section('title', __t('wishlist.title') . ' - ' . site('store_name'))

@section('content')

<div class="container-app py-6">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="flex items-center gap-2 text-body-sm text-on-surface-variant mb-stack-lg">
        <a href="{{ route('home') }}" class="hover:text-primary transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-xs" aria-hidden="true">home</span>
            {{ __t('nav.home') }}
        </a>
        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_left</span>
        <span class="text-primary font-bold">{{ __t('wishlist.title') }}</span>
    </nav>

    {{-- Page Title --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 border-b border-outline-variant pb-6">
        <div>
            <div class="flex items-center gap-3 text-primary mb-2">
                <span class="material-symbols-outlined text-headline-md" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">favorite</span>
                <h1 class="font-headline-md text-headline-md">{{ __t('wishlist.title') }}</h1>
            </div>
            <p class="text-on-surface-variant font-body-md">
                {{ __t('wishlist.you_have') }}
                <span class="font-bold text-on-surface">{{ $wishlists->count() }}</span>
                {{ __t('wishlist.in_wishlist') }}
            </p>
        </div>
        @auth
        <a href="{{ route('shop.index') }}"
           class="flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-all active:scale-95 shadow-md min-h-[44px]">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">shopping_bag</span>
            {{ __t('wishlist.continue_shopping') }}
        </a>
        @endauth
    </div>

    @if($wishlists->count() > 0)
        @php $symbol = currentCurrencySymbol(); @endphp

        {{-- Responsive grid: 2 cols mobile, 3 sm, 4 md, 5 lg, 6 xl --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">
            @foreach($wishlists as $wishlist)
                @php $product = $wishlist->product; @endphp
                @if($product)
                    <div class="relative">
                        @include('frontend.partials.product-card', ['product' => $product, 'symbol' => $symbol])
                        {{-- Override wishlist button to remove instead of add --}}
                        <form action="{{ route('wishlist.destroy', $product) }}" method="POST"
                              class="absolute top-2 start-2 z-20">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-9 h-9 rounded-full bg-white/90 backdrop-blur shadow-md text-error hover:bg-error hover:text-white transition flex items-center justify-center"
                                    title="{{ __t('wishlist.remove') }}"
                                    aria-label="{{ __t('wishlist.remove') }}">
                                <span class="material-symbols-outlined text-base" aria-hidden="true">delete</span>
                            </button>
                        </form>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Pagination --}}
        @if(method_exists($wishlists, 'links'))
            <div class="mt-8">{{ $wishlists->links() }}</div>
        @endif

    @else
        {{-- Empty State --}}
        <div class="card max-w-lg mx-auto animate-fade-up">
            <div class="card-body py-12 sm:py-16 text-center px-4">
                <div class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-error-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-5xl text-on-error-container" aria-hidden="true">favorite</span>
                </div>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-2">{{ __t('wishlist.empty') }}</h2>
                <p class="text-on-surface-variant font-body-md mb-8">{{ __t('wishlist.empty_desc') }}</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg inline-flex">
                    <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                    {{ __t('wishlist.discover_products') }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
