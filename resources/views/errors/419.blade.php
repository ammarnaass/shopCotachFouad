@extends('frontend.layout')

@section('title', '419 - ' . site('store_name'))

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="container-app max-w-2xl text-center">
        <div class="mb-6 relative inline-block">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-yellow-500/20 blur-3xl rounded-full"></div>
            <h1 class="relative text-[8rem] sm:text-[10rem] md:text-[12rem] font-black leading-none bg-gradient-to-l from-amber-600 to-yellow-500 bg-clip-text text-transparent">
                419
            </h1>
        </div>

        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-800 mb-3">
            {{ __t('errors.419_title', [], 'انتهت صلاحية الجلسة') }}
        </h2>
        <p class="text-gray-500 text-base sm:text-lg mb-8 max-w-md mx-auto">
            {{ __t('errors.419_desc', [], 'انتهت صلاحية جلستك. يرجى تحديث الصفحة والمحاولة مرة أخرى.') }}
        </p>

        <button type="button" onclick="window.location.reload()"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-l from-amber-600 to-yellow-500 hover:from-amber-700 hover:to-yellow-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition active:scale-95 min-h-[44px]">
            <span class="material-symbols-outlined" aria-hidden="true">refresh</span>
            {{ __t('errors.refresh', [], 'تحديث الصفحة') }}
        </button>
    </div>
</section>
@endsection
