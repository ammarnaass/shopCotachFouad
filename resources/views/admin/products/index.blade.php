@extends('admin.layout')

@section('title', __t('admin.products.title'))

@section('page_title', __t('admin.products.title'))

@section('content')
@php
    use App\Models\Catalog\Product;
    $totalProducts = Product::count();
    $activeCount = Product::where('status', 'active')->count();
    $lowStockCount = Product::where('stock', '<', 10)->count();
    $totalStockValue = Product::sum(DB::raw('stock * price'));
@endphp

<!-- Header Section -->
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-headline-md text-headline-md font-bold text-on-surface">{{ __t('admin.products.title') }}</h1>
        <p class="text-on-surface-variant font-body-sm text-body-sm mt-1">{{ __t('admin.products.subtitle') }}</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="bg-primary text-white px-6 py-2.5 rounded-xl font-label-md text-label-md flex items-center gap-2 hover:bg-primary-container active:scale-95 transition-all shadow-sm">
        <span class="material-symbols-outlined text-lg">add_circle</span>
        {{ __t('admin.products.add_new') }}
    </a>
</div>

<!-- Filter Bar -->
<div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm mb-6 border border-outline-variant/30">
    <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-4 w-full">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __t('common.search') }}"
                       class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl pr-10 pl-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary text-label-md transition-all outline-none"/>
                <span class="material-symbols-outlined absolute right-3 top-2.5 text-on-surface-variant text-xl">search</span>
            </div>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-label-md font-bold text-on-surface-variant">{{ __t('common.filter') }}:</span>
            <select name="category_id" class="bg-surface-container-low border border-outline-variant/30 rounded-lg text-body-sm pr-10 pl-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none cursor-pointer">
                <option value="">{{ __t('common.all') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-surface-container-low border border-outline-variant/30 rounded-lg text-body-sm pr-10 pl-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none cursor-pointer">
                <option value="">{{ __t('common.all') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __t('common.active') }}</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __t('common.inactive') }}</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __t('admin.products.draft') }}</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-surface-container-high text-on-surface rounded-lg font-label-md text-label-md hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">filter_list</span>
                {{ __t('common.filter') }}
            </button>
            <a href="{{ route('admin.products.export') }}" class="px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-label-md text-label-md hover:bg-surface-container-low transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">ios_share</span>
                {{ __t('common.export') }}
            </a>
            @if(request()->hasAny(['search', 'status', 'category_id']))
                <a href="{{ route('admin.products.index') }}" class="text-outline text-sm px-3 py-2 hover:text-on-surface transition-colors">{{ __t('admin.products.reset') }}</a>
            @endif
        </div>
    </form>
</div>

