@extends('admin.layout')

@section('title', __t('admin.orders.title'))

@section('page_title', __t('admin.orders.title'))

@section('content')
<div class="mb-2">
    <p class="text-sm text-on-surface-variant">{{ __t('admin.orders.description') }}</p>
</div>

{{-- Bulk Actions Form --}}
<form method="POST" action="{{ route('admin.orders.bulkAction') }}" id="bulkForm">
    @csrf
    <input type="hidden" name="template_id" id="bulkTemplateId" value="">
    <div class="card p-4 mb-4 hidden" id="bulkBar">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-sm text-on-surface-variant"><span id="selectedCount">0</span> {{ __t('admin.orders.selected_count') }}</span>
            <select name="action" class="px-3 py-1.5 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest" required>
                <option value="">{{ __t('common.select_action') }}</option>
                <option value="update_status">{{ __t('admin.orders.update_status') }}</option>
                <option value="delete">{{ __t('common.delete') }}</option>
                <option value="print_invoices">{{ __t('admin.orders.print_invoices') }}</option>
                <option value="print_customer_labels">{{ __t('admin.orders.print_customer_labels') }}</option>
            </select>
            <div id="statusSelect" class="hidden">
                <select name="status" class="px-3 py-1.5 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest" required>
                    <option value="pending">{{ __t('order_status.pending') }}</option>
                    <option value="confirmed">{{ __t('order_status.confirmed') }}</option>
                    <option value="processing">{{ __t('order_status.processing') }}</option>
                    <option value="shipped">{{ __t('order_status.shipped') }}</option>
                    <option value="delivered">{{ __t('order_status.delivered') }}</option>
                    <option value="cancelled">{{ __t('order_status.cancelled') }}</option>
                </select>
            </div>
            <div id="invoiceTemplateSelect" class="hidden">
                @php
                    $bulkInvoiceTemplates = \App\Models\Documents\InvoiceTemplate::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
                @endphp
                <select id="bulkInvoiceTemplate" class="px-3 py-1.5 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest">
                    <option value="">{{ __t('admin.orders.default_template') }}</option>
                    @foreach($bulkInvoiceTemplates as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }}@if($tpl->is_default) ({{ __t('admin.orders.default_template') }})@endif</option>
                    @endforeach
                </select>
            </div>
            <div id="labelTemplateSelect" class="hidden">
                @php
                    $bulkLabelTemplates = \App\Models\Documents\LabelTemplate::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
                @endphp
                <select id="bulkLabelTemplate" class="px-3 py-1.5 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest">
                    <option value="">{{ __t('admin.orders.default_template') }}</option>
                    @foreach($bulkLabelTemplates as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }}@if($tpl->is_default) ({{ __t('admin.orders.default_template') }})@endif</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('{{ __t('admin.orders.bulk_confirm') }}')">
                <span class="material-symbols-outlined">check</span> {{ __t('common.apply') }}
            </button>
        </div>
    </div>
