@extends('admin.layout')

@section('title', __t('admin.coupons.page_title'))

@section('content')
@php
    $totalCoupons = $coupons->total() ?? $coupons->count();
    $activeCoupons = \App\Models\Coupon::where('status', 'active')->count();
    $totalUsed = \App\Models\Coupon::sum('used_count');
@endphp

{{-- Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">confirmation_number</span>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ __t('admin.coupons.page_title') }}</h1>
                <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ __t('admin.coupons.manage') }}</p>
            </div>
        </div>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
        <span class="material-symbols-outlined text-lg">add_circle</span>
        <span>{{ __t('admin.coupons.add') }}</span>
    </a>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.coupons.total', [], 'إجمالي الكوبونات') }}</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">loyalty</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-on-surface mt-2">{{ number_format($totalCoupons) }}</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.common.active') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">check_circle</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-2">{{ number_format($activeCoupons) }}</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.coupons.usage_total', [], 'مرات الاستخدام') }}</span>
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">redeem</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-purple-600 mt-2">{{ number_format($totalUsed) }}</div>
    </div>
</div>

{{-- Coupons Table Card --}}
<div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-start">
            <thead>
                <tr class="bg-surface-container-low/70 border-b border-outline-variant/40 text-on-surface-variant text-xs uppercase tracking-wider font-bold">
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.coupons.code') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.coupons.type') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.coupons.value') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.coupons.min_order') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.coupons.usage') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.coupons.expiry_date') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.coupons.status') }}</th>
                    <th class="px-5 py-3.5 text-center">{{ __t('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30 text-sm">
                @forelse($coupons as $coupon)
                    <tr class="hover:bg-surface-container-low/40 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <div class="inline-flex items-center gap-2 bg-pink-50 text-pink-700 border border-pink-200/60 px-3 py-1 rounded-lg font-mono font-black text-sm tracking-wide">
                                <span>{{ $coupon->code }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if($coupon->type === 'percent')
                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                    <span class="material-symbols-outlined text-[13px]">percent</span>
                                    {{ __t('admin.coupons.percent') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                    <span class="material-symbols-outlined text-[13px]">attach_money</span>
                                    {{ __t('admin.coupons.fixed_amount') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-black text-on-surface text-base">
                            @if($coupon->type === 'percent')
                                {{ $coupon->value }}%
                            @else
                                {{ number_format($coupon->value, 0) }} <span class="text-xs text-on-surface-variant font-normal">د.ج</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-on-surface-variant font-medium">
                            {{ $coupon->min_order ? number_format($coupon->min_order, 0) . ' د.ج' : '—' }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-on-surface">{{ $coupon->used_count }}</span>
                                <span class="text-on-surface-variant text-xs">/ {{ $coupon->usage_limit ?? '∞' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-xs font-medium text-on-surface-variant">
                            @if($coupon->expiry_date)
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">event</span>
                                    {{ \Carbon\Carbon::parse($coupon->expiry_date)->format('Y-m-d') }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($coupon->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ __t('admin.common.active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    {{ __t('admin.common.inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="p-2 rounded-lg text-primary hover:bg-primary/10 transition-colors" title="{{ __t('admin.common.edit') }}">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.coupons.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="{{ __t('admin.common.delete') }}">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-on-surface-variant">
                            <div class="w-16 h-16 rounded-2xl bg-surface-container-low text-primary/40 mx-auto flex items-center justify-center mb-3">
                                <span class="material-symbols-outlined text-3xl">confirmation_number</span>
                            </div>
                            <h3 class="text-base font-bold text-on-surface mb-1">{{ __t('admin.coupons.no_coupons') }}</h3>
                            <p class="text-xs text-on-surface-variant mb-4">أنشئ كوبونات خصم لزيادة المبيعات وجذب العملاء</p>
                            <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-on-primary text-xs font-bold shadow-sm hover:brightness-105 transition-all">
                                <span class="material-symbols-outlined text-base">add</span>
                                {{ __t('admin.coupons.add_first') }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
        <div class="p-4 border-t border-outline-variant/40 bg-surface-container-low/30">{{ $coupons->links() }}</div>
    @endif
</div>
@endsection