<!-- Bulk Action Form & Data Table -->
<form method="POST" action="{{ route('admin.products.bulkAction') }}" id="bulkForm">
    @csrf
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant">
                        <th class="p-4 w-12 text-center">
                            <input class="rounded text-primary focus:ring-primary h-4 w-4 cursor-pointer" type="checkbox" id="selectAll"/>
                        </th>
                        <th class="p-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.products.image') }}</th>
                        <th class="p-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.products.name') }}</th>
                        <th class="p-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.products.sku') }}</th>
                        <th class="p-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.products.price') }}</th>
                        <th class="p-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.products.stock') }}</th>
                        <th class="p-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.products.category') }}</th>
                        <th class="p-4 font-label-md text-label-md text-on-surface-variant text-center">{{ __t('common.status') }}</th>
                        <th class="p-4 font-label-md text-label-md text-on-surface-variant text-center">{{ __t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($products as $product)
                        <tr class="hover:bg-surface-container-low transition-colors group">
                            <td class="p-4 text-center">
                                <input class="product-checkbox rounded text-primary focus:ring-primary h-4 w-4 cursor-pointer" type="checkbox" name="product_ids[]" value="{{ $product->id }}"/>
                            </td>
                            <td class="p-4">
                                <div class="w-12 h-12 rounded-lg bg-surface-container-high overflow-hidden border border-outline-variant flex-shrink-0">
                                    @if($product->primaryImage)
                                        <img src="{{ asset('storage/' . $product->primaryImage->image) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-outline">
                                            <span class="material-symbols-outlined">image</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 font-body-md text-body-md font-medium">
                                <a href="{{ route('admin.products.show', $product) }}" class="font-medium text-on-surface hover:text-primary transition-colors">
                                    {{ $product->name }}
                                </a>
                                @if($product->featured)
                                    <span class="material-symbols-outlined text-sm text-warning align-text-bottom mr-1" style="font-variation-settings:'FILL' 1">star</span>
                                @endif
                            </td>
                            <td class="p-4 font-label-sm text-label-sm text-secondary font-mono">{{ $product->sku ?? '—' }}</td>
                            <td class="p-4 font-body-md text-body-md font-bold">
                                @if($product->sale_price)
                                    <div>
                                        <span class="font-bold text-on-surface">{{ number_format($product->sale_price, 0) }} {{ currentCurrencySymbol() }}</span>
                                        <span class="text-xs text-outline line-through mr-1">{{ number_format($product->price, 0) }} {{ currentCurrencySymbol() }}</span>
                                    </div>
                                @else
                                    <span class="font-semibold text-on-surface">{{ number_format($product->price, 0) }} {{ currentCurrencySymbol() }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $product->stock < 10 ? 'bg-error-container text-on-error-container' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $product->stock < 10 ? 'bg-error' : 'bg-emerald-500' }}"></span>
                                    {{ $product->stock }} {{ __t('admin.products.pieces') }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="bg-secondary-fixed text-on-secondary-fixed px-3 py-1 rounded-full text-[12px] font-bold">
                                    {{ $product->category->name ?? '—' }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold border
                                    @switch($product->status)
                                        @case('active') bg-emerald-50 text-emerald-700 border-emerald-200 @break
                                        @case('inactive') bg-surface-container-high text-on-surface-variant border-outline-variant @break
                                        @case('draft') bg-amber-50 text-amber-700 border-amber-200 @break
                                    @endswitch">
                                    <span class="w-1.5 h-1.5 rounded-full
                                        @switch($product->status)
                                            @case('active') bg-emerald-500 @break
                                            @case('inactive') bg-outline @break
                                            @case('draft') bg-amber-500 @break
                                        @endswitch"></span>
                                    @switch($product->status)
                                        @case('active') {{ __t('common.active') }} @break
                                        @case('inactive') {{ __t('common.inactive') }} @break
                                        @case('draft') {{ __t('admin.products.draft') }} @break
                                    @endswitch
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.products.gallery', $product) }}" class="p-1.5 text-outline hover:text-primary transition-colors" title="{{ __t('admin.products.gallery') }}">
                                        <span class="material-symbols-outlined text-[20px]">photo_library</span>
                                    </a>
                                    <a href="{{ route('admin.products.show', $product) }}" class="p-1.5 text-outline hover:text-primary transition-colors" title="{{ __t('common.preview') }}">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="p-1.5 text-outline hover:text-primary transition-colors" title="{{ __t('common.edit') }}">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.products.delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-outline hover:text-error transition-colors" title="{{ __t('common.delete') }}">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-12 text-center text-on-surface-variant bg-surface-container-lowest">
                                <span class="material-symbols-outlined text-4xl text-outline mb-2 block">inventory_2</span>
                                <p class="text-on-surface-variant font-body-md">{{ __t('admin.products.no_products') }}</p>
                                <a href="{{ route('admin.products.create') }}" class="text-primary hover:underline text-sm mt-2 inline-block">{{ __t('admin.products.add_new') }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="p-4 flex justify-between items-center bg-surface-container-low border-t border-outline-variant">
            <span class="text-body-sm text-on-surface-variant font-medium">{{ __t('admin.products.showing_count') }} {{ $products->count() }} {{ __t('admin.products.of_total') }} {{ $totalProducts }} {{ __t('admin.products.products_label') }}</span>
            @if($products->hasPages())
                <div>
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Action Floating Toolbar (Mockup Style) -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-on-surface text-surface-container-lowest px-6 py-3 rounded-full shadow-2xl flex items-center gap-6 z-50 transform translate-y-24 transition-transform duration-300" id="bulk-toolbar">
        <div class="flex items-center gap-2 border-l border-white/20 pl-4">
            <span class="bg-primary text-white w-6 h-6 rounded-full flex items-center justify-center text-[12px] font-bold" id="selection-count">0</span>
            <span class="font-label-md text-label-md">{{ __t('admin.products.selected_products') }}</span>
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" name="action" value="activate" class="flex items-center gap-1.5 hover:text-primary transition-colors font-label-md text-label-md bg-transparent border-none text-surface-container-lowest cursor-pointer">
                <span class="material-symbols-outlined text-lg">check_circle</span>
                {{ __t('common.active') }}
            </button>
            <button type="submit" name="action" value="deactivate" class="flex items-center gap-1.5 hover:text-primary transition-colors font-label-md text-label-md bg-transparent border-none text-surface-container-lowest cursor-pointer">
                <span class="material-symbols-outlined text-lg">block</span>
                {{ __t('common.inactive') }}
            </button>
            <button type="submit" name="action" value="feature" class="flex items-center gap-1.5 hover:text-warning transition-colors font-label-md text-label-md bg-transparent border-none text-surface-container-lowest cursor-pointer">
                <span class="material-symbols-outlined text-lg">star</span>
                {{ __t('admin.products.feature') }}
            </button>
            <button type="submit" name="action" value="unfeature" class="flex items-center gap-1.5 hover:text-warning transition-colors font-label-md text-label-md bg-transparent border-none text-surface-container-lowest cursor-pointer">
                <span class="material-symbols-outlined text-lg">star_half</span>
                {{ __t('admin.products.unfeature') }}
            </button>
            <button type="submit" name="action" value="delete" onclick="return confirm('{{ __t('admin.products.delete_confirm') }}')" class="flex items-center gap-1.5 hover:text-error transition-colors font-label-md text-label-md bg-transparent border-none text-surface-container-lowest cursor-pointer">
                <span class="material-symbols-outlined text-lg">delete</span>
                {{ __t('common.delete') }}
            </button>
        </div>
        <button type="button" class="text-white/60 hover:text-white bg-transparent border-none cursor-pointer" onclick="clearSelection()">
            <span class="material-symbols-outlined text-xl">close</span>
        </button>
    </div>
</form>

<!-- Summary Bento Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-primary-container/5 p-5 rounded-xl border border-primary/10 flex flex-col justify-between h-32">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-primary">{{ __t('admin.products.total_products') }}</span>
            <span class="material-symbols-outlined text-primary">inventory_2</span>
        </div>
        <span class="text-[32px] font-bold text-on-background">{{ number_format($totalProducts) }}</span>
    </div>
    <div class="bg-green-50 p-5 rounded-xl border border-green-100 flex flex-col justify-between h-32">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-green-700">{{ __t('admin.products.active_products') }}</span>
            <span class="material-symbols-outlined text-green-700">check_circle</span>
        </div>
        <span class="text-[32px] font-bold text-green-800">{{ number_format($activeCount) }}</span>
    </div>
    <div class="bg-red-50 p-5 rounded-xl border border-red-100 flex flex-col justify-between h-32">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-red-700">{{ __t('admin.products.low_stock') }}</span>
            <span class="material-symbols-outlined text-red-700">error</span>
        </div>
        <span class="text-[32px] font-bold text-red-800">{{ number_format($lowStockCount) }}</span>
    </div>
    <div class="bg-surface-container-high p-5 rounded-xl border border-outline-variant flex flex-col justify-between h-32">
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-on-surface-variant">{{ __t('admin.products.total_stock_value') }}</span>
            <span class="material-symbols-outlined text-on-surface-variant">account_balance_wallet</span>
        </div>
        <span class="text-[32px] font-bold text-on-background">{{ number_format($totalStockValue, 0) }} {{ currentCurrencySymbol() }}</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const toolbar = document.getElementById('bulk-toolbar');
    const countSpan = document.getElementById('selection-count');

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    function updateBulkBar() {
        const checked = document.querySelectorAll('.product-checkbox:checked').length;
        if (checked > 0) {
            toolbar.style.transform = 'translate(-50%, 0)';
            toolbar.classList.remove('translate-y-24');
            countSpan.textContent = checked;
        } else {
            toolbar.style.transform = 'translate(-50%, 150%)';
            toolbar.classList.add('translate-y-24');
        }
    }

    function clearSelection() {
        checkboxes.forEach(cb => cb.checked = false);
        if (selectAll) selectAll.checked = false;
        updateBulkBar();
    }

    // Micro-interactions for table rows selection (excluding buttons/links)
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', (e) => {
            if (e.target.tagName !== 'INPUT' && !e.target.closest('a') && !e.target.closest('button') && !e.target.closest('form')) {
                const checkbox = row.querySelector('.product-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateBulkBar();
                }
            }
        });
    });
</script>
@endpush