</form>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card">
        <p class="text-xs font-medium text-on-surface-variant">{{ __t('admin.orders.total_orders') }}</p>
        <p class="text-2xl font-bold mt-1 text-on-surface">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs font-medium text-on-surface-variant">{{ __t('admin.orders.pending_orders') }}</p>
        <p class="text-2xl font-bold mt-1 text-warning">{{ number_format($stats['pending']) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs font-medium text-on-surface-variant">{{ __t('admin.orders.processing_orders') }}</p>
        <p class="text-2xl font-bold mt-1 text-primary">{{ number_format($stats['processing']) }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs font-medium text-on-surface-variant">{{ __t('admin.orders.today_revenue') }}</p>
        <p class="text-2xl font-bold mt-1 text-emerald-600">{{ number_format($stats['today_revenue'], 0) }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="card p-4 mb-6">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="space-y-3">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">{{ __t('common.search') }}</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __t('admin.orders.search_placeholder') }}"
                           class="form-input pr-10">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                </div>
            </div>
            <div>
                <label class="form-label">{{ __t('admin.orders.status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __t('common.all') }}</option>
                    @foreach(\App\Models\Order\Order::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __t('admin.orders.payment_status') }}</label>
                <select name="payment_status" class="form-select">
                    <option value="">{{ __t('common.all') }}</option>
                    @foreach(\App\Models\Order\Order::PAYMENT_STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ request('payment_status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __t('admin.orders.date_from') }}</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">{{ __t('admin.orders.date_to') }}</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <span class="material-symbols-outlined">filter_alt</span> {{ __t('common.filter') }}
            </button>
            @if(request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">{{ __t('common.reset') }}</a>
            @endif
        </div>
    </form>
</div>

{{-- Orders Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table-wrap">
            <thead>
                <tr>
                    <th class="w-10">
                        <input type="checkbox" id="selectAll" class="form-checkbox">
                    </th>
                    <th>{{ __t('admin.orders.order_id') }}</th>
                    <th>{{ __t('admin.orders.customer') }}</th>
                    <th>{{ __t('admin.orders.type') }}</th>
                    <th>{{ __t('admin.orders.items') }}</th>
                    <th>{{ __t('admin.orders.total') }}</th>
                    <th>{{ __t('admin.orders.status') }}</th>
                    <th>{{ __t('admin.orders.payment') }}</th>
                    <th>{{ __t('admin.orders.date') }}</th>
                    <th>{{ __t('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="product-checkbox form-checkbox">
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-semibold text-primary hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td>
                            <div class="font-medium text-on-surface">{{ $order->user?->name ?? $order->shippingAddress?->name ?? __t('admin.orders.guest') }}</div>
                            <div class="text-xs text-on-surface-variant">{{ $order->user?->email ?? $order->guest_email ?? '—' }}</div>
                        </td>
                        <td>
                            @if($order->is_instant_buy)
                                <span class="badge badge-primary">
                                    <span class="material-symbols-outlined text-sm">bolt</span> {{ __t('admin.orders.instant') }}
                                </span>
                            @else
                                <span class="text-on-surface-variant text-xs">{{ __t('admin.orders.normal') }}</span>
                            @endif
                        </td>
                        <td>{{ $order->items->count() }}</td>
                        <td class="font-bold text-on-surface">{{ number_format($order->grand_total, 0) }}</td>
                        <td>
                            <span class="badge
                                @switch($order->status)
                                    @case('pending') badge-warning @break
                                    @case('confirmed') badge-info @break
                                    @case('processing') badge-primary @break
                                    @case('shipped') badge-primary @break
                                    @case('delivered') badge-success @break
                                    @case('cancelled') badge-danger @break
                                @endswitch">
                                {{ $order->status_name }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $order->payment_status === 'paid' ? 'badge-success' : 'badge-danger' }}">
                                {{ $order->payment_status === 'paid' ? __t('admin.orders.paid') : __t('admin.orders.unpaid') }}
                            </span>
                        </td>
                        <td class="text-xs text-on-surface-variant">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.orders.show', $order) }}" class="p-1.5 text-primary hover:bg-primary/10 rounded-lg transition-all" title="{{ __t('common.view') }}">
                                    <span class="material-symbols-outlined">visibility</span>
                                </a>
                                <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="p-1.5 text-secondary hover:bg-secondary/10 rounded-lg transition-all" title="فاتورة">
                                    <span class="material-symbols-outlined">receipt</span>
                                </a>
                                <a href="{{ route('admin.orders.label', $order) }}" target="_blank" class="p-1.5 text-tertiary hover:bg-tertiary/10 rounded-lg transition-all" title="ملصق العميل">
                                    <span class="material-symbols-outlined">local_shipping</span>
                                </a>
                                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.orders.delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-error hover:bg-error-container/30 rounded-lg transition-all" title="{{ __t('common.delete') }}">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-on-surface-variant">
                            <span class="material-symbols-outlined text-4xl text-outline mb-3 block">inventory_2</span>
                            <p>{{ __t('admin.orders.no_orders') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="p-4 border-t border-outline-variant/30">{{ $orders->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const bulkBar = document.getElementById('bulkBar');
    const selectedCount = document.getElementById('selectedCount');
    const statusSelect = document.getElementById('statusSelect');
    const invoiceTemplateSelect = document.getElementById('invoiceTemplateSelect');
    const labelTemplateSelect = document.getElementById('labelTemplateSelect');
    const bulkInvoiceTemplate = document.getElementById('bulkInvoiceTemplate');
    const bulkLabelTemplate = document.getElementById('bulkLabelTemplate');
    const bulkTemplateId = document.getElementById('bulkTemplateId');
    const bulkForm = document.getElementById('bulkForm');
    const actionSelect = bulkForm.querySelector('select[name="action"]');

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    actionSelect?.addEventListener('change', function() {
        statusSelect.classList.toggle('hidden', this.value !== 'update_status');
        invoiceTemplateSelect.classList.toggle('hidden', this.value !== 'print_invoices');
        labelTemplateSelect.classList.toggle('hidden', this.value !== 'print_customer_labels');
        bulkTemplateId.value = '';
    });

    bulkInvoiceTemplate?.addEventListener('change', function() { bulkTemplateId.value = this.value; });
    bulkLabelTemplate?.addEventListener('change', function() { bulkTemplateId.value = this.value; });

    bulkForm?.addEventListener('submit', function(e) {
        const action = actionSelect.value;
        if (action === 'print_invoices') {
            e.preventDefault();
            bulkTemplateId.value = bulkInvoiceTemplate?.value || '';
            bulkForm.action = '{{ route('admin.orders.bulkInvoice') }}';
            bulkForm.submit();
        } else if (action === 'print_customer_labels') {
            e.preventDefault();
            bulkTemplateId.value = bulkLabelTemplate?.value || '';
            bulkForm.action = '{{ route('admin.orders.bulkLabel') }}';
            bulkForm.submit();
        }
    });

    function updateBulkBar() {
        const count = document.querySelectorAll('.product-checkbox:checked').length;
        selectedCount.textContent = count;
        bulkBar.classList.toggle('hidden', count === 0);
    }
</script>
@endpush