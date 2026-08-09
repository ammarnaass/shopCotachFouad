@extends('frontend.layout')

@section('title', __t('auth.login.title') . ' - ' . site('store_name'))
@section('description', __t('auth.login.description') . ' ' . site('store_name'))

@push('styles')
<style>
    .auth-form .form-input {
        background-color: #fff;
        border-color: {{ $siteSettings['primary_color'] ?? '#004ac6' }} !important;
    }
</style>
@endpush

@section('content')

<section class="min-h-[85vh] flex items-center justify-center py-12 bg-white">
    <div class="w-full max-w-md mx-auto px-4">
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
                    <h1 class="text-2xl font-extrabold mb-1 text-white">{{ __t('auth.login.welcome_back') }}</h1>
                    <p class="text-white/80 text-sm">{{ __t('auth.login.subtitle') }}</p>
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

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label">{{ __t('auth.login.email') }}</label>
                        <div class="relative">
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="example@email.com"
                                   autocomplete="email"
                                   class="form-input pl-11 @error('email') form-input-error @enderror">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">mail</span>
                        </div>
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">{{ __t('auth.login.password') }}</label>
                        <div class="relative">
                            <input type="password" name="password" required
                                   placeholder="••••••••"
                                   autocomplete="current-password"
                                   class="form-input pl-11 @error('password') form-input-error @enderror">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">lock</span>
                        </div>
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 cursor-pointer text-on-surface-variant">
                            <input type="checkbox" name="remember" class="form-checkbox">
                            <span>{{ __t('auth.login.remember_me') }}</span>
                        </label>
                        <a href="#" class="text-brand-600 font-semibold hover:underline">
                            {{ __t('auth.login.forgot_password') }}
                        </a>
                    </div>

                    <button type="submit" class="btn-block btn-lg mt-2 text-white font-bold rounded-xl py-3.5 transition-all duration-200 hover:brightness-110 active:scale-[0.98] shadow-lg" style="background: {{ $siteSettings['primary_color'] ?? '#004ac6' }};">
                        <span class="material-symbols-outlined align-middle ms-1">login</span>
                        {{ __t('auth.login.submit') }}
                    </button>
                </form>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-outline-variant"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-surface-container-lowest px-3 text-on-surface-variant">{{ __t('auth.login.or') }}</span>
                    </div>
                </div>

                <p class="text-center text-sm text-on-surface-variant">
                    {{ __t('auth.login.no_account') }}
                    <a href="{{ route('register') }}" class="text-brand-600 font-bold hover:underline">
                        {{ __t('auth.login.register_now') }}
                    </a>
                </p>
            </div>
        </div>

        <p class="text-center text-xs text-on-surface-variant/60 mt-6">
            {{ __t('auth.login.terms_prefix') }}
            <a href="{{ route('page.show', ['slug' => 'terms']) }}" class="text-brand-600 hover:underline">{{ __t('auth.login.terms') }}</a>
            {{ __t('auth.login.and') }}
            <a href="{{ route('page.show', ['slug' => 'privacy']) }}" class="text-brand-600 hover:underline">{{ __t('auth.login.privacy') }}</a>
        </p>
    </div>
</section>
@endsection
