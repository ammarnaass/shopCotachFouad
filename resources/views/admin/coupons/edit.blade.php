@extends('admin.layout')

@section('title', __t('admin.coupons.edit_title') . ' ' . $coupon->code)

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-2.5">
        <a href="{{ route('admin.coupons.index') }}" class="w-10 h-10 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-primary hover:bg-primary/10 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_forward</span>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ __t('admin.coupons.edit_coupon') }}</h1>
                <span class="bg-pink-50 text-pink-700 border border-pink-200/60 font-mono font-black text-sm px-2.5 py-0.5 rounded-lg">{{ $coupon->code }}</span>
            </div>
            <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ __t('admin.coupons.manage') }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="max-w-4xl">
    @csrf
    @method('PUT')

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden p-6 sm:p-8">
        {{-- Usage alert info --}}
        <div class="bg-blue-50/80 border border-blue-200/60 p-4 rounded-xl mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2.5 text-blue-900 text-sm font-semibold">
                <span class="material-symbols-outlined text-blue-600">info</span>
                <span>{{ __t('admin.coupons.used_count', ['count' => $coupon->used_count]) }}</span>
            </div>
            <span class="text-xs font-bold text-blue-700 bg-blue-100/80 px-2.5 py-1 rounded-full">
                {{ $coupon->usage_limit ? 'من أصل ' . $coupon->usage_limit : 'استخدام غير محدود' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Code --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                    {{ __t('admin.coupons.code') }} <span class="text-error">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required style="text-transform: uppercase" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-mono font-bold tracking-wider text-base focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('code') border-error @enderror">
                @error('code')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Type --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                    {{ __t('admin.coupons.type') }} <span class="text-error">*</span>
                </label>
                <select name="type" required class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('type') border-error @enderror">
                    <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>{{ __t('admin.coupons.fixed_amount') }} (د.ج)</option>
                    <option value="percent" {{ old('type', $coupon->type) === 'percent' ? 'selected' : '' }}>{{ __t('admin.coupons.percent') }} (%)</option>
                </select>
                @error('type')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Value --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                    {{ __t('admin.coupons.discount_value') }} <span class="text-error">*</span>
                </label>
                <input type="number" name="value" value="{{ old('value', $coupon->value) }}" required min="0" step="0.01" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-bold text-base focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('value') border-error @enderror">
                @error('value')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Min Order --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                    {{ __t('admin.coupons.min_order') }}
                </label>
                <input type="number" name="min_order" value="{{ old('min_order', $coupon->min_order) }}" min="0" step="0.01" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-medium text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('min_order') border-error @enderror">
                @error('min_order')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Max Discount --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                    {{ __t('admin.coupons.max_discount') }}
                </label>
                <input type="number" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}" min="0" step="0.01" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-medium text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('max_discount') border-error @enderror">
                @error('max_discount')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Usage Limit --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                    {{ __t('admin.coupons.usage_limit') }}
                </label>
                <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-medium text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('usage_limit') border-error @enderror">
                <p class="text-xs text-on-surface-variant mt-1.5">{{ __t('admin.coupons.unlimited_hint') }}</p>
                @error('usage_limit')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Expiry Date --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                    {{ __t('admin.coupons.expiry_date') }}
                </label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date', $coupon->expiry_date ? \Carbon\Carbon::parse($coupon->expiry_date)->format('Y-m-d') : '') }}" class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-medium text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('expiry_date') border-error @enderror">
                @error('expiry_date')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">
                    {{ __t('admin.coupons.status') }} <span class="text-error">*</span>
                </label>
                <select name="status" required class="w-full px-4 py-3 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface font-semibold text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('status') border-error @enderror">
                    <option value="active" {{ old('status', $coupon->status) === 'active' ? 'selected' : '' }}>{{ __t('admin.common.active') }}</option>
                    <option value="inactive" {{ old('status', $coupon->status) === 'inactive' ? 'selected' : '' }}>{{ __t('admin.common.inactive') }}</option>
                </select>
                @error('status')<p class="text-error text-xs mt-1.5 font-medium">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-outline-variant/30 flex items-center gap-3">
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
                <span class="material-symbols-outlined text-lg">save</span>
                <span>{{ __t('admin.coupons.update') }}</span>
            </button>
            <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-on-surface font-semibold text-sm hover:bg-surface-container transition-colors">
                {{ __t('admin.common.cancel') }}
            </a>
        </div>
    </div>
</form>
@endsection

