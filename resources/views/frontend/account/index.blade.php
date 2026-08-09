@extends('frontend.layout')

@section('title', __t('account.title') . ' - ' . site('store_name'))
@section('description', __t('account.manage_account') . ' ' . site('store_name'))

@section('content')
@php
    $countries = config('ecommerce.countries', []);
@endphp

<section class="bg-gradient-to-l from-brand-600 via-brand-500 to-accent-500 text-white py-10 md:py-14">
    <div class="container-app">
        <nav class="flex items-center gap-2 text-sm text-white/80 mb-4">
            <a href="{{ route('home') }}" class="hover:text-white transition flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">home</span>
                {{ __t('nav.home') }}
            </a>
            <span class="material-symbols-outlined text-[10px] text-white/50">chevron_right</span>
            <span class="text-white font-medium">{{ __t('account.title') }}</span>
        </nav>
        <h1 class="text-3xl md:text-4xl font-extrabold mb-2">{{ __t('account.title') }}</h1>
        <p class="text-white/90">{{ __t('account.manage_desc') }}</p>
    </div>
</section>

<div class="container-app py-8 md:py-10" x-data="{ tab: 'profile' }">
    @if(session('success'))
        <div class="alert alert-success mb-5 animate-slide-down">
            <span class="material-symbols-outlined text-lg">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-5">
            <span class="material-symbols-outlined text-lg">warning</span>
            <div class="flex-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid lg:grid-cols-4 gap-6">
        {{-- ============ SIDEBAR ============ --}}
        <aside class="lg:col-span-1">
            <div class="card sticky top-24 animate-fade-up">
                {{-- Profile --}}
                <div class="p-5 text-center border-b border-gray-100">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500 to-accent-500 mx-auto flex items-center justify-center text-white text-3xl font-extrabold shadow-lg">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <div class="font-bold mt-3 text-gray-800">{{ $user->name }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</div>
                    @if($user->roles->count() > 0)
                        <div class="mt-2 flex justify-center gap-1 flex-wrap">
                            @foreach($user->roles as $r)
                                <span class="badge badge-primary text-[10px]">{{ $r->display_name ?? $r->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                {{-- Nav --}}
                <nav class="p-3 space-y-1">
                    <button @click="tab='profile'" :class="tab==='profile' ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'" class="w-full text-end px-3 py-3 rounded-lg text-sm transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs w-4">person</span>
                        {{ __t('account.profile') }}
                    </button>
                    <button @click="tab='addresses'" :class="tab==='addresses' ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'" class="w-full text-end px-3 py-3 rounded-lg text-sm transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs w-4">location_on</span>
                        {{ __t('account.addresses') }}
                    </button>
                    <button @click="tab='password'" :class="tab==='password' ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'" class="w-full text-end px-3 py-3 rounded-lg text-sm transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs w-4">lock</span>
                        {{ __t('account.password_section') }}
                    </button>
                    <a href="{{ route('orders.index') }}" class="w-full text-end px-3 py-3 rounded-lg text-sm transition flex items-center gap-2 text-gray-700 hover:bg-gray-50">
                        <span class="material-symbols-outlined text-xs w-4">inventory_2</span>
                        {{ __t('nav.my_orders') }} ({{ $user->orders->count() }})
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="w-full text-end px-3 py-3 rounded-lg text-sm transition flex items-center gap-2 text-gray-700 hover:bg-gray-50">
                        <span class="material-symbols-outlined text-xs w-4">favorite</span>
                        {{ __t('nav.wishlist') }}
                    </a>
                </nav>
            </div>
        </aside>

        {{-- ============ CONTENT ============ --}}
        <div class="lg:col-span-3 space-y-5">

            {{-- Profile --}}
            <div x-show="tab==='profile'" x-cloak class="card animate-fade-up">
                <div class="card-header">
                    <h2 class="font-bold text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-600">person</span>
                        {{ __t('account.profile') }}
                    </h2>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('account.update') }}">
                        @csrf @method('PUT')
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">{{ __t('account.name') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                       class="form-input @error('name') form-input-error @enderror">
                                @error('name')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="form-label">{{ __t('account.email') }} <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                       class="form-input @error('email') form-input-error @enderror">
                                @error('email')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="form-label">{{ __t('common.country') }} <span class="text-rose-500">*</span></label>
                                <select name="country_code" class="form-input appearance-none">
                                    @foreach($countries as $code => $info)
                                        <option value="{{ $code }}" {{ $user->country_code == $code ? 'selected' : '' }}>
                                            {{ $info['name'] }} - {{ $info['name_en'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">{{ __t('common.state') }}</label>
                                <input type="text" name="state_code" value="{{ old('state_code', $user->state_code) }}"
                                       class="form-input">
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">{{ __t('account.phone') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                                       class="form-input @error('phone') form-input-error @enderror">
                                @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="mt-5 flex justify-end">
                            <button type="submit" class="btn-primary">
                                <span class="material-symbols-outlined">save</span>
                                {{ __t('account.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Addresses --}}
            <div x-show="tab==='addresses'" x-cloak class="card animate-fade-up">
                <div class="card-header flex items-center justify-between">
                    <h2 class="font-bold text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-600">location_on</span>
                        {{ __t('account.addresses') }}
                    </h2>
                    <span class="badge badge-gray">{{ $user->addresses->count() }}</span>
                </div>
                <div class="card-body p-5">
                    @if($user->addresses->isEmpty())
                        <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-8 text-center mb-5">
                            <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">map</span>
                            <p class="text-gray-500">{{ __t('account.no_addresses') }}</p>
                        </div>
                    @else
                        <div class="space-y-3 mb-5">
                            @foreach($user->addresses as $addr)
                                <div class="rounded-xl p-4 border-2 transition
                                            {{ $addr->is_default ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3 flex-1 min-w-0">
                                            <div class="w-10 h-10 rounded-lg {{ $addr->is_default ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-outlined">location_on</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-gray-800 flex items-center gap-2 flex-wrap">
                                                    {{ $addr->name }}
                                                    <span class="text-xs text-gray-500 font-normal">({{ $addr->phone }})</span>
                                                    @if($addr->is_default)
                                                        <span class="badge badge-primary text-[10px]"><span class="material-symbols-outlined text-[8px]">check</span>{{ __t('common.default') }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-600 mt-1">{{ $addr->full_address }}</div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            @if(!$addr->is_default)
                                                <form method="POST" action="{{ route('account.address.default', $addr) }}">
                                                    @csrf
                                                    <button class="text-xs text-brand-600 hover:underline font-semibold px-3 py-2 rounded-lg hover:bg-brand-50">{{ __t('account.set_default') }}</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('account.address.destroy', $addr) }}" onsubmit="return confirm('{{ __t('account.confirm_delete') }}')">
                                                @csrf @method('DELETE')
                                                <button class="text-xs text-rose-600 hover:underline font-semibold px-3 py-2 rounded-lg hover:bg-rose-50">{{ __t('common.delete') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <details class="group rounded-xl border-2 border-dashed border-gray-200 hover:border-brand-300 transition">
                        <summary class="cursor-pointer px-4 py-3 font-semibold text-brand-600 flex items-center gap-2 list-none">
                            <span class="material-symbols-outlined">add_circle</span>
                            {{ __t('account.add_address') }}
                        </summary>
                        <form method="POST" action="{{ route('account.address.store') }}" class="mt-3 p-4 border-t border-gray-100 grid md:grid-cols-2 gap-3">
                            @csrf
                            <input type="text" name="name" placeholder="{{ __t('account.name_placeholder') }}" required class="form-input text-sm">
                            <input type="text" name="phone" placeholder="{{ __t('account.phone_placeholder') }}" required class="form-input text-sm">
                            <select name="country_code" class="form-input text-sm appearance-none">
                                @foreach($countries as $code => $info)
                                    <option value="{{ $code }}" {{ ($user->country_code ?? 'DZ') == $code ? 'selected' : '' }}>{{ $info['name'] }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="state_code" placeholder="{{ __t('account.state_placeholder') }}" class="form-input text-sm">
                            <input type="text" name="city" placeholder="{{ __t('account.city_placeholder') }}" required class="form-input text-sm">
                            <input type="text" name="district" placeholder="{{ __t('account.district_placeholder') }}" class="form-input text-sm">
                            <input type="text" name="zip" placeholder="{{ __t('account.zip_placeholder') }}" class="form-input text-sm md:col-span-2">
                            <textarea name="address" placeholder="{{ __t('account.address_placeholder') }}" required class="form-input text-sm md:col-span-2" rows="2"></textarea>
                            <label class="md:col-span-2 flex items-center gap-2 text-sm cursor-pointer">
                                <input type="checkbox" name="is_default" value="1" class="form-checkbox">
                                <span>{{ __t('account.set_default') }}</span>
                            </label>
                            <div class="md:col-span-2">
                                <button type="submit" class="btn-primary btn-block">
                                <span class="material-symbols-outlined">save</span>
                                    {{ __t('common.save') }}
                                </button>
                            </div>
                        </form>
                    </details>
                </div>
            </div>

            {{-- Password --}}
            <div x-show="tab==='password'" x-cloak class="card animate-fade-up">
                <div class="card-header">
                    <h2 class="font-bold text-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-600">lock</span>
                        {{ __t('account.password_section') }}
                    </h2>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('account.password') }}" class="space-y-4 max-w-md">
                        @csrf @method('PUT')
                        <div>
                            <label class="form-label">{{ __t('account.current_password') }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="current_password" required
                                       class="form-input pl-11 @error('current_password') form-input-error @enderror">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">key</span>
                            </div>
                            @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __t('account.new_password') }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" required minlength="6"
                                       class="form-input pl-11 @error('password') form-input-error @enderror">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">lock</span>
                            </div>
                            @error('password')<p class="form-error">{{ $message }}</p>@enderror
                            <p class="form-help"><span class="material-symbols-outlined text-xs ms-1">info</span>{{ __t('account.password_hint') }}</p>
                        </div>
                        <div>
                            <label class="form-label">{{ __t('account.confirm_password') }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" required minlength="6"
                                       class="form-input pl-11">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">shield</span>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="btn-primary">
                                <span class="material-symbols-outlined">shield</span>
                                {{ __t('common.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
