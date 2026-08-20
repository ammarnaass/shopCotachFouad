@extends('admin.layout')

@section('title', $method ? __t('admin.shipping.method_edit_title') : __t('admin.shipping.method_add_title'))

@section('content')
{{-- Breadcrumb --}}
<nav class="flex mb-6 text-sm text-on-surface-variant">
    <a href="{{ route('admin.shipping.index', ['tab' => 'methods']) }}" class="hover:text-primary transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">local_shipping</span>
        إعدادات الشحن
    </a>
    <span class="material-symbols-outlined mx-2 text-xs mt-0.5">chevron_right</span>
    <span class="text-on-surface font-semibold">{{ $method ? __t('admin.shipping.method_edit_title') : __t('admin.shipping.method_add_title') }}</span>
</nav>

{{-- Header Actions --}}
<div class="flex items-center justify-between mb-8">
    @php
        $returnZoneId = request('return_to_zone', $defaultZoneId ?? null);
        $backUrl = $returnZoneId ? route('admin.shipping.zone.edit', $returnZoneId) : route('admin.shipping.index', ['tab' => 'methods']);
        $currentSelectedZoneId = old('zone_id', $method?->shipping_zone_id ?? $method?->zone_id ?? $defaultZoneId ?? request('zone_id'));
        $currentSelectedZone = $zones->firstWhere('id', $currentSelectedZoneId);
    @endphp
    <div>
        <h1 class="text-[32px] font-bold text-on-surface leading-10">{{ $method ? __t('admin.shipping.method_edit_title') : __t('admin.shipping.method_add_title') }}</h1>
        <p class="text-on-surface-variant text-sm mt-1.5">{{ $method ? __t('admin.shipping.method_edit_description') : __t('admin.shipping.method_add_description') }}</p>
        @if($currentSelectedZone)
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-lg text-xs font-bold mt-2">
                <span class="material-symbols-outlined text-sm">location_on</span>
                <span>المنطقة / الولاية التابعة لها: {{ $currentSelectedZone->name }}</span>
            </div>
        @endif
    </div>
    <a href="{{ $backUrl }}" class="flex items-center gap-2 text-primary font-bold hover:underline transition-all">
        <span class="material-symbols-outlined">arrow_back</span>
        <span>{{ $returnZoneId ? 'الرجوع لمنطقة الشحن' : __t('admin.shipping.back_to_list') }}</span>
    </a>
</div>

