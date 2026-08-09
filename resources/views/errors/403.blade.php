@extends('frontend.layout')

@section('title', '403 - ' . site('store_name'))

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="container-app max-w-2xl text-center">
        <div class="mb-6 relative inline-block">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-500/20 to-orange-500/20 blur-3xl rounded-full"></div>
            <h1 class="relative text-[8rem] sm:text-[10rem] md:text-[12rem] font-black leading-none bg-gradient-to-l from-rose-600 to-orange-500 bg-clip-text text-transparent">
                403
            </h1>
        </div>

        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-800 mb-3">
            {{ __t('errors.403_title', [], 'غير مصرح بالوصول') }}
        </h2>
        <p class="text-gray-500 text-base sm:text-lg mb-8 max-w-md mx-auto">
            {{ __t('errors.403_desc', [], 'عذراً، ليس لديك صلاحية الوصول إلى هذه الصفحة.') }}
        </p>

        <a href="{{ url('/') }}"
           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-l from-rose-600 to-orange-500 hover:from-rose-700 hover:to-orange-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition active:scale-95 min-h-[44px]">
            <span class="material-symbols-outlined" aria-hidden="true">home</span>
            {{ __t('errors.go_home', [], 'العودة للرئيسية') }}
        </a>
    </div>
</section>
@endsection
