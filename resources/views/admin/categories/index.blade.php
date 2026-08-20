@extends('admin.layout')

@section('title', __t('admin.categories.title'))

@section('content')
@php
    $totalCats = $categories->total() ?? $categories->count();
    $activeCats = \App\Models\Catalog\Category::where('status', 'active')->count();
    $totalProducts = \App\Models\Catalog\Product::count();
@endphp

{{-- Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">category</span>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ __t('admin.categories.title') }}</h1>
                <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ __t('admin.categories.description') }}</p>
            </div>
        </div>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
        <span class="material-symbols-outlined text-lg">add_circle</span>
        <span>{{ __t('admin.categories.add_new') }}</span>
    </a>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.categories.title') }}</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">grid_view</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-on-surface mt-2">{{ number_format($totalCats) }}</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.common.active') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">check_circle</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-2">{{ number_format($activeCats) }}</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">إجمالي المنتجات</span>
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">inventory_2</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-purple-600 mt-2">{{ number_format($totalProducts) }}</div>
    </div>
</div>

{{-- Categories Table Card --}}
<div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-start">
            <thead>
                <tr class="bg-surface-container-low/70 border-b border-outline-variant/40 text-on-surface-variant text-xs uppercase tracking-wider font-bold">
                    <th class="px-5 py-3.5 text-start">#</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.categories.name') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.categories.parent') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.categories.order') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.categories.products_count') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('common.status') }}</th>
                    <th class="px-5 py-3.5 text-center">{{ __t('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30 text-sm">
                @forelse($categories as $cat)
                    <tr class="hover:bg-surface-container-low/40 transition-colors duration-150">
                        <td class="px-5 py-4 text-xs font-mono text-on-surface-variant font-medium">{{ $cat->id }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($cat->image)
                                    <img src="{{ asset('storage/' . $cat->image) }}" class="w-11 h-11 rounded-xl object-cover ring-1 ring-outline-variant/50 shadow-2xs" alt="{{ $cat->name }}">
                                @else
                                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-primary-container text-white flex items-center justify-center shadow-2xs">
                                        @if($cat->icon)
                                            @categoryIcon($cat->icon, 'text-xl text-white')
                                        @else
                                            <span class="material-symbols-outlined text-xl">category</span>
                                        @endif
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.categories.edit', $cat) }}" class="font-bold text-on-surface hover:text-primary transition-colors block">{{ $cat->name }}</a>
                                    <span class="text-xs text-on-surface-variant font-mono">/category/{{ $cat->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if($cat->parent)
                                <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 border border-purple-200/60 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                    <span class="material-symbols-outlined text-[13px]">subdirectory_arrow_left</span>
                                    {{ $cat->parent->name }}
                                </span>
                            @else
                                <span class="text-xs text-on-surface-variant font-medium bg-surface-container-low px-2 py-0.5 rounded-md">{{ __t('admin.categories.none') }} (رئيسي)</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs font-mono font-bold text-on-surface-variant">{{ $cat->order ?? 0 }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200/60 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                <span class="material-symbols-outlined text-[13px]">inventory_2</span>
                                {{ $cat->products()->count() ?? 0 }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($cat->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ __t('common.active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    {{ __t('common.inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="p-2 rounded-lg text-primary hover:bg-primary/10 transition-colors" title="{{ __t('common.edit') }}">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('common.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="{{ __t('common.delete') }}">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center text-on-surface-variant">
                            <div class="w-16 h-16 rounded-2xl bg-surface-container-low text-primary/40 mx-auto flex items-center justify-center mb-3">
                                <span class="material-symbols-outlined text-3xl">category</span>
                            </div>
                            <h3 class="text-base font-bold text-on-surface mb-1">{{ __t('admin.categories.no_categories') }}</h3>
                            <p class="text-xs text-on-surface-variant mb-4">أنشئ تصنيفات لتنظيم المنتجات وتسهيل التصفح للعملاء</p>
                            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-on-primary text-xs font-bold shadow-sm hover:brightness-105 transition-all">
                                <span class="material-symbols-outlined text-base">add</span>
                                {{ __t('admin.categories.add_new') }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
        <div class="p-4 border-t border-outline-variant/40 bg-surface-container-low/30">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection

