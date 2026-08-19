@extends('admin.layout')

@section('title', __t('admin.settings.title'))

@php
$activeTab = request('tab', 'store');
$tabs = [
    'store' => ['icon' => 'store', 'title' => __t('admin.settings.store_tab')],
    'store_extended' => ['icon' => 'location_on', 'title' => __t('admin.settings.store_extended_tab')],
    'currency' => ['icon' => 'payments', 'title' => __t('admin.settings.currency_tab')],
    'social' => ['icon' => 'share', 'title' => __t('admin.settings.social_tab')],
    'contact' => ['icon' => 'headset_mic', 'title' => __t('admin.settings.contact_tab')],
    'seo' => ['icon' => 'search', 'title' => __t('admin.settings.seo_tab')],
    'invoice_info' => ['icon' => 'receipt_long', 'title' => __t('admin.settings.invoice_info_tab')],
];
@endphp

@push('styles')
<style>
    .settings-card {
        background: white;
        border: 1px solid #e1e2ed;
        box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    input:focus, select:focus, textarea:focus {
        outline: none !important;
        border-color: #004ac6 !important;
        box-shadow: 0 0 0 2px rgba(0, 74, 198, 0.2) !important;
    }
</style>
@endpush

@section('content')
<nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-3">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">{{ __t('admin.settings.dashboard') }}</a>
    <span class="material-symbols-outlined text-xs">chevron_left</span>
    <a href="{{ route('admin.settings.index') }}" class="hover:text-primary transition-colors">{{ __t('admin.settings.title') }}</a>
    <span class="material-symbols-outlined text-xs">chevron_left</span>
    <span class="text-primary font-semibold">{{ $tabs[$activeTab]['title'] ?? __t('admin.settings.general') }}</span>
</nav>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <h3 class="text-2xl font-bold text-on-surface">{{ __t('admin.settings.general_settings') }}</h3>
    <button type="submit" form="settings-form"
            class="px-6 py-2.5 rounded-xl bg-primary text-white font-medium hover:bg-primary-container shadow-sm transition-all active:scale-95 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">save</span>
        {{ __t('admin.settings.save') }}
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
    <div class="flex border-b border-outline-variant overflow-x-auto">
        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.settings.index', ['tab' => $key]) }}#{{ $key }}"
               class="flex items-center gap-2 px-5 py-3.5 font-medium text-sm whitespace-nowrap transition-all {{ $activeTab === $key ? 'border-b-2 border-primary text-primary bg-primary-fixed/30' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined text-lg">{{ $tab['icon'] }}</span>
                {{ $tab['title'] }}
            </a>
        @endforeach
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="settings-form">
    @csrf
    <input type="hidden" name="group" value="{{ $activeTab }}">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-8">

            @if($activeTab === 'store')
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">storefront</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.settings.store_info') }}</h4>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_name') }} <span class="text-error">*</span></label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">storefront</span>
                            <input type="text" name="store_name" value="{{ old('store_name', $settings['store']['store_name']) }}" required
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('store_name') border-error @enderror">
                        </div>
                        @error('store_name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_email') }} <span class="text-error">*</span></label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">mail</span>
                            <input type="email" name="store_email" value="{{ old('store_email', $settings['store']['store_email']) }}" required
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('store_email') border-error @enderror">
                        </div>
                        @error('store_email')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_phone') }} <span class="text-error">*</span></label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">call</span>
                            <input type="text" name="store_phone" value="{{ old('store_phone', $settings['store']['store_phone']) }}" required
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('store_phone') border-error @enderror">
                        </div>
                        @error('store_phone')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_address') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_on</span>
                            <input type="text" name="store_address" value="{{ old('store_address', $settings['store']['store_address']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('store_address') border-error @enderror">
                        </div>
                        @error('store_address')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_description') }}</label>
                        <textarea name="store_description" rows="3"
                                  class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md @error('store_description') border-error @enderror">{{ old('store_description', $settings['store']['store_description']) }}</textarea>
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.store_description_hint') }}</p>
                        @error('store_description')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">image</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.settings.logo_favicon') }}</h4>
                </div>

                <div class="space-y-3 mb-6">
                    <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.logo') }}</label>
                    @php
                        $logoVal = $settings['store']['store_logo'];
                        $logoUrl = $logoVal && !preg_match('#^https?://#i', $logoVal) ? asset('storage/' . $logoVal) : $logoVal;
                    @endphp
                    @if($logoVal)
                    <div class="bg-surface-container-low border-2 border-dashed border-outline-variant rounded-lg p-3 flex items-center gap-3">
                        <img src="{{ $logoUrl }}" alt="logo" class="h-16 w-16 object-contain bg-white rounded border p-1">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-on-surface-variant truncate" dir="ltr">{{ $logoVal }}</p>
                            <p class="text-xs text-emerald-600 mt-0.5 flex items-center gap-1"><span class="material-symbols-outlined text-sm">check_circle</span> {{ __t('admin.settings.current_logo') }}</p>
                        </div>
                        <button type="button" onclick="if(confirm('{{ __t('admin.settings.delete_logo_confirm') }}')) document.getElementById('remove-store-logo-form').submit()" class="bg-error-container/30 hover:bg-error-container text-error px-3 py-1.5 rounded text-xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">delete</span> {{ __t('common.delete') }}
                        </button>
                    </div>
                    @endif
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-on-surface-variant mb-1">{{ __t('common.upload') }}</label>
                            <input type="file" name="store_logo_file" accept="image/jpeg,image/jpg,image/png,image/webp,image/svg+xml" class="w-full text-sm file:rounded-lg file:border-0 file:bg-primary-fixed file:text-primary file:px-3 file:py-1.5 file:text-xs @error('store_logo_file') border-error @enderror">
                            <p class="text-xs text-on-surface-variant mt-1">
                                <span class="material-symbols-outlined text-xs align-text-bottom">info</span>
                                JPEG, PNG, WEBP, SVG &#8212; {{ __t('admin.settings.up_to_1mb') }}<br>
                                <span class="inline-block bg-primary-fixed/50 text-primary px-1.5 py-0.5 rounded mt-0.5">{{ __t('admin.settings.recommended_logo') }}</span>
                            </p>
                            @error('store_logo_file')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-on-surface-variant mb-1">{{ __t('admin.settings.or_external_url') }}</label>
                            <input type="url" name="store_logo" value="{{ old('store_logo', $logoVal && preg_match('#^https?://#i', $logoVal) ? $logoVal : '') }}" class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md font-mono text-sm" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <div class="space-y-3 pt-4 border-t border-outline-variant">
                    <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.favicon') }}</label>
                    @php
                        $favVal = $settings['store']['store_favicon'] ?? '';
                        $favUrl = $favVal && !preg_match('#^https?://#i', $favVal) ? asset('storage/' . $favVal) : $favVal;
                    @endphp
                    @if($favVal)
                    <div class="bg-surface-container-low border-2 border-dashed border-outline-variant rounded-lg p-3 flex items-center gap-3">
                        <img src="{{ $favUrl }}" alt="favicon" class="h-10 w-10 object-contain bg-white rounded border p-1">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-on-surface-variant truncate" dir="ltr">{{ $favVal }}</p>
                            <p class="text-xs text-emerald-600 mt-0.5 flex items-center gap-1"><span class="material-symbols-outlined text-sm">check_circle</span> {{ __t('admin.settings.current_favicon') }}</p>
                        </div>
                        <button type="button" onclick="if(confirm('{{ __t('admin.settings.delete_favicon_confirm') }}')) document.getElementById('remove-store-favicon-form').submit()" class="bg-error-container/30 hover:bg-error-container text-error px-3 py-1.5 rounded text-xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">delete</span> {{ __t('common.delete') }}
                        </button>
                    </div>
                    @endif
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-on-surface-variant mb-1">{{ __t('common.upload') }}</label>
                            <input type="file" name="store_favicon_file" accept="image/x-icon,image/png,image/svg+xml,.ico" class="w-full text-sm file:rounded-lg file:border-0 file:bg-primary-fixed file:text-primary file:px-3 file:py-1.5 file:text-xs @error('store_favicon_file') border-error @enderror">
                            <p class="text-xs text-on-surface-variant mt-1">
                                <span class="material-symbols-outlined text-xs align-text-bottom">info</span>
                                ICO, PNG, SVG &#8212; {{ __t('admin.settings.up_to_256kb') }}<br>
                                <span class="inline-block bg-primary-fixed/50 text-primary px-1.5 py-0.5 rounded mt-0.5">{{ __t('admin.settings.recommended_favicon') }}</span>
                            </p>
                            @error('store_favicon_file')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-on-surface-variant mb-1">{{ __t('admin.settings.or_external_url') }}</label>
                            <input type="url" name="store_favicon" value="{{ old('store_favicon', $favVal && preg_match('#^https?://#i', $favVal) ? $favVal : '') }}" class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md font-mono text-sm" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'store_extended')
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">location_on</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.settings.store_extended_title') }}</h4>
                </div>
                <p class="text-xs text-on-surface-variant mb-6">{{ __t('admin.settings.store_extended_hint') }}</p>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_wilaya') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_on</span>
                            <input type="text" name="store_wilaya" value="{{ old('store_wilaya', $settings['store_extended']['store_wilaya']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('store_wilaya') border-error @enderror">
                        </div>
                        @error('store_wilaya')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_commune') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_city</span>
                            <input type="text" name="store_commune" value="{{ old('store_commune', $settings['store_extended']['store_commune']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('store_commune') border-error @enderror">
                        </div>
                        @error('store_commune')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_postal_code') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">markunread_mailbox</span>
                            <input type="text" name="store_postal_code" value="{{ old('store_postal_code', $settings['store_extended']['store_postal_code']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('store_postal_code') border-error @enderror">
                        </div>
                        @error('store_postal_code')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_website') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">language</span>
                            <input type="text" name="store_website" value="{{ old('store_website', $settings['store_extended']['store_website']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md font-mono text-sm" dir="ltr" placeholder="https://...">
                        </div>
                        @error('store_website')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.store_phone_secondary') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">call</span>
                            <input type="text" name="store_phone_secondary" value="{{ old('store_phone_secondary', $settings['store_extended']['store_phone_secondary']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('store_phone_secondary') border-error @enderror">
                        </div>
                        @error('store_phone_secondary')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'currency')
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">payments</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.settings.currency_region') }}</h4>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6 flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-600 shrink-0">info</span>
                    <p class="text-sm text-amber-800">{{ __t('admin.settings.currency_region_hint') }}</p>
                </div>

                @php
                    $countries = config('ecommerce.countries', []);
                    $currentDefault = \App\Models\Settings\Setting::get('default_country', config('ecommerce.default_country', 'DZ'));
                @endphp

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.default_country') }} *</label>
                        <select name="default_country" required
                                class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md">
                            @foreach($countries as $code => $info)
                                <option value="{{ $code }}" {{ $currentDefault === $code ? 'selected' : '' }}>
                                    {{ $info['flag'] ?? '' }} {{ $info['name'] }} - {{ $info['name_en'] }} ({{ $info['currency_symbol'] ?? '' }} {{ $info['currency'] ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.default_country_hint') }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.fallback_currency') }}</label>
                        <select name="fallback_currency"
                                class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md">
                            @php $fallbackCurr = \App\Models\Settings\Setting::get('fallback_currency', 'SDG'); @endphp
                            @php
                                $currencies = ['SDG' => __t('admin.settings.currency_sdg'), 'EGP' => __t('admin.settings.currency_egp'), 'DZD' => __t('admin.settings.currency_dzd'), 'MAD' => __t('admin.settings.currency_mad'), 'TND' => __t('admin.settings.currency_tnd'), 'LYD' => __t('admin.settings.currency_lyd'), 'USD' => __t('admin.settings.currency_usd'), 'EUR' => __t('admin.settings.currency_eur')];
                            @endphp
                            @foreach($currencies as $code => $name)
                                <option value="{{ $code }}" {{ $fallbackCurr === $code ? 'selected' : '' }}>{{ $name }} ({{ $code }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.fallback_currency_hint') }}</p>
                    </div>
                </div>

                <div class="mt-6 p-5 bg-gradient-to-l from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-lg">
                    <h3 class="font-semibold text-sm mb-3 text-on-surface-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600">visibility</span>
                        {{ __t('admin.settings.selected_country_preview') }}
                    </h3>
                    @if(isset($countries[$currentDefault]))
                        @php $cur = $countries[$currentDefault]; @endphp
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                            <div class="bg-white p-3 rounded-lg">
                                <p class="text-xs text-on-surface-variant">{{ __t('admin.settings.currency') }}</p>
                                <p class="text-2xl font-bold text-on-surface mt-1">{{ $cur['currency_symbol'] ?? '&#8212;' }}</p>
                                <p class="text-xs text-on-surface-variant mt-1">{{ $cur['currency'] ?? '&#8212;' }}</p>
                            </div>
                            <div class="bg-white p-3 rounded-lg">
                                <p class="text-xs text-on-surface-variant">{{ __t('admin.settings.dial_code') }}</p>
                                <p class="text-2xl font-bold text-on-surface mt-1">{{ $cur['dial_code'] ?? '&#8212;' }}</p>
                            </div>
                            <div class="bg-white p-3 rounded-lg">
                                <p class="text-xs text-on-surface-variant">{{ __t('admin.settings.country_name') }}</p>
                                <p class="text-sm font-bold text-on-surface mt-1">{{ $cur['name'] ?? '&#8212;' }}</p>
                            </div>
                            <div class="bg-white p-3 rounded-lg">
                                <p class="text-xs text-on-surface-variant">{{ __t('admin.settings.country_name_en') }}</p>
                                <p class="text-sm font-bold text-on-surface mt-1">{{ $cur['name_en'] ?? '&#8212;' }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6">
                    <h3 class="font-semibold text-sm mb-3 text-on-surface-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600">language</span>
                        {{ __t('admin.settings.supported_countries') }}
                    </h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($countries as $code => $info)
                            <div class="p-3 bg-white border-2 {{ $currentDefault === $code ? 'border-primary bg-primary-fixed/30' : 'border-outline-variant' }} rounded-lg flex items-center gap-3">
                                <span class="text-3xl">{{ $info['flag'] ?? '' }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm truncate">{{ $info['name'] }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $info['name_en'] }} &#8226; {{ $info['currency_symbol'] ?? '' }} {{ $info['currency'] ?? '' }}</p>
                                </div>
                                @if($currentDefault === $code)
                                    <span class="bg-primary text-on-primary text-xs px-2 py-0.5 rounded-full">{{ __t('admin.settings.default') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'social')
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">share</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.settings.social_links') }}</h4>
                </div>

                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">
                            <span class="material-symbols-outlined text-sm align-text-bottom">call</span>
                            {{ __t('admin.settings.whatsapp_number') }}
                        </label>
                        <input type="text" name="social_whatsapp" value="{{ old('social_whatsapp', $settings['social']['social_whatsapp'] ?? '') }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md" placeholder="{{ __t('admin.settings.whatsapp_placeholder') }}">
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.whatsapp_hint') }}</p>
                    </div>

                    <div class="pt-4 border-t border-outline-variant">
                        <h5 class="font-semibold text-sm mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">public</span>
                            {{ __t('admin.settings.social_media') }}
                        </h5>
                        <div class="grid md:grid-cols-2 gap-4">
                            @php
                                $socialFields = [
                                    'social_facebook' => ['icon' => 'facebook', 'label' => 'Facebook', 'placeholder' => 'https://facebook.com/...'],
                                    'social_instagram' => ['icon' => 'instagram', 'label' => 'Instagram', 'placeholder' => 'https://instagram.com/...'],
                                    'social_tiktok' => ['icon' => 'music_note', 'label' => 'TikTok', 'placeholder' => 'https://tiktok.com/@...'],
                                    'social_youtube' => ['icon' => 'play_circle', 'label' => 'YouTube', 'placeholder' => 'https://youtube.com/@...'],
                                    'social_telegram' => ['icon' => 'send', 'label' => 'Telegram', 'placeholder' => 'https://t.me/...'],
                                    'social_snapchat' => ['icon' => 'camera', 'label' => 'Snapchat', 'placeholder' => 'https://snapchat.com/add/...'],
                                ];
                            @endphp
                            @foreach($socialFields as $key => $field)
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-medium text-on-surface-variant flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm">{{ $field['icon'] }}</span>
                                        {{ $field['label'] }}
                                    </label>
                                    <input type="url" name="{{ $key }}" value="{{ old($key, $settings['social'][$key] ?? '') }}"
                                           class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md font-mono text-sm" placeholder="{{ $field['placeholder'] }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'contact')
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">headset_mic</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.settings.contact_info') }}</h4>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.contact_email') }}</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact']['contact_email'] ?? '') }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md" placeholder="support@example.com">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.contact_phone') }}</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact']['contact_phone'] ?? '') }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.whatsapp_number') }}</label>
                        <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact']['contact_whatsapp'] ?? '') }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md" placeholder="966500000000">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.contact_address') }}</label>
                        <textarea name="contact_address" rows="2"
                                  class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md">{{ old('contact_address', $settings['contact']['contact_address'] ?? '') }}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.working_hours') }}</label>
                        <input type="text" name="contact_working_hours" value="{{ old('contact_working_hours', $settings['contact']['contact_working_hours'] ?? '') }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md" placeholder="{{ __t('admin.settings.working_hours_placeholder') }}">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.support_hours') }}</label>
                        <input type="text" name="contact_support_hours" value="{{ old('contact_support_hours', $settings['contact']['contact_support_hours'] ?? '') }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md" placeholder="{{ __t('admin.settings.support_hours_placeholder') }}">
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'seo')
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">search</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.settings.seo_meta') }}</h4>
                </div>

                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.meta_title') }}</label>
                        <input type="text" name="seo_meta_title" value="{{ old('seo_meta_title', $settings['seo']['seo_meta_title'] ?? '') }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md" placeholder="{{ __t('admin.settings.meta_title_placeholder') }}">
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.meta_title_hint') }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.meta_description') }}</label>
                        <textarea name="seo_meta_description" rows="3"
                                  class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md" placeholder="{{ __t('admin.settings.meta_description_placeholder') }}">{{ old('seo_meta_description', $settings['seo']['seo_meta_description'] ?? '') }}</textarea>
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.meta_description_hint') }}</p>
                    </div>

                    <div class="pt-4 border-t border-outline-variant space-y-4">
                        <h5 class="font-semibold text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">code</span>
                            {{ __t('admin.settings.tracking_codes') }}
                        </h5>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-on-surface-variant">Google Analytics ID</label>
                            <input type="text" name="seo_ga_id" value="{{ old('seo_ga_id', $settings['seo']['seo_ga_id'] ?? '') }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md font-mono text-sm" placeholder="G-XXXXXXXXXX">
                            <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.ga_hint') }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-on-surface-variant">Facebook Pixel ID</label>
                            <input type="text" name="seo_fb_pixel" value="{{ old('seo_fb_pixel', $settings['seo']['seo_fb_pixel'] ?? '') }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md font-mono text-sm" placeholder="1234567890">
                            <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.fb_pixel_hint') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            @elseif($activeTab === 'invoice_info')
            <section class="settings-card rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    <h4 class="font-semibold text-lg">{{ __t('admin.settings.invoice_info_title') }}</h4>
                </div>
                <p class="text-xs text-on-surface-variant mb-6">{{ __t('admin.settings.invoice_info_hint') }}</p>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_business_name') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">storefront</span>
                            <input type="text" name="invoice_business_name" value="{{ old('invoice_business_name', $settings['invoice_info']['invoice_business_name']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('invoice_business_name') border-error @enderror">
                        </div>
                        @error('invoice_business_name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_legal_name') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">gavel</span>
                            <input type="text" name="invoice_legal_name" value="{{ old('invoice_legal_name', $settings['invoice_info']['invoice_legal_name']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('invoice_legal_name') border-error @enderror">
                        </div>
                        @error('invoice_legal_name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_rc') }}</label>
                        <input type="text" name="invoice_rc" value="{{ old('invoice_rc', $settings['invoice_info']['invoice_rc']) }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md font-mono text-sm @error('invoice_rc') border-error @enderror" dir="ltr">
                        @error('invoice_rc')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_nif') }}</label>
                        <input type="text" name="invoice_nif" value="{{ old('invoice_nif', $settings['invoice_info']['invoice_nif']) }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md font-mono text-sm @error('invoice_nif') border-error @enderror" dir="ltr">
                        @error('invoice_nif')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_nis') }}</label>
                        <input type="text" name="invoice_nis" value="{{ old('invoice_nis', $settings['invoice_info']['invoice_nis']) }}"
                               class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md font-mono text-sm @error('invoice_nis') border-error @enderror" dir="ltr">
                        @error('invoice_nis')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_phone') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">call</span>
                            <input type="text" name="invoice_phone" value="{{ old('invoice_phone', $settings['invoice_info']['invoice_phone']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('invoice_phone') border-error @enderror" dir="ltr">
                        </div>
                        @error('invoice_phone')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_email') }}</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">mail</span>
                            <input type="email" name="invoice_email" value="{{ old('invoice_email', $settings['invoice_info']['invoice_email']) }}"
                                   class="w-full rounded-lg border-outline-variant bg-white p-2.5 pl-10 text-body-md @error('invoice_email') border-error @enderror" dir="ltr">
                        </div>
                        @error('invoice_email')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_address') }}</label>
                        <textarea name="invoice_address" rows="2"
                                  class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md @error('invoice_address') border-error @enderror">{{ old('invoice_address', $settings['invoice_info']['invoice_address']) }}</textarea>
                        @error('invoice_address')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-medium text-on-surface-variant">{{ __t('admin.settings.invoice_notes') }}</label>
                        <textarea name="invoice_notes" rows="3"
                                  class="w-full rounded-lg border-outline-variant bg-white p-2.5 text-body-md @error('invoice_notes') border-error @enderror">{{ old('invoice_notes', $settings['invoice_info']['invoice_notes']) }}</textarea>
                        <p class="text-xs text-on-surface-variant mt-1">{{ __t('admin.settings.invoice_notes_hint') }}</p>
                        @error('invoice_notes')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
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
                    @if($activeTab === 'store')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_store_1') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_store_2') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_store_3') }}</p>
                    @elseif($activeTab === 'store_extended')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_store_extended_1') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_store_extended_2') }}</p>
                    @elseif($activeTab === 'currency')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_currency_1') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_currency_2') }}</p>

                    @elseif($activeTab === 'social')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_social_1') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_social_2') }}</p>
                    @elseif($activeTab === 'contact')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_contact_1') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_contact_2') }}</p>
                    @elseif($activeTab === 'seo')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_seo_1') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_seo_2') }}</p>
                    @elseif($activeTab === 'invoice_info')
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_invoice_info_1') }}</p>
                        <p class="flex items-start gap-2"><span class="material-symbols-outlined text-xs mt-0.5 text-amber-600">check_circle</span> {{ __t('admin.settings.tip_invoice_info_2') }}</p>
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

<form method="POST" action="{{ route('admin.settings.removeImage') }}" id="remove-store-logo-form">@csrf<input type="hidden" name="key" value="store_logo"></form>
<form method="POST" action="{{ route('admin.settings.removeImage') }}" id="remove-store-favicon-form">@csrf<input type="hidden" name="key" value="store_favicon"></form>

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

