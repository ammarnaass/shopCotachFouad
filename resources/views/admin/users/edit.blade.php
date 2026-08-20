@extends('admin.layout')

@section('title', __t('admin.users.edit_title') . ' ' . $user->name)

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-2.5">
        <a href="{{ route('admin.users.show', $user) }}" class="w-10 h-10 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-primary hover:bg-primary/10 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_forward</span>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ __t('admin.users.edit_user') }}</h1>
                <span class="text-xs text-on-surface-variant font-mono bg-surface-container-low px-2 py-0.5 rounded-lg">#{{ $user->id }}</span>
            </div>
            <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ $user->name }} ({{ $user->email }})</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic Info Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-6 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">person</span>
                    <h2 class="font-bold text-lg text-on-surface">{{ __t('admin.users.basic_info') }}</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.users.name') }} <span class="text-error">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('name') border-error @enderror">
                        @error('name')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.users.email') }} <span class="text-error">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('email') border-error @enderror">
                            @error('email')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.users.phone') }}</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" dir="ltr" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('phone') border-error @enderror">
                            @error('phone')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Change Password Card --}}
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-5 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">lock</span>
                    <h2 class="font-bold text-lg text-on-surface">{{ __t('admin.users.change_password') }}</h2>
                </div>

                <div class="bg-amber-50/80 border border-amber-200/60 p-4 rounded-xl mb-5 flex items-center gap-2 text-amber-800 text-xs font-medium">
                    <span class="material-symbols-outlined text-amber-600 text-base">info</span>
                    <span>{{ __t('admin.users.keep_password_hint') }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.users.new_password') }}</label>
                        <input type="password" name="password" minlength="6" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('password') border-error @enderror">
                        @error('password')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.users.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" minlength="6" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Settings --}}
        <div class="space-y-6">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-2 pb-4 mb-5 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary text-2xl">admin_panel_settings</span>
                    <h2 class="font-bold text-lg text-on-surface">{{ __t('admin.users.permissions') }}</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.users.role') }} <span class="text-error">*</span></label>
                        <select name="role" required class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('role') border-error @enderror">
                            <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>{{ __t('admin.users.customer') }}</option>
                            <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>{{ __t('admin.users.manager') }}</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>{{ __t('admin.users.admin') }}</option>
                        </select>
                        @error('role')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">{{ __t('admin.users.status') }} <span class="text-error">*</span></label>
                        <select name="status" required class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('status') border-error @enderror">
                            <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>{{ __t('admin.common.active') }}</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>{{ __t('admin.common.inactive') }}</option>
                            <option value="banned" {{ old('status', $user->status) === 'banned' ? 'selected' : '' }}>{{ __t('admin.users.banned') }}</option>
                        </select>
                        @error('status')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-outline-variant/30 flex flex-col gap-2.5">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined text-lg">save</span>
                        <span>{{ __t('admin.users.update') }}</span>
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-on-surface font-semibold text-sm hover:bg-surface-container transition-colors text-center">
                        {{ __t('admin.common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

