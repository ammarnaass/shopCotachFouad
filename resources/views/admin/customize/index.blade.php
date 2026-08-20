@extends('admin.layout')

@section('title', __t('admin.customize.title') ?? 'تخصيص المظهر والمتجر')

@php
$activeTab = request('tab', 'theme');
$tabs = [
    'theme' => ['icon' => 'palette', 'title' => __t('admin.customize.tab_theme') ?? 'المظهر والألوان', 'badge' => null],
    'banners' => ['icon' => 'campaign', 'title' => __t('admin.customize.tab_banners') ?? 'البانرات والعروض', 'badge' => null],
    'sections' => ['icon' => 'view_quilt', 'title' => __t('admin.customize.tab_sections') ?? 'أقسام الرئيسية', 'badge' => null],
    'header' => ['icon' => 'link', 'title' => __t('admin.customize.tab_header') ?? 'قائمة الهيدر', 'badge' => null],
    'announcement' => ['icon' => 'ad_units', 'title' => __t('admin.customize.tab_announcement') ?? 'الشريط العلوي', 'badge' => null],
    'whatsapp' => ['icon' => 'chat', 'title' => __t('admin.customize.tab_whatsapp') ?? 'زر الواتساب', 'badge' => null],
    'footer' => ['icon' => 'dock', 'title' => __t('admin.customize.tab_footer') ?? 'الفوتر والتذييل', 'badge' => null],
];
@endphp

@section('content')

