@extends('frontend.layout')

@section('title', __t('auth.login.title') . ' - ' . site('store_name'))
@section('description', __t('auth.login.description') . ' ' . site('store_name'))

@section('content')
<section class="min-h-[85vh] flex items-center justify-center py-12 md:py-16 bg-surface-container-low/20">
    <div class="w-full max-w-md mx-auto px-4">
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
                                <span class="material-symbols-outlined text-3xl">storefront</span>
                            </div>
                        @endif
                    </a>

                    <h1 class="text-2xl font-black text-white tracking-tight mb-1">{{ __t('auth.login.welcome_back') ?? 'مرحباً بعودتك' }}</h1>
                    <p class="text-white/85 text-xs sm:text-sm font-medium">{{ __t('auth.login.subtitle') ?? 'سجل دخولك لمتابعة التسوق وإدارة طلباتك' }}</p>
                </div>
            </div>

            {{-- Form Body --}}
            <div class="p-6 sm:p-8" x-data="{ showPassword: false }">

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

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    {{-- Email Input --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            {{ __t('auth.login.email') ?? 'البريد الإلكتروني' }}
                        </label>
                        <div class="relative">
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                   placeholder="name@example.com"
                                   autocomplete="email"
                                   class="w-full ps-11 pe-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-medium text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('email') border-red-400 bg-red-50/30 @enderror" dir="ltr">
                            <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">mail</span>
                        </div>
                        @error('email')
                            <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Input with Show/Hide Toggle --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ __t('auth.login.password') ?? 'كلمة المرور' }}
                            </label>
                        </div>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required
                                   placeholder="••••••••"
                                   autocomplete="current-password"
                                   class="w-full ps-11 pe-11 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-low/30 text-xs sm:text-sm font-medium text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('password') border-red-400 bg-red-50/30 @enderror" dir="ltr">
                            <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg pointer-events-none">lock</span>
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute end-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors p-1"
                                    aria-label="إظهار / إخفاء كلمة المرور">
                                <span class="material-symbols-outlined text-lg" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-error font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me & Forgot Password --}}
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none text-on-surface-variant font-semibold">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-primary border-outline-variant focus:ring-primary">
                            <span>{{ __t('auth.login.remember_me') ?? 'تذكرني' }}</span>
                        </label>
                        <a href="{{ route('page.show', 'contact') }}" class="text-primary font-bold hover:underline">
                            {{ __t('auth.login.forgot_password') ?? 'نسيت كلمة المرور؟' }}
                        </a>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-primary text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg hover:brightness-105 active:scale-[0.98] transition-all duration-200 mt-2">
                        <span class="material-symbols-outlined text-lg">login</span>
                        <span>{{ __t('auth.login.submit') ?? 'تسجيل الدخول' }}</span>
                    </button>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-outline-variant/60"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-surface-container-lowest px-3 text-on-surface-variant font-semibold">{{ __t('auth.login.or') ?? 'أو' }}</span>
                    </div>
                </div>

                {{-- Create Account Link --}}
                <div class="text-center">
                    <p class="text-xs sm:text-sm text-on-surface-variant">
                        {{ __t('auth.login.no_account') ?? 'ليس لديك حساب بعد؟' }}
                        <a href="{{ route('register') }}" class="text-primary font-black hover:underline ms-1">
                            {{ __t('auth.login.register_now') ?? 'إنشاء حساب جديد' }}
                        </a>
                    </p>
                </div>
            </div>
        </div>

        {{-- Terms & Privacy Notice --}}
        <p class="text-center text-[11px] text-on-surface-variant/70 mt-6 leading-relaxed">
            {{ __t('auth.login.terms_prefix') ?? 'بتسجيل الدخول، أنت توافق على' }}
            <a href="{{ route('page.show', ['slug' => 'terms']) }}" class="text-primary hover:underline font-semibold">{{ __t('auth.login.terms') ?? 'الشروط والأحكام' }}</a>
            {{ __t('auth.login.and') ?? 'و' }}
            <a href="{{ route('page.show', ['slug' => 'privacy']) }}" class="text-primary hover:underline font-semibold">{{ __t('auth.login.privacy') ?? 'سياسة الخصوصية' }}</a>
        </p>
    </div>
</section>
@endsection
