@extends('admin.layout')

@section('title', __t('admin.pages.title'))

@section('content')
@php
    $totalPages = $pages->total() ?? $pages->count();
    $activePages = \App\Models\Page::where('is_active', true)->count();
@endphp

{{-- Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">description</span>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">{{ __t('admin.pages.title') }}</h1>
                <p class="text-on-surface-variant text-xs sm:text-sm mt-0.5">{{ __t('admin.pages.manage_pages') }}</p>
            </div>
        </div>
    </div>
    <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary font-bold text-sm shadow-sm hover:shadow-md hover:brightness-105 active:scale-[0.98] transition-all">
        <span class="material-symbols-outlined text-lg">add_circle</span>
        <span>{{ __t('admin.pages.add_new') }}</span>
    </a>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">إجمالي الصفحات</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">article</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-on-surface mt-2">{{ number_format($totalPages) }}</div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.pages.published') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">check_circle</span>
            </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-2">{{ number_format($activePages) }}</div>
    </div>
</div>

{{-- Pages Table Card --}}
<div class="bg-surface-container-lowest border border-outline-variant/40 rounded-2xl shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-start">
            <thead>
                <tr class="bg-surface-container-low/70 border-b border-outline-variant/40 text-on-surface-variant text-xs uppercase tracking-wider font-bold">
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.pages.title_field') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.pages.slug') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.pages.status') }}</th>
                    <th class="px-5 py-3.5 text-start">{{ __t('admin.pages.sort_order') }}</th>
                    <th class="px-5 py-3.5 text-center">{{ __t('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30 text-sm">
                @forelse($pages as $page)
                    <tr class="hover:bg-surface-container-low/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-bold text-on-surface">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-base">{{ $page->icon ?? 'description' }}</span>
                                </div>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="hover:text-primary transition-colors">
                                    {{ $page->title }}
                                </a>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-on-surface-variant font-mono text-xs">
                            <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="hover:text-primary inline-flex items-center gap-1">
                                <span>/page/{{ $page->slug }}</span>
                                <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            @if($page->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ __t('admin.pages.published') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ __t('admin.pages.draft') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs font-mono font-bold text-on-surface-variant">{{ $page->sort_order }}</td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.pages.edit', $page) }}" class="p-2 rounded-lg text-primary hover:bg-primary/10 transition-colors" title="{{ __t('common.edit') }}">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.pages.delete_confirm') }}')">
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
                        <td colspan="5" class="px-5 py-16 text-center text-on-surface-variant">
                            <div class="w-16 h-16 rounded-2xl bg-surface-container-low text-primary/40 mx-auto flex items-center justify-center mb-3">
                                <span class="material-symbols-outlined text-3xl">description</span>
                            </div>
                            <h3 class="text-base font-bold text-on-surface mb-1">{{ __t('admin.pages.no_pages') }}</h3>
                            <p class="text-xs text-on-surface-variant mb-4">أنشئ صفحات تعريفية وسياسات لتعزيز مصداقية المتجر</p>
                            <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-on-primary text-xs font-bold shadow-sm hover:brightness-105 transition-all">
                                <span class="material-symbols-outlined text-base">add</span>
                                {{ __t('admin.pages.add_new') }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pages->hasPages())
        <div class="p-4 border-t border-outline-variant/40 bg-surface-container-low/30">{{ $pages->links() }}</div>
    @endif
</div>
@endsection