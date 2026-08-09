@extends('frontend.layout')

@section('title', '404 - ' . site('store_name'))

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="container-app max-w-2xl text-center">
        <div class="mb-6 relative inline-block">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-500/20 to-accent-500/20 blur-3xl rounded-full"></div>
            <h1 class="relative text-[8rem] sm:text-[10rem] md:text-[12rem] font-black leading-none bg-gradient-to-l from-brand-600 to-accent-500 bg-clip-text text-transparent">
                404
            </h1>
        </div>

        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-800 mb-3">
            {{ __t('errors.404_title', [], 'الصفحة غير موجودة') }}
        </h2>
        <p class="text-gray-500 text-base sm:text-lg mb-8 max-w-md mx-auto">
            {{ __t('errors.404_desc', [], 'عذراً، الصفحة التي تبحث عنها قد تكون محذوفة أو غير متاحة.') }}
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url('/') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-l from-brand-600 to-accent-500 hover:from-brand-700 hover:to-accent-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition active:scale-95 min-h-[44px]">
                <span class="material-symbols-outlined" aria-hidden="true">home</span>
                {{ __t('errors.go_home', [], 'العودة للرئيسية') }}
            </a>
            <a href="{{ route('shop.index') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-gray-200 hover:border-brand-500 text-gray-700 font-bold rounded-xl transition active:scale-95 min-h-[44px]">
                <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                {{ __t('errors.browse_shop', [], 'تصفح المتجر') }}
            </a>
        </div>
    </div>
</section>
@endsection
