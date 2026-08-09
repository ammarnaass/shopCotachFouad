@extends('frontend.layout')

@section('title', __t('auth.register.title') . ' - ' . site('store_name'))
@section('description', __t('auth.register.description') . ' ' . site('store_name'))

@push('styles')
<style>
    .auth-form .form-input {
        background-color: #fff;
        border-color: {{ $siteSettings['primary_color'] ?? '#004ac6' }} !important;
    }
</style>
@endpush

@section('content')
@php
    $countries = config('ecommerce.countries', []);
    $defaultCountry = old('country_code', config('ecommerce.store.default_country', 'DZ'));
@endphp

<section class="min-h-[85vh] flex items-center justify-center py-12 bg-white">
    <div class="w-full max-w-2xl mx-auto px-4">
        <div class="card animate-fade-up overflow-hidden border border-outline-variant/60 shadow-xl backdrop-blur-sm">
            {{-- Header with logo --}}
            <div class="relative overflow-hidden p-8 text-center" style="background: {{ $siteSettings['primary_color'] ?? '#004ac6' }};">
                <div class="absolute inset-0 bg-black/20"></div>
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
                <div class="relative z-10">
                    @if(site('store_logo'))
                        <div class="w-20 h-20 mx-auto mb-4 rounded-2xl backdrop-blur-lg p-2 flex items-center justify-center shadow-lg border border-white/30" style="background: rgba(255,255,255,0.15);">
                            <img src="{{ site('store_logo') }}" alt="{{ site('store_name') }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-20 h-20 mx-auto mb-4 rounded-2xl backdrop-blur-lg flex items-center justify-center border border-white/30" style="background: rgba(255,255,255,0.15);">
                            <span class="material-symbols-outlined text-3xl text-white">storefront</span>
                        </div>
                    @endif
                    <h1 class="text-2xl font-extrabold mb-1 text-white">{{ __t('auth.register.heading') }}</h1>
                    <p class="text-white/80 text-sm">{{ __t('auth.register.subtitle') }}</p>
                </div>
            </div>

            <div class="card-body p-6 md:p-8 auth-form">
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

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="form-label">{{ __t('auth.register.full_name') }} <span class="text-error">*</span></label>
                        <div class="relative">
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="{{ __t('auth.register.full_name_placeholder') }}"
                                   class="form-input pl-11 @error('name') form-input-error @enderror">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">person</span>
                        </div>
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">{{ __t('auth.register.email') }} <span class="text-error">*</span></label>
                        <div class="relative">
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="example@email.com"
                                   autocomplete="email"
                                   class="form-input pl-11 @error('email') form-input-error @enderror">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">mail</span>
                        </div>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">{{ __t('auth.register.country') }} <span class="text-error">*</span></label>
                            <div class="relative">
                                <select name="country_code" id="country_code" required
                                        onchange="updateStates(this.value)"
                                        class="form-input pl-11 appearance-none @error('country_code') form-input-error @enderror">
                                    @foreach($countries as $code => $info)
                                        <option value="{{ $code }}" {{ $defaultCountry == $code ? 'selected' : '' }}
                                                data-dial="{{ $info['dial_code'] }}">
                                            {{ $info['name'] }} ({{ $info['name_en'] }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">public</span>
                            </div>
                            @error('country_code')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="form-label">{{ __t('auth.register.state') }}</label>
                            <div class="relative">
                                <select name="state_code" id="state_code" class="form-input pl-11 appearance-none">
                                    <option value="">{{ __t('auth.register.state_placeholder') }}</option>
                                    @foreach($countries[$defaultCountry]['states'] ?? [] as $code => $name)
                                        <option value="{{ $code }}" {{ old('state_code') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">location_on</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">{{ __t('auth.register.phone') }} <span class="text-error">*</span></label>
                        <div class="flex gap-2" dir="ltr">
                            <input type="text" id="dial_code" value="{{ $countries[$defaultCountry]['dial_code'] ?? '' }}"
                                   readonly
                                   class="w-20 px-3 py-2.5 border border-outline-variant rounded-xl bg-surface-container-low text-center font-semibold text-on-surface-variant">
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                   placeholder="5XXXXXXXX"
                                   class="flex-1 form-input text-end @error('phone') form-input-error @enderror">
                        </div>
                        <p class="form-help"><span class="material-symbols-outlined text-xs ms-1">info</span>{{ __t('auth.register.phone_help') }}</p>
                        @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">{{ __t('auth.register.password') }} <span class="text-error">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" required minlength="6"
                                       placeholder="••••••••"
                                       autocomplete="new-password"
                                       class="form-input pl-11 @error('password') form-input-error @enderror">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">lock</span>
                            </div>
                            @error('password')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="form-label">{{ __t('auth.register.password_confirmation') }} <span class="text-error">*</span></label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" required
                                       placeholder="••••••••"
                                       autocomplete="new-password"
                                       class="form-input pl-11">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">lock</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-block btn-lg mt-2 text-white font-bold rounded-xl py-3.5 transition-all duration-200 hover:brightness-110 active:scale-[0.98] shadow-lg" style="background: {{ $siteSettings['primary_color'] ?? '#004ac6' }};">
                        <span class="material-symbols-outlined align-middle ms-1">person_add</span>
                        {{ __t('auth.register.submit') }}
                    </button>
                </form>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-outline-variant"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-surface-container-lowest px-3 text-on-surface-variant">{{ __t('auth.register.or') }}</span>
                    </div>
                </div>

                <p class="text-center text-sm text-on-surface-variant">
                    {{ __t('auth.register.has_account') }}
                    <a href="{{ route('login') }}" class="text-brand-600 font-bold hover:underline">
                        {{ __t('auth.register.login') }}
                    </a>
                </p>
            </div>
        </div>
    </div>
</section>

@php
    $countriesJson = json_encode($countries, JSON_UNESCAPED_UNICODE);
@endphp
<script>
const countriesData = @json($countriesJson);

function updateStates(countryCode) {
    const dialEl = document.getElementById('dial_code');
    const stateEl = document.getElementById('state_code');
    const info = countriesData[countryCode];
    if (!info) return;
    dialEl.value = info.dial_code;
    stateEl.innerHTML = '<option value="">' + __t('auth.register.state_placeholder') + '</option>';
    if (info.states) {
        for (const [code, name] of Object.entries(info.states)) {
            const opt = document.createElement('option');
            opt.value = code;
            opt.textContent = name;
            stateEl.appendChild(opt);
        }
    }
}
</script>
@endsection
