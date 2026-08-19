@extends('admin.layout')

@section('title', __t('admin.shipping.page_title'))

@section('content')
@php
    $activeTab = request('tab', 'zones');
@endphp

<!-- Page Header with Breadcrumb -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <nav aria-label="Breadcrumb" class="flex text-outline text-sm mb-2">
            <ol class="flex items-center space-x-2 space-x-reverse">
                <li><a class="hover:text-primary transition-colors" href="{{ route('admin.dashboard') }}">{{ __t('admin.dashboard') }}</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-primary font-bold">{{ __t('admin.shipping.page_title') }}</li>
            </ol>
        </nav>
        <h2 class="text-[32px] font-bold leading-10 text-primary">{{ __t('admin.shipping.page_title') }}</h2>
    </div>
    <div class="flex gap-3">
        @if($activeTab === 'zones')
            <a href="{{ route('admin.shipping.zone.create') }}" class="bg-primary text-white px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-primary-container transition-all shadow-sm active:scale-95 font-medium">
                <span class="material-symbols-outlined">add_location</span>
                {{ __t('admin.shipping.add_zone') }}
            </a>
        @elseif($activeTab === 'methods')
            <a href="{{ route('admin.shipping.method.create') }}" class="bg-primary text-white px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-primary-container transition-all shadow-sm active:scale-95 font-medium">
                <span class="material-symbols-outlined">add</span>
                {{ __t('admin.shipping.add_method') }}
            </a>
        @elseif($activeTab === 'companies')
            <a href="{{ route('admin.shipping.company.create') }}" class="bg-primary text-white px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-primary-container transition-all shadow-sm active:scale-95 font-medium">
                <span class="material-symbols-outlined">add</span>
                {{ __t('admin.shipping.add_company') }}
            </a>
        @elseif($activeTab === 'labels')
            <a href="{{ route('admin.shipping.label.create') }}" class="bg-primary text-white px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-primary-container transition-all shadow-sm active:scale-95 font-medium">
                <span class="material-symbols-outlined">add</span>
                {{ __t('admin.shipping.create_label') }}
            </a>
        @elseif($activeTab === 'pickups')
            <a href="{{ route('admin.shipping.pickup.create') }}" class="bg-primary text-white px-6 py-3 rounded-xl flex items-center gap-2 hover:bg-primary-container transition-all shadow-sm active:scale-95 font-medium">
                <span class="material-symbols-outlined">add</span>
                {{ __t('admin.shipping.add_pickup') }}
            </a>
        @endif
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center mx-auto mb-2 text-primary">
            <span class="material-symbols-outlined">map</span>
        </div>
        <div class="text-2xl font-bold text-on-surface">{{ $stats['zones_count'] }}</div>
        <div class="text-xs text-on-surface-variant">{{ __t('admin.shipping.zones') }}</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-secondary-container flex items-center justify-center mx-auto mb-2 text-secondary">
            <span class="material-symbols-outlined">local_shipping</span>
        </div>
        <div class="text-2xl font-bold text-on-surface">{{ $stats['methods_count'] }}</div>
        <div class="text-xs text-on-surface-variant">{{ __t('admin.shipping.methods') }}</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center mx-auto mb-2 text-emerald-700">
            <span class="material-symbols-outlined">business</span>
        </div>
        <div class="text-2xl font-bold text-on-surface">{{ $stats['carriers_count'] }}</div>
        <div class="text-xs text-on-surface-variant">{{ __t('admin.shipping.companies') }}</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center mx-auto mb-2 text-orange-700">
            <span class="material-symbols-outlined">receipt</span>
        </div>
        <div class="text-2xl font-bold text-on-surface">{{ $stats['labels_count'] }}</div>
        <div class="text-xs text-on-surface-variant">{{ __t('admin.shipping.labels') }}</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center mx-auto mb-2 text-amber-700">
            <span class="material-symbols-outlined">schedule</span>
        </div>
        <div class="text-2xl font-bold text-on-surface">{{ $stats['pending_labels'] }}</div>
        <div class="text-xs text-on-surface-variant">{{ __t('admin.shipping.pending') }}</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center mx-auto mb-2 text-indigo-700">
            <span class="material-symbols-outlined">local_shipping</span>
        </div>
        <div class="text-2xl font-bold text-on-surface">{{ $stats['shipped_labels'] }}</div>
        <div class="text-xs text-on-surface-variant">{{ __t('admin.shipping.shipped') }}</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4 text-center">
        <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center mx-auto mb-2 text-teal-700">
            <span class="material-symbols-outlined">storefront</span>
        </div>
        <div class="text-2xl font-bold text-on-surface">{{ $stats['pickup_offices_count'] }}</div>
        <div class="text-xs text-on-surface-variant">{{ __t('admin.shipping.pickup_offices') }}</div>
    </div>
</div>

<!-- Sub Navigation Tabs -->
<div class="flex border-b border-outline-variant mb-8 gap-8 overflow-x-auto no-scrollbar">
    <a href="{{ route('admin.shipping.index', ['tab' => 'zones']) }}"
       class="pb-4 px-2 whitespace-nowrap font-title-lg flex items-center gap-1.5 {{ $activeTab === 'zones' ? 'text-primary font-bold border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary transition-colors' }}">
        <span class="material-symbols-outlined text-lg">map</span> {{ __t('admin.shipping.zones') }}
    </a>
    <a href="{{ route('admin.shipping.index', ['tab' => 'methods']) }}"
       class="pb-4 px-2 whitespace-nowrap font-title-lg flex items-center gap-1.5 {{ $activeTab === 'methods' ? 'text-primary font-bold border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary transition-colors' }}">
        <span class="material-symbols-outlined text-lg">local_shipping</span> {{ __t('admin.shipping.methods') }}
    </a>
    <a href="{{ route('admin.shipping.index', ['tab' => 'companies']) }}"
       class="pb-4 px-2 whitespace-nowrap font-title-lg flex items-center gap-1.5 {{ $activeTab === 'companies' ? 'text-primary font-bold border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary transition-colors' }}">
        <span class="material-symbols-outlined text-lg">business</span> {{ __t('admin.shipping.companies') }}
    </a>
    <a href="{{ route('admin.shipping.index', ['tab' => 'labels']) }}"
       class="pb-4 px-2 whitespace-nowrap font-title-lg flex items-center gap-1.5 {{ $activeTab === 'labels' ? 'text-primary font-bold border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary transition-colors' }}">
        <span class="material-symbols-outlined text-lg">receipt</span> {{ __t('admin.shipping.labels') }}
    </a>
    <a href="{{ route('admin.shipping.index', ['tab' => 'pickups']) }}"
       class="pb-4 px-2 whitespace-nowrap font-title-lg flex items-center gap-1.5 {{ $activeTab === 'pickups' ? 'text-primary font-bold border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary transition-colors' }}">
        <span class="material-symbols-outlined text-lg">storefront</span> {{ __t('admin.shipping.pickup_offices') }}
    </a>
</div>

