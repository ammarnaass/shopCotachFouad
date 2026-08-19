@extends('admin.layout')

@section('title', __t('admin.slider.page_title'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold">{{ __t('admin.slider.page_title') }}</h1>
        <p class="text-on-surface-variant text-sm mt-1">{{ __t('admin.slider.subtitle') }}</p>
    </div>
    <a href="{{ route('admin.slider.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium hover:bg-primary-container shadow-sm transition-all active:scale-95">
        <span class="material-symbols-outlined text-sm">add</span>
        {{ __t('admin.slider.add_slide') }}
    </a>
</div>

@if($slides->count() > 0)
<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full" id="slider-table">
            <thead class="bg-surface-container-low">
                <tr class="border-b border-outline-variant">
                    <th class="w-12 px-4 py-3 text-start text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.slider.drag_handle') }}</th>
                    <th class="w-24 px-4 py-3 text-start text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.slider.image') }}</th>
                    <th class="w-32 px-4 py-3 text-start text-xs font-semibold text-on-surface-variant uppercase tracking-wider hidden md:table-cell">{{ __t('admin.slider.effect') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('admin.slider.title') }}</th>
                    <th class="w-32 px-4 py-3 text-start text-xs font-semibold text-on-surface-variant uppercase tracking-wider hidden md:table-cell">{{ __t('admin.slider.status') }}</th>
                    <th class="w-24 px-4 py-3 text-start text-xs font-semibold text-on-surface-variant uppercase tracking-wider hidden lg:table-cell">{{ __t('admin.slider.sort_order') }}</th>
                    <th class="w-40 px-4 py-3 text-start text-xs font-semibold text-on-surface-variant uppercase tracking-wider hidden xl:table-cell">{{ __t('admin.slider.schedule') }}</th>
                    <th class="w-48 px-4 py-3 text-start text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ __t('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($slides as $slide)
                <tr class="{{ !$slide->is_active ? 'opacity-60' : '' }}" data-id="{{ $slide->id }}">
                    <td class="px-4 py-3">
                        <button type="button" class="drag-handle p-1.5 rounded-lg hover:bg-surface-container cursor-grab" title="{{ __t('admin.slider.drag_hint') }}" aria-label="{{ __t('admin.slider.drag_hint') }}">
                            <span class="material-symbols-outlined text-lg text-on-surface-variant">drag_indicator</span>
                        </button>
                    </td>
                    <td class="px-4 py-3">
                        <div class="w-20 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 border border-outline-variant">
                            @if($slide->image)
                                <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-gray-300 text-2xl">image</span>
                                </div>
                            @endif
                        </div>
                     </td>
                     <td class="px-4 py-3 hidden md:table-cell">
                         <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-container/10 text-primary">{{ $slide->animation_effect }}</span>
                     </td>
                     <td class="px-4 py-3 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            @if($slide->badge)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-container text-on-primary-container">{{ $slide->badge }}</span>
                            @endif
                            <h3 class="font-bold text-sm truncate">{{ $slide->title }}</h3>
                        </div>
                        @if($slide->subtitle)
                            <p class="text-xs text-on-surface-variant truncate">{{ $slide->subtitle }}</p>
                        @endif
                        @if($slide->description)
                            <p class="text-xs text-on-surface-variant truncate mt-0.5 line-clamp-1">{{ $slide->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <form method="POST" action="{{ route('admin.slider.toggle', $slide) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="p-1.5 rounded-lg transition {{ $slide->is_active ? 'text-green-600 bg-green-50 hover:bg-green-100' : 'text-gray-400 bg-gray-50 hover:bg-gray-100' }}" title="{{ $slide->is_active ? __t('admin.slider.deactivate') : __t('admin.slider.activate') }}" aria-label="{{ $slide->is_active ? __t('admin.slider.deactivate') : __t('admin.slider.activate') }}">
                                <span class="material-symbols-outlined text-lg">{{ $slide->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="text-xs text-on-surface-variant font-mono">#{{ $slide->sort_order }}</span>
                    </td>
                    <td class="px-4 py-3 hidden xl:table-cell">
                        <div class="text-xs text-on-surface-variant space-y-0.5">
                            @if($slide->starts_at || $slide->ends_at)
                                @if($slide->starts_at)
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">schedule</span>
                                        <span>{{ $slide->starts_at->format('Y/m/d H:i') }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">schedule</span>
                                        <span>{{ __t('admin.slider.immediate') }}</span>
                                    </div>
                                @endif
                                @if($slide->ends_at)
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">event_busy</span>
                                        <span>{{ $slide->ends_at->format('Y/m/d H:i') }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">event_busy</span>
                                        <span>{{ __t('admin.slider.no_end') }}</span>
                                    </div>
                                @endif
                            @else
                                <span class="text-emerald-600">{{ __t('admin.slider.always') }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.slider.edit', $slide) }}" class="p-1.5 rounded-lg text-primary hover:bg-primary-container/10 transition" title="{{ __t('common.edit') }}" aria-label="{{ __t('common.edit') }}">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>

                            <form method="POST" action="{{ route('admin.slider.destroy', $slide) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('{{ __t('admin.slider.delete_confirm') }}')" class="p-1.5 rounded-lg text-error hover:bg-error-container/10 transition" title="{{ __t('common.delete') }}" aria-label="{{ __t('common.delete') }}">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('slider-table');
    const tbody = table?.querySelector('tbody');
    if (!tbody) return;

    // Initialize SortableJS
    if (typeof Sortable !== 'undefined') {
        new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-primary-50',
            dragClass: 'shadow-lg opacity-80',
            onEnd: function(evt) {
                const items = [];
                tbody.querySelectorAll('tr[data-id]').forEach((row, index) => {
                    items.push({
                        id: row.dataset.id,
                        sort_order: index + 1
                    });
                });

                fetch('{{ route('admin.slider.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ items })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Reorder failed:', data.message);
                        // Optionally show error toast
                    }
                })
                .catch(err => console.error('Reorder error:', err));
            }
        });
    }
});
</script>
@endpush

@else
<div class="bg-surface-container-lowest rounded-xl shadow-sm p-12 text-center">
    <span class="material-symbols-outlined text-6xl text-outline mb-3 block">slideshow</span>
    <h3 class="font-bold text-lg mb-2">{{ __t('admin.slider.empty_title') }}</h3>
    <p class="text-on-surface-variant text-sm mb-6">{{ __t('admin.slider.empty_desc') }}</p>
    <a href="{{ route('admin.slider.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-medium hover:bg-primary-container shadow-sm transition-all active:scale-95">
        <span class="material-symbols-outlined text-sm">add</span>
        {{ __t('admin.slider.add_slide') }}
    </a>
</div>
@endif
@endsection