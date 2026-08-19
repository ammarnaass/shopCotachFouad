@php
    // Helper to compute image URL from array data (replicates Slide model accessors)
    $getImageUrl = function ($slide) {
        if (empty($slide['image'])) return null;
        if (preg_match('#^(https?://|data:)#i', $slide['image'])) return $slide['image'];
        return asset('storage/' . $slide['image']);
    };
    $getMobileImageUrl = function ($slide) {
        if (empty($slide['mobile_image'])) return null;
        if (preg_match('#^(https?://|data:)#i', $slide['mobile_image'])) return $slide['mobile_image'];
        return asset('storage/' . $slide['mobile_image']);
    };
    $getEffectiveMobileImageUrl = function ($slide) use ($getMobileImageUrl, $getImageUrl) {
        return $getMobileImageUrl($slide) ?? $getImageUrl($slide);
    };
    $animDuration = site('slider_animation_duration', 500);
    $entranceStagger = site('slider_entrance_stagger', 80);
@endphp

@if(!empty($slides))
<section class="hero-slider relative"
         data-autoplay="5000"
         data-pause-on-hover="true"
         data-duration="{{ $animDuration }}"
         data-stagger="{{ $entranceStagger }}"
         role="region" aria-label="{{ __t('home.hero_slider_label') }}">
    <div class="hero-slider-track overflow-hidden relative" role="list">
        @foreach($slides as $index => $slide)
            @php
                $imageUrl = $getImageUrl($slide);
                $mobileImageUrl = $getMobileImageUrl($slide);
                $effectiveMobileImageUrl = $getEffectiveMobileImageUrl($slide);
                $animEffect = $slide['animation_effect'] ?? 'fade';
                $entranceEffect = $slide['entrance_effect'] ?? 'fade-up';
            @endphp
            <article class="hero-slide absolute inset-0 {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none' }}"
                     data-effect="{{ $animEffect }}"
                     data-entrance="{{ $entranceEffect }}"
                     role="listitem" aria-hidden="{{ $index !== 0 }}">
                <picture class="block h-full">
                    @if($effectiveMobileImageUrl)
                        <source media="(max-width: 767px)" srcset="{{ $effectiveMobileImageUrl }}">
                    @endif
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}"
                             alt="{{ $slide['title'] }}"
                             loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                             fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}"
                             decoding="async"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-bl from-brand-700 via-brand-600 to-brand-500"></div>
                    @endif
                </picture>

                <div class="absolute inset-0 bg-gradient-to-l from-black/60 via-black/40 to-black/20"></div>

                <div class="hero-slide-content container-app relative z-10 flex flex-col items-center justify-center h-full px-4 py-12 text-center text-white"
                     data-entrance="{{ $entranceEffect }}">
                    @if(!empty($slide['badge']))
                        <span class="inline-flex items-center gap-2 bg-primary-container text-on-primary-container font-label-caps px-4 py-1.5 rounded-full text-xs font-bold mb-4 shadow-sm" data-animate-index="0">
                            <span class="material-symbols-outlined text-sm">auto_awesome</span>
                            {{ $slide['badge'] }}
                        </span>
                    @endif

                    @if(!empty($slide['title']))
                        <h1 class="mb-4 font-sora text-3xl sm:text-4xl lg:text-5xl font-black leading-tight text-white drop-shadow-md" data-animate-index="1">{{ $slide['title'] }}</h1>
                    @endif

                    @if(!empty($slide['subtitle']))
                        <p class="mb-4 text-base sm:text-lg lg:text-xl text-surface-dim leading-relaxed max-w-2xl font-body-lg" data-animate-index="2">{{ $slide['subtitle'] }}</p>
                    @endif

                    @if(!empty($slide['description']))
                        <p class="mb-6 text-base sm:text-lg lg:text-xl text-surface-dim/90 leading-relaxed max-w-2xl font-body-lg" data-animate-index="3">{{ $slide['description'] }}</p>
                    @endif

                    @if(!empty($slide['btn_text']) && !empty($slide['link']))
                        <div class="flex flex-col sm:flex-row gap-4 justify-center w-full max-w-xl" data-animate-index="4">
                            <a href="{{ $slide['link'] }}"
                               target="{{ $slide['button_target'] ?? '_same' }}"
                               rel="{{ ($slide['button_target'] ?? '_same') === '_blank' ? 'noopener noreferrer' : '' }}"
                               class="inline-flex items-center justify-center gap-2 bg-primary-container text-on-primary-container font-sora font-extrabold text-base py-3.5 px-8 rounded-full hover:bg-inverse-primary hover:scale-105 active:scale-95 transition-all shadow-lg">
                                <span class="material-symbols-outlined text-lg">shopping_bag</span>
                                {{ $slide['btn_text'] }}
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    @if(count($slides) > 1)
    <nav class="hero-nav-side absolute top-1/2 -translate-y-1/2 z-20 left-3 sm:left-4"
         aria-label="{{ __t('home.slider_navigation') }}">
        <button type="button"
                class="hero-prev inline-flex items-center justify-center w-11 h-11 rounded-full bg-black/30 backdrop-blur-md text-white hover:bg-black/50 hover:scale-110 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black/50"
                aria-label="{{ __t('home.prev_slide') }}">
            <span class="material-symbols-outlined text-xl">chevron_left</span>
        </button>
    </nav>

    <nav class="hero-nav-side absolute top-1/2 -translate-y-1/2 z-20 right-3 sm:right-4"
         aria-label="{{ __t('home.slider_navigation') }}">
        <button type="button"
                class="hero-next inline-flex items-center justify-center w-11 h-11 rounded-full bg-black/30 backdrop-blur-md text-white hover:bg-black/50 hover:scale-110 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black/50"
                aria-label="{{ __t('home.next_slide') }}">
            <span class="material-symbols-outlined text-xl">chevron_right</span>
        </button>
    </nav>

    <div class="hero-dots absolute bottom-6 left-1/2 -translate-x-1/2 inline-flex gap-1.5 z-20"
         role="tablist" aria-label="{{ __t('home.slide_selector') }}">
        @foreach($slides as $index => $slide)
            <button type="button"
                    role="tab"
                    aria-selected="{{ $index === 0 }}"
                    aria-label="{{ __t('home.go_to_slide', ['num' => $index + 1]) }}"
                    class="hero-dot w-2.5 h-2.5 rounded-full transition-all {{ $index === 0 ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/75' }}"
                    data-index="{{ $index }}">
            </button>
        @endforeach
    </div>
    @endif
</section>
@endif