{{-- ========== ZONES TAB ========== --}}
@if($activeTab === 'zones')
    <div class="space-y-6">
        @forelse($zones as $zone)
            <section class="zone-card bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
                {{-- Zone Header --}}
                <div class="bg-surface-container-low p-6 flex justify-between items-center border-b border-outline-variant">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary bg-primary-fixed p-2.5 rounded-lg">location_on</span>
                        <div>
                            <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                                {{ $zone->name }}
                                @if($zone->is_default)
                                    <span class="text-tertiary text-xs bg-tertiary-fixed px-2 py-0.5 rounded-full font-label-sm">★ {{ __t('admin.shipping.default') }}</span>
                                @endif
                            </h3>
                            <p class="text-outline text-sm mt-1">
                                @if($zone->countries)
                                    <span class="inline-flex items-center gap-1 ml-2"><span class="material-symbols-outlined text-sm">language</span>{{ $zone->getFormattedCountries() }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm flex items-center gap-1 ml-2 {{ $zone->status === 'active' ? 'text-green-600 font-bold' : 'text-red-500 font-bold' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $zone->status === 'active' ? 'bg-green-600' : 'bg-red-500' }}"></span>
                            {{ $zone->status === 'active' ? __t('admin.common.active') : __t('admin.common.inactive') }}
                        </span>
                        <a href="{{ route('admin.shipping.zone.edit', $zone) }}" class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all" title="{{ __t('admin.common.edit') }}">
                            <span class="material-symbols-outlined">edit</span>
                        </a>
                        <form action="{{ route('admin.shipping.zone.destroy', $zone) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.shipping.confirm_delete_zone') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-error hover:bg-error-container rounded-lg transition-all" title="{{ __t('admin.common.delete') }}">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Zone Body --}}
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Coverage Detail --}}
                    <div class="col-span-1 space-y-4 border-l border-outline-variant/20 pl-4">
                        @if($zone->countries)
                            <div>
                                <label class="text-outline text-xs font-bold block mb-1">{{ __t('admin.shipping.countries') }}</label>
                                <p class="text-sm font-semibold text-on-surface">{{ $zone->getFormattedCountries() }}</p>
                            </div>
                        @endif
                        @if($zone->states)
                            <div>
                                <label class="text-outline text-xs font-bold block mb-1">{{ __t('admin.shipping.states') }}</label>
                                <p class="text-sm text-on-surface">{{ $zone->getFormattedStates() }}</p>
                            </div>
                        @endif
                        @if($zone->cities)
                            <div>
                                <label class="text-outline text-xs font-bold block mb-1">{{ __t('admin.shipping.cities') }}</label>
                                <p class="text-xs text-on-surface-variant leading-relaxed">{{ $zone->getFormattedCities() }}</p>
                            </div>
                        @endif
                        @if(!$zone->countries && !$zone->states && !$zone->cities)
                            <div>
                                <p class="text-sm text-on-surface-variant">{{ __t('admin.shipping.no_coverage') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Shipping Methods --}}
                    <div class="col-span-2 space-y-4">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-bold text-on-surface">{{ __t('admin.shipping.available_methods') }}</h4>
                            <button type="button" onclick="toggleAddMethod({{ $zone->id }})" class="text-primary hover:underline text-sm font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">add</span>
                                {{ __t('admin.shipping.add_method') }}
                            </button>
                        </div>

                        @php $zoneMethods = $zone->methods; @endphp
                        @if($zoneMethods->count() > 0)
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($zoneMethods as $method)
                                    <div class="flex items-center justify-between p-4 bg-surface rounded-xl border border-outline-variant hover:border-primary transition-all group">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-surface-container-high rounded-full flex items-center justify-center">
                                                <span class="material-symbols-outlined text-primary">local_shipping</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-on-surface">{{ $method->name }}</span>
                                                    @if($method->carrier)
                                                        <span class="text-xs text-on-surface-variant font-medium">({{ $method->carrier->name }})</span>
                                                    @endif
                                                    <span class="bg-primary-fixed text-on-primary-fixed-variant px-2 py-0.5 rounded text-xs font-semibold">{{ $method->getTypeLabel() }}</span>
                                                </div>
                                                @if($method->estimated_days)
                                                    <p class="text-outline text-xs mt-1">{{ __t('admin.shipping.estimated_days') }}: {{ $method->estimated_days }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-left flex items-center gap-4">
                                            <div>
                                                @if($method->type === 'flat_rate')
                                                    <div class="text-xl font-bold text-primary">{{ number_format($method->flat_rate_amount, 2) }} <span class="text-sm font-medium">{{ currentCurrencySymbol() }}</span></div>
                                                @elseif($method->type === 'free_shipping')
                                                    <div class="font-bold text-emerald-600 text-lg">{{ __t('admin.shipping.free') }}</div>
                                                    @if($method->free_shipping_min)
                                                        <span class="text-xs text-on-surface-variant block">{{ __t('admin.shipping.above') }} {{ number_format($method->free_shipping_min, 0) }} {{ currentCurrencySymbol() }}</span>
                                                    @endif
                                                @elseif($method->type === 'weight_based')
                                                    <div class="font-bold text-purple-700">{{ __t('admin.shipping.weight_based') }}</div>
                                                @else
                                                    <div class="font-bold text-on-surface-variant">{{ $method->getTypeLabel() }}</div>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1 pr-2 border-r border-outline-variant/30">
                                                <a href="{{ route('admin.shipping.method.edit', $method) }}" class="text-primary hover:text-primary-container p-1">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </a>
                                <form action="{{ route('admin.shipping.method.destroy', $method) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.shipping.confirm_delete_method') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-error hover:text-red-700 p-1">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-on-surface-variant text-sm text-center py-8 bg-surface rounded-xl border border-dashed border-outline-variant">{{ __t('admin.shipping.no_methods_for_zone') }}</p>
                        @endif

                        {{-- Quick add method form --}}
                        <div id="addMethod-{{ $zone->id }}" class="hidden bg-primary-fixed/20 rounded-xl p-4 border border-primary/20">
                            <form action="{{ route('admin.shipping.zone.method.store', $zone) }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                    <div>
                                        <label class="text-xs text-on-surface-variant block mb-1">{{ __t('admin.shipping.method_name') }}</label>
                                        <input type="text" name="name" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white outline-none focus:ring-1 focus:ring-primary" placeholder="{{ __t('admin.shipping.standard_shipping') }}" required>
                                    </div>
                                    <div>
                                        <label class="text-xs text-on-surface-variant block mb-1">{{ __t('admin.shipping.type') }}</label>
                                        <select name="type" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white outline-none cursor-pointer">
                                            <option value="flat_rate">{{ __t('admin.shipping.flat_rate') }}</option>
                                            <option value="free_shipping">{{ __t('admin.shipping.free_shipping') }}</option>
                                            <option value="weight_based">{{ __t('admin.shipping.weight_based') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-on-surface-variant block mb-1">{{ __t('admin.shipping.amount') }} ({{ currentCurrencySymbol() }})</label>
                                        <input type="number" name="flat_rate_amount" step="0.01" min="0" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white outline-none" placeholder="25">
                                    </div>
                                    <div>
                                        <label class="text-xs text-on-surface-variant block mb-1">{{ __t('admin.shipping.delivery_time') }}</label>
                                        <input type="text" name="estimated_days" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white outline-none" placeholder="{{ __t('admin.shipping.days_placeholder') }}">
                                    </div>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <button type="submit" class="bg-primary hover:bg-primary-container text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1 font-semibold">
                                        <span class="material-symbols-outlined text-sm">check</span> {{ __t('admin.shipping.add_method') }}
                                    </button>
                                    <button type="button" onclick="toggleAddMethod({{ $zone->id }})" class="bg-white hover:bg-surface border border-outline-variant text-on-surface px-4 py-2 rounded-lg text-sm">{{ __t('admin.common.cancel') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        @empty
            <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-xl border border-dashed border-outline-variant">
                <span class="material-symbols-outlined text-5xl mb-3 text-outline">map</span>
                <p class="text-lg mb-2 font-bold">{{ __t('admin.shipping.no_zones') }}</p>
                <a href="{{ route('admin.shipping.zone.create') }}" class="text-primary hover:text-primary text-sm inline-flex items-center gap-1 font-bold">
                    <span class="material-symbols-outlined text-sm">add</span> {{ __t('admin.shipping.add_zone') }}
                </a>
            </div>
        @endforelse

        <div class="mt-4">{{ $zones->withQueryString()->links() }}</div>
    </div>

{{-- ========== METHODS TAB ========== --}}
@elseif($activeTab === 'methods')
    <div class="space-y-3">
        @forelse($methods as $method)
            <div class="flex items-center justify-between p-4 bg-surface-container-lowest rounded-xl border border-outline-variant hover:border-primary transition-all shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-surface-container-high rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">local_shipping</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-on-surface text-base">{{ $method->name }}</span>
                            <span class="bg-primary-fixed text-on-primary-fixed-variant px-2 py-0.5 rounded text-xs font-semibold">{{ $method->getTypeLabel() }}</span>
                        </div>
                        <div class="text-xs text-on-surface-variant mt-1.5 flex items-center gap-3">
                            @if($method->zone)
                                <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-xs">location_on</span>{{ $method->zone->name }}</span>
                            @endif
                            @if($method->carrier)
                                <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-xs">business</span>{{ $method->carrier->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <div class="text-left">
                        @if($method->type === 'flat_rate')
                            <div class="text-xl font-bold text-primary">{{ number_format($method->flat_rate_amount, 2) }} <span class="text-sm font-medium">{{ currentCurrencySymbol() }}</span></div>
                        @elseif($method->type === 'free_shipping')
                            <div class="font-bold text-emerald-600 text-lg">{{ __t('admin.shipping.free') }}</div>
                            @if($method->free_shipping_min)
                                <span class="text-xs text-on-surface-variant">{{ __t('admin.shipping.above') }} {{ number_format($method->free_shipping_min, 0) }} {{ currentCurrencySymbol() }}</span>
                            @endif
                        @elseif($method->type === 'weight_based')
                            <div class="font-bold text-purple-700">{{ __t('admin.shipping.weight_based') }}</div>
                        @else
                            <div class="font-bold text-on-surface-variant">{{ $method->getTypeLabel() }}</div>
                        @endif
                    </div>
                    @if($method->estimated_days)
                        <span class="text-on-surface-variant inline-flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span>{{ $method->estimated_days }}</span>
                    @endif
                    <span class="flex items-center gap-1 text-xs {{ $method->status ? 'text-green-600 font-bold' : 'text-red-500 font-bold' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $method->status ? 'bg-green-600' : 'bg-red-500' }}"></span> {{ $method->status ? __t('admin.common.active') : __t('admin.common.inactive') }}
                    </span>
                    <div class="flex gap-1 pr-2 border-r border-outline-variant/30">
                        <a href="{{ route('admin.shipping.method.edit', $method) }}" class="text-primary hover:text-primary-container p-1">
                            <span class="material-symbols-outlined">edit</span>
                        </a>
                        <form action="{{ route('admin.shipping.method.destroy', $method) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.shipping.confirm_delete_method') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-error hover:text-red-700 p-1">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-xl border border-dashed border-outline-variant">
                <span class="material-symbols-outlined text-5xl mb-3 text-outline">local_shipping</span>
                <p class="font-bold text-lg">{{ __t('admin.shipping.no_methods') }}</p>
            </div>
        @endforelse
        <div class="mt-4">{{ $methods->withQueryString()->links() }}</div>
    </div>

{{-- ========== COMPANIES TAB ========== --}}
@elseif($activeTab === 'companies')
    <div>
        <div class="overflow-x-auto bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant text-xs border-b border-outline-variant">
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.company') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.website') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.tracking_url') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.status') }}</th>
                        <th class="px-6 py-4 font-bold text-center">{{ __t('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($companies as $company)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($company->logo)
                                        <img src="{{ $company->logo }}" class="w-10 h-10 rounded object-contain bg-white border p-1" alt="{{ $company->name }}">
                                    @else
                                        <div class="w-10 h-10 rounded bg-surface-container-high flex items-center justify-center text-outline">
                                            <span class="material-symbols-outlined text-lg">local_shipping</span>
                                        </div>
                                    @endif
                                    <span class="font-bold text-on-surface">{{ $company->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($company->website)
                                    <a href="{{ $company->website }}" target="_blank" class="text-primary hover:underline font-mono">{{ $company->website }}</a>
                                @else
                                    <span class="text-on-surface-variant">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-on-surface-variant max-w-xs truncate" title="{{ $company->tracking_url }}">
                                {{ $company->tracking_url ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $company->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-error-container text-error' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $company->status === 'active' ? 'bg-emerald-500' : 'bg-error' }}"></span>
                                    {{ $company->status === 'active' ? __t('admin.common.active') : __t('admin.common.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.shipping.company.edit', $company) }}" class="p-1 text-on-surface-variant hover:text-primary transition-colors" title="{{ __t('admin.common.edit') }}">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                    <form action="{{ route('admin.shipping.company.destroy', $company) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.shipping.confirm_delete_company') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 text-error hover:text-red-700 transition-colors" title="{{ __t('admin.common.delete') }}">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-12 text-on-surface-variant font-bold">{{ __t('admin.shipping.no_companies') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $companies->withQueryString()->links() }}</div>
    </div>

{{-- ========== LABELS TAB ========== --}}
@elseif($activeTab === 'labels')
    <div>
        <div class="overflow-x-auto bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant text-xs border-b border-outline-variant">
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.tracking_number') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.order') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.carrier') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.weight') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.cost') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __t('admin.shipping.status') }}</th>
                        <th class="px-6 py-4 font-bold text-center">{{ __t('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($labels as $label)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.shipping.label.show', $label) }}" class="font-mono text-primary font-bold hover:underline">
                                    {{ $label->tracking_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                @if($label->order)
                                    <a href="{{ route('admin.orders.show', $label->order) }}" class="text-primary hover:underline font-bold">
                                        #{{ $label->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-on-surface-variant">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-on-surface font-semibold">{{ $label->carrier?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-on-surface-variant font-medium">{{ $label->weight ? $label->weight . ' ' . __t('admin.shipping.kg') : '-' }}</td>
                            <td class="px-6 py-4 font-bold text-primary">{{ number_format($label->cost, 2) }} {{ currentCurrencySymbol() }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $label->getStatusColor() }}-100 text-{{ $label->getStatusColor() }}-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $label->getStatusColor() }}-500"></span>
                                    {{ $label->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.shipping.label.show', $label) }}" class="p-1 text-on-surface-variant hover:text-primary transition-colors" title="{{ __t('admin.shipping.view') }}">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.shipping.label.pdf', $label) }}" class="p-1 text-on-surface-variant hover:text-primary transition-colors" title="{{ __t('admin.shipping.download_label') }}">
                                        <span class="material-symbols-outlined">picture_as_pdf</span>
                                    </a>
                                    @if($label->status === 'pending')
                                        <form action="{{ route('admin.shipping.label.updateStatus', $label) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="shipped">
                                            <button type="submit" class="p-1 text-indigo-600 hover:text-indigo-800 transition-colors" title="{{ __t('admin.shipping.ship_now') }}">
                                                <span class="material-symbols-outlined">local_shipping</span>
                                            </button>
                                        </form>
                                    @elseif($label->status === 'shipped')
                                        <form action="{{ route('admin.shipping.label.updateStatus', $label) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" class="p-1 text-emerald-600 hover:text-emerald-800 transition-colors" title="{{ __t('admin.shipping.delivered') }}">
                                                <span class="material-symbols-outlined">check_circle</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-on-surface-variant font-bold">{{ __t('admin.shipping.no_labels') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $labels->withQueryString()->links() }}</div>
    </div>

{{-- ========== PICKUP OFFICES TAB ========== --}}
@elseif($activeTab === 'pickups')
    <div class="bg-white rounded-xl border border-outline-variant overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant border-b border-outline-variant">
                        <th class="text-right py-3 px-4 font-semibold">{{ __t('admin.shipping.name') }}</th>
                        <th class="text-right py-3 px-4 font-semibold">{{ __t('admin.shipping.carrier') }}</th>
                        <th class="text-right py-3 px-4 font-semibold">{{ __t('admin.shipping.city') }}</th>
                        <th class="text-right py-3 px-4 font-semibold">{{ __t('admin.shipping.country') }}</th>
                        <th class="text-right py-3 px-4 font-semibold">{{ __t('admin.shipping.phone') }}</th>
                        <th class="text-center py-3 px-4 font-semibold">{{ __t('admin.shipping.status') }}</th>
                        <th class="text-center py-3 px-4 font-semibold">{{ __t('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pickupOffices as $office)
                        <tr class="border-b border-outline-variant/50 hover:bg-surface-container-lowest transition-colors">
                            <td class="py-3 px-4 font-medium">{{ $office->name }}</td>
                            <td class="py-3 px-4">{{ $office->carrier?->name ?? '—' }}</td>
                            <td class="py-3 px-4">{{ $office->city }}</td>
                            <td class="py-3 px-4">{{ $office->country_code }}</td>
                            <td class="py-3 px-4">{{ $office->phone ?? '—' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $office->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $office->is_active ? __t('admin.common.active') : __t('admin.common.inactive') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.shipping.pickup.edit', $office) }}"
                                       class="p-1.5 text-primary hover:bg-primary-fixed rounded-lg transition-colors" title="{{ __t('admin.common.edit') }}">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <form action="{{ route('admin.shipping.pickup.destroy', $office) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.shipping.confirm_delete_pickup') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-error hover:bg-error/10 rounded-lg transition-colors" title="{{ __t('admin.common.delete') }}">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-12 text-on-surface-variant font-bold">{{ __t('admin.shipping.no_pickups') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 p-4">{{ $pickupOffices->withQueryString()->links() }}</div>
    </div>
@endif

@push('scripts')
<script>
function toggleAddMethod(zoneId) {
    const el = document.getElementById('addMethod-' + zoneId);
    if(el) el.classList.toggle('hidden');
}
</script>
@endpush
@endsection
