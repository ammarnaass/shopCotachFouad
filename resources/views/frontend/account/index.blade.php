@extends('frontend.layout')

@section('title', __t('account.title') . ' - ' . site('store_name'))
@section('description', __t('account.manage_account') . ' ' . site('store_name'))

@section('content')
@php
    $countries = config('ecommerce.countries', []);
    $dzStates = config('ecommerce.countries.DZ.states', []);
@endphp

{{-- ============ HERO / HEADER ============ --}}
<section class="relative overflow-hidden bg-gradient-to-l from-primary via-primary/90 to-primary-container text-white py-10 md:py-14">
    <div class="absolute -top-24 -end-24 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -start-24 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container-app relative z-10">
        <nav class="flex items-center gap-2 text-xs font-semibold text-white/80 mb-4">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">home</span>
                <span>{{ __t('nav.home') }}</span>
            </a>
            <span class="material-symbols-outlined text-xs text-white/40">chevron_left</span>
            <span class="text-white font-bold">{{ __t('account.title') }}</span>
        </nav>

        <div class="flex items-center gap-4">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-2xl sm:text-3xl border border-white/25 shadow-lg flex-shrink-0">
                <span class="material-symbols-outlined">manage_accounts</span>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white mb-1">{{ __t('account.title') }}</h1>
                <p class="text-white/90 text-xs sm:text-sm">{{ __t('account.manage_desc') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ============ MAIN CONTAINER ============ --}}
