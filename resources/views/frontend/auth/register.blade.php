@extends('frontend.layout')

@section('title', __t('auth.register.title') . ' - ' . site('store_name'))
@section('description', __t('auth.register.description') . ' ' . site('store_name'))

@section('content')
@php
    $countries = config('ecommerce.countries', []);
    $defaultCountry = old('country_code', config('ecommerce.store.default_country', 'DZ'));
@endphp

<section class="min-h-[85vh] flex items-center justify-center py-12 md:py-16 bg-surface-container-low/20">
    <div class="w-full max-w-xl mx-auto px-4">
        <div class="bg-surface-container-lowest border border-outline-variant/60 rounded-3xl shadow-xl overflow-hidden animate-fade-up">

            {{-- Brand Top Banner --}}
            <div class="relative overflow-hidden p-8 text-center bg-gradient-to-br from-primary via-primary/95 to-primary-container text-white">
                <div class="absolute -top-12 -end-12 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-8 -start-8 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10">
                    <a href="{{ route('home') }}" class="inline-block group mb-3">
                        @if(site('store_logo'))
                            <div class="w-18 h-18 mx-auto rounded-2xl bg-white/15 backdrop-blur-md p-2 flex items-center justify-center shadow-lg border border-white/25 group-hover:scale-105 transition-transform">
                                <img src="{{ site('store_logo') }}" alt="{{ site('store_name') }}" class="w-full h-full object-contain">
                            </div>
                        @else
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-white shadow-lg group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-3xl">person_add</span>
                            </div>
                        @endif
                    </a>

                    <h1 class="text-2xl font-black text-white tracking-tight mb-1">{{ __t('auth.register.heading') ?? 'إنشاء حساب جديد' }}</h1>
                    <p class="text-white/85 text-xs sm:text-sm font-medium">{{ __t('auth.register.subtitle') ?? 'انضم إلينا واستمتع بتجربة تسوق فريدة ومميزة' }}</p>
                </div>
            </div>

            {{-- Form Body --}}
            <div class="p-6 sm:p-8" x-data="{ showPassword: false, showConfirm: false }">

                @if($errors->any())
                    <div class="mb-5 p-4 rounded-2xl bg-red-50/80 border border-red-200/80 text-error flex items-start gap-3">
                        <span class="material-symbols-outlined text-xl shrink-0 mt-0.5">error</span>
                        <div class="text-xs sm:text-sm font-semibold space-y-1">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    {{-- Full Name --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            {{ __t('auth.register.full_name') ?? 'الاسم الكامل' }} <span class="text-error">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                   placeholder="{{ __t('auth.register.full_name_placeholder') ?? 'مثال: محمد أحمد' }}"
                                   class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-medium text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('name') border-red-400 bg-red-50/30 @enderror">
                            <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">person</span>
                        </div>
                        @error('name')
                            <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email Address --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            {{ __t('auth.register.email') ?? 'البريد الإلكتروني' }} <span class="text-error">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="name@example.com"
                                   autocomplete="email"
                                   class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-medium text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('email') border-red-400 bg-red-50/30 @enderror" dir="ltr">
                            <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">mail</span>
                        </div>
                        @error('email')
                            <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Country & State Grid --}}
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('auth.register.country') ?? 'الدولة' }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <select name="country_code" id="country_code" required
                                        onchange="updateStates(this.value)"
                                        class="w-full ps-11 pe-8 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-semibold text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none cursor-pointer">
                                    @foreach($countries as $code => $info)
                                        <option value="{{ $code }}" {{ $defaultCountry == $code ? 'selected' : '' }}
                                                data-dial="{{ $info['dial_code'] }}">
                                            {{ $info['flag'] ?? '🌐' }} {{ $info['name'] }} ({{ $info['name_en'] }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">public</span>
                                <span class="material-symbols-outlined absolute end-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm pointer-events-none">expand_more</span>
                            </div>
                            @error('country_code')
                                <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('auth.register.state') ?? 'الولاية / المنطقة' }}
                            </label>
                            <div class="relative">
                                <select name="state_code" id="state_code"
                                        class="w-full ps-11 pe-8 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-semibold text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none cursor-pointer">
                                    <option value="">{{ __t('auth.register.state_placeholder') ?? '— اختر الولاية / المحافظة —' }}</option>
                                    @foreach($countries[$defaultCountry]['states'] ?? [] as $code => $name)
                                        <option value="{{ $code }}" {{ old('state_code') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">location_on</span>
                                <span class="material-symbols-outlined absolute end-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm pointer-events-none">expand_more</span>
                            </div>
                        </div>
                    </div>

                    {{-- Phone Number --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            {{ __t('auth.register.phone') ?? 'رقم الهاتف' }} <span class="text-error">*</span>
                        </label>
                        <div class="flex gap-2" dir="ltr">
                            <input type="text" id="dial_code" value="{{ $countries[$defaultCountry]['dial_code'] ?? '+213' }}"
                                   readonly
                                   class="w-20 px-3 py-3 border border-outline-variant/60 rounded-xl bg-surface-container-low text-center font-mono font-bold text-xs text-on-surface-variant">
                            <input type="tel" name="phone" value="{{ old('phone') }}" required
                                   placeholder="550000000"
                                   class="flex-1 ps-4 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-mono text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('phone') border-red-400 bg-red-50/30 @enderror">
                        </div>
                        <p class="text-[11px] text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">info</span>
                            <span>{{ __t('auth.register.phone_help') ?? 'أدخل الرقم بدون رمز الدولة' }}</span>
                        </p>
                        @error('phone')
                            <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password & Confirm Grid --}}
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('auth.register.password') ?? 'كلمة المرور' }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="password" required minlength="6"
                                       placeholder="••••••••"
                                       autocomplete="new-password"
                                       class="w-full ps-10 pe-10 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-medium text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('password') border-red-400 bg-red-50/30 @enderror" dir="ltr">
                                <span class="material-symbols-outlined absolute start-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base pointer-events-none">lock</span>
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute end-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1"
                                        aria-label="إظهار / إخفاء">
                                    <span class="material-symbols-outlined text-base" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('auth.register.password_confirmation') ?? 'تأكيد كلمة المرور' }} <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required minlength="6"
                                       placeholder="••••••••"
                                       autocomplete="new-password"
                                       class="w-full ps-10 pe-10 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-medium text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" dir="ltr">
                                <span class="material-symbols-outlined absolute start-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base pointer-events-none">lock_clock</span>
                                <button type="button" @click="showConfirm = !showConfirm"
                                        class="absolute end-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1"
                                        aria-label="إظهار / إخفاء">
                                    <span class="material-symbols-outlined text-base" x-text="showConfirm ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-primary text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg hover:brightness-105 active:scale-[0.98] transition-all duration-200 mt-3">
                        <span class="material-symbols-outlined text-lg">person_add</span>
                        <span>{{ __t('auth.register.submit') ?? 'إنشاء الحساب' }}</span>
                    </button>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-outline-variant/60"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-surface-container-lowest px-3 text-on-surface-variant font-semibold">{{ __t('auth.register.or') ?? 'أو' }}</span>
                    </div>
                </div>

                {{-- Has Account Link --}}
                <div class="text-center">
                    <p class="text-xs sm:text-sm text-on-surface-variant">
                        {{ __t('auth.register.has_account') ?? 'لديك حساب بالفعل؟' }}
                        <a href="{{ route('login') }}" class="text-primary font-black hover:underline ms-1">
                            {{ __t('auth.register.login') ?? 'تسجيل الدخول' }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const countriesData = @json($countries);

function updateStates(countryCode) {
    const dialEl = document.getElementById('dial_code');
    const stateEl = document.getElementById('state_code');
    const info = countriesData[countryCode];
    if (!info) return;
    if (dialEl) dialEl.value = info.dial_code || '';
    if (!stateEl) return;
    stateEl.innerHTML = '<option value="">' + @json(__t('auth.register.state_placeholder') ?? '— اختر الولاية / المحافظة —') + '</option>';
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
