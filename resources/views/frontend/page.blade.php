@extends('frontend.layout')

@section('title', $page['title'] . ' - ' . site('store_name'))
@section('description', $page['intro'] ?? '')

@section('content')
@php
    $colorMap = [
        'blue' => ['gradient' => 'from-blue-600 via-blue-700 to-indigo-800', 'bg' => 'bg-blue-50/70', 'border' => 'border-blue-500', 'icon' => 'bg-blue-100 text-blue-700', 'text' => 'text-blue-700', 'accent' => 'blue'],
        'green' => ['gradient' => 'from-emerald-600 via-teal-700 to-emerald-900', 'bg' => 'bg-emerald-50/70', 'border' => 'border-emerald-500', 'icon' => 'bg-emerald-100 text-emerald-700', 'text' => 'text-emerald-700', 'accent' => 'emerald'],
        'purple' => ['gradient' => 'from-purple-600 via-indigo-700 to-purple-900', 'bg' => 'bg-purple-50/70', 'border' => 'border-purple-500', 'icon' => 'bg-purple-100 text-purple-700', 'text' => 'text-purple-700', 'accent' => 'purple'],
        'indigo' => ['gradient' => 'from-indigo-600 via-blue-700 to-indigo-900', 'bg' => 'bg-indigo-50/70', 'border' => 'border-indigo-500', 'icon' => 'bg-indigo-100 text-indigo-700', 'text' => 'text-indigo-700', 'accent' => 'indigo'],
        'red' => ['gradient' => 'from-rose-600 via-red-700 to-rose-900', 'bg' => 'bg-rose-50/70', 'border' => 'border-rose-500', 'icon' => 'bg-rose-100 text-rose-700', 'text' => 'text-rose-700', 'accent' => 'rose'],
    ];
    $color = $colorMap[$page['color'] ?? 'indigo'] ?? $colorMap['indigo'];
@endphp

{{-- ============ HERO ============ --}}
<section class="relative overflow-hidden bg-gradient-to-l {{ $color['gradient'] }} text-white py-14 md:py-20">
    {{-- Decorative subtle glow --}}
    <div class="absolute -top-24 -end-24 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -start-24 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container-app relative z-10">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs font-semibold text-white/80 mb-6">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>{{ __t('nav.breadcrumb_home') }}</span>
            </a>
            <span class="material-symbols-outlined text-xs text-white/40">chevron_left</span>
            <span class="text-white font-bold">{{ $page['title'] }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-3xl sm:text-4xl border border-white/25 shadow-lg flex-shrink-0">
                <span class="material-symbols-outlined">{{ $page['icon'] ?? 'description' }}</span>
            </div>
            <div>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black tracking-tight text-white mb-2">{{ $page['title'] }}</h1>
                @if($page['intro'] ?? null)
                    <p class="text-white/90 text-sm sm:text-base max-w-2xl font-medium leading-relaxed">{{ Str::limit($page['intro'], 150) }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ============ MAIN CONTENT ============ --}}
<div class="container-app py-12 md:py-16">
    <div class="max-w-4xl mx-auto">

        {{-- Intro Block --}}
        @if($page['intro'] ?? null)
            <div class="mb-10 p-6 sm:p-7 {{ $color['bg'] }} border-s-4 {{ $color['border'] }} rounded-2xl border border-outline-variant/30 shadow-2xs">
                <div class="flex items-start gap-3.5">
                    <span class="material-symbols-outlined text-2xl {{ $color['text'] }} flex-shrink-0 mt-0.5">format_quote</span>
                    <p class="text-on-surface leading-relaxed text-base sm:text-lg font-medium">{{ $page['intro'] }}</p>
                </div>
            </div>
        @endif

        {{-- Sections / Structured Content --}}
        @if(($page['sections'] ?? []) && count($page['sections']) > 0)
            <div class="space-y-6 mb-12">
                @foreach($page['sections'] as $i => $section)
                    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-6 sm:p-7 shadow-xs hover:shadow-md transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl {{ $color['icon'] }} flex items-center justify-center font-black text-base flex-shrink-0 font-mono shadow-2xs">
                                {{ $i + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="font-extrabold text-lg sm:text-xl text-on-surface mb-2.5 leading-snug">{{ $section['title'] }}</h2>
                                <div class="text-on-surface-variant leading-relaxed text-sm sm:text-base space-y-2">
                                    {!! nl2br(e($section['body'])) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($page['content'] ?? null)
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-6 sm:p-8 shadow-xs mb-12 prose max-w-none text-on-surface leading-relaxed text-base">
                {!! nl2br(e($page['content'])) !!}
            </div>
        @endif

        {{-- CTA Help Banner --}}
        <div class="bg-gradient-to-l {{ $color['gradient'] }} text-white rounded-3xl p-8 sm:p-12 text-center shadow-lg relative overflow-hidden">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shadow-inner">
                <span class="material-symbols-outlined text-3xl">support_agent</span>
            </div>
            <h3 class="text-2xl sm:text-3xl font-black mb-2">{{ __t('page.help_heading') }}</h3>
            <p class="text-white/90 mb-6 text-sm sm:text-base max-w-lg mx-auto">{{ __t('page.help_text') }}</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('page.show', 'contact') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-gray-900 font-bold text-sm shadow-md hover:bg-gray-50 active:scale-[0.98] transition-all">
                    <span class="material-symbols-outlined text-lg">mail</span>
                    <span>{{ __t('page.contact_btn') }}</span>
                </a>
                @php
                    $ctaWa = preg_replace('/[^0-9]/', '', site('contact_whatsapp', site('social_whatsapp', '')));
                    $ctaWa = ltrim($ctaWa, '0');
                    if (strlen($ctaWa) < 12) $ctaWa = '213' . $ctaWa;
                @endphp
                <a href="https://wa.me/{{ $ctaWa }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-md active:scale-[0.98] transition-all">
                    <span class="material-symbols-outlined text-lg">chat</span>
                    <span>{{ __t('page.whatsapp_btn') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