<div class="container-app py-8 md:py-12" x-data="{ tab: 'profile', country: '{{ old('country_code', $user->country_code ?? 'DZ') }}' }">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 flex items-center gap-3 shadow-xs animate-fade-in">
            <span class="material-symbols-outlined text-emerald-600 text-xl flex-shrink-0">check_circle</span>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200/80 text-red-800 flex items-start gap-3 shadow-xs animate-fade-in">
            <span class="material-symbols-outlined text-red-600 text-xl flex-shrink-0 mt-0.5">error</span>
            <div class="flex-1 text-sm font-medium space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- ============ SIDEBAR ============ --}}
        <aside class="lg:col-span-1">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden sticky top-24">
                {{-- Profile Info Summary --}}
                <div class="p-6 text-center border-b border-outline-variant/30 bg-surface-container-low/30">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-primary/10 shadow-sm mx-auto mb-3" alt="{{ $user->name }}">
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary to-primary-container text-white mx-auto flex items-center justify-center text-3xl font-black shadow-sm ring-4 ring-primary/10 mb-3">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ $user->name }}</h2>
                    <p class="text-xs text-on-surface-variant font-mono mt-0.5 truncate">{{ $user->email }}</p>

                    <div class="mt-2.5 flex justify-center gap-1.5 flex-wrap">
                        @if($user->role === 'admin' || $user->roles->contains('name', 'admin'))
                            <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 border border-red-200/60 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                <span class="material-symbols-outlined text-[13px]">shield_person</span>
                                {{ __t('admin.users.role_admin') ?? 'مدير' }}
                            </span>
                        @elseif($user->role === 'manager' || $user->roles->contains('name', 'manager'))
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200/60 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                <span class="material-symbols-outlined text-[13px]">badge</span>
                                {{ __t('admin.users.role_manager') ?? 'مشرف' }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 bg-surface-container-low text-on-surface-variant border border-outline-variant/60 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                <span class="material-symbols-outlined text-[13px]">person</span>
                                {{ __t('admin.users.role_customer') ?? 'عميل' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Navigation Links --}}
                <nav class="p-3 space-y-1.5">
                    <button type="button" @click="tab='profile'"
                            :class="tab==='profile' ? 'bg-primary/10 text-primary font-bold shadow-2xs' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'"
                            class="w-full text-start px-3.5 py-3 rounded-xl text-sm transition-all flex items-center justify-between group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-lg transition-transform group-hover:scale-110">person</span>
                            <span>{{ __t('account.profile') }}</span>
                        </div>
                        <span class="material-symbols-outlined text-sm opacity-60" x-show="tab==='profile'">chevron_left</span>
                    </button>

                    <button type="button" @click="tab='addresses'"
                            :class="tab==='addresses' ? 'bg-primary/10 text-primary font-bold shadow-2xs' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'"
                            class="w-full text-start px-3.5 py-3 rounded-xl text-sm transition-all flex items-center justify-between group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-lg transition-transform group-hover:scale-110">location_on</span>
                            <span>{{ __t('account.addresses') }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold font-mono" :class="tab==='addresses' ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant'">{{ $user->addresses->count() }}</span>
                    </button>

                    <button type="button" @click="tab='password'"
                            :class="tab==='password' ? 'bg-primary/10 text-primary font-bold shadow-2xs' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface'"
                            class="w-full text-start px-3.5 py-3 rounded-xl text-sm transition-all flex items-center justify-between group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-lg transition-transform group-hover:scale-110">lock</span>
                            <span>{{ __t('account.password_section') }}</span>
                        </div>
                        <span class="material-symbols-outlined text-sm opacity-60" x-show="tab==='password'">chevron_left</span>
                    </button>

                    <div class="pt-2 my-2 border-t border-outline-variant/30"></div>

                    <a href="{{ route('orders.index') }}" class="w-full text-start px-3.5 py-3 rounded-xl text-sm transition-all flex items-center justify-between text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface group">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-lg transition-transform group-hover:scale-110">inventory_2</span>
                            <span>{{ __t('nav.my_orders') }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold font-mono bg-blue-50 text-blue-700 border border-blue-200/60">{{ $user->orders->count() }}</span>
                    </a>

                    <a href="{{ route('wishlist.index') }}" class="w-full text-start px-3.5 py-3 rounded-xl text-sm transition-all flex items-center justify-between text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface group">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-lg transition-transform group-hover:scale-110">favorite</span>
                            <span>{{ __t('nav.wishlist') }}</span>
                        </div>
                        <span class="material-symbols-outlined text-sm opacity-40">chevron_left</span>
                    </a>
                </nav>
            </div>
        </aside>

        {{-- ============ CONTENT TABS ============ --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Tab 1: Profile --}}
            <div x-show="tab==='profile'" x-cloak class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
                <div class="px-6 py-5 border-b border-outline-variant/30 bg-surface-container-low/30 flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-2xl">person</span>
                    <div>
                        <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('account.profile') }}</h2>
                        <p class="text-xs text-on-surface-variant">تعديل بياناتك ومعلومات التواصل الأساسية</p>
                    </div>
                </div>

                <div class="p-6 sm:p-7">
                    <form method="POST" action="{{ route('account.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                    {{ __t('account.name') }} <span class="text-error">*</span>
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">person</span>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                           class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('name') border-error @enderror">
                                </div>
                                @error('name')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                    {{ __t('account.email') }} <span class="text-error">*</span>
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">mail</span>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                           class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('email') border-error @enderror">
                                </div>
                                @error('email')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                    {{ __t('common.country') }} <span class="text-error">*</span>
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">public</span>
                                    <select name="country_code" x-model="country" class="w-full ps-11 pe-8 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                                        @foreach($countries as $code => $info)
                                            <option value="{{ $code }}" {{ old('country_code', $user->country_code ?? 'DZ') == $code ? 'selected' : '' }}>
                                                {{ $info['name'] }} - {{ $info['name_en'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute end-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base pointer-events-none">expand_more</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                    {{ __t('common.state') }}
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">location_city</span>
                                    <template x-if="country === 'DZ'">
                                        <select name="state_code" class="w-full ps-11 pe-8 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                                            <option value="">اختر الولاية</option>
                                            @foreach($dzStates as $code => $name)
                                                <option value="{{ $code }}" {{ old('state_code', $user->state_code) == $code ? 'selected' : '' }}>
                                                    {{ $code }} - {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </template>
                                    <template x-if="country !== 'DZ'">
                                        <input type="text" name="state_code" value="{{ old('state_code', $user->state_code) }}" placeholder="الولاية أو المحافظة"
                                               class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                    </template>
                                    <span x-show="country === 'DZ'" class="material-symbols-outlined absolute end-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base pointer-events-none">expand_more</span>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                    {{ __t('account.phone') }} <span class="text-error">*</span>
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">call</span>
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required dir="ltr"
                                           class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('phone') border-error @enderror" placeholder="+213 550 00 00 00">
                                </div>
                                @error('phone')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-outline-variant/30 flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
                                <span class="material-symbols-outlined text-lg">save</span>
                                <span>{{ __t('account.save') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab 2: Addresses --}}
            <div x-show="tab==='addresses'" x-cloak class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
                <div class="px-6 py-5 border-b border-outline-variant/30 bg-surface-container-low/30 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-2xl">location_on</span>
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('account.addresses') }}</h2>
                            <p class="text-xs text-on-surface-variant">إدارة عناوين الشحن والتوصيل المسجلة</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold font-mono bg-surface-container-lowest px-2.5 py-1 rounded-full border border-outline-variant/40 text-on-surface-variant">
                        {{ $user->addresses->count() }} عنوان
                    </span>
                </div>

                <div class="p-6 sm:p-7">
                    @if($user->addresses->isEmpty())
                        <div class="bg-surface-container-low/40 border-2 border-dashed border-outline-variant/60 rounded-2xl p-8 sm:p-12 text-center mb-6">
                            <div class="w-14 h-14 rounded-2xl bg-surface-container-lowest text-primary/40 mx-auto flex items-center justify-center mb-3 shadow-2xs">
                                <span class="material-symbols-outlined text-3xl">map</span>
                            </div>
                            <h3 class="text-base font-bold text-on-surface mb-1">{{ __t('account.no_addresses') }}</h3>
                            <p class="text-xs text-on-surface-variant">أضف عنوانك لتسريع عملية الطلب والشحن</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            @foreach($user->addresses as $addr)
                                <div class="rounded-2xl p-5 border transition-all duration-200 flex flex-col justify-between
                                            {{ $addr->is_default ? 'border-primary/60 bg-primary/5 shadow-xs ring-1 ring-primary/20' : 'border-outline-variant/50 bg-surface-container-lowest hover:border-outline-variant' }}">
                                    <div>
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-9 h-9 rounded-xl {{ $addr->is_default ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant' }} flex items-center justify-center flex-shrink-0 shadow-2xs">
                                                    <span class="material-symbols-outlined text-lg">location_on</span>
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-sm text-on-surface">{{ $addr->name }}</h3>
                                                    <p class="text-xs text-on-surface-variant font-mono" dir="ltr">{{ $addr->phone }}</p>
                                                </div>
                                            </div>
                                            @if($addr->is_default)
                                                <span class="inline-flex items-center gap-1 bg-primary text-on-primary px-2.5 py-0.5 rounded-full text-[11px] font-bold shadow-2xs">
                                                    <span class="material-symbols-outlined text-[12px]">check</span>
                                                    {{ __t('common.default') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed mb-4">{{ $addr->full_address }}</p>
                                    </div>

                                    <div class="pt-3 border-t border-outline-variant/30 flex items-center justify-between gap-2">
                                        @if(!$addr->is_default)
                                            <form method="POST" action="{{ route('account.address.default', $addr) }}">
                                                @csrf
                                                <button type="submit" class="text-xs text-primary hover:underline font-bold px-2 py-1 rounded-lg hover:bg-primary/10 transition-colors">
                                                    {{ __t('account.set_default') }}
                                                </button>
                                            </form>
                                        @else
                                            <div></div>
                                        @endif
                                        <form method="POST" action="{{ route('account.address.destroy', $addr) }}" onsubmit="return confirm('{{ __t('account.confirm_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-error hover:underline font-semibold px-2 py-1 rounded-lg hover:bg-red-50 transition-colors">
                                                {{ __t('common.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Add Address Accordion --}}
                    <details class="group rounded-2xl border border-outline-variant/60 bg-surface-container-low/20 overflow-hidden transition-all duration-200">
                        <summary class="cursor-pointer px-5 py-4 font-bold text-sm text-primary flex items-center justify-between list-none hover:bg-surface-container-low/50 transition-colors">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-xl">add_circle</span>
                                <span>{{ __t('account.add_address') }}</span>
                            </span>
                            <span class="material-symbols-outlined text-base transition-transform group-open:rotate-180">expand_more</span>
                        </summary>
                        <form method="POST" action="{{ route('account.address.store') }}" class="p-5 sm:p-6 border-t border-outline-variant/30 bg-surface-container-lowest grid grid-cols-1 md:grid-cols-2 gap-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">{{ __t('account.name') }} <span class="text-error">*</span></label>
                                <input type="text" name="name" placeholder="{{ __t('account.name_placeholder') }}" required class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">{{ __t('account.phone') }} <span class="text-error">*</span></label>
                                <input type="text" name="phone" placeholder="{{ __t('account.phone_placeholder') }}" required dir="ltr" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">{{ __t('common.country') }} <span class="text-error">*</span></label>
                                <select name="country_code" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                                    @foreach($countries as $code => $info)
                                        <option value="{{ $code }}" {{ ($user->country_code ?? 'DZ') == $code ? 'selected' : '' }}>{{ $info['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">{{ __t('common.state') }}</label>
                                <input type="text" name="state_code" placeholder="{{ __t('account.state_placeholder') }}" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">{{ __t('account.city_placeholder') }} <span class="text-error">*</span></label>
                                <input type="text" name="city" placeholder="{{ __t('account.city_placeholder') }}" required class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">{{ __t('account.district_placeholder') }}</label>
                                <input type="text" name="district" placeholder="{{ __t('account.district_placeholder') }}" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">{{ __t('account.zip_placeholder') }}</label>
                                <input type="text" name="zip" placeholder="{{ __t('account.zip_placeholder') }}" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">{{ __t('account.address_placeholder') }} <span class="text-error">*</span></label>
                                <textarea name="address" placeholder="{{ __t('account.address_placeholder') }}" required class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" rows="2"></textarea>
                            </div>
                            <div class="md:col-span-2 pt-2">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                                    <input type="checkbox" name="is_default" value="1" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20">
                                    <span class="text-xs sm:text-sm font-semibold text-on-surface">{{ __t('account.set_default') }}</span>
                                </label>
                            </div>
                            <div class="md:col-span-2 pt-2">
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
                                    <span class="material-symbols-outlined text-lg">save</span>
                                    <span>{{ __t('common.save') }}</span>
                                </button>
                            </div>
                        </form>
                    </details>
                </div>
            </div>

            {{-- Tab 3: Password --}}
            <div x-show="tab==='password'" x-cloak class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
                <div class="px-6 py-5 border-b border-outline-variant/30 bg-surface-container-low/30 flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-2xl">lock</span>
                    <div>
                        <h2 class="font-extrabold text-base sm:text-lg text-on-surface">{{ __t('account.password_section') }}</h2>
                        <p class="text-xs text-on-surface-variant">تحديث وتأمين كلمة المرور الخاصة بحسابك</p>
                    </div>
                </div>

                <div class="p-6 sm:p-7">
                    <div class="bg-amber-50/80 border border-amber-200/60 p-4 rounded-xl mb-6 flex items-start gap-2.5 text-amber-800 text-xs font-medium">
                        <span class="material-symbols-outlined text-amber-600 text-base mt-0.5">info</span>
                        <span>{{ __t('account.password_hint') }}</span>
                    </div>

                    <form method="POST" action="{{ route('account.password') }}" class="space-y-5 max-w-lg">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                {{ __t('account.current_password') }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">key</span>
                                <input type="password" name="current_password" required
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('current_password') border-error @enderror">
                            </div>
                            @error('current_password')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                {{ __t('account.new_password') }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">lock</span>
                                <input type="password" name="password" required minlength="6"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('password') border-error @enderror">
                            </div>
                            @error('password')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                                {{ __t('account.confirm_password') }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">shield</span>
                                <input type="password" name="password_confirmation" required minlength="6"
                                       class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            </div>
                        </div>

                        <div class="pt-3">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
                                <span class="material-symbols-outlined text-lg">shield</span>
                                <span>{{ __t('common.update') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
