@extends('frontend.layout')

@section('title', $page['title'] . ' - ' . site('store_name'))
@section('description', $page['intro'] ?? '')

@section('content')
@php
    $colorMap = [
        'blue' => ['gradient' => 'from-blue-600 via-blue-500 to-indigo-500', 'bg' => 'bg-blue-50', 'border' => 'border-blue-500', 'icon' => 'bg-blue-100 text-blue-600', 'text' => 'text-blue-600', 'accent' => 'blue'],
        'green' => ['gradient' => 'from-emerald-600 via-emerald-500 to-teal-500', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-500', 'icon' => 'bg-emerald-100 text-emerald-600', 'text' => 'text-emerald-600', 'accent' => 'emerald'],
        'purple' => ['gradient' => 'from-purple-600 via-purple-500 to-pink-500', 'bg' => 'bg-purple-50', 'border' => 'border-purple-500', 'icon' => 'bg-purple-100 text-purple-600', 'text' => 'text-purple-600', 'accent' => 'purple'],
        'indigo' => ['gradient' => 'from-indigo-600 via-indigo-500 to-blue-500', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-500', 'icon' => 'bg-indigo-100 text-indigo-600', 'text' => 'text-indigo-600', 'accent' => 'indigo'],
        'red' => ['gradient' => 'from-rose-600 via-rose-500 to-pink-500', 'bg' => 'bg-rose-50', 'border' => 'border-rose-500', 'icon' => 'bg-rose-100 text-rose-600', 'text' => 'text-rose-600', 'accent' => 'rose'],
    ];
    $color = $colorMap[$page['color'] ?? 'indigo'] ?? $colorMap['indigo'];
@endphp

{{-- ============ HERO ============ --}}
<section class="relative overflow-hidden bg-gradient-to-l {{ $color['gradient'] }} text-white">
    {{-- Decorative pattern --}}
    <div class="absolute inset-0 opacity-10">
        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
            <defs>
                <pattern id="page-pattern" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                    <circle cx="25" cy="25" r="1.5" fill="white"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#page-pattern)"/>
        </svg>
    </div>
    <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>

    <div class="container-app relative z-10 py-12 md:py-16">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-white/80 mb-6">
            <a href="{{ route('home') }}" class="hover:text-white transition flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">home</span>
                {{ __t('nav.breadcrumb_home') }}
            </a>
            <span class="material-symbols-outlined text-[10px] text-white/50">chevron_right</span>
            <span class="text-white font-medium">{{ $page['title'] }}</span>
        </nav>

        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-4xl border border-white/30 shadow-lg">
                <span class="material-symbols-outlined">{{ $page['icon'] }}</span>
            </div>
            <div>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-2 text-balance">{{ $page['title'] }}</h1>
                @if($page['intro'] ?? null)
                    <p class="text-white/90 text-lg max-w-2xl text-pretty">{{ Str::limit($page['intro'], 120) }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ============ MAIN CONTENT ============ --}}
<div class="container-app py-10 md:py-16">
    <div class="max-w-4xl mx-auto">

        {{-- Intro --}}
        @if($page['intro'] ?? null)
            <div class="mb-10 p-6 {{ $color['bg'] }} border-r-4 {{ $color['border'] }} rounded-2xl animate-fade-up">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-2xl {{ $color['text'] }} flex-shrink-0 mt-1">format_quote</span>
                    <p class="text-gray-700 leading-relaxed text-base md:text-lg">{{ $page['intro'] }}</p>
                </div>
            </div>
        @endif

        {{-- Sections --}}
        @if(($page['sections'] ?? []) && count($page['sections']) > 0)
            <div class="space-y-5 mb-10">
                @foreach($page['sections'] as $i => $section)
                    <div class="card card-hover animate-fade-up" style="animation-delay: {{ $i * 60 }}ms">
                        <div class="card-body p-6">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl {{ $color['icon'] }} flex items-center justify-center font-bold text-lg flex-shrink-0">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h2 class="font-bold text-lg md:text-xl text-gray-800 mb-2 leading-snug">{{ $section['title'] }}</h2>
                                    <p class="text-gray-600 leading-relaxed text-base">{{ $section['body'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- CTA --}}
        <div class="card overflow-hidden mt-10">
            <div class="bg-gradient-to-l {{ $color['gradient'] }} text-white p-8 md:p-10 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30">
                    <span class="material-symbols-outlined text-3xl">headphones</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold mb-2">{{ __t('page.help_heading') }}</h3>
                <p class="text-white/90 mb-6 text-lg">{{ __t('page.help_text') }}</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('page.show', 'contact') }}" class="btn btn-lg bg-white text-gray-800 hover:bg-gray-100 shadow-lg">
                        <span class="material-symbols-outlined">mail</span>
                        {{ __t('page.contact_btn') }}
                    </a>
                    @php
                        $ctaWa = preg_replace('/[^0-9]/', '', site('contact_whatsapp', site('social_whatsapp', '')));
                        $ctaWa = ltrim($ctaWa, '0');
                        if (strlen($ctaWa) < 12) $ctaWa = '213' . $ctaWa;
                    @endphp
                    <a href="https://wa.me/{{ $ctaWa }}" target="_blank" class="btn btn-lg bg-green-500 hover:bg-green-600 text-white shadow-lg">
                        <span class="material-symbols-outlined text-xl">whatsapp</span>
                        {{ __t('page.whatsapp_btn') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
