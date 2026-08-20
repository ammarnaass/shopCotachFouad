@extends('admin.layout')

@section('title', __t('admin.settings.title') ?? 'الإعدادات العامة')

@php
$activeTab = request('tab', 'store');
$tabs = [
    'store' => ['icon' => 'storefront', 'title' => __t('admin.settings.store_tab') ?? 'معلومات المتجر والشعار', 'badge' => 'الأساسية'],
    'store_extended' => ['icon' => 'location_on', 'title' => __t('admin.settings.store_extended_tab') ?? 'العنوان والموقع', 'badge' => null],
    'currency' => ['icon' => 'payments', 'title' => __t('admin.settings.currency_tab') ?? 'العملة والدولة', 'badge' => null],
    'social' => ['icon' => 'share', 'title' => __t('admin.settings.social_tab') ?? 'التواصل الاجتماعي', 'badge' => null],
    'contact' => ['icon' => 'headset_mic', 'title' => __t('admin.settings.contact_tab') ?? 'خدمة العملاء والاتصال', 'badge' => null],
    'seo' => ['icon' => 'search', 'title' => __t('admin.settings.seo_tab') ?? 'السيو والتحليلات', 'badge' => null],
    'invoice_info' => ['icon' => 'receipt_long', 'title' => __t('admin.settings.invoice_info_tab') ?? 'بيانات الفاتورة', 'badge' => null],
];
@endphp

@section('content')

