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

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold">{{ __t('admin.users.title') }}</h1>
        <p class="text-on-surface-variant text-sm mt-1">{{ __t('admin.users.subtitle') }}</p>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
                        <p class="text-on-surface-variant text-xs">{{ __t('admin.users.total') }}</p>
        <p class="text-2xl font-bold mt-1">{{ number_format($totalUsers) }}</p>
    </div>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
                        <p class="text-on-surface-variant text-xs">{{ __t('admin.users.admins') }}</p>
        <p class="text-2xl font-bold mt-1 text-red-600">{{ number_format($adminsCount) }}</p>
    </div>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
                        <p class="text-on-surface-variant text-xs">{{ __t('admin.users.managers') }}</p>
        <p class="text-2xl font-bold mt-1 text-primary">{{ number_format($managersCount) }}</p>
    </div>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
                        <p class="text-on-surface-variant text-xs">{{ __t('admin.users.customers') }}</p>
        <p class="text-2xl font-bold mt-1">{{ number_format($customersCount) }}</p>
    </div>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
                        <p class="text-on-surface-variant text-xs">{{ __t('admin.users.banned') }}</p>
        <p class="text-2xl font-bold mt-1 text-red-600">{{ number_format($bannedCount) }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-surface-container-lowest rounded-xl shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold mb-1 text-on-surface-variant">{{ __t('common.search') }}</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __t('admin.users.search_placeholder') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1 text-on-surface-variant">{{ __t('admin.users.role') }}</label>
                <select name="role" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">{{ __t('common.all') }}</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __t('admin.users.role_admin') }}</option>
                    <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>{{ __t('admin.users.role_manager') }}</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>{{ __t('admin.users.role_customer') }}</option>
                </select>
        </div>
        <button type="submit" class="bg-primary hover:bg-primary text-white px-4 py-2 rounded-lg text-sm">
            <span class="material-symbols-outlined ml-1">search</span>{{ __t('common.filter') }}
        </button>
        @if(request('search') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-on-surface px-4 py-2 rounded-lg text-sm">{{ __t('admin.users.reset') }}</a>
        @endif
    </form>
</div>

<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-container-low text-on-surface-variant text-xs">
                <tr>
                    <th class="px-4 py-3 text-right">{{ __t('admin.users.customer') }}</th>
                    <th class="px-4 py-3 text-right">{{ __t('common.email') }}</th>
                    <th class="px-4 py-3 text-right">{{ __t('common.phone') }}</th>
                    <th class="px-4 py-3 text-right">{{ __t('admin.users.role') }}</th>
                    <th class="px-4 py-3 text-right">{{ __t('common.status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __t('admin.users.orders') }}</th>
                    <th class="px-4 py-3 text-right">{{ __t('admin.users.date') }}</th>
                    <th class="px-4 py-3 text-right">{{ __t('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-t hover:bg-surface-container-low">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" class="w-10 h-10 rounded-full object-cover" alt="">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.users.show', $user) }}" class="font-semibold text-primary hover:underline">{{ $user->name }}</a>
                                    <div class="text-xs text-on-surface-variant">#{{ $user->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-xs">{{ $user->phone ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                @switch($user->role)
                                    @case('admin') bg-error-container text-on-error-container @break
                                    @case('manager') bg-blue-100 text-blue-700 @break
                                    @case('customer') bg-gray-100 text-gray-700 @break
                                @endswitch">
                            @switch($user->role)
                                @case('admin') {{ __t('admin.users.role_admin') }} @break
                                @case('manager') {{ __t('admin.users.role_manager') }} @break
                                @case('customer') {{ __t('admin.users.role_customer') }} @break
                            @endswitch
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                @switch($user->status ?? 'active')
                                    @case('active') bg-emerald-50 text-emerald-700 @break
                                    @case('inactive') bg-gray-100 text-gray-700 @break
                                    @case('banned') bg-error-container text-on-error-container @break
                                @endswitch">
                                @switch($user->status ?? 'active')
                                    @case('active') {{ __t('common.active') }} @break
                                    @case('inactive') {{ __t('common.inactive') }} @break
                                    @case('banned') {{ __t('admin.users.banned') }} @break
                                @endswitch
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold">{{ $user->orders()->count() }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-on-surface-variant">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-primary hover:text-primary" title="{{ __t('common.preview') }}">
                                    <span class="material-symbols-outlined">visibility</span>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-green-600 hover:text-green-800" title="تعديل">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.users.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="{{ __t('common.delete') }}">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-4xl text-gray-300 mb-3">group</span>
                            <p>{{ __t('admin.users.no_users') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="p-4 border-t">{{ $users->links() }}</div>
    @endif
</div>
@endsection
