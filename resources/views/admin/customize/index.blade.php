@extends('admin.layout')

@section('title', __t('admin.customize.title'))

@php
$activeTab = request('tab', 'theme');
$tabs = [
    'theme' => ['icon' => 'palette', 'title' => __t('admin.customize.tab_theme')],
    'banners' => ['icon' => 'campaign', 'title' => __t('admin.customize.tab_banners')],
    'sections' => ['icon' => 'visibility', 'title' => __t('admin.customize.tab_sections')],
    'header' => ['icon' => 'link', 'title' => __t('admin.customize.tab_header')],
    'announcement' => ['icon' => 'ad_units', 'title' => __t('admin.customize.tab_announcement')],
    'whatsapp' => ['icon' => 'chat', 'title' => __t('admin.customize.tab_whatsapp')],
    'footer' => ['icon' => 'directions_walk', 'title' => __t('admin.customize.tab_footer')],
];
@endphp

@section('content')
<nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-3">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">{{ __t('admin.settings.dashboard') }}</a>
    <span class="material-symbols-outlined text-xs">chevron_left</span>
    <span class="text-primary font-semibold">{{ __t('admin.customize.title') }}</span>
</nav>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-on-surface">{{ __t('admin.customize.title') }}</h1>
        <p class="text-on-surface-variant text-sm mt-1">{{ __t('admin.customize.description') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <form method="POST" action="{{ route('admin.customize.reset') }}" onsubmit="return confirm('{{ __t('admin.customize.reset_confirm') }}')">
            @csrf
            <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-on-surface px-4 py-2 rounded-lg text-sm flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">undo</span>{{ __t('admin.customize.reset_defaults') }}
            </button>
        </form>
        <button type="submit" form="customize-form"
                class="px-6 py-2.5 rounded-xl bg-primary text-white font-medium hover:bg-primary-container shadow-sm transition-all active:scale-95 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">save</span>
            {{ __t('admin.customize.save') }}
        </button>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
    <div class="flex border-b border-outline-variant overflow-x-auto">
        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.customize.index', ['tab' => $key]) }}#{{ $key }}"
               class="flex items-center gap-2 px-5 py-3.5 font-medium text-sm whitespace-nowrap transition-all {{ $activeTab === $key ? 'border-b-2 border-primary text-primary bg-primary-fixed/30' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined text-lg">{{ $tab['icon'] }}</span>
                {{ $tab['title'] }}
            </a>
        @endforeach
    </div>
</div>

<form method="POST" action="{{ route('admin.customize.update') }}" enctype="multipart/form-data" id="customize-form">
    @csrf
    <input type="hidden" name="theme" value="{{ $current['theme'] }}">
    <input type="hidden" name="primary_color" value="{{ $current['primary_color'] }}">
    <input type="hidden" name="accent_color" value="{{ $current['accent_color'] }}">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-8">

            @if($activeTab === 'theme')
            {{-- Theme --}}
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-purple-600">palette</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.customize.theme') }}</h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($themes as $key => $theme)
                        <label class="cursor-pointer relative block group">
                            <input type="radio" name="theme" value="{{ $key }}"
                                   data-primary="{{ $theme['colors'][2] ?? '#2563eb' }}"
                                   data-accent="{{ $theme['colors'][3] ?? '#f59e0b' }}"
                                   {{ old('theme', $current['theme']) === $key ? 'checked' : '' }}
                                   class="peer hidden theme-preset-radio">
                            <div class="border-2 border-outline-variant peer-checked:border-primary peer-checked:bg-primary/5 rounded-xl p-4 hover:border-primary/50 transition duration-200 shadow-sm relative h-full flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="material-symbols-outlined text-xl text-primary">{{ $theme['icon'] }}</span>
                                        <span class="font-bold text-sm">{{ $theme['name'] }}</span>
                                    </div>
                                    <p class="text-xs text-on-surface-variant leading-relaxed">{{ $theme['description'] }}</p>
                                </div>
                                <div class="flex gap-1.5 mt-4">
                                    @foreach($theme['colors'] as $color)
                                        <div class="w-6 h-6 rounded-md shadow-sm border border-black/10" style="background: {{ $color }}" title="{{ $color }}"></div>
                                    @endforeach
                                </div>
                                <div class="absolute top-2 left-2 bg-primary text-white w-5 h-5 rounded-full flex items-center justify-between opacity-0 scale-75 peer-checked:opacity-100 peer-checked:scale-100 transition duration-200">
                                    <span class="material-symbols-outlined text-xs font-bold mx-auto">check</span>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- Colors --}}
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">colorize</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.customize.colors') }}</h4>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-2">{{ __t('admin.customize.primary_color') }}</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="primary_color_picker" value="{{ old('primary_color', $current['primary_color']) }}" class="w-16 h-12 rounded border-2 cursor-pointer">
                            <input type="text" id="primary_color_display" value="{{ old('primary_color', $current['primary_color']) }}" class="flex-1 px-3 py-2 border rounded-lg font-mono text-sm" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                            <input type="hidden" name="primary_color" id="primary_color" value="{{ old('primary_color', $current['primary_color']) }}">
                        </div>
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.customize.primary_color_hint') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.accent_color') }}</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="accent_color_picker" value="{{ old('accent_color', $current['accent_color']) }}" class="w-16 h-12 rounded border-2 cursor-pointer">
                            <input type="text" id="accent_color_display" value="{{ old('accent_color', $current['accent_color']) }}" class="flex-1 px-3 py-2 border rounded-lg font-mono text-sm" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                            <input type="hidden" name="accent_color" id="accent_color" value="{{ old('accent_color', $current['accent_color']) }}">
                        </div>
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.customize.accent_color_hint') }}</p>
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'banners')
            {{-- Banners --}}
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-orange-600">campaign</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.customize.banners') }}</h4>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-600 shrink-0">info</span>
                    <p class="text-sm text-blue-800">{{ __t('admin.customize.hero_moved_to_slider') }}</p>
                    <a href="{{ route('admin.slider.index') }}" class="shrink-0 inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs transition">
                        <span class="material-symbols-outlined text-sm">slideshow</span>
                        {{ __t('admin.customize.go_to_slider') }}
                    </a>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    @for($i=1; $i<=2; $i++)
                        <div class="border-2 border-dashed border-outline-variant rounded-xl p-4">
                            <h3 class="font-bold text-sm text-on-surface mb-3">
                                <span class="material-symbols-outlined ml-1">image</span>{{ __t('admin.customize.banner') }} {{ $i }}
                            </h3>
                            <div class="space-y-3">
                                <input type="text" name="banner_{{ $i }}_title" value="{{ old("banner_{$i}_title", $current["banner_{$i}_title"]) }}" placeholder="{{ __t('admin.customize.banner_title') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                <input type="text" name="banner_{{ $i }}_subtitle" value="{{ old("banner_{$i}_subtitle", $current["banner_{$i}_subtitle"]) }}" placeholder="{{ __t('admin.customize.banner_subtitle') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">

                                @php
                                    $bVal = $current["banner_{$i}_image"];
                                    $bUrl = $bVal && !preg_match('#^https?://#i', $bVal) ? asset('storage/' . $bVal) : $bVal;
                                @endphp

                                @if($bVal)
                                    <div class="bg-surface-container-low border border-dashed border-outline-variant rounded p-2 flex items-center gap-2">
                                        <img src="{{ $bUrl }}" alt="banner {{ $i }}" class="h-12 w-20 object-cover rounded border">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-on-surface-variant truncate" dir="ltr">{{ $bVal }}</p>
                                        </div>
                                        <button type="button" onclick="if(confirm('{{ __t('admin.customize.delete_banner_confirm', ['num' => $i]) }}')) document.getElementById('remove-banner-{{ $i }}-form').submit()" class="bg-error-container hover:bg-error-container text-on-error-container px-2 py-1 rounded text-xs">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold mb-1 text-on-surface-variant">{{ __t('common.upload') }}</label>
                                        <input type="file" name="banner_{{ $i }}_image_file" accept="image/jpeg,image/jpg,image/png,image/webp" class="w-full text-xs @error("banner_{$i}_image_file") border-red-500 @enderror">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold mb-1 text-on-surface-variant">{{ __t('admin.customize.or_url') }}</label>
                                        <input type="url" name="banner_{{ $i }}_image" value="{{ old("banner_{$i}_image", $bVal && preg_match('#^https?://#i', $bVal) ? $bVal : '') }}" placeholder="https://..." class="w-full px-2 py-1.5 border rounded focus:ring-2 focus:ring-blue-500 text-xs font-mono">
                                    </div>
                                </div>
                                <input type="url" name="banner_{{ $i }}_link" value="{{ old("banner_{$i}_link", $current["banner_{$i}_link"]) }}" placeholder="{{ __t('admin.customize.banner_link') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>
                        </div>
                    @endfor
                </div>
            </section>

            @elseif($activeTab === 'sections')
            {{-- Homepage sections: reorder + show/hide --}}
            @php
                $sectionOrder = json_decode($current['home_section_order'] ?? '[]', true) ?: ["hero","marquee","categories","featured","latest","banner_1","banner_2"];
                $sectionLabels = [
                    'hero' => ['label' => 'السلايدر الرئيسي', 'icon' => 'view_carousel'],
                    'marquee' => ['label' => 'المميزات', 'icon' => 'verified'],
                    'categories' => ['label' => 'الأقسام', 'icon' => 'category'],
                    'featured' => ['label' => 'منتجات مميزة', 'icon' => 'star'],
                    'latest' => ['label' => 'أحدث المنتجات', 'icon' => 'new_releases'],
                    'banner_1' => ['label' => 'بانر رئيسي', 'icon' => 'campaign'],
                    'banner_2' => ['label' => 'بانر ثانوي', 'icon' => 'campaign'],
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
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-indigo-600">visibility</span>
                    <h4 class="font-semibold text-lg">أقسام الصفحة الرئيسية</h4>
                </div>
                <p class="text-sm text-on-surface-variant mb-4">اسحب لإعادة ترتيب الأقسام، واستخدم الأسهم أو المفتاح لإظهار/إخفاء كل قسم.</p>
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
                    <div class="space-y-2">
                        <template x-for="(key, idx) in sections" :key="key">
                            <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-lg border border-outline-variant/50 cursor-move hover:border-primary/30 transition-colors">
                                <span class="material-symbols-outlined text-gray-400 cursor-grab" draggable="true"
                                      @dragstart="dragIdx = idx" @dragover.prevent @drop.prevent="swap(idx)">drag_indicator</span>
                                <span class="material-symbols-outlined text-primary text-lg" x-text="sectionMeta[key]?.icon || 'widgets'"></span>
                                <span class="flex-1 text-sm font-semibold" x-text="sectionMeta[key]?.label || key"></span>
                                <template x-if="toggleMap[key]">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" :name="toggleMap[key]" value="1" class="sr-only peer"
                                               :checked="currentToggles[toggleMap[key]] === '1'"
                                               @change="currentToggles[toggleMap[key]] = $event.target.checked ? '1' : '0'">
                                        <div class="w-9 h-5 bg-gray-300 peer-checked:bg-primary rounded-full peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                                    </label>
                                </template>
                                <template x-if="!toggleMap[key]">
                                    <span class="text-xs text-gray-400">دائم</span>
                                </template>
                                <button type="button" @click="moveUp(idx)" :disabled="idx === 0" class="p-1 hover:bg-gray-200 rounded disabled:opacity-30">
                                    <span class="material-symbols-outlined text-sm">keyboard_arrow_up</span>
                                </button>
                                <button type="button" @click="moveDown(idx)" :disabled="idx === sections.length - 1" class="p-1 hover:bg-gray-200 rounded disabled:opacity-30">
                                    <span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            {{-- Slider animation timing --}}
            <section class="settings-card rounded-xl p-6 space-y-4">
                <div class="flex items-center gap-2 mb-4 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-indigo-600">animation</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.customize.slider_animation_settings') }}</h4>
                </div>
                <p class="text-sm text-on-surface-variant mb-4">{{ __t('admin.customize.slider_animation_desc') }}</p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.slider_animation_duration') }}</label>
                        <input type="number" name="slider_animation_duration" value="{{ $current['slider_animation_duration'] }}" min="100" max="2000" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.customize.slider_animation_duration_hint') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.slider_entrance_stagger') }}</label>
                        <input type="number" name="slider_entrance_stagger" value="{{ $current['slider_entrance_stagger'] }}" min="10" max="300" class="w-full px-3 py-2 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary">
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.customize.slider_entrance_stagger_hint') }}</p>
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'header')
            {{-- Header links: reorder + show/hide --}}
            @php
                $navToggleMap = [
                    'home' => 'nav_show_home',
                    'products' => 'nav_show_products',
                    'contact' => 'nav_show_contact',
                ];
            @endphp
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-indigo-600">link</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.customize.header_nav') }}</h4>
                </div>
                <p class="text-sm text-on-surface-variant mb-4">{{ __t('admin.customize.nav_reorder_hint') }}</p>

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
                        'home': { label: '{{ __t('admin.customize.nav_home') }}', icon: 'home' },
                        'products': { label: '{{ __t('admin.customize.nav_products') }}', icon: 'shopping_bag' },
                        'contact': { label: '{{ __t('admin.customize.nav_contact') }}', icon: 'mail' }
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

                    {{-- Current nav items --}}
                    <div class="space-y-2">
                        <template x-for="(key, idx) in items" :key="key">
                            <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-lg border border-outline-variant/50 cursor-move hover:border-primary/30 transition-colors">
                                <span class="material-symbols-outlined text-gray-400 cursor-grab" draggable="true"
                                      @dragstart="dragIdx = idx" @dragover.prevent @drop.prevent="swap(idx)">drag_indicator</span>
                                <span class="material-symbols-outlined text-primary text-lg" x-text="getIcon(key)"></span>
                                <span class="flex-1 text-sm font-semibold" x-text="getLabel(key)"></span>
                                <template x-if="toggleMap[key]">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" :name="toggleMap[key]" value="1" class="sr-only peer"
                                               :checked="currentToggles[toggleMap[key]] === '1'"
                                               @change="currentToggles[toggleMap[key]] = $event.target.checked ? '1' : '0'">
                                        <div class="w-9 h-5 bg-gray-300 peer-checked:bg-primary rounded-full peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                                    </label>
                                </template>
                                <template x-if="!toggleMap[key]">
                                    <span class="text-xs text-gray-400">—</span>
                                </template>
                                <button type="button" @click="moveUp(idx)" :disabled="idx === 0" class="p-1 hover:bg-gray-200 rounded disabled:opacity-30">
                                    <span class="material-symbols-outlined text-sm">keyboard_arrow_up</span>
                                </button>
                                <button type="button" @click="moveDown(idx)" :disabled="idx === items.length - 1" class="p-1 hover:bg-gray-200 rounded disabled:opacity-30">
                                    <span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
                                </button>
                                <template x-if="!isBuiltin(key)">
                                    <button type="button" @click="removeItem(idx)" class="p-1 hover:bg-red-100 rounded text-red-500">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Add categories --}}
                    <div class="mt-5 pt-4 border-t border-outline-variant/30">
                        <label class="block text-sm font-semibold mb-2">{{ __t('admin.customize.nav_add_category') }}</label>
                        <template x-if="availableCategories.length > 0">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="cat in availableCategories" :key="cat.id">
                                    <button type="button" @click="addItem('cat-' + cat.id)"
                                            class="flex items-center gap-1.5 px-3 py-1.5 border border-dashed border-outline-variant rounded-lg text-xs hover:bg-primary/5 hover:border-primary/40 transition">
                                        <span class="material-symbols-outlined text-sm text-primary">add</span>
                                        <span x-text="cat.name"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                        <template x-if="availableCategories.length === 0">
                            <p class="text-xs text-gray-400">{{ __t('admin.customize.nav_no_more_categories') }}</p>
                        </template>
                    </div>

                    {{-- Add pages --}}
                    <div class="mt-5 pt-4 border-t border-outline-variant/30">
                        <label class="block text-sm font-semibold mb-2">{{ __t('admin.customize.nav_add_page') }}</label>
                        <template x-if="availablePages.length > 0">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="page in availablePages" :key="page.id">
                                    <button type="button" @click="addItem('page-' + page.id)"
                                            class="flex items-center gap-1.5 px-3 py-1.5 border border-dashed border-outline-variant rounded-lg text-xs hover:bg-primary/5 hover:border-primary/40 transition">
                                        <span class="material-symbols-outlined text-sm text-primary">add</span>
                                        <span x-text="page.title"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                        <template x-if="availablePages.length === 0">
                            <p class="text-xs text-gray-400">{{ __t('admin.customize.nav_no_more_pages') }}</p>
                        </template>
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'announcement')
            {{-- Top Announcement Bar --}}
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-blue-600">ad_units</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.customize.announcement_bar') }}</h4>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-surface-container-low max-w-xs">
                            <input type="checkbox" name="top_bar_show" value="1" {{ (old('_token') ? old('top_bar_show') : $current['top_bar_show']) == '1' ? 'checked' : '' }} class="w-5 h-5 text-primary rounded">
                            <span class="text-sm font-semibold">{{ __t('admin.customize.enable_announcement_bar') }}</span>
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.announcement_text') }}</label>
                        <input type="text" name="top_bar_text" value="{{ old('top_bar_text', $current['top_bar_text']) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="{{ __t('admin.customize.announcement_placeholder') }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.announcement_phone') }}</label>
                        <input type="tel" name="top_bar_phone" value="{{ old('top_bar_phone', $current['top_bar_phone']) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="+213...">
                    </div>
                    <div class="md:col-span-2 mt-2 pt-4 border-t border-outline-variant">
                        <h5 class="text-sm font-semibold mb-3 text-on-surface-variant">عناصر الشريط</h5>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-surface-container-low max-w-xs">
                            <input type="checkbox" name="top_bar_show_cod" value="1" {{ (old('_token') ? old('top_bar_show_cod') : $current['top_bar_show_cod']) == '1' ? 'checked' : '' }} class="w-5 h-5 text-primary rounded">
                            <span class="text-sm font-semibold">إظهار الدفع عند الاستلام</span>
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-surface-container-low max-w-xs">
                            <input type="checkbox" name="top_bar_show_track" value="1" {{ (old('_token') ? old('top_bar_show_track') : $current['top_bar_show_track']) == '1' ? 'checked' : '' }} class="w-5 h-5 text-primary rounded">
                            <span class="text-sm font-semibold">إظهار تتبع الطلب</span>
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-surface-container-low max-w-xs">
                            <input type="checkbox" name="top_bar_show_help" value="1" {{ (old('_token') ? old('top_bar_show_help') : $current['top_bar_show_help']) == '1' ? 'checked' : '' }} class="w-5 h-5 text-primary rounded">
                            <span class="text-sm font-semibold">إظهار المساعدة</span>
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1">نص الزر</label>
                        <input type="text" name="top_bar_btn_text" value="{{ old('top_bar_btn_text', $current['top_bar_btn_text']) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="مثال: اطلب الآن">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1">رابط الزر</label>
                        <input type="url" name="top_bar_btn_url" value="{{ old('top_bar_btn_url', $current['top_bar_btn_url']) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm" placeholder="https://...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">{{ __t('admin.customize.bg_color') }}</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="top_bar_bg_color_picker" value="{{ old('top_bar_bg_color', $current['top_bar_bg_color']) }}" class="w-16 h-12 rounded border-2 cursor-pointer">
                            <input type="text" id="top_bar_bg_color_display" value="{{ old('top_bar_bg_color', $current['top_bar_bg_color']) }}" class="flex-1 px-3 py-2 border rounded-lg font-mono text-sm" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                            <input type="hidden" name="top_bar_bg_color" id="top_bar_bg_color" value="{{ old('top_bar_bg_color', $current['top_bar_bg_color']) }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">{{ __t('admin.customize.text_color') }}</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="top_bar_text_color_picker" value="{{ old('top_bar_text_color', $current['top_bar_text_color']) }}" class="w-16 h-12 rounded border-2 cursor-pointer">
                            <input type="text" id="top_bar_text_color_display" value="{{ old('top_bar_text_color', $current['top_bar_text_color']) }}" class="flex-1 px-3 py-2 border rounded-lg font-mono text-sm" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                            <input type="hidden" name="top_bar_text_color" id="top_bar_text_color" value="{{ old('top_bar_text_color', $current['top_bar_text_color']) }}">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.announcement_link') }}</label>
                        <input type="url" name="top_bar_link" value="{{ old('top_bar_link', $current['top_bar_link']) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm" placeholder="https://...">
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'whatsapp')
            {{-- WhatsApp Floating Button --}}
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-green-600">chat</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.customize.whatsapp_button') }}</h4>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-surface-container-low max-w-xs">
                            <input type="checkbox" name="whatsapp_btn_show" value="1" {{ (old('_token') ? old('whatsapp_btn_show') : $current['whatsapp_btn_show']) == '1' ? 'checked' : '' }} class="w-5 h-5 text-primary rounded">
                            <span class="text-sm font-semibold">{{ __t('admin.customize.enable_whatsapp_btn') }}</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.whatsapp_number') }} <span class="text-xs text-on-surface-variant">({{ __t('admin.customize.whatsapp_number_hint') }})</span></label>
                        <input type="text" name="whatsapp_btn_phone" value="{{ old('whatsapp_btn_phone', $current['whatsapp_btn_phone']) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm" placeholder="966500000000">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.whatsapp_position') }}</label>
                        <select name="whatsapp_btn_position" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="right" {{ old('whatsapp_btn_position', $current['whatsapp_btn_position']) === 'right' ? 'selected' : '' }}>{{ __t('admin.customize.bottom_right') }}</option>
                            <option value="left" {{ old('whatsapp_btn_position', $current['whatsapp_btn_position']) === 'left' ? 'selected' : '' }}>{{ __t('admin.customize.bottom_left') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.whatsapp_default_message') }}</label>
                        <input type="text" name="whatsapp_btn_text" value="{{ old('whatsapp_btn_text', $current['whatsapp_btn_text']) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="{{ __t('admin.customize.whatsapp_default_placeholder') }}">
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'footer')
            {{-- Footer --}}
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-teal-600">directions_walk</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.customize.footer') }}</h4>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.footer_about') }}</label>
                        <textarea name="footer_about" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('footer_about', $current['footer_about']) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __t('admin.customize.footer_copyright') }}</label>
                        <input type="text" name="footer_copyright" value="{{ old('footer_copyright', $current['footer_copyright']) }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-600 shrink-0">info</span>
                    <p class="text-sm text-blue-800">{{ __t('admin.customize.footer_contact_hint') }}</p>
                    <a href="{{ route('admin.settings.index', ['tab' => 'contact']) }}" class="shrink-0 inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs transition">
                        <span class="material-symbols-outlined text-sm">settings</span>
                        {{ __t('admin.customize.go_to_settings') }}
                    </a>
                </div>
            </section>
            @endif

        </div>

        <div class="lg:col-span-4 space-y-8">
            <section class="settings-card rounded-xl p-5">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary text-lg">lightbulb</span>
                    <h4 class="font-semibold">{{ __t('admin.settings.quick_tips') }}</h4>
                </div>
                <div class="space-y-3 text-sm text-on-surface-variant">
                    @if($activeTab === 'theme')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.customize.tip_theme_1') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.customize.tip_theme_2') }}</p>
                    @elseif($activeTab === 'banners')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.customize.tip_banners_1') }}</p>
                    @elseif($activeTab === 'sections')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.customize.tip_sections_1') }}</p>
                    @elseif($activeTab === 'header')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.customize.tip_header_1') }}</p>
                    @elseif($activeTab === 'announcement')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.customize.tip_announcement_1') }}</p>
                    @elseif($activeTab === 'whatsapp')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.customize.tip_whatsapp_1') }}</p>
                    @elseif($activeTab === 'footer')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.customize.tip_footer_1') }}</p>
                    @endif
                </div>
            </section>

            <section class="settings-card rounded-xl p-5 bg-gradient-to-br from-primary-fixed/20 to-transparent">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">help</span>
                    <h4 class="font-semibold">{{ __t('admin.settings.need_help') }}</h4>
                </div>
                <p class="text-sm text-on-surface-variant">{{ __t('admin.settings.need_help_desc') }}</p>
            </section>
        </div>
    </div>
</form>

@for($i = 1; $i <= 2; $i++)
    <form id="remove-banner-{{ $i }}-form" method="POST" action="{{ route('admin.customize.removeImage') }}" style="display:none">
        @csrf
        <input type="hidden" name="key" value="banner_{{ $i }}_image">
    </form>
@endfor

@if(session('success'))
<div id="success-toast" class="fixed bottom-6 right-6 z-50 bg-emerald-600 text-white px-5 py-3.5 rounded-xl shadow-lg flex items-center gap-3 animate-slide-up">
    <span class="material-symbols-outlined">check_circle</span>
    <span class="text-sm font-medium">{{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" class="ml-4 opacity-70 hover:opacity-100">
        <span class="material-symbols-outlined text-sm">close</span>
    </button>
</div>
<script>
    setTimeout(() => { const el = document.getElementById('success-toast'); if(el) el.remove(); }, 5000);
</script>
@endif

@if($errors->any())
<div id="error-toast" class="fixed bottom-6 right-6 z-50 bg-error text-white px-5 py-3.5 rounded-xl shadow-lg flex items-center gap-3 animate-slide-up">
    <span class="material-symbols-outlined">error</span>
    <span class="text-sm font-medium">{{ __t('admin.settings.validation_error') }}</span>
    <button onclick="this.parentElement.remove()" class="ml-4 opacity-70 hover:opacity-100">
        <span class="material-symbols-outlined text-sm">close</span>
    </button>
</div>
<script>
    setTimeout(() => { const el = document.getElementById('error-toast'); if(el) el.remove(); }, 5000);
</script>
@endif

<style>
@keyframes slide-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>
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