{{-- ============ TOP HEADER & BREADCRUMBS ============ --}}
<div class="mb-6 space-y-3">
    <nav class="flex items-center gap-2 text-on-surface-variant text-xs sm:text-sm">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">dashboard</span>
            <span>{{ __t('admin.settings.dashboard') ?? 'لوحة التحكم' }}</span>
        </a>
        <span class="material-symbols-outlined text-xs opacity-60">chevron_left</span>
        <a href="{{ route('admin.settings.index') }}" class="hover:text-primary transition-colors">{{ __t('admin.settings.title') ?? 'الإعدادات' }}</a>
        <span class="material-symbols-outlined text-xs opacity-60">chevron_left</span>
        <span class="text-primary font-bold">{{ $tabs[$activeTab]['title'] ?? 'الإعدادات العامة' }}</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary text-3xl">tune</span>
                <span>{{ __t('admin.settings.general_settings') ?? 'الإعدادات العامة' }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-on-surface-variant mt-1">تخصيص معلومات المتجر، الهوية والشعار، العملة، التواصل، والسيو</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" form="settings-form"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-white font-bold text-sm shadow-sm hover:brightness-105 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">save</span>
                <span>{{ __t('admin.settings.save') ?? 'حفظ التغييرات' }}</span>
            </button>
        </div>
    </div>
</div>

{{-- ============ TABS NAVIGATION BAR ============ --}}
<div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-1.5 shadow-xs mb-8 overflow-hidden">
    <div class="flex items-center gap-1 overflow-x-auto no-scrollbar scroll-smooth">
        @foreach($tabs as $key => $tab)
            @php $isActive = $activeTab === $key; @endphp
            <a href="{{ route('admin.settings.index', ['tab' => $key]) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm whitespace-nowrap transition-all duration-200 shrink-0
                      {{ $isActive
                          ? 'bg-primary text-white shadow-2xs'
                          : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined text-lg">{{ $tab['icon'] }}</span>
                <span>{{ $tab['title'] }}</span>
                @if(!empty($tab['badge']))
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $isActive ? 'bg-white/20 text-white' : 'bg-primary/10 text-primary' }}">
                        {{ $tab['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</div>

{{-- ============ MAIN SETTINGS FORM ============ --}}
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="settings-form">
    @csrf
    <input type="hidden" name="group" value="{{ $activeTab }}">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- LEFT MAIN COLUMN --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. TAB: STORE BASIC & BRANDING --}}
            @if($activeTab === 'store')
                {{-- Store Basic Information Card --}}
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">storefront</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.settings.store_info') ?? 'معلومات المتجر الأساسية' }}</h2>
                            <p class="text-xs text-on-surface-variant">البيانات التعريفية الرئيسية التي تظهر للزوار والعملاء</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('admin.settings.store_name') ?? 'اسم المتجر' }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">storefront</span>
                                <input type="text" name="store_name" value="{{ old('store_name', $settings['store']['store_name']) }}" required
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_name') border-error @enderror">
                            </div>
                            @error('store_name')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('admin.settings.store_email') ?? 'البريد الإلكتروني للمتجر' }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">mail</span>
                                <input type="email" name="store_email" value="{{ old('store_email', $settings['store']['store_email']) }}" required dir="ltr"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_email') border-error @enderror">
                            </div>
                            @error('store_email')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('admin.settings.store_phone') ?? 'رقم الهاتف الرئيسي' }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">call</span>
                                <input type="text" name="store_phone" value="{{ old('store_phone', $settings['store']['store_phone']) }}" required dir="ltr"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_phone') border-error @enderror">
                            </div>
                            @error('store_phone')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('admin.settings.store_address') ?? 'عنوان المقر / المحل' }}
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">location_on</span>
                                <input type="text" name="store_address" value="{{ old('store_address', $settings['store']['store_address']) }}"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_address') border-error @enderror">
                            </div>
                            @error('store_address')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('admin.settings.store_description') ?? 'الوصف التعريفي للمتجر' }}
                            </label>
                            <textarea name="store_description" rows="3"
                                      class="w-full p-4 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_description') border-error @enderror"
                                      placeholder="اكتب نبذة مختصرة عن متجرك ونوعية المنتجات التي يقدمها...">{{ old('store_description', $settings['store']['store_description']) }}</textarea>
                            <p class="text-xs text-on-surface-variant">{{ __t('admin.settings.store_description_hint') ?? 'يستخدم في تعريف المتجر وفي نتائج محركات البحث عند عدم توفر وصف مخصص.' }}</p>
                            @error('store_description')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Store Logo & Favicon Card --}}
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">image</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.settings.logo_favicon') ?? 'شعار المتجر والأيقونة (Favicon)' }}</h2>
                            <p class="text-xs text-on-surface-variant">إدارة الهوية البصرية لشريط الهيدر، الفوتر، وعلامة تبويب المتصفح</p>
                        </div>
                    </div>

                    {{-- 1. Logo Section --}}
                    <div class="space-y-4 mb-8">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.logo') ?? 'شعار المتجر (Logo)' }}</label>
                        @php
                            $logoVal = $settings['store']['store_logo'];
                            $logoUrl = $logoVal && !preg_match('#^https?://#i', $logoVal) ? asset('storage/' . $logoVal) : $logoVal;
                        @endphp

                        @if($logoVal)
                            <div class="bg-surface-container-low/40 border border-outline-variant/60 rounded-2xl p-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-16 h-16 rounded-xl bg-white border border-outline-variant/40 p-1.5 flex items-center justify-center shadow-2xs shrink-0">
                                        <img src="{{ $logoUrl }}" alt="Logo" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-mono font-bold text-on-surface truncate" dir="ltr">{{ $logoVal }}</p>
                                        <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-semibold mt-0.5">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            <span>{{ __t('admin.settings.current_logo') ?? 'الشعار الحالي نشط' }}</span>
                                        </span>
                                    </div>
                                </div>
                                <button type="button" onclick="if(confirm('{{ __t('admin.settings.delete_logo_confirm') ?? 'هل أنت متأكد من رغبتك في حذف الشعار؟' }}')) document.getElementById('remove-store-logo-form').submit()"
                                        class="px-3 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-error text-xs font-bold transition-colors flex items-center gap-1.5 shrink-0 border border-red-200/60">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                    <span>{{ __t('common.delete') ?? 'حذف الشعار' }}</span>
                                </button>
                            </div>
                        @endif

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/20 space-y-2">
                                <label class="block text-xs font-bold text-on-surface">{{ __t('common.upload') ?? 'رفع ملف صورة من الجهاز' }}</label>
                                <input type="file" name="store_logo_file" accept="image/jpeg,image/jpg,image/png,image/webp,image/svg+xml"
                                       class="w-full text-xs file:rounded-xl file:border-0 file:bg-primary file:text-white file:px-3 file:py-2 file:font-bold file:text-xs hover:file:brightness-105 transition-all @error('store_logo_file') border-error @enderror">
                                <p class="text-[11px] text-on-surface-variant flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs text-primary">info</span>
                                    <span>JPEG, PNG, WEBP, SVG — أقصى حجم 1MB</span>
                                </p>
                                @error('store_logo_file')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                            </div>

                            <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/20 space-y-2">
                                <label class="block text-xs font-bold text-on-surface">{{ __t('admin.settings.or_external_url') ?? 'أو رابط صورة خارجي (URL)' }}</label>
                                <input type="url" name="store_logo" value="{{ old('store_logo', $logoVal && preg_match('#^https?://#i', $logoVal) ? $logoVal : '') }}"
                                       class="w-full p-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="https://domain.com/logo.png" dir="ltr">
                                <p class="text-[11px] text-on-surface-variant">رابط مباشر لصورة الشعار المستضافة خارجياً</p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Favicon Section --}}
                    <div class="space-y-4 pt-6 border-t border-outline-variant/40">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.favicon') ?? 'أيقونة التبويب (Favicon)' }}</label>
                        @php
                            $favVal = $settings['store']['store_favicon'] ?? '';
                            $favUrl = $favVal && !preg_match('#^https?://#i', $favVal) ? asset('storage/' . $favVal) : $favVal;
                        @endphp

                        @if($favVal)
                            <div class="bg-surface-container-low/40 border border-outline-variant/60 rounded-2xl p-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-white border border-outline-variant/40 p-1.5 flex items-center justify-center shadow-2xs shrink-0">
                                        <img src="{{ $favUrl }}" alt="Favicon" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-mono font-bold text-on-surface truncate" dir="ltr">{{ $favVal }}</p>
                                        <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-semibold mt-0.5">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            <span>{{ __t('admin.settings.current_favicon') ?? 'الأيقونة الحالية نشطة' }}</span>
                                        </span>
                                    </div>
                                </div>
                                <button type="button" onclick="if(confirm('{{ __t('admin.settings.delete_favicon_confirm') ?? 'هل أنت متأكد من رغبتك في حذف الأيقونة؟' }}')) document.getElementById('remove-store-favicon-form').submit()"
                                        class="px-3 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-error text-xs font-bold transition-colors flex items-center gap-1.5 shrink-0 border border-red-200/60">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                    <span>{{ __t('common.delete') ?? 'حذف الأيقونة' }}</span>
                                </button>
                            </div>
                        @endif

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/20 space-y-2">
                                <label class="block text-xs font-bold text-on-surface">{{ __t('common.upload') ?? 'رفع ملف Favicon من الجهاز' }}</label>
                                <input type="file" name="store_favicon_file" accept="image/x-icon,image/png,image/svg+xml,.ico"
                                       class="w-full text-xs file:rounded-xl file:border-0 file:bg-primary file:text-white file:px-3 file:py-2 file:font-bold file:text-xs hover:file:brightness-105 transition-all @error('store_favicon_file') border-error @enderror">
                                <p class="text-[11px] text-on-surface-variant flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs text-primary">info</span>
                                    <span>ICO, PNG, SVG — أقصى حجم 256KB</span>
                                </p>
                                @error('store_favicon_file')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                            </div>

                            <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/20 space-y-2">
                                <label class="block text-xs font-bold text-on-surface">{{ __t('admin.settings.or_external_url') ?? 'أو رابط Favicon خارجي' }}</label>
                                <input type="url" name="store_favicon" value="{{ old('store_favicon', $favVal && preg_match('#^https?://#i', $favVal) ? $favVal : '') }}"
                                       class="w-full p-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="https://domain.com/favicon.ico" dir="ltr">
                                <p class="text-[11px] text-on-surface-variant">رابط مباشر لأيقونة الموقع</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2. TAB: STORE EXTENDED / LOCATION --}}
            @if($activeTab === 'store_extended')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">location_on</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.settings.store_extended_title') ?? 'العنوان والموقع الجغرافي' }}</h2>
                            <p class="text-xs text-on-surface-variant">{{ __t('admin.settings.store_extended_hint') ?? 'بيانات العنوان التفصيلية للمتجر والموقع الرسمي' }}</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.store_wilaya') ?? 'الولاية / المحافظة' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">location_on</span>
                                <input type="text" name="store_wilaya" value="{{ old('store_wilaya', $settings['store_extended']['store_wilaya']) }}"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_wilaya') border-error @enderror" placeholder="مثال: الجزائر العاصمة">
                            </div>
                            @error('store_wilaya')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.store_commune') ?? 'البلدية / المدينة' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">location_city</span>
                                <input type="text" name="store_commune" value="{{ old('store_commune', $settings['store_extended']['store_commune']) }}"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_commune') border-error @enderror" placeholder="مثال: باب الزوار">
                            </div>
                            @error('store_commune')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.store_postal_code') ?? 'الرمز البريدي' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">markunread_mailbox</span>
                                <input type="text" name="store_postal_code" value="{{ old('store_postal_code', $settings['store_extended']['store_postal_code']) }}" dir="ltr"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_postal_code') border-error @enderror" placeholder="16000">
                            </div>
                            @error('store_postal_code')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.store_website') ?? 'رابط الموقع الإلكتروني' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">language</span>
                                <input type="text" name="store_website" value="{{ old('store_website', $settings['store_extended']['store_website']) }}" dir="ltr"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_website') border-error @enderror" placeholder="https://amarstore.com">
                            </div>
                            @error('store_website')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.store_phone_secondary') ?? 'رقم هاتف إضافي / ثانوي' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">call</span>
                                <input type="text" name="store_phone_secondary" value="{{ old('store_phone_secondary', $settings['store_extended']['store_phone_secondary']) }}" dir="ltr"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('store_phone_secondary') border-error @enderror" placeholder="+213 550 00 00 00">
                            </div>
                            @error('store_phone_secondary')<p class="text-error text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            @endif

            {{-- 3. TAB: CURRENCY & REGION --}}
            @if($activeTab === 'currency')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">payments</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.settings.currency_region') ?? 'العملة والدولة الافتراضية' }}</h2>
                            <p class="text-xs text-on-surface-variant">{{ __t('admin.settings.currency_region_hint') ?? 'تحديد الدولة الافتراضية ونظام العملات المستخدم في المتجر' }}</p>
                        </div>
                    </div>

                    @php
                        $countries = config('ecommerce.countries', []);
                        $currentDefault = \App\Models\Settings\Setting::get('default_country', config('ecommerce.default_country', 'DZ'));
                    @endphp

                    <div class="grid md:grid-cols-2 gap-5 mb-8">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.default_country') ?? 'الدولة الافتراضية للمتجر' }} <span class="text-error">*</span></label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">public</span>
                                <select name="default_country" required
                                        class="w-full ps-11 pe-8 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                                    @foreach($countries as $code => $info)
                                        <option value="{{ $code }}" {{ $currentDefault === $code ? 'selected' : '' }}>
                                            {{ $info['flag'] ?? '' }} {{ $info['name'] }} - {{ $info['name_en'] }} ({{ $info['currency_symbol'] ?? '' }} {{ $info['currency'] ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute end-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base pointer-events-none">expand_more</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant">{{ __t('admin.settings.default_country_hint') ?? 'تحدد الدولة العملة الافتراضية ومفتاح الاتصال وقائمة الولايات.' }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.fallback_currency') ?? 'العملة الاحتياطية' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">attach_money</span>
                                <select name="fallback_currency"
                                        class="w-full ps-11 pe-8 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                                    @php $fallbackCurr = \App\Models\Settings\Setting::get('fallback_currency', 'DZD'); @endphp
                                    @php
                                        $currencies = [
                                            'DZD' => 'الدينار الجزائري (DZD)',
                                            'USD' => 'الدولار الأمريكي (USD)',
                                            'EUR' => 'اليورو الأوروبي (EUR)',
                                            'MAD' => 'الدرهم المغربي (MAD)',
                                            'TND' => 'الدينار التونسي (TND)',
                                            'EGP' => 'الجنيه المصري (EGP)',
                                            'SAR' => 'الريال السعودي (SAR)',
                                        ];
                                    @endphp
                                    @foreach($currencies as $code => $name)
                                        <option value="{{ $code }}" {{ $fallbackCurr === $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute end-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base pointer-events-none">expand_more</span>
                            </div>
                            <p class="text-[11px] text-on-surface-variant">{{ __t('admin.settings.fallback_currency_hint') ?? 'العملة المستخدمة كبديل في حال عدم تحديد عملة خاصة بالدولة.' }}</p>
                        </div>
                    </div>

                    {{-- Selected Country Preview Banner --}}
                    @if(isset($countries[$currentDefault]))
                        @php $cur = $countries[$currentDefault]; @endphp
                        <div class="p-6 rounded-2xl bg-gradient-to-l from-primary/10 via-primary/5 to-surface-container-low border border-primary/20">
                            <h3 class="font-bold text-sm text-on-surface mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-lg">preview</span>
                                <span>معاينة إعدادات الدولة النشطة: {{ $cur['flag'] ?? '' }} {{ $cur['name'] }}</span>
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/40 text-center">
                                    <span class="text-xs text-on-surface-variant">رمز العملة</span>
                                    <p class="text-xl font-bold font-mono text-primary mt-1">{{ $cur['currency_symbol'] ?? '—' }}</p>
                                    <span class="text-[11px] font-mono text-on-surface-variant">{{ $cur['currency'] ?? '—' }}</span>
                                </div>
                                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/40 text-center">
                                    <span class="text-xs text-on-surface-variant">مفتاح الاتصال</span>
                                    <p class="text-xl font-bold font-mono text-on-surface mt-1" dir="ltr">{{ $cur['dial_code'] ?? '—' }}</p>
                                    <span class="text-[11px] text-on-surface-variant">الهاتف الدولي</span>
                                </div>
                                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/40 text-center">
                                    <span class="text-xs text-on-surface-variant">الاسم بالعربية</span>
                                    <p class="text-sm font-bold text-on-surface mt-2">{{ $cur['name'] ?? '—' }}</p>
                                </div>
                                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/40 text-center">
                                    <span class="text-xs text-on-surface-variant">الاسم بالإنجليزية</span>
                                    <p class="text-sm font-bold text-on-surface mt-2 font-mono">{{ $cur['name_en'] ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 4. TAB: SOCIAL MEDIA --}}
            @if($activeTab === 'social')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">share</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.settings.social_links') ?? 'روابط وحسابات التواصل الاجتماعي' }}</h2>
                            <p class="text-xs text-on-surface-variant">تظهر هذه الروابط في فوتر المتجر وأزرار التواصل السريعة</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- WhatsApp Highlight --}}
                        <div class="p-5 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600 text-lg">chat</span>
                                <span>{{ __t('admin.settings.whatsapp_number') ?? 'رقم الواتساب المباشر للطلبات والاستفسارات' }}</span>
                            </label>
                            <input type="text" name="social_whatsapp" value="{{ old('social_whatsapp', $settings['social']['social_whatsapp'] ?? '') }}" dir="ltr"
                                   class="w-full p-3 rounded-xl border border-emerald-300 bg-white text-emerald-950 font-mono text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600"
                                   placeholder="213550000000 (بدون علامة + أو مسافات)">
                            <p class="text-[11px] text-emerald-700">يستخدم في زر المحادثة السريع وطلبات الشراء عبر الواتساب</p>
                        </div>

                        {{-- Social Platforms Grid --}}
                        @php
                            $socialFields = [
                                'social_facebook' => ['icon' => 'public', 'label' => 'Facebook', 'placeholder' => 'https://facebook.com/yourpage', 'color' => 'text-blue-600'],
                                'social_instagram' => ['icon' => 'photo_camera', 'label' => 'Instagram', 'placeholder' => 'https://instagram.com/yourprofile', 'color' => 'text-pink-600'],
                                'social_tiktok' => ['icon' => 'music_note', 'label' => 'TikTok', 'placeholder' => 'https://tiktok.com/@youraccount', 'color' => 'text-neutral-900'],
                                'social_youtube' => ['icon' => 'play_circle', 'label' => 'YouTube', 'placeholder' => 'https://youtube.com/@yourchannel', 'color' => 'text-red-600'],
                                'social_telegram' => ['icon' => 'send', 'label' => 'Telegram', 'placeholder' => 'https://t.me/yourchannel', 'color' => 'text-sky-500'],
                                'social_snapchat' => ['icon' => 'photo_library', 'label' => 'Snapchat', 'placeholder' => 'https://snapchat.com/add/yourname', 'color' => 'text-amber-500'],
                            ];
                        @endphp

                        <div class="grid md:grid-cols-2 gap-5">
                            @foreach($socialFields as $key => $field)
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-on-surface flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base {{ $field['color'] }}">{{ $field['icon'] }}</span>
                                        <span>{{ $field['label'] }}</span>
                                    </label>
                                    <input type="url" name="{{ $key }}" value="{{ old($key, $settings['social'][$key] ?? '') }}" dir="ltr"
                                           class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                                           placeholder="{{ $field['placeholder'] }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- 5. TAB: CONTACT & CUSTOMER SUPPORT --}}
            @if($activeTab === 'contact')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">headset_mic</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.settings.contact_info') ?? 'بيانات الاتصال وخدمة العملاء' }}</h2>
                            <p class="text-xs text-on-surface-variant">المعلومات التي تظهر في صفحة "اتصل بنا" ومواعيد العمل</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.contact_email') ?? 'بريد الدعم وخدمة العملاء' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">mail</span>
                                <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact']['contact_email'] ?? '') }}" dir="ltr"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="support@amarstore.com">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.contact_phone') ?? 'هاتف الدعم المباشر' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">call</span>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact']['contact_phone'] ?? '') }}" dir="ltr"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="+213 550 00 00 00">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.whatsapp_number') ?? 'واتساب خدمة العملاء' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">chat</span>
                                <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact']['contact_whatsapp'] ?? '') }}" dir="ltr"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="213550000000">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.working_hours') ?? 'أوقات وساعات العمل' }}</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">schedule</span>
                                <input type="text" name="contact_working_hours" value="{{ old('contact_working_hours', $settings['contact']['contact_working_hours'] ?? '') }}"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="السبت - الخميس: 9:00 ص - 8:00 م">
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.contact_address') ?? 'عنوان الاستقبال والتواصل' }}</label>
                            <textarea name="contact_address" rows="2"
                                      class="w-full p-4 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" placeholder="العنوان التفصيلي">{{ old('contact_address', $settings['contact']['contact_address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 6. TAB: SEO & ANALYTICS --}}
            @if($activeTab === 'seo')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs space-y-6"
                     x-data="{
                         metaTitle: '{{ old('seo_meta_title', $settings['seo']['seo_meta_title'] ?? '') }}',
                         metaDesc: '{{ old('seo_meta_description', $settings['seo']['seo_meta_description'] ?? '') }}'
                     }">
                    <div class="flex items-center gap-3 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">search</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.settings.seo_meta') ?? 'تهيئة محركات البحث (SEO) والتحليلات' }}</h2>
                            <p class="text-xs text-on-surface-variant">تحسين ظهور موقعك في نتائج Google وإضافة أكواد التتبع</p>
                        </div>
                    </div>

                    {{-- Google SERP Preview Card --}}
                    <div class="p-5 rounded-2xl bg-surface-container-low/40 border border-outline-variant/60 space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-primary">visibility</span>
                            <span>معاينة النتيجة في Google (Google SERP Snippet)</span>
                        </span>
                        <div class="p-4 bg-white rounded-xl border border-outline-variant/40 space-y-1 shadow-2xs">
                            <div class="flex items-center gap-2 text-xs text-neutral-600" dir="ltr">
                                <span class="w-4 h-4 rounded-full bg-neutral-200 flex items-center justify-center text-[10px] font-bold text-neutral-700">G</span>
                                <span class="truncate">{{ config('app.url') }}</span>
                            </div>
                            <h3 class="text-base font-semibold text-blue-800 hover:underline cursor-pointer truncate"
                                x-text="metaTitle || '{{ site('store_name', 'Amar Store') }} - المتجر الرياضي الأول في الجزائر'"></h3>
                            <p class="text-xs text-neutral-600 leading-relaxed line-clamp-2"
                                x-text="metaDesc || 'تسوق أفضل المنتجات الأصلية والمكملات الغذائية بأعلى جودة وأفضل الأسعار مع توصيل سريع لـ 58 ولاية والدفع عند الاستلام.'"></p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.meta_title') ?? 'عنوان الميتا (Meta Title)' }}</label>
                                <span class="text-[11px] font-mono text-on-surface-variant" x-text="metaTitle.length + ' / 60 حرف'"></span>
                            </div>
                            <input type="text" name="seo_meta_title" x-model="metaTitle"
                                   class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                                   placeholder="مثال: متجر عمار - أفضل المنتجات والمكملات الأصلية في الجزائر">
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.meta_description') ?? 'وصف الميتا (Meta Description)' }}</label>
                                <span class="text-[11px] font-mono text-on-surface-variant" x-text="metaDesc.length + ' / 160 حرف'"></span>
                            </div>
                            <textarea name="seo_meta_description" rows="3" x-model="metaDesc"
                                      class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                                      placeholder="اكتب وصفاً جذاباً وموجزاً يظهر في نتائج البحث..."></textarea>
                        </div>

                        {{-- Analytics Codes --}}
                        <div class="pt-4 border-t border-outline-variant/40 space-y-4">
                            <h3 class="font-bold text-sm text-on-surface flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-base">code</span>
                                <span>أكواد التتبع والبيكسل (Analytics & Pixels)</span>
                            </h3>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-on-surface">Google Analytics ID</label>
                                    <input type="text" name="seo_ga_id" value="{{ old('seo_ga_id', $settings['seo']['seo_ga_id'] ?? '') }}" dir="ltr"
                                           class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                           placeholder="G-XXXXXXXXXX">
                                    <p class="text-[11px] text-on-surface-variant">معرف القياس الخاص بـ Google Analytics 4</p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-on-surface">Facebook Pixel ID</label>
                                    <input type="text" name="seo_fb_pixel" value="{{ old('seo_fb_pixel', $settings['seo']['seo_fb_pixel'] ?? '') }}" dir="ltr"
                                           class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                           placeholder="123456789012345">
                                    <p class="text-[11px] text-on-surface-variant">معرف بيكسل Meta لتتبع الإعلانات والمبيعات</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 7. TAB: INVOICE & LEGAL INFO --}}
            @if($activeTab === 'invoice_info')
                <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-outline-variant/40">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">receipt_long</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('admin.settings.invoice_info_title') ?? 'بيانات الفواتير والمطبوعات الرسمية' }}</h2>
                            <p class="text-xs text-on-surface-variant">{{ __t('admin.settings.invoice_info_hint') ?? 'تظهر هذه البيانات القانونية في فواتير الطلبات وبوالص الشحن' }}</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.invoice_business_name') ?? 'الاسم التجاري في الفاتورة' }}</label>
                            <input type="text" name="invoice_business_name" value="{{ old('invoice_business_name', $settings['invoice_info']['invoice_business_name']) }}"
                                   class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.invoice_legal_name') ?? 'الاسم القانوني / الشركة' }}</label>
                            <input type="text" name="invoice_legal_name" value="{{ old('invoice_legal_name', $settings['invoice_info']['invoice_legal_name']) }}"
                                   class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.invoice_rc') ?? 'السجل التجاري (RC)' }}</label>
                            <input type="text" name="invoice_rc" value="{{ old('invoice_rc', $settings['invoice_info']['invoice_rc']) }}" dir="ltr"
                                   class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="00/00-0000000B00">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.invoice_nif') ?? 'الرقم الجبائي (NIF)' }}</label>
                            <input type="text" name="invoice_nif" value="{{ old('invoice_nif', $settings['invoice_info']['invoice_nif']) }}" dir="ltr"
                                   class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="000000000000000">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.invoice_nis') ?? 'رقم التعريف الإحصائي (NIS)' }}</label>
                            <input type="text" name="invoice_nis" value="{{ old('invoice_nis', $settings['invoice_info']['invoice_nis']) }}" dir="ltr"
                                   class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="00000000000">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.invoice_phone') ?? 'هاتف الفاتورة' }}</label>
                            <input type="text" name="invoice_phone" value="{{ old('invoice_phone', $settings['invoice_info']['invoice_phone']) }}" dir="ltr"
                                   class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.invoice_address') ?? 'العنوان المطبوع في الفاتورة' }}</label>
                            <textarea name="invoice_address" rows="2"
                                      class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">{{ old('invoice_address', $settings['invoice_info']['invoice_address']) }}</textarea>
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">{{ __t('admin.settings.invoice_notes') ?? 'ملاحظات وشروط أسفل الفاتورة' }}</label>
                            <textarea name="invoice_notes" rows="3"
                                      class="w-full p-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                      placeholder="مثال: البضاعة المباعة تستبدل أو تسترجع خلال 48 ساعة من تاريخ الاستلام في حال وجود عيب مصنعي...">{{ old('invoice_notes', $settings['invoice_info']['invoice_notes']) }}</textarea>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- RIGHT SIDEBAR (TIPS & QUICK ACTIONS) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Save Action Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-5 shadow-xs sticky top-24">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-outline-variant/40">
                    <span class="material-symbols-outlined text-primary text-xl">save</span>
                    <h3 class="font-bold text-sm text-on-surface">إجراءات الحفظ</h3>
                </div>

                <p class="text-xs text-on-surface-variant mb-4 leading-relaxed">
                    عند تعديل أي من الحقول، اضغط على زر الحفظ لتطبيق التغييرات وتحديث إعدادات الموقع فوراً.
                </p>

                <button type="submit" form="settings-form"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary text-white font-bold text-sm shadow-sm hover:brightness-105 active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-lg">save</span>
                    <span>{{ __t('admin.settings.save') ?? 'حفظ التغييرات' }}</span>
                </button>
            </div>

            {{-- Contextual Tips Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-outline-variant/40">
                    <span class="material-symbols-outlined text-primary text-xl">lightbulb</span>
                    <h3 class="font-bold text-sm text-on-surface">{{ __t('admin.settings.quick_tips') ?? 'نصائح وتوجيهات' }}</h3>
                </div>

                <div class="space-y-3 text-xs text-on-surface-variant leading-relaxed">
                    @if($activeTab === 'store')
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>اسم المتجر هو الهوية الرئيسية التي تظهر في عنوان الصفحات ورسائل البريد والفواتير.</span>
                        </p>
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>احرص على رفع شعار بخلفية شفافة (PNG أو SVG) لتحقيق أفضل مظهر في الهيدر.</span>
                        </p>
                    @elseif($activeTab === 'currency')
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>تغيير الدولة الافتراضية يحدث رمز العملة التلقائي لجميع المنتجات والطلبات.</span>
                        </p>
                    @elseif($activeTab === 'seo')
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>احرص على ألا يتجاوز عنوان الميتا 60 حرفاً حتى لا يتم قصه في نتائج محركات البحث.</span>
                        </p>
                    @elseif($activeTab === 'social')
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>رقم الواتساب يجب كتابته مع كود الدولة وبدون رمز + لضمان عمل الروابط المباشرة.</span>
                        </p>
                    @else
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 mt-0.5">check_circle</span>
                            <span>تأكد من مراجعة البيانات بعناية قبل اعتمادها.</span>
                        </p>
                    @endif
                </div>
            </div>

        </div>

    </div>
</form>

{{-- Hidden forms for logo and favicon deletion --}}
<form method="POST" action="{{ route('admin.settings.removeImage') }}" id="remove-store-logo-form">@csrf<input type="hidden" name="key" value="store_logo"></form>
<form method="POST" action="{{ route('admin.settings.removeImage') }}" id="remove-store-favicon-form">@csrf<input type="hidden" name="key" value="store_favicon"></form>

{{-- Floating Success / Error Toasts --}}
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
