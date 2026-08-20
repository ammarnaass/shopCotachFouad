@extends('admin.layout')

@section('title', __t('admin.users.title') . ' - ' . $user->name)

@section('content')
{{-- Breadcrumb --}}
<div class="mb-8 flex items-center gap-2.5">
    <a href="{{ route('admin.users.index') }}" class="w-10 h-10 rounded-xl bg-surface-container-low text-on-surface-variant hover:text-primary hover:bg-primary/10 flex items-center justify-center transition-colors">
        <span class="material-symbols-outlined text-xl">arrow_forward</span>
    </a>
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ $user->name }}</h1>
            <span class="text-xs text-on-surface-variant font-mono bg-surface-container-low px-2 py-0.5 rounded-lg">#{{ $user->id }}</span>
        </div>
        <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ __t('admin.users.title') }} / {{ $user->email }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- User Info Card --}}
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs p-6 h-fit">
        <div class="flex flex-col items-center text-center pb-6 border-b border-outline-variant/30">
            @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-primary/10 shadow-sm mb-3" alt="{{ $user->name }}">
            @else
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-primary-container text-white flex items-center justify-center font-black text-2xl shadow-sm mb-3">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
            @endif
            <h2 class="text-xl font-extrabold text-on-surface">{{ $user->name }}</h2>
            <div class="flex items-center gap-2 mt-2">
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
            </div>
        </div>

        <div class="py-6 space-y-4 text-sm border-b border-outline-variant/30">
            <div class="flex items-center gap-3 text-on-surface">
                <span class="material-symbols-outlined text-primary text-xl">mail</span>
                <span class="font-mono text-xs">{{ $user->email }}</span>
            </div>
            @if($user->phone)
                <div class="flex items-center gap-3 text-on-surface">
                    <span class="material-symbols-outlined text-primary text-xl">call</span>
                    <span dir="ltr" class="font-mono text-xs">{{ $user->phone }}</span>
                </div>
            @endif
            <div class="flex items-center gap-3 text-on-surface">
                <span class="material-symbols-outlined text-primary text-xl">event</span>
                <span class="text-xs text-on-surface-variant font-medium">تاريخ التسجيل: {{ $user->created_at->format('Y-m-d') }}</span>
            </div>
        </div>

        <div class="pt-6 flex gap-2.5">
            <a href="{{ route('admin.users.edit', $user) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-sm font-bold shadow-sm hover:brightness-105 active:scale-[0.98] transition-all">
                <span class="material-symbols-outlined text-base">edit</span>
                <span>{{ __t('common.edit') }}</span>
            </a>
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.users.confirm_delete') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="{{ __t('common.delete') }}">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Stats & Details --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs">
                <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.users.orders') }}</span>
                <p class="text-2xl sm:text-3xl font-black text-on-surface mt-1.5">{{ $user->orders->count() }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs">
                <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.users.total_spent') ?? 'إجمالي المشتريات' }}</span>
                <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1.5">{{ number_format($user->orders->sum('total'), 0) }} <span class="text-xs font-normal">د.ج</span></p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs">
                <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.users.reviews_count') ?? 'التقييمات' }}</span>
                <p class="text-2xl sm:text-3xl font-black text-primary mt-1.5">{{ $user->reviews->count() }}</p>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/30 flex items-center justify-between bg-surface-container-low/40">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">shopping_cart</span>
                    <h3 class="font-bold text-base text-on-surface">{{ __t('admin.users.orders') }}</h3>
                </div>
                <span class="text-xs font-bold text-on-surface-variant bg-surface-container-lowest px-2.5 py-1 rounded-full border border-outline-variant/40">
                    {{ $user->orders->count() }} طلب
                </span>
            </div>
            @if($user->orders->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead class="bg-surface-container-low/70 text-on-surface-variant text-xs uppercase tracking-wider font-bold">
                            <tr>
                                <th class="px-5 py-3 text-start">#</th>
                                <th class="px-5 py-3 text-start">{{ __t('admin.users.order_total') ?? 'المجموع' }}</th>
                                <th class="px-5 py-3 text-start">{{ __t('common.status') }}</th>
                                <th class="px-5 py-3 text-start">{{ __t('admin.users.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @foreach($user->orders->take(10) as $order)
                                <tr class="hover:bg-surface-container-low/40 transition-colors">
                                    <td class="px-5 py-3.5"><a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-primary hover:underline">#{{ $order->id }}</a></td>
                                    <td class="px-5 py-3.5 font-bold text-on-surface">{{ number_format($order->total, 0) }} د.ج</td>
                                    <td class="px-5 py-3.5">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $order->status }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-on-surface-variant font-medium">{{ $order->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl text-outline mb-2 block">inbox</span>
                    <p class="text-sm font-medium">{{ __t('admin.users.no_orders') ?? 'لا توجد طلبات سابقة لهذا المستخدم' }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