{{-- ============ TOP HEADER & ACTIONS ============ --}}
<div class="mb-6 space-y-3">
    <nav class="flex items-center gap-2 text-on-surface-variant text-xs sm:text-sm">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">dashboard</span>
            <span>{{ __t('admin.settings.dashboard') ?? 'لوحة التحكم' }}</span>
        </a>
        <span class="material-symbols-outlined text-xs opacity-60">chevron_left</span>
        <span class="text-primary font-bold">{{ __t('admin.customize.title') ?? 'التخصيص' }}</span>
        <span class="material-symbols-outlined text-xs opacity-60">chevron_left</span>
        <span class="text-on-surface font-semibold">{{ $tabs[$activeTab]['title'] ?? 'المظهر' }}</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary text-3xl">palette</span>
                <span>{{ __t('admin.customize.title') ?? 'تخصيص المظهر وتجربة المتجر' }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-on-surface-variant mt-1">{{ __t('admin.customize.description') ?? 'التحكم في ألوان الهوية، البانرات الترويجية، ترتيب الأقسام، وشريط الإعلانات' }}</p>
        </div>

        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.customize.reset') }}" onsubmit="return confirm('{{ __t('admin.customize.reset_confirm') ?? 'هل أنت متأكد من استعادة الإعدادات الافتراضية؟' }}')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-low hover:bg-surface-container text-on-surface text-xs sm:text-sm font-semibold transition-all shadow-2xs">
                    <span class="material-symbols-outlined text-base text-on-surface-variant">undo</span>
                    <span>{{ __t('admin.customize.reset_defaults') ?? 'استعادة الافتراضي' }}</span>
                </button>
            </form>

            <button type="submit" form="customize-form"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white font-bold text-xs sm:text-sm shadow-sm hover:brightness-105 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">save</span>
                <span>{{ __t('admin.customize.save') ?? 'حفظ التغييرات' }}</span>
            </button>
        </div>
    </div>
</div>

{{-- ============ TABS BAR ============ --}}
<div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-1.5 shadow-xs mb-8 overflow-hidden">
    <div class="flex items-center gap-1 overflow-x-auto no-scrollbar scroll-smooth">
        @foreach($tabs as $key => $tab)
            @php $isActive = $activeTab === $key; @endphp
            <a href="{{ route('admin.customize.index', ['tab' => $key]) }}#{{ $key }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition-all duration-200 shrink-0
                      {{ $isActive
                          ? 'bg-primary text-white shadow-2xs'
                          : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined text-lg">{{ $tab['icon'] }}</span>
                <span>{{ $tab['title'] }}</span>
            </a>
        @endforeach
    </div>
</div>

{{-- ============ CUSTOMIZATION FORM ============ --}}
<form method="POST" action="{{ route('admin.customize.update') }}" enctype="multipart/form-data" id="customize-form">
    @csrf
    <input type="hidden" name="theme" value="{{ $current['theme'] }}">
    <input type="hidden" name="primary_color" value="{{ $current['primary_color'] }}">
    <input type="hidden" name="accent_color" value="{{ $current['accent_color'] }}">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- LEFT MAIN AREA --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. TAB: THEME & BRAND COLORS --}}
            @if($activeTab === 'theme')
                {{-- Themes Presets --}}
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">palette</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.customize.theme') ?? 'سمات وقوالب الألوان الجاهزة' }}</h2>
                            <p class="text-xs text-on-surface-variant">اختر قالباً لونياً متناسقاً لمتجرك لتطبيقه بلمسة واحدة</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($themes as $key => $theme)
                            <label class="cursor-pointer relative block group">
                                <input type="radio" name="theme" value="{{ $key }}"
                                       data-primary="{{ $theme['colors'][2] ?? '#2563eb' }}"
                                       data-accent="{{ $theme['colors'][3] ?? '#f59e0b' }}"
                                       {{ old('theme', $current['theme']) === $key ? 'checked' : '' }}
                                       class="peer hidden theme-preset-radio">
                                <div class="border-2 border-outline-variant/60 peer-checked:border-primary peer-checked:bg-primary/5 rounded-2xl p-5 hover:border-primary/50 transition duration-200 shadow-2xs relative h-full flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="material-symbols-outlined text-xl text-primary">{{ $theme['icon'] }}</span>
                                            <span class="font-bold text-sm text-on-surface">{{ $theme['name'] }}</span>
                                        </div>
                                        <p class="text-xs text-on-surface-variant leading-relaxed">{{ $theme['description'] }}</p>
                                    </div>
                                    <div class="flex gap-2 mt-5">
                                        @foreach($theme['colors'] as $color)
                                            <div class="w-7 h-7 rounded-lg shadow-2xs border border-black/10 transition-transform group-hover:scale-105" style="background: {{ $color }}" title="{{ $color }}"></div>
                                        @endforeach
                                    </div>
                                    <div class="absolute top-3 end-3 bg-primary text-white w-6 h-6 rounded-full flex items-center justify-center opacity-0 scale-75 peer-checked:opacity-100 peer-checked:scale-100 transition duration-200 shadow-xs">
                                        <span class="material-symbols-outlined text-xs font-bold">check</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Custom Colors Picker --}}
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">colorize</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.customize.colors') ?? 'تخصيص الألوان يدوياً' }}</h2>
                            <p class="text-xs text-on-surface-variant">حدد كود اللون الأساسي والثانوي ليتوافق مع هوية علامتك التجارية</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/20 space-y-2">
                            <label class="block text-xs font-bold text-on-surface">{{ __t('admin.customize.primary_color') ?? 'اللون الأساسي (Primary Color)' }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="primary_color_picker" value="{{ old('primary_color', $current['primary_color']) }}" class="w-14 h-12 rounded-xl border-2 border-outline-variant cursor-pointer p-0.5 bg-white">
                                <input type="text" id="primary_color_display" value="{{ old('primary_color', $current['primary_color']) }}" class="flex-1 px-3.5 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest font-mono text-sm uppercase" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                <input type="hidden" name="primary_color" id="primary_color" value="{{ old('primary_color', $current['primary_color']) }}">
                            </div>
                            <p class="text-[11px] text-on-surface-variant">{{ __t('admin.customize.primary_color_hint') ?? 'يستخدم في الأزرار الرئيسية، شريط الهيدر، والعناصر المميزة.' }}</p>
                        </div>

                        <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/20 space-y-2">
                            <label class="block text-xs font-bold text-on-surface">{{ __t('admin.customize.accent_color') ?? 'اللون الثانوي / التمييز (Accent Color)' }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="accent_color_picker" value="{{ old('accent_color', $current['accent_color']) }}" class="w-14 h-12 rounded-xl border-2 border-outline-variant cursor-pointer p-0.5 bg-white">
                                <input type="text" id="accent_color_display" value="{{ old('accent_color', $current['accent_color']) }}" class="flex-1 px-3.5 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest font-mono text-sm uppercase" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                                <input type="hidden" name="accent_color" id="accent_color" value="{{ old('accent_color', $current['accent_color']) }}">
                            </div>
                            <p class="text-[11px] text-on-surface-variant">{{ __t('admin.customize.accent_color_hint') ?? 'يستخدم في الشارات، العروض الخاصة، والخصومات.' }}</p>
                        </div>
                    </div>

                    {{-- Live Colors Demonstration Card --}}
                    <div class="p-5 rounded-2xl bg-surface-container-low/30 border border-outline-variant/60">
                        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3 block">معاينة تفاعلية للألوان المختارة</span>
                        <div class="flex items-center gap-3 flex-wrap">
                            <button type="button" class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs shadow-2xs">زر أساسي (Primary)</button>
                            <button type="button" class="px-4 py-2.5 rounded-xl bg-primary/10 text-primary font-bold text-xs">زر ناعم (Subtle)</button>
                            <span class="px-3 py-1 rounded-full bg-accent-500 text-white font-bold text-xs shadow-2xs">شارة العرض (Accent)</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2. TAB: BANNERS & PROMOTIONS --}}
            @if($activeTab === 'banners')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">campaign</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.customize.banners') ?? 'البانرات الإعلانية والترويجية' }}</h2>
                            <p class="text-xs text-on-surface-variant">التحكم في البانرات التسويقية المعروضة في الصفحة الرئيسية</p>
                        </div>
                    </div>

                    {{-- Slider Info Box --}}
                    <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-4 mb-6 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-blue-600 text-2xl shrink-0">slideshow</span>
                            <div>
                                <h3 class="font-bold text-xs sm:text-sm text-blue-900">{{ __t('admin.customize.hero_moved_to_slider') ?? 'إدارة شرائح السلايدر الرئيسي' }}</h3>
                                <p class="text-xs text-blue-700 mt-0.5">يمكنك إضافة وتعديل وحذف شرائح البانر المتحرك من قسم السلايدر المخصص.</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.slider.index') }}" class="shrink-0 inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-2xs">
                            <span class="material-symbols-outlined text-sm">open_in_new</span>
                            <span>{{ __t('admin.customize.go_to_slider') ?? 'قسم السلايدر' }}</span>
                        </a>
                    </div>

                    {{-- Banners 1 & 2 Grid --}}
                    <div class="grid md:grid-cols-2 gap-6">
                        @for($i=1; $i<=2; $i++)
                            <div class="border border-outline-variant/60 rounded-2xl p-5 bg-surface-container-low/20 space-y-4">
                                <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30">
                                    <span class="material-symbols-outlined text-primary text-lg">image</span>
                                    <h3 class="font-extrabold text-sm text-on-surface">{{ __t('admin.customize.banner') ?? 'البانر الترويجي' }} #{{ $i }}</h3>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant mb-1">{{ __t('admin.customize.banner_title') ?? 'عنوان البانر' }}</label>
                                        <input type="text" name="banner_{{ $i }}_title" value="{{ old("banner_{$i}_title", $current["banner_{$i}_title"]) }}" placeholder="مثال: تخفيضات الصيف الكبرى" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant mb-1">{{ __t('admin.customize.banner_subtitle') ?? 'الوصف الفرعي' }}</label>
                                        <input type="text" name="banner_{{ $i }}_subtitle" value="{{ old("banner_{$i}_subtitle", $current["banner_{$i}_subtitle"]) }}" placeholder="مثال: خصم يصل إلى 40% على جميع المنتجات" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    </div>

                                    @php
                                        $bVal = $current["banner_{$i}_image"];
                                        $bUrl = $bVal && !preg_match('#^https?://#i', $bVal) ? asset('storage/' . $bVal) : $bVal;
                                    @endphp

                                    @if($bVal)
                                        <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-xl p-3 flex items-center justify-between gap-3 shadow-2xs">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <img src="{{ $bUrl }}" alt="banner {{ $i }}" class="h-12 w-20 object-cover rounded-lg border border-outline-variant/40 shrink-0">
                                                <p class="text-xs font-mono text-on-surface-variant truncate" dir="ltr">{{ $bVal }}</p>
                                            </div>
                                            <button type="button" onclick="if(confirm('{{ __t('admin.customize.delete_banner_confirm', ['num' => $i]) ?? 'هل أنت متأكد من حذف هذا البانر؟' }}')) document.getElementById('remove-banner-{{ $i }}-form').submit()"
                                                    class="p-1.5 rounded-lg bg-red-50 text-error hover:bg-red-100 text-xs transition-colors shrink-0">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-bold mb-1 text-on-surface-variant">{{ __t('common.upload') ?? 'رفع صورة' }}</label>
                                            <input type="file" name="banner_{{ $i }}_image_file" accept="image/jpeg,image/jpg,image/png,image/webp" class="w-full text-xs @error("banner_{$i}_image_file") border-red-500 @enderror">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold mb-1 text-on-surface-variant">{{ __t('admin.customize.or_url') ?? 'أو رابط صورة' }}</label>
                                            <input type="url" name="banner_{{ $i }}_image" value="{{ old("banner_{$i}_image", $bVal && preg_match('#^https?://#i', $bVal) ? $bVal : '') }}" placeholder="https://..." class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant/60 bg-surface-container-lowest text-xs font-mono">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant mb-1">{{ __t('admin.customize.banner_link') ?? 'رابط التوجيه عند النقر' }}</label>
                                        <input type="url" name="banner_{{ $i }}_link" value="{{ old("banner_{$i}_link", $current["banner_{$i}_link"]) }}" placeholder="https://..." class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs font-mono text-on-surface">
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif

            {{-- 3. TAB: HOMEPAGE SECTIONS REORDER --}}
            @if($activeTab === 'sections')
                @php
                    $sectionOrder = json_decode($current['home_section_order'] ?? '[]', true) ?: ["hero","marquee","categories","featured","latest","banner_1","banner_2"];
                    $sectionLabels = [
                        'hero' => ['label' => 'السلايدر والبانر الرئيسي', 'icon' => 'view_carousel'],
                        'marquee' => ['label' => 'مزايا وضمانات المتجر', 'icon' => 'verified'],
                        'categories' => ['label' => 'أقسام وتصنيفات المنتجات', 'icon' => 'category'],
                        'featured' => ['label' => 'المنتجات الأكثر طلباً والمميزة', 'icon' => 'star'],
                        'latest' => ['label' => 'أحدث الإضافات للمتجر', 'icon' => 'bolt'],
                        'banner_1' => ['label' => 'البانر الترويجي الأول', 'icon' => 'campaign'],
                        'banner_2' => ['label' => 'البانر الترويجي الثاني', 'icon' => 'campaign'],
                    ];
                    $toggleMap = [
                        'hero' => 'show_hero',
                        'marquee' => 'show_marquee',
                        'categories' => 'show_categories',
                        'featured' => 'show_featured',
                        'latest' => 'show_latest',
                        'banner_1' => 'show_banner_1',
                        'banner_2' => null,
                    ];
                @endphp

                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">view_quilt</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">ترتيب أقسام الصفحة الرئيسية</h2>
                            <p class="text-xs text-on-surface-variant">اسحب أو استخدم الأسهم لإعادة الترتيب، واستخدم المفاتيح لإظهار أو إخفاء أي قسم</p>
                        </div>
                    </div>

                    <div x-data="{
                        sections: {{ json_encode($sectionOrder) }},
                        dragIdx: null,
                        sectionMeta: {{ json_encode($sectionLabels) }},
                        toggleMap: {{ json_encode($toggleMap) }},
                        currentToggles: {
                            'show_hero': '{{ $current["show_hero"] ?? "1" }}',
                            'show_marquee': '{{ $current["show_marquee"] ?? "1" }}',
                            'show_categories': '{{ $current["show_categories"] ?? "1" }}',
                            'show_featured': '{{ $current["show_featured"] ?? "1" }}',
                            'show_latest': '{{ $current["show_latest"] ?? "1" }}',
                            'show_banner_1': '{{ $current["show_banner_1"] ?? "1" }}'
                        },
                        moveUp(i) { if (i > 0) { let a = [...this.sections]; [a[i-1], a[i]] = [a[i], a[i-1]]; this.sections = a; } },
                        moveDown(i) { if (i < this.sections.length - 1) { let a = [...this.sections]; [a[i], a[i+1]] = [a[i+1], a[i]]; this.sections = a; } },
                        swap(i) { if (this.dragIdx !== null && this.dragIdx !== i) { let a = [...this.sections]; [a[this.dragIdx], a[i]] = [a[i], a[this.dragIdx]]; this.sections = a; } this.dragIdx = null; }
                    }">
                        <input type="hidden" name="home_section_order" :value="JSON.stringify(sections)">

                        <div class="space-y-2.5">
                            <template x-for="(key, idx) in sections" :key="key">
                                <div class="flex items-center gap-3.5 p-3.5 bg-surface-container-low/40 rounded-2xl border border-outline-variant/50 hover:border-primary/40 transition-all">
                                    <span class="material-symbols-outlined text-on-surface-variant cursor-grab" draggable="true"
                                          @dragstart="dragIdx = idx" @dragover.prevent @drop.prevent="swap(idx)">drag_indicator</span>

                                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-base" x-text="sectionMeta[key]?.icon || 'widgets'"></span>
                                    </div>

                                    <span class="flex-1 text-xs sm:text-sm font-bold text-on-surface" x-text="sectionMeta[key]?.label || key"></span>

                                    {{-- Toggle Switch --}}
                                    <template x-if="toggleMap[key]">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :name="toggleMap[key]" value="1" class="sr-only peer"
                                                   :checked="currentToggles[toggleMap[key]] === '1'"
                                                   @change="currentToggles[toggleMap[key]] = $event.target.checked ? '1' : '0'">
                                            <div class="w-10 h-6 bg-surface-container-high peer-checked:bg-primary rounded-full peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-1 after:left-[3px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all shadow-2xs"></div>
                                        </label>
                                    </template>
                                    <template x-if="!toggleMap[key]">
                                        <span class="text-xs text-on-surface-variant px-2 py-0.5 rounded-md bg-surface-container">دائم</span>
                                    </template>

                                    <div class="flex items-center gap-1 border-s border-outline-variant/40 ps-2">
                                        <button type="button" @click="moveUp(idx)" :disabled="idx === 0" class="w-7 h-7 flex items-center justify-center hover:bg-surface-container rounded-lg disabled:opacity-20 text-on-surface-variant">
                                            <span class="material-symbols-outlined text-base">keyboard_arrow_up</span>
                                        </button>
                                        <button type="button" @click="moveDown(idx)" :disabled="idx === sections.length - 1" class="w-7 h-7 flex items-center justify-center hover:bg-surface-container rounded-lg disabled:opacity-20 text-on-surface-variant">
                                            <span class="material-symbols-outlined text-base">keyboard_arrow_down</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Slider Timing Settings --}}
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">animation</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.customize.slider_animation_settings') ?? 'توقيت وتأثيرات حركة السلايدر' }}</h2>
                            <p class="text-xs text-on-surface-variant">{{ __t('admin.customize.slider_animation_desc') ?? 'التحكم في سرعة الانتقال بين الشرائح والتأثيرات الحركية' }}</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.slider_animation_duration') ?? 'مدة الانتقال (بالميلي ثانية)' }}</label>
                            <input type="number" name="slider_animation_duration" value="{{ $current['slider_animation_duration'] }}" min="100" max="2000" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs font-mono font-bold text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <p class="text-[11px] text-on-surface-variant">{{ __t('admin.customize.slider_animation_duration_hint') ?? 'المدة الزمنية للانتقال السلس بين شريحة وأخرى.' }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.slider_entrance_stagger') ?? 'تأخير دخول العناصر (Stagger)' }}</label>
                            <input type="number" name="slider_entrance_stagger" value="{{ $current['slider_entrance_stagger'] }}" min="10" max="300" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs font-mono font-bold text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <p class="text-[11px] text-on-surface-variant">{{ __t('admin.customize.slider_entrance_stagger_hint') ?? 'الفارق الزمني بين ظهور العنوان، الوصف، والأزرار.' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 4. TAB: HEADER NAVIGATION LINKS --}}
            @if($activeTab === 'header')
                @php
                    $navToggleMap = [
                        'home' => 'nav_show_home',
                        'products' => 'nav_show_products',
                        'contact' => 'nav_show_contact',
                    ];
                @endphp

                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">link</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.customize.header_nav') ?? 'روابط وقوائم شريط الهيدر' }}</h2>
                            <p class="text-xs text-on-surface-variant">{{ __t('admin.customize.nav_reorder_hint') ?? 'إعادة ترتيب وتخصيص الروابط الظاهرة في القائمة الرئيسية للمتجر' }}</p>
                        </div>
                    </div>

                    <div x-data="{
                        items: {{ json_encode($navItemsOrder) }},
                        allCategories: {{ json_encode($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()) }},
                        allPages: {{ json_encode($pages->map(fn($p) => ['id' => $p->id, 'title' => $p->title])->values()) }},
                        toggleMap: {{ json_encode($navToggleMap) }},
                        currentToggles: {
                            'nav_show_home': '{{ $current['nav_show_home'] ?? '1' }}',
                            'nav_show_products': '{{ $current['nav_show_products'] ?? '1' }}',
                            'nav_show_contact': '{{ $current['nav_show_contact'] ?? '1' }}'
                        },
                        sectionLabels: {
                            'home': { label: '{{ __t('admin.customize.nav_home') ?? 'الرئيسية' }}', icon: 'home' },
                            'products': { label: '{{ __t('admin.customize.nav_products') ?? 'المنتجات' }}', icon: 'shopping_bag' },
                            'contact': { label: '{{ __t('admin.customize.nav_contact') ?? 'اتصل بنا' }}', icon: 'mail' }
                        },
                        moveUp(i) { if (i > 0) { let a = [...this.items]; [a[i-1], a[i]] = [a[i], a[i-1]]; this.items = a; } },
                        moveDown(i) { if (i < this.items.length - 1) { let a = [...this.items]; [a[i], a[i+1]] = [a[i+1], a[i]]; this.items = a; } },
                        swap(i) { if (this.dragIdx !== null && this.dragIdx !== i) { let a = [...this.items]; [a[this.dragIdx], a[i]] = [a[i], a[this.dragIdx]]; this.items = a; } this.dragIdx = null; },
                        dragIdx: null,
                        getLabel(key) {
                            if (this.sectionLabels[key]) return this.sectionLabels[key].label;
                            let m;
                            if ((m = key.match(/^cat-(\d+)$/))) {
                                let c = this.allCategories.find(c => c.id == m[1]);
                                return c ? c.name : key;
                            }
                            if ((m = key.match(/^page-(\d+)$/))) {
                                let p = this.allPages.find(p => p.id == m[1]);
                                return p ? p.title : key;
                            }
                            return key;
                        },
                        getIcon(key) {
                            if (this.sectionLabels[key]) return this.sectionLabels[key].icon;
                            if (key.match(/^cat-/)) return 'category';
                            if (key.match(/^page-/)) return 'description';
                            return 'widgets';
                        },
                        isBuiltin(key) { return ['home', 'products', 'contact'].includes(key); },
                        removeItem(idx) { this.items = this.items.filter((_, i) => i !== idx); },
                        addItem(key) { if (!this.items.includes(key)) { this.items.push(key); } },
                        get availableCategories() {
                            let used = this.items.filter(k => k.match(/^cat-/)).map(k => parseInt(k.replace('cat-', '')));
                            return this.allCategories.filter(c => !used.includes(c.id));
                        },
                        get availablePages() {
                            let used = this.items.filter(k => k.match(/^page-/)).map(k => parseInt(k.replace('page-', '')));
                            return this.allPages.filter(p => !used.includes(p.id));
                        }
                    }">
                        <input type="hidden" name="nav_items_order" :value="JSON.stringify(items)">

                        {{-- Current Navigation Items List --}}
                        <div class="space-y-2.5 mb-6">
                            <template x-for="(key, idx) in items" :key="key">
                                <div class="flex items-center gap-3.5 p-3.5 bg-surface-container-low/40 rounded-2xl border border-outline-variant/50 hover:border-primary/40 transition-all">
                                    <span class="material-symbols-outlined text-on-surface-variant cursor-grab" draggable="true"
                                          @dragstart="dragIdx = idx" @dragover.prevent @drop.prevent="swap(idx)">drag_indicator</span>

                                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-base" x-text="getIcon(key)"></span>
                                    </div>

                                    <span class="flex-1 text-xs sm:text-sm font-bold text-on-surface" x-text="getLabel(key)"></span>

                                    <template x-if="toggleMap[key]">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :name="toggleMap[key]" value="1" class="sr-only peer"
                                                   :checked="currentToggles[toggleMap[key]] === '1'"
                                                   @change="currentToggles[toggleMap[key]] = $event.target.checked ? '1' : '0'">
                                            <div class="w-10 h-6 bg-surface-container-high peer-checked:bg-primary rounded-full peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-1 after:left-[3px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all shadow-2xs"></div>
                                        </label>
                                    </template>
                                    <template x-if="!toggleMap[key]">
                                        <span class="text-xs text-on-surface-variant">—</span>
                                    </template>

                                    <div class="flex items-center gap-1 border-s border-outline-variant/40 ps-2">
                                        <button type="button" @click="moveUp(idx)" :disabled="idx === 0" class="w-7 h-7 flex items-center justify-center hover:bg-surface-container rounded-lg disabled:opacity-20 text-on-surface-variant">
                                            <span class="material-symbols-outlined text-base">keyboard_arrow_up</span>
                                        </button>
                                        <button type="button" @click="moveDown(idx)" :disabled="idx === items.length - 1" class="w-7 h-7 flex items-center justify-center hover:bg-surface-container rounded-lg disabled:opacity-20 text-on-surface-variant">
                                            <span class="material-symbols-outlined text-base">keyboard_arrow_down</span>
                                        </button>
                                        <template x-if="!isBuiltin(key)">
                                            <button type="button" @click="removeItem(idx)" class="w-7 h-7 flex items-center justify-center hover:bg-red-50 text-error rounded-lg transition-colors">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Add Categories --}}
                        <div class="pt-5 border-t border-outline-variant/40 mb-4">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2.5">{{ __t('admin.customize.nav_add_category') ?? 'إضافة تصنيف إلى القائمة الرئيسية' }}</label>
                            <template x-if="availableCategories.length > 0">
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="cat in availableCategories" :key="cat.id">
                                        <button type="button" @click="addItem('cat-' + cat.id)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-dashed border-outline-variant/80 hover:border-primary text-on-surface hover:text-primary hover:bg-primary/5 text-xs font-semibold transition-all">
                                            <span class="material-symbols-outlined text-sm text-primary">add</span>
                                            <span x-text="cat.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="availableCategories.length === 0">
                                <p class="text-xs text-on-surface-variant">{{ __t('admin.customize.nav_no_more_categories') ?? 'تمت إضافة جميع التصنيفات المتاحة.' }}</p>
                            </template>
                        </div>

                        {{-- Add Pages --}}
                        <div class="pt-4 border-t border-outline-variant/40">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2.5">{{ __t('admin.customize.nav_add_page') ?? 'إضافة صفحة تعريفية إلى القائمة' }}</label>
                            <template x-if="availablePages.length > 0">
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="page in availablePages" :key="page.id">
                                        <button type="button" @click="addItem('page-' + page.id)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-dashed border-outline-variant/80 hover:border-primary text-on-surface hover:text-primary hover:bg-primary/5 text-xs font-semibold transition-all">
                                            <span class="material-symbols-outlined text-sm text-primary">add</span>
                                            <span x-text="page.title"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="availablePages.length === 0">
                                <p class="text-xs text-on-surface-variant">{{ __t('admin.customize.nav_no_more_pages') ?? 'تمت إضافة جميع الصفحات المتاحة.' }}</p>
                            </template>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 5. TAB: ANNOUNCEMENT TOP BAR --}}
            @if($activeTab === 'announcement')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">ad_units</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.customize.announcement_bar') ?? 'شريط الإعلانات الترويجي العلوي' }}</h2>
                            <p class="text-xs text-on-surface-variant">يظهر في أعلى المتجر لعرض رسائل الشحن المجاني، العروض، وأرقام التواصل</p>
                        </div>
                    </div>

                    {{-- Enable Toggle --}}
                    <div class="p-4 rounded-2xl bg-surface-container-low/30 border border-outline-variant/60 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-on-surface">{{ __t('admin.customize.enable_announcement_bar') ?? 'تفعيل شريط الإعلانات العلوي' }}</h3>
                            <p class="text-xs text-on-surface-variant mt-0.5">إظهار أو إخفاء الشريط في جميع صفحات المتجر</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="top_bar_show" value="1" {{ (old('_token') ? old('top_bar_show') : $current['top_bar_show']) == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-surface-container-high peer-checked:bg-primary rounded-full peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-1 after:left-[3px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all shadow-2xs"></div>
                        </label>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.announcement_text') ?? 'نص الإعلان أو العرض' }}</label>
                            <input type="text" name="top_bar_text" value="{{ old('top_bar_text', $current['top_bar_text']) }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs sm:text-sm font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="{{ __t('admin.customize.announcement_placeholder') ?? 'مثال: 🔥 توصيل سريع ومجاني لجميع الطلبات أكثر من 10,000 د.ج' }}">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.announcement_phone') ?? 'رقم هاتف الشريط' }}</label>
                            <input type="tel" name="top_bar_phone" value="{{ old('top_bar_phone', $current['top_bar_phone']) }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs font-mono text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="+213 550 00 00 00" dir="ltr">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.announcement_link') ?? 'رابط التوجيه' }}</label>
                            <input type="url" name="top_bar_link" value="{{ old('top_bar_link', $current['top_bar_link']) }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs font-mono text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="https://..." dir="ltr">
                        </div>

                        {{-- Color Controls --}}
                        <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/20 space-y-2">
                            <label class="block text-xs font-bold text-on-surface">{{ __t('admin.customize.bg_color') ?? 'لون خلفية الشريط' }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="top_bar_bg_color_picker" value="{{ old('top_bar_bg_color', $current['top_bar_bg_color']) }}" class="w-14 h-11 rounded-xl border-2 border-outline-variant cursor-pointer p-0.5 bg-white">
                                <input type="text" id="top_bar_bg_color_display" value="{{ old('top_bar_bg_color', $current['top_bar_bg_color']) }}" class="flex-1 px-3 py-2 rounded-xl border border-outline-variant/60 bg-surface-container-lowest font-mono text-xs uppercase">
                                <input type="hidden" name="top_bar_bg_color" id="top_bar_bg_color" value="{{ old('top_bar_bg_color', $current['top_bar_bg_color']) }}">
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/20 space-y-2">
                            <label class="block text-xs font-bold text-on-surface">{{ __t('admin.customize.text_color') ?? 'لون نص الشريط' }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="top_bar_text_color_picker" value="{{ old('top_bar_text_color', $current['top_bar_text_color']) }}" class="w-14 h-11 rounded-xl border-2 border-outline-variant cursor-pointer p-0.5 bg-white">
                                <input type="text" id="top_bar_text_color_display" value="{{ old('top_bar_text_color', $current['top_bar_text_color']) }}" class="flex-1 px-3 py-2 rounded-xl border border-outline-variant/60 bg-surface-container-lowest font-mono text-xs uppercase">
                                <input type="hidden" name="top_bar_text_color" id="top_bar_text_color" value="{{ old('top_bar_text_color', $current['top_bar_text_color']) }}">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 6. TAB: WHATSAPP FLOATING BUTTON --}}
            @if($activeTab === 'whatsapp')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">chat</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.customize.whatsapp_button') ?? 'زر المحادثة العائم (WhatsApp)' }}</h2>
                            <p class="text-xs text-on-surface-variant">زر عائم ثابت في أسفل الشاشة للتواصل المباشر مع العملاء عبر الواتساب</p>
                        </div>
                    </div>

                    {{-- Enable Toggle --}}
                    <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-emerald-950">{{ __t('admin.customize.enable_whatsapp_btn') ?? 'تفعيل زر الواتساب العائم' }}</h3>
                            <p class="text-xs text-emerald-700 mt-0.5">إظهار أيقونة المراسلة المباشرة في جميع صفحات المتجر</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="whatsapp_btn_show" value="1" {{ (old('_token') ? old('whatsapp_btn_show') : $current['whatsapp_btn_show']) == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-surface-container-high peer-checked:bg-emerald-600 rounded-full peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-1 after:left-[3px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all shadow-2xs"></div>
                        </label>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.whatsapp_number') ?? 'رقم هاتف الواتساب' }} <span class="text-xs text-on-surface-variant">({{ __t('admin.customize.whatsapp_number_hint') ?? 'مع كود الدولة' }})</span></label>
                            <input type="text" name="whatsapp_btn_phone" value="{{ old('whatsapp_btn_phone', $current['whatsapp_btn_phone']) }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs font-mono text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="213550000000" dir="ltr">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.whatsapp_position') ?? 'موضع الزر في الشاشة' }}</label>
                            <div class="relative">
                                <select name="whatsapp_btn_position" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary appearance-none">
                                    <option value="right" {{ old('whatsapp_btn_position', $current['whatsapp_btn_position']) === 'right' ? 'selected' : '' }}>{{ __t('admin.customize.bottom_right') ?? 'أسفل اليمين' }}</option>
                                    <option value="left" {{ old('whatsapp_btn_position', $current['whatsapp_btn_position']) === 'left' ? 'selected' : '' }}>{{ __t('admin.customize.bottom_left') ?? 'أسفل اليسار' }}</option>
                                </select>
                                <span class="material-symbols-outlined absolute end-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.whatsapp_default_message') ?? 'الرسالة الافتراضية عند فتح المحادثة' }}</label>
                            <input type="text" name="whatsapp_btn_text" value="{{ old('whatsapp_btn_text', $current['whatsapp_btn_text']) }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs sm:text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="{{ __t('admin.customize.whatsapp_default_placeholder') ?? 'مرحباً، أود الاستفسار عن المنتجات المتاحة...' }}">
                        </div>
                    </div>
                </div>
            @endif

            {{-- 7. TAB: FOOTER --}}
            @if($activeTab === 'footer')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">dock</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.customize.footer') ?? 'تذييل المتجر (Footer)' }}</h2>
                            <p class="text-xs text-on-surface-variant">النبذة التعريفية وحقوق الملكية الفكرية في أسفل كل الصفحات</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.footer_about') ?? 'نبذة عن المتجر في الفوتر' }}</label>
                            <textarea name="footer_about" rows="3" class="w-full p-4 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs sm:text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary">{{ old('footer_about', $current['footer_about']) }}</textarea>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.customize.footer_copyright') ?? 'نص حقوق النشر (Copyright)' }}</label>
                            <input type="text" name="footer_copyright" value="{{ old('footer_copyright', $current['footer_copyright']) }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-xs sm:text-sm text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        </div>
                    </div>

                    <div class="p-4 bg-surface-container-low/40 border border-outline-variant/60 rounded-2xl flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">contact_support</span>
                            <p class="text-xs text-on-surface-variant">{{ __t('admin.customize.footer_contact_hint') ?? 'تعديل أرقام وعناوين التواصل المعروضة في الفوتر يتم من خلال قسم إعدادات الاتصال.' }}</p>
                        </div>
                        <a href="{{ route('admin.settings.index', ['tab' => 'contact']) }}" class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary text-white text-xs font-bold transition-all shadow-2xs hover:brightness-105">
                            <span class="material-symbols-outlined text-sm">settings</span>
                            <span>{{ __t('admin.customize.go_to_settings') ?? 'إعدادات الاتصال' }}</span>
                        </a>
                    </div>
                </div>
            @endif

        </div>

        {{-- RIGHT SIDEBAR (TIPS & ACTIONS) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Save Action Sticky Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-5 shadow-xs sticky top-24">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-outline-variant/40">
                    <span class="material-symbols-outlined text-primary text-xl">save</span>
                    <h3 class="font-bold text-sm text-on-surface">إجراءات الحفظ</h3>
                </div>

                <p class="text-xs text-on-surface-variant mb-4 leading-relaxed">
                    جميع التعديلات على الألوان والبانرات وترتيب الأقسام تنعكس فورياً في واجهة المتجر بعد الحفظ.
                </p>

                <button type="submit" form="customize-form"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary text-white font-bold text-sm shadow-sm hover:brightness-105 active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-lg">save</span>
                    <span>{{ __t('admin.customize.save') ?? 'حفظ التغييرات' }}</span>
                </button>
            </div>

            {{-- Contextual Tips Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-outline-variant/40">
                    <span class="material-symbols-outlined text-primary text-xl">lightbulb</span>
                    <h3 class="font-bold text-sm text-on-surface">{{ __t('admin.settings.quick_tips') ?? 'نصائح وتوجيهات' }}</h3>
                </div>

                <div class="space-y-3 text-xs text-on-surface-variant leading-relaxed">
                    @if($activeTab === 'theme')
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>اختر ألواناً متناسقة ذات تباين جيد مع النصوص لضمان راحة القراءة للمستخدمين.</span>
                        </p>
                    @elseif($activeTab === 'sections')
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>وضع الأقسام الأكثر طلباً في أعلى الصفحة الرئيسية يزيد من نسبة تحويل الزوار إلى مشترين.</span>
                        </p>
                    @elseif($activeTab === 'whatsapp')
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>زر الواتساب العائم يسهل على العملاء الاستفسار وإتمام الطلبات المباشرة بسرعة.</span>
                        </p>
                    @elseif($activeTab === 'announcement')
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>استخدم الشريط العلوي للإعلان عن عروض مؤقتة أو التوصيل المجاني لتحفيز الطلبات.</span>
                        </p>
                    @else
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>تأكد من مراجعة الإعدادات وحفظها لتطبيقها على المتجر.</span>
                        </p>
                    @endif
                </div>
            </div>

        </div>

    </div>
</form>

{{-- Hidden forms for banner removal --}}
@for($i = 1; $i <= 2; $i++)
    <form id="remove-banner-{{ $i }}-form" method="POST" action="{{ route('admin.customize.removeImage') }}" style="display:none">
        @csrf
        <input type="hidden" name="key" value="banner_{{ $i }}_image">
    </form>
@endfor

{{-- Success / Error Toasts --}}
@if(session('success'))
<div id="success-toast" class="fixed bottom-6 end-6 z-50 bg-emerald-600 text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 animate-slide-up border border-emerald-500">
    <span class="material-symbols-outlined text-xl">check_circle</span>
    <span class="text-sm font-bold">{{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100 ms-3">
        <span class="material-symbols-outlined text-base">close</span>
    </button>
</div>
<script>
    setTimeout(() => { const el = document.getElementById('success-toast'); if(el) el.remove(); }, 5000);
</script>
@endif

@if($errors->any())
<div id="error-toast" class="fixed bottom-6 end-6 z-50 bg-error text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 animate-slide-up border border-red-400">
    <span class="material-symbols-outlined text-xl">error</span>
    <span class="text-sm font-bold">{{ __t('admin.settings.validation_error') ?? 'يرجى مراجعة الأخطاء وتصحيحها' }}</span>
    <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100 ms-3">
        <span class="material-symbols-outlined text-base">close</span>
    </button>
</div>
<script>
    setTimeout(() => { const el = document.getElementById('error-toast'); if(el) el.remove(); }, 5000);
</script>
@endif

@endsection

@push('scripts')
<script>
    function wireColor(pickerId, displayId, hiddenId) {
        const picker = document.getElementById(pickerId);
        const display = document.getElementById(displayId);
        const hidden = document.getElementById(hiddenId);
        if (!picker || !display || !hidden) return;
        picker.addEventListener('input', e => {
            display.value = e.target.value;
            hidden.value = e.target.value;
        });
        display.addEventListener('input', e => {
            let v = e.target.value.trim();
            if (!v.startsWith('#')) v = '#' + v;
            if (/^#[0-9A-Fa-f]{6}$/.test(v)) {
                picker.value = v;
                hidden.value = v;
                display.value = v.toUpperCase();
            } else {
                hidden.value = v;
            }
        });
    }
    wireColor('primary_color_picker', 'primary_color_display', 'primary_color');
    wireColor('accent_color_picker', 'accent_color_display', 'accent_color');
    wireColor('top_bar_bg_color_picker', 'top_bar_bg_color_display', 'top_bar_bg_color');
    wireColor('top_bar_text_color_picker', 'top_bar_text_color_display', 'top_bar_text_color');

    document.querySelectorAll('.theme-preset-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            if (!this.checked) return;
            const p = this.dataset.primary;
            const a = this.dataset.accent;
            if (p) {
                const pickerP = document.getElementById('primary_color_picker');
                const dispP = document.getElementById('primary_color_display');
                const hidP = document.getElementById('primary_color');
                if (pickerP) pickerP.value = p;
                if (dispP) dispP.value = p.toUpperCase();
                if (hidP) hidP.value = p;
            }
            if (a) {
                const pickerA = document.getElementById('accent_color_picker');
                const dispA = document.getElementById('accent_color_display');
                const hidA = document.getElementById('accent_color');
                if (pickerA) pickerA.value = a;
                if (dispA) dispA.value = a.toUpperCase();
                if (hidA) hidA.value = a;
            }
        });
    });
</script>
@endpush
