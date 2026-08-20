@extends('admin.layout')

@section('title', __t('admin.users.title'))

@section('content')
@php
    use App\Models\User\User;
    $totalUsers = User::count();
    $adminsCount = User::where('role', 'admin')->count();
    $managersCount = User::where('role', 'manager')->count();
    $customersCount = User::where('role', 'customer')->count();
    $bannedCount = User::where('status', 'banned')->count();
@endphp

{{-- Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">group</span>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ __t('admin.users.title') }}</h1>
                <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ __t('admin.users.subtitle') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.users.total') }}</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">people</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-on-surface mt-2">{{ number_format($totalUsers) }}</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.users.customers') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">shopping_bag</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-2">{{ number_format($customersCount) }}</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.users.admins') }} / {{ __t('admin.users.managers') }}</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">admin_panel_settings</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-indigo-600 mt-2">{{ number_format($adminsCount + $managersCount) }}</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.users.banned') }}</span>
            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">block</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-red-600 mt-2">{{ number_format($bannedCount) }}</div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-4 sm:p-5 mb-8">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[240px]">
            <div class="relative">
                <span class="material-symbols-outlined absolute start-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __t('admin.users.search_placeholder') }}" class="w-full ps-10 pe-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
            </div>
        </div>
        <div class="w-auto min-w-[160px]">
            <select name="role" class="w-full px-4 py-2.5 rounded-xl border border-outline-variant/60 bg-surface-container-lowest text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="">{{ __t('common.all') }} (الأدوار)</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __t('admin.users.role_admin') }}</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>{{ __t('admin.users.role_manager') }}</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>{{ __t('admin.users.role_customer') }}</option>
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:brightness-105 active:scale-[0.98] transition-all">
            <span class="material-symbols-outlined text-base">filter_list</span>
            <span>{{ __t('common.filter') }}</span>
        </button>
        @if(request('search') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 px-4 py-2.5 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-on-surface text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined text-base">restart_alt</span>
                <span>{{ __t('admin.users.reset') }}</span>
            </a>
        @endif
    </form>
</div>

{{-- Users Table Card --}}
<div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-start">
            <thead>
                <tr class="bg-surface-container-low/70 border-b border-outline-variant/40 text-on-surface-variant text-xs uppercase tracking-wider font-bold">
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.users.customer') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('common.email') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('common.phone') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.users.role') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('common.status') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.users.orders') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.users.date') }}</th>
                    <th class="px-5 py-3.5 text-center">{{ __t('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30 text-sm">
                @forelse($users as $user)
                    <tr class="hover:bg-surface-container-low/40 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-outline-variant/40" alt="{{ $user->name }}">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary-container text-white flex items-center justify-center font-black text-sm shadow-xs">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.users.show', $user) }}" class="font-bold text-on-surface hover:text-primary transition-colors block">{{ $user->name }}</a>
                                    <span class="text-xs text-on-surface-variant font-mono">#{{ $user->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-xs font-medium text-on-surface-variant font-mono">{{ $user->email }}</td>
                        <td class="px-5 py-4 text-xs font-medium text-on-surface" dir="ltr">{{ $user->phone ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @switch($user->role)
                                @case('admin')
                                    <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 border border-red-200/60 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                        <span class="material-symbols-outlined text-[13px]">shield_person</span>
                                        {{ __t('admin.users.role_admin') }}
                                    </span>
                                    @break
                                @case('manager')
                                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200/60 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                        <span class="material-symbols-outlined text-[13px]">badge</span>
                                        {{ __t('admin.users.role_manager') }}
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 border border-gray-200/60 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                        <span class="material-symbols-outlined text-[13px]">person</span>
                                        {{ __t('admin.users.role_customer') }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-5 py-4">
                            @switch($user->status ?? 'active')
                                @case('active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ __t('common.active') }}
                                    </span>
                                    @break
                                @case('banned')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        {{ __t('admin.users.banned') }}
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        {{ __t('common.inactive') }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-surface-container-low text-on-surface font-bold text-xs">
                                <span class="material-symbols-outlined text-[13px] text-primary">receipt_long</span>
                                {{ $user->orders()->count() }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs font-medium text-on-surface-variant">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.users.show', $user) }}" class="p-2 rounded-lg text-primary hover:bg-primary/10 transition-colors" title="{{ __t('common.preview') }}">
                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors" title="تعديل">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.users.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="{{ __t('common.delete') }}">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-on-surface-variant">
                            <div class="w-16 h-16 rounded-2xl bg-surface-container-low text-primary/40 mx-auto flex items-center justify-center mb-3">
                                <span class="material-symbols-outlined text-3xl">group</span>
                            </div>
                            <h3 class="text-base font-bold text-on-surface mb-1">{{ __t('admin.users.no_users') }}</h3>
                            <p class="text-xs text-on-surface-variant">لم يتم العثور على أي مستخدمين يطابقون معايير البحث</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="p-4 border-t border-outline-variant/40 bg-surface-container-low/30">{{ $users->links() }}</div>
    @endif
</div>
@endsection