<form action="{{ $method ? route('admin.shipping.method.update', $method) : route('admin.shipping.method.store') }}" method="POST">
    @csrf
    @if($method) @method('PUT') @endif
    @if($returnZoneId)
        <input type="hidden" name="return_to_zone" value="{{ $returnZoneId }}">
    @endif

    @php
        $ranges = old('weight_ranges', $method?->weight_ranges ?? []);
        if (is_string($ranges)) $ranges = json_decode($ranges, true) ?? [];
        if (empty($ranges)) $ranges = [['max' => '', 'cost' => '']];

        $productIds = old('product_ids', $method?->product_ids ?? []);
        if (is_string($productIds)) $productIds = json_decode($productIds, true) ?? [];
    @endphp

    {{-- Main Form Card --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden mb-8">
        <div class="p-6 border-b border-outline-variant bg-surface-container-low">
            <h3 class="text-lg font-bold flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined">settings_suggest</span>
                {{ __t('admin.shipping.method_basic_details') }}
            </h3>
        </div>
        <div class="p-8 space-y-8">
            {{-- Basic Info Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.method_name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $method?->name) }}" class="form-input @error('name') form-input-error @enderror" placeholder="مثال: شحن عادي، توصيل سريع" required>
                    @error('name')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.type') }} *</label>
                    <div class="relative">
                        <select name="type" id="typeSelect" class="form-select @error('type') form-input-error @enderror" onchange="showTypeFields()" required>
                            <option value="flat_rate" {{ old('type', $method?->type) === 'flat_rate' ? 'selected' : '' }}>{{ __t('admin.shipping.flat_rate') }}</option>
                            <option value="free_shipping" {{ old('type', $method?->type) === 'free_shipping' ? 'selected' : '' }}>{{ __t('admin.shipping.free_shipping') }}</option>
                            <option value="weight_based" {{ old('type', $method?->type) === 'weight_based' ? 'selected' : '' }}>{{ __t('admin.shipping.weight_based') }}</option>
                            <option value="zone_based" {{ old('type', $method?->type) === 'zone_based' ? 'selected' : '' }}>{{ __t('admin.shipping.zone_based') }}</option>
                            <option value="product_based" {{ old('type', $method?->type) === 'product_based' ? 'selected' : '' }}>{{ __t('admin.shipping.product_based') }}</option>
                            <option value="courier_api" {{ old('type', $method?->type) === 'courier_api' ? 'selected' : '' }}>{{ __t('admin.shipping.courier_api') }}</option>
                        </select>
                    </div>
                    @error('type')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.zone') }} *</label>
                    <div class="relative">
                        <select name="zone_id" class="form-select @error('zone_id') form-input-error @enderror" required>
                            <option value="">— اختر منطقة الشحن / الولاية —</option>
                            @foreach($zones as $z)
                                @php
                                    $selectedZoneVal = (string)old('zone_id', $method?->shipping_zone_id ?? $method?->zone_id ?? $defaultZoneId ?? request('zone_id', ''));
                                @endphp
                                <option value="{{ $z->id }}" {{ $selectedZoneVal === (string)$z->id ? 'selected' : '' }}>
                                    {{ $z->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('zone_id')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.carrier') }}</label>
                    <div class="relative">
                        <select name="carrier_id" class="form-select">
                            <option value="">- {{ __t('admin.shipping.self_delivery') }} -</option>
                            @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ old('carrier_id', $method?->carrier_id) == $carrier->id ? 'selected' : '' }}>{{ $carrier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.estimated_delivery') }}</label>
                    <input type="text" name="estimated_days" value="{{ old('estimated_days', $method?->estimated_days) }}" class="form-input" placeholder="مثال: 3-5 أيام">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.display_order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $method?->sort_order ?? 0) }}" class="form-input" min="0">
                </div>
            </div>

            {{-- Type Specific Fields Panel Container --}}

            {{-- 1. Flat Rate, Courier API, Product Based, Zone Based pricing fields --}}
            <div id="flat_rate_fields" class="bg-surface-container-low p-6 rounded-xl space-y-4 border border-outline-variant type-fields" data-type="flat_rate,courier_api,product_based,zone_based">
                <h4 class="font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined">payments</span>
                    {{ __t('admin.shipping.flat_rate_settings') }}
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.shipping_amount') }} *</label>
                        <div class="relative">
                            <input type="number" name="flat_rate_amount" step="0.01" min="0" value="{{ old('flat_rate_amount', $method?->flat_rate_amount) }}" class="form-input text-left pl-12" placeholder="0.00">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold text-sm">{{ currentCurrencySymbol() }}</span>
                        </div>
                        @error('flat_rate_amount')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.tax_status') }}</label>
                        <select name="tax_status" class="form-select">
                            <option value="taxable" {{ old('tax_status', $method?->tax_status) === 'taxable' ? 'selected' : '' }}>{{ __t('admin.shipping.taxable') }}</option>
                            <option value="none" {{ old('tax_status', $method?->tax_status) === 'none' ? 'selected' : '' }}>{{ __t('admin.shipping.no_tax') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 2. Free Shipping Settings --}}
            <div id="free_shipping_fields" class="bg-surface-container-low p-6 rounded-xl space-y-6 border border-outline-variant type-fields hidden" data-type="free_shipping">
                <h4 class="font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined">redeem</span>
                    {{ __t('admin.shipping.free_shipping_settings') }}
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.free_shipping_min') }} ({{ currentCurrencySymbol() }})</label>
                        <div class="relative">
                            <input type="number" name="free_shipping_min" step="0.01" min="0" value="{{ old('free_shipping_min', $method?->free_shipping_min) }}" class="form-input text-left pl-12" placeholder="200.00">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold text-sm">{{ currentCurrencySymbol() }}</span>
                        </div>
                        @error('free_shipping_min')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.free_shipping_requires') }}</label>
                        <select name="free_shipping_requires" class="form-select">
                            <option value="min_amount" {{ old('free_shipping_requires', $method?->free_shipping_requires) === 'min_amount' ? 'selected' : '' }}>{{ __t('admin.shipping.min_amount_only') }}</option>
                            <option value="coupon" {{ old('free_shipping_requires', $method?->free_shipping_requires) === 'coupon' ? 'selected' : '' }}>{{ __t('admin.shipping.valid_coupon') }}</option>
                            <option value="both" {{ old('free_shipping_requires', $method?->free_shipping_requires) === 'both' ? 'selected' : '' }}>{{ __t('admin.shipping.both') }}</option>
                        </select>
                        @error('free_shipping_requires')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 3. Weight Based Settings --}}
            <div id="weight_based_fields" class="bg-surface-container-low p-6 rounded-xl space-y-4 border border-outline-variant type-fields hidden" data-type="weight_based">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h4 class="font-bold text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">scale</span>
                        {{ __t('admin.shipping.weight_categories') }}
                    </h4>
                    <button type="button" onclick="addWeightRange()" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 hover:bg-primary-container active:scale-95 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">add</span>
                        إضافة نطاق جديد
                    </button>
                </div>
                <div class="overflow-x-auto rounded-lg border border-outline-variant bg-white">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low text-on-surface-variant text-xs border-b border-outline-variant">
                                <th class="px-6 py-3 font-bold">{{ __t('admin.shipping.max_weight_kg') }}</th>
                                <th class="px-6 py-3 font-bold">{{ __t('admin.shipping.total_cost') }} ({{ currentCurrencySymbol() }})</th>
                                <th class="px-6 py-3 font-bold text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="weightRanges">
                            @foreach($ranges as $i => $range)
                                <tr class="hover:bg-surface-container-low/50 transition-colors weight-range-row border-b border-outline-variant/30">
                                    <td class="px-6 py-4">
                                        <input type="number" name="weight_ranges[{{ $i }}][max]" step="0.1" min="0" value="{{ $range['max'] ?? '' }}" class="form-input max-w-[200px]" placeholder="مثال: 5">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="weight_ranges[{{ $i }}][cost]" step="0.01" min="0" value="{{ $range['cost'] ?? '' }}" class="form-input max-w-[200px]" placeholder="مثال: 25">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button type="button" onclick="this.closest('.weight-range-row').remove()" class="text-error hover:bg-error-container/30 rounded-full p-2 transition-all" title="{{ __t('admin.shipping.delete_range') }}">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 4. Product-based settings --}}
            <div id="product_based_fields" class="bg-surface-container-low p-6 rounded-xl space-y-4 border border-outline-variant type-fields hidden" data-type="product_based">
                <h4 class="font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined">inventory_2</span>
                    {{ __t('admin.shipping.included_products') }}
                </h4>
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-on-surface-variant">{{ __t('admin.shipping.select_products') }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 p-4 bg-white rounded-lg border border-outline-variant max-h-60 overflow-y-auto">
                        @foreach($products as $product)
                            <label class="flex items-center gap-2.5 p-2 rounded hover:bg-surface-container-low cursor-pointer">
                                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="form-checkbox" {{ in_array($product->id, $productIds) ? 'checked' : '' }}>
                                <span class="text-sm text-on-surface font-medium">{{ $product->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 5. Courier API Settings --}}
            <div id="courier_api_fields" class="bg-surface-container-low p-6 rounded-xl space-y-4 border border-outline-variant type-fields hidden" data-type="courier_api">
                <h4 class="font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined">api</span>
                    {{ __t('admin.shipping.api_settings') }}
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface-variant">API Endpoint</label>
                        <input type="url" name="api_settings[endpoint]" value="{{ old('api_settings.endpoint', $method?->api_settings['endpoint'] ?? '') }}" class="form-input text-left" placeholder="https://api.carrier.com/v1/rates">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface-variant">API Key / Token</label>
                        <input type="text" name="api_settings[key]" value="{{ old('api_settings.key', $method?->api_settings['key'] ?? '') }}" class="form-input text-left" placeholder="Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...">
                    </div>
                </div>
            </div>

            {{-- Toggles --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-outline-variant">
                <div class="flex items-center justify-between p-5 bg-surface-container-low rounded-xl border border-outline-variant">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                        <div>
                            <p class="font-bold text-on-surface">{{ __t('admin.shipping.enable_tax') }}</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">{{ __t('admin.shipping.tax_description') }}</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="taxToggle" class="sr-only peer" {{ old('tax_status', $method?->tax_status ?? 'taxable') === 'taxable' ? 'checked' : '' }}>
                        <input type="hidden" name="tax_status" id="taxStatusInput" value="{{ old('tax_status', $method?->tax_status ?? 'taxable') }}">
                        <div class="w-11 h-6 bg-outline-variant peer-focus:outline-none rounded-full peer peer-checked:bg-primary peer-checked:after:-translate-x-full after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-5 bg-surface-container-low rounded-xl border border-outline-variant">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary">visibility</span>
                        <div>
                            <p class="font-bold text-on-surface">{{ __t('admin.shipping.enable_method') }}</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">{{ __t('admin.shipping.method_visibility') }}</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="status" value="1" class="sr-only peer" {{ old('status', $method?->status ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-outline-variant peer-focus:outline-none rounded-full peer peer-checked:bg-primary peer-checked:after:-translate-x-full after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Form Footer Actions --}}
        <div class="p-6 bg-surface-container-low border-t border-outline-variant flex items-center justify-between gap-4">
            <a href="{{ $backUrl }}" class="px-6 py-3 rounded-lg border border-outline text-on-surface-variant hover:bg-surface-container-high transition-all font-bold text-sm">
                إلغاء
            </a>
            <button type="submit" class="bg-primary text-white px-8 py-3 rounded-lg flex items-center gap-2 hover:bg-primary-container transition-all shadow-sm active:scale-95 font-bold text-sm">
                <span class="material-symbols-outlined">save</span>
                <span>{{ $method ? 'تحديث طريقة الشحن' : 'حفظ طريقة الشحن' }}</span>
            </button>
        </div>
    </div>

    {{-- Bento Styled Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-primary/5 p-5 rounded-xl border border-primary/20 flex flex-col gap-3">
            <span class="material-symbols-outlined text-primary bg-primary/10 w-fit p-2 rounded-lg">info</span>
            <h5 class="font-bold text-on-surface text-sm">{{ __t('admin.shipping.shipping_tip') }}</h5>
            <p class="text-xs text-on-surface-variant leading-relaxed">أظهرت الدراسات أن توفير خيار "الشحن المجاني" يزيد من نسبة إتمام الطلبات بنسبة 30%.</p>
        </div>
        <div class="bg-secondary/5 p-5 rounded-xl border border-secondary/20 flex flex-col gap-3">
            <span class="material-symbols-outlined text-secondary bg-secondary/10 w-fit p-2 rounded-lg">speed</span>
            <h5 class="font-bold text-on-surface text-sm">{{ __t('admin.shipping.fast_delivery') }}</h5>
            <p class="text-xs text-on-surface-variant leading-relaxed">يفضل العملاء استلام طلباتهم في غضون يومين إلى ثلاثة أيام كحد أقصى.</p>
        </div>
        <div class="bg-tertiary/5 p-5 rounded-xl border border-tertiary/20 flex flex-col gap-3">
            <span class="material-symbols-outlined text-tertiary bg-tertiary/10 w-fit p-2 rounded-lg">security</span>
            <h5 class="font-bold text-on-surface text-sm">{{ __t('admin.shipping.insurance') }}</h5>
            <p class="text-xs text-on-surface-variant leading-relaxed">تأكد من تفعيل خيار تتبع الشحنة مع شركات الشحن المختارة لضمان رضا العميل.</p>
        </div>
    </div>
</form>

@push('scripts')
<script>
function showTypeFields() {
    const type = document.getElementById('typeSelect').value;
    document.querySelectorAll('.type-fields').forEach(el => {
        const types = el.dataset.type.split(',');
        if (types.includes(type)) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
}

// Set initial view state on load
document.addEventListener('DOMContentLoaded', function() {
    showTypeFields();

    const taxToggle = document.getElementById('taxToggle');
    const taxStatusInput = document.getElementById('taxStatusInput');
    if (taxToggle && taxStatusInput) {
        taxToggle.addEventListener('change', function() {
            taxStatusInput.value = this.checked ? 'taxable' : 'none';
        });
    }
});

let rangeIndex = {{ count($ranges) }};
function addWeightRange() {
    const html = `
        <tr class="hover:bg-surface-container-low/50 transition-colors weight-range-row border-b border-outline-variant/30">
            <td class="px-6 py-4">
                <input type="number" name="weight_ranges[${rangeIndex}][max]" step="0.1" min="0" class="form-input max-w-[200px]" placeholder="مثال: 5">
            </td>
            <td class="px-6 py-4">
                <input type="number" name="weight_ranges[${rangeIndex}][cost]" step="0.01" min="0" class="form-input max-w-[200px]" placeholder="مثال: 25">
            </td>
            <td class="px-6 py-4 text-center">
                <button type="button" onclick="this.closest('.weight-range-row').remove()" class="text-error hover:bg-error-container/30 rounded-full p-2 transition-all" title="{{ __t('admin.shipping.delete_range') }}">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </td>
        </tr>
    `;
    document.getElementById('weightRanges').insertAdjacentHTML('beforeend', html);
    rangeIndex++;
}
</script>
@endpush
@endsection
