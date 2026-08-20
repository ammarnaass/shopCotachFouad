@extends('admin.layout')

@section('title', __t('admin.orders.details') . ' ' . $order->order_number)

@section('page_title', __t('admin.orders.details') . ' #' . $order->order_number)

@section('content')
@push('styles')
<style>
    .card-glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(226, 232, 240, 0.5); }
</style>
@endpush

@php
    $shippingLabel = \App\Models\Shipping\ShippingLabel::where('order_id', $order->id)->first();
    $invoiceTemplates = \App\Models\Documents\InvoiceTemplate::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
    $labelTemplates   = \App\Models\Documents\LabelTemplate::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
@endphp

{{-- Breadcrumb & Actions --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <nav class="flex gap-2 text-outline text-sm mb-1">
            <a href="{{ route('admin.orders.index') }}" class="hover:text-primary transition-colors">{{ __t('admin.orders.title') }}</a>
            <span>/</span>
            <span class="text-primary font-bold">#{{ $order->order_number }}</span>
        </nav>
        <h3 class="text-xl font-bold text-on-surface flex items-center gap-2">
            {{ __t('admin.orders.details') }} #{{ $order->order_number }}
            @if($order->is_instant_buy)
                <span class="px-2 py-0.5 bg-tertiary-fixed text-tertiary text-[10px] rounded-lg font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">bolt</span> {{ __t('admin.orders.instant_order') }}
                </span>
            @endif
        </h3>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.orders.index') }}" class="px-5 py-2.5 bg-surface text-primary border border-primary rounded-xl text-sm font-medium hover:bg-primary/5 transition-all flex items-center gap-2 active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            {{ __t('admin.orders.back_to_list') }}
        </a>
    </div>
</div>

{{-- Status Banner --}}
<div class="card-glass shadow-sm rounded-2xl p-6 mb-8 flex flex-wrap items-center justify-between gap-6 border-r-4 border-primary">
    <div class="flex items-center gap-5">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary text-[28px]">
                @switch($order->status)
                    @case('pending') new_releases @break
                    @case('confirmed') fact_check @break
                    @case('processing') sync @break
                    @case('shipped') local_shipping @break
                    @case('delivered') check_circle @break
                    @case('cancelled') cancel @break
                    @default new_releases
                @endswitch
            </span>
        </div>
        <div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-primary text-white text-[12px] font-bold rounded-full">{{ $order->status_name }}</span>
                <span class="text-outline text-sm">{{ $order->created_at->format('d/m/Y') }} {{ __t('admin.orders.at_time') }} {{ $order->created_at->format('H:i') }}</span>
            </div>
            <p class="text-on-surface-variant text-sm mt-1">
                @if($order->status === 'pending') {{ __t('admin.orders.status_pending_desc') }}
                @elseif($order->status === 'confirmed') {{ __t('admin.orders.status_confirmed_desc') }}
                @elseif($order->status === 'processing') {{ __t('admin.orders.status_processing_desc') }}
                @elseif($order->status === 'shipped') {{ __t('admin.orders.status_shipped_desc') }}
                @elseif($order->status === 'delivered') {{ __t('admin.orders.status_delivered_desc') }}
                @elseif($order->status === 'cancelled') {{ __t('admin.orders.status_cancelled_desc') }}
                @else {{ __t('admin.orders.status_label') }}: {{ $order->status_name }}
                @endif
            </p>
        </div>
    </div>
    
    <div class="flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="flex flex-wrap items-center gap-3">
            @csrf
            <div class="relative">
                <select name="status" required class="appearance-none pr-4 pl-10 py-2.5 bg-primary text-white border-none rounded-xl text-sm font-medium focus:ring-0 cursor-pointer">
                    @foreach(\App\Models\Order\Order::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white pointer-events-none">expand_more</span>
            </div>
            <input type="text" name="note" placeholder="{{ __t('admin.orders.change_reason') }}..." class="px-4 py-2.5 bg-white text-on-surface border border-outline-variant rounded-xl text-sm placeholder:text-outline focus:ring-2 focus:ring-primary outline-none">
            <button type="submit" class="px-4 py-2.5 bg-primary-container text-white rounded-xl text-sm font-medium hover:opacity-90 transition-all flex items-center gap-2 active:scale-95">
                <span class="material-symbols-outlined text-[20px]">sync</span>
                {{ __t('common.update') }}
            </button>
        </form>

        <div class="h-8 w-px bg-outline-variant mx-1"></div>

        @if($shippingLabel)
            <a href="{{ route('admin.shipping.label.pdf', $shippingLabel) }}" target="_blank" class="px-4 py-2.5 bg-white text-on-surface border border-outline-variant rounded-xl font-label-md text-label-md hover:bg-surface-container-low transition-all flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[20px]">print</span>
                {{ __t('admin.orders.print_label') }}
            </a>
        @endif

        {{-- Invoice Printing Dropdown --}}
        <div class="relative inline-block text-right" x-data="{ open: false, templateId: '' }" @click.outside="open = false">
            <button @click="open = !open" type="button" class="px-4 py-2.5 bg-white text-on-surface border border-outline-variant rounded-xl font-label-md text-label-md hover:bg-surface-container-low transition-all flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[20px] text-primary">receipt</span>
                الفاتورة
                <span class="material-symbols-outlined text-[16px]">expand_more</span>
            </button>
            <div x-show="open" x-cloak class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-outline-variant z-50 py-1 text-xs">
                @if($invoiceTemplates->isNotEmpty())
                    <div class="px-3 py-2 border-b border-outline-variant">
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">قالب الفاتورة</label>
                        <select x-model.number="templateId" class="w-full p-1.5 border border-outline-variant rounded-lg text-xs bg-white">
                            @foreach($invoiceTemplates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}@if($tpl->is_default) (افتراضي)@endif</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <a :href="templateId ? '{{ route('admin.orders.invoice', ['order' => $order->id, 'print' => 1]) }}&template_id=' + templateId : '{{ route('admin.orders.invoice', ['order' => $order->id, 'print' => 1]) }}'" target="_blank" class="flex items-center gap-2 px-4 py-2 hover:bg-surface-container-low text-on-surface font-medium">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    معاينة / طباعة
                </a>
                <a :href="templateId ? '{{ route('admin.orders.invoice', ['order' => $order->id]) }}?template_id=' + templateId : '{{ route('admin.orders.invoice', ['order' => $order->id]) }}'" class="flex items-center gap-2 px-4 py-2 hover:bg-surface-container-low text-on-surface font-medium">
                    <span class="material-symbols-outlined text-[16px]">download</span>
                    تحميل PDF
                </a>
            </div>
        </div>

        {{-- Label Printing Dropdown --}}
        <div class="relative inline-block text-right" x-data="{ open: false, templateId: '' }" @click.outside="open = false">
            <button @click="open = !open" type="button" class="px-4 py-2.5 bg-white text-on-surface border border-outline-variant rounded-xl font-label-md text-label-md hover:bg-surface-container-low transition-all flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[20px] text-primary">label</span>
                ملصق الشحن
                <span class="material-symbols-outlined text-[16px]">expand_more</span>
            </button>
            <div x-show="open" x-cloak class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-outline-variant z-50 py-1 text-xs">
                @if($labelTemplates->isNotEmpty())
                    <div class="px-3 py-2 border-b border-outline-variant">
                        <label class="block text-[11px] font-bold text-on-surface-variant mb-1">قالب الملصق</label>
                        <select x-model.number="templateId" class="w-full p-1.5 border border-outline-variant rounded-lg text-xs bg-white">
                            @foreach($labelTemplates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}@if($tpl->is_default) (افتراضي)@endif</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <a :href="templateId ? '{{ route('admin.orders.label', ['order' => $order->id, 'print' => 1]) }}&template_id=' + templateId : '{{ route('admin.orders.label', ['order' => $order->id, 'print' => 1]) }}'" target="_blank" class="flex items-center gap-2 px-4 py-2 hover:bg-surface-container-low text-on-surface font-medium">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    معاينة / طباعة
                </a>
                <a :href="templateId ? '{{ route('admin.orders.label', ['order' => $order->id]) }}?template_id=' + templateId : '{{ route('admin.orders.label', ['order' => $order->id]) }}'" class="flex items-center gap-2 px-4 py-2 hover:bg-surface-container-low text-on-surface font-medium">
                    <span class="material-symbols-outlined text-[16px]">download</span>
                    تحميل PDF
                </a>
            </div>
        </div>

        @if($order->canBeCancelled())
            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('{{ __t('admin.orders.cancel_confirm') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 bg-error/10 text-error border border-error/20 rounded-xl font-label-md text-label-md hover:bg-error hover:text-white transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                    {{ __t('admin.orders.cancel_order') }}
                </button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    <!-- Right Column: Order Details (8 columns) -->
    <div class="col-span-12 lg:col-span-8 space-y-6">
        <!-- Product Table -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-outline-variant/30">
            <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center">
                <h4 class="font-title-lg text-title-lg flex items-center gap-2 font-bold text-on-surface">
                    <span class="material-symbols-outlined text-primary">shopping_bag</span>
                    {{ __t('admin.orders.order_contents') }}
                </h4>
                <span class="text-outline text-body-sm">{{ $order->items->count() }} {{ __t('admin.orders.product_unit') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low">
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.orders.product') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.orders.options') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant text-center">{{ __t('admin.orders.quantity') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant">{{ __t('admin.orders.price') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($order->items as $item)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-xl bg-surface-container-low p-1 border border-outline-variant flex-shrink-0">
                                            @if($item->product && $item->product->primaryImage)
                                                <img src="{{ asset('storage/' . $item->product->primaryImage->image) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover rounded-lg">
                                            @else
                                                <div class="w-full h-full rounded-lg bg-surface-container-high flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-outline">inventory_2</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-headline-sm text-headline-sm text-on-surface font-bold">{{ $item->product_name }}</p>
                                            <p class="text-outline text-[12px]">SKU: {{ $item->sku ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->variant_name)
                                        <span class="px-2 py-0.5 bg-surface-container-high rounded text-[12px] text-on-surface-variant">{{ $item->variant_name }}</span>
                                    @else
                                        <span class="text-outline text-[12px]">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-lg">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 font-headline-sm text-headline-sm text-primary font-bold">{{ number_format($item->price, 0) }} {{ currentCurrencySymbol() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Summary & Shipping Info (Side by Side) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Financial Summary -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-outline-variant/30">
                <h4 class="font-title-lg text-title-lg mb-6 flex items-center gap-2 border-b border-outline-variant pb-4 font-bold text-on-surface">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    {{ __t('admin.orders.cost_summary') }}
                </h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-body-md">
                        <span class="text-on-surface-variant">{{ __t('admin.orders.subtotal') }}</span>
                        <span class="font-medium text-on-surface">{{ number_format($order->subtotal, 0) }} {{ currentCurrencySymbol() }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between items-center text-body-md">
                            <span class="text-on-surface-variant flex items-center gap-2">
                                {{ __t('admin.orders.discount') }} 
                                @if($order->coupon)
                                    <span class="px-2 py-0.5 bg-tertiary-fixed text-tertiary text-[10px] rounded-lg font-bold">{{ $order->coupon->code }}</span>
                                @endif
                            </span>
                            <span class="text-error font-medium">-{{ number_format($order->discount, 0) }} {{ currentCurrencySymbol() }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center text-body-md">
                        <span class="text-on-surface-variant">{{ __t('admin.orders.shipping_cost') }}</span>
                        <span class="font-medium text-on-surface">{{ number_format($order->shipping_cost, 0) }} {{ currentCurrencySymbol() }}</span>
                    </div>
                    @if($order->tax > 0)
                        <div class="flex justify-between items-center text-body-md">
                            <span class="text-on-surface-variant">{{ __t('admin.orders.tax') }}</span>
                            <span class="font-medium text-on-surface">{{ number_format($order->tax, 0) }} {{ currentCurrencySymbol() }}</span>
                        </div>
                    @endif
                    @if($order->cod_fee > 0)
                        <div class="flex justify-between items-center text-body-md">
                            <span class="text-on-surface-variant">{{ __t('admin.orders.cod_fee') }}</span>
                            <span class="font-medium text-on-surface">{{ number_format($order->cod_fee, 0) }} {{ currentCurrencySymbol() }}</span>
                        </div>
                    @endif
                    <div class="pt-4 border-t border-dashed border-outline-variant flex justify-between items-center">
                        <span class="font-headline-sm text-headline-sm text-on-surface font-bold">{{ __t('admin.orders.grand_total') }}</span>
                        <span class="text-display-lg font-display-lg text-primary font-bold">{{ number_format($order->grand_total, 0) }} {{ currentCurrencySymbol() }}</span>
                    </div>
                    <div class="mt-4 p-3 bg-surface-container-low rounded-xl flex items-center justify-between">
                        <span class="text-body-sm text-on-surface-variant">{{ __t('admin.orders.payment_method') }}:</span>
                        <span class="font-label-md text-label-md flex items-center gap-1 font-bold text-on-surface">
                            @php $payment = $order->payment->first(); @endphp
                            {{ $payment->method ?? __t('admin.orders.cod') }}
                            <span class="material-symbols-outlined text-primary text-[18px]">payments</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-outline-variant/30">
                <h4 class="font-title-lg text-title-lg mb-6 flex items-center gap-2 border-b border-outline-variant pb-4 font-bold text-on-surface">
                    <span class="material-symbols-outlined text-primary">distance</span>
                    {{ __t('admin.orders.shipping_info') }}
                </h4>
                <div class="space-y-6">
                    @if($order->shippingCompany)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-outline text-body-sm mb-1">{{ __t('admin.orders.shipping_company') }}</p>
                                <div class="flex items-center gap-3">
                                    <span class="font-semibold text-on-surface">{{ $order->shippingCompany->name }}</span>
                                    @if($shippingLabel)
                                        <a class="text-primary hover:underline text-label-md" href="{{ route('admin.shipping.label.show', $shippingLabel) }}">{{ __t('admin.orders.track_package') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($order->tracking_number)
                        <div>
                            <p class="text-outline text-body-sm mb-1">{{ __t('admin.orders.tracking_number') }}</p>
                            <div class="flex items-center justify-between p-3 bg-surface-container-low rounded-xl border border-outline-variant/30">
                                <span class="font-mono font-bold tracking-wider text-primary">{{ $order->tracking_number }}</span>
                                <button onclick="navigator.clipboard.writeText('{{ $order->tracking_number }}')" class="material-symbols-outlined text-outline hover:text-primary transition-colors">content_copy</button>
                            </div>
                        </div>
                    @endif
                    @if(!$order->shippingCompany && !$order->tracking_number)
                        <div class="text-center py-6">
                            <p class="text-on-surface-variant text-sm">{{ __t('admin.orders.no_shipping_info') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Update Log -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-outline-variant/30">
            <h4 class="font-title-lg text-title-lg mb-8 flex items-center gap-2 font-bold text-on-surface">
                <span class="material-symbols-outlined text-primary">history_edu</span>
                {{ __t('admin.orders.update_log') }}
            </h4>
            <div class="relative space-y-8 pr-4">
                <div class="absolute right-[19px] top-2 bottom-2 w-0.5 bg-outline-variant/50"></div>
                
                @php
                    $statusIcons = [
                        'pending' => 'add_task',
                        'confirmed' => 'fact_check',
                        'processing' => 'sync',
                        'shipped' => 'local_shipping',
                        'delivered' => 'check_circle',
                        'cancelled' => 'cancel',
                    ];
                    $statusColors = [
                        'pending' => 'bg-primary text-white',
                        'confirmed' => 'bg-secondary-fixed-dim text-secondary',
                        'processing' => 'bg-secondary-fixed-dim text-secondary',
                        'shipped' => 'bg-secondary-fixed-dim text-secondary',
                        'delivered' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                @endphp

                @forelse($statusHistory as $index => $history)
                    <div class="relative flex gap-6">
                        <div class="w-10 h-10 {{ $statusColors[$history->status] ?? 'bg-surface-container-high text-outline' }} rounded-full flex items-center justify-center z-10 shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-[20px]">{{ $statusIcons[$history->status] ?? 'circle' }}</span>
                        </div>
                        <div class="pt-1 flex-1">
                            <p class="font-headline-sm text-headline-sm text-on-surface font-bold">
                                {{ \App\Models\Order\Order::STATUSES[$history->status] ?? $history->status }}
                            </p>
                            <p class="text-on-surface-variant text-body-md mt-1">
                                {{ $history->note ?? __t('admin.orders.auto_update_note') }}
                            </p>
                            <p class="text-outline text-[12px] mt-2">
                                {{ $history->created_at->format('Y-m-d H:i:s') }} • {{ $history->user?->name ?? __t('admin.orders.system') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 opacity-70 text-sm">
                        <span class="material-symbols-outlined text-2xl mb-2 block">history</span>
                        <p>{{ __t('admin.orders.no_history') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Left Column: Customer & Details (4 columns) -->
    <div class="col-span-12 lg:col-span-4 space-y-6">
        <!-- Customer Info -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-outline-variant/30">
            <h4 class="font-title-lg text-title-lg mb-6 flex items-center gap-2 border-b border-outline-variant/30 pb-4 font-bold text-on-surface">
                <span class="material-symbols-outlined text-primary">person</span>
                {{ __t('admin.orders.customer_info') }}
            </h4>
            <div class="flex flex-col items-center text-center mb-6">
                <div class="relative mb-4">
                    <div class="w-24 h-24 rounded-full bg-primary/10 border border-outline-variant/50 shadow-md flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-[48px]" style="font-variation-settings: 'FILL' 1;">person</span>
                    </div>
                </div>
                <h5 class="font-display-lg text-display-lg text-on-surface font-bold">{{ $order->user?->name ?? $order->shippingAddress?->name ?? __t('admin.orders.guest_customer') }}</h5>
            </div>
            <div class="space-y-4">
                @php
                    $phone = $order->user?->phone ?? $order->guest_phone ?? $order->shippingAddress?->phone ?? null;
                    $email = $order->user?->email ?? $order->guest_email ?? null;
                @endphp
                @if($phone)
                    <div class="flex items-center gap-3 p-3 bg-surface rounded-xl border border-outline-variant/30">
                        <span class="material-symbols-outlined text-primary">call</span>
                        <div>
                            <p class="text-[11px] text-outline">{{ __t('admin.orders.phone') }}</p>
                            <p class="text-body-md font-bold" dir="ltr">{{ $phone }}</p>
                        </div>
                    </div>
                @endif
                @if($email)
                    <div class="flex items-center gap-3 p-3 bg-surface rounded-xl border border-outline-variant/30 overflow-hidden">
                        <span class="material-symbols-outlined text-primary">mail</span>
                        <div class="overflow-hidden">
                            <p class="text-[11px] text-outline">{{ __t('admin.orders.shipping_email') }}</p>
                            <p class="text-body-md font-bold truncate">{{ $email }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Delivery Address -->
        @if($order->shippingAddress)
            <div class="bg-white rounded-2xl shadow-sm p-6 overflow-hidden border border-outline-variant/30">
                <div class="flex justify-between items-center mb-6 border-b border-outline-variant pb-4">
                    <h4 class="font-title-lg text-title-lg flex items-center gap-2 font-bold text-on-surface">
                        <span class="material-symbols-outlined text-primary">location_on</span>
                        {{ __t('admin.orders.delivery_address') }}
                    </h4>
                </div>
                <div class="relative w-full h-32 rounded-xl bg-surface-container-low mb-4 overflow-hidden border border-outline-variant flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-[48px] animate-pulse">location_on</span>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-outline text-body-sm">{{ __t('admin.orders.recipient_name') }}</p>
                        <p class="font-headline-sm text-headline-sm text-on-surface font-bold">{{ $order->shippingAddress->name }}</p>
                    </div>
                    <div>
                        <p class="text-outline text-body-sm">{{ __t('admin.orders.address_label') }}</p>
                        <p class="text-body-md text-on-surface font-medium">{{ $order->shippingAddress->address }}</p>
                        <div class="mt-2 space-y-1 text-xs text-on-surface-variant">
                            <p class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-primary">location_city</span>
                                <span><strong>المدينة / البلدية:</strong> {{ $order->shippingAddress->city ?: '-' }}</span>
                            </p>
                            <p class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-primary">map</span>
                                <span><strong>الولاية / المحافظة:</strong> {{ $order->shippingAddress->state_name ?: ($order->shippingAddress->city ?: '-') }} @if($order->shippingAddress->state_number) <span class="inline-block px-1.5 py-0.5 bg-primary/10 text-primary font-bold rounded text-[11px]">(ولاية رقم {{ $order->shippingAddress->state_number }})</span>@endif</span>
                            </p>
                            <p class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px] text-primary">public</span>
                                <span><strong>الدولة:</strong> {{ $order->shippingAddress->country_name ?: 'الجزائر' }} ({{ strtoupper($order->shippingAddress->country_code ?: 'DZ') }})</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Internal Notes -->
        <div class="bg-secondary-fixed text-on-secondary-fixed rounded-2xl shadow-sm p-6 border border-outline-variant/30">
            <h4 class="font-title-lg text-title-lg mb-4 flex items-center gap-2 font-bold text-on-surface">
                <span class="material-symbols-outlined">note_alt</span>
                {{ __t('admin.orders.internal_notes') }}
            </h4>
            <form method="POST" action="{{ route('admin.orders.notes.store', $order) }}" class="mb-4">
                @csrf
                <textarea name="note" required class="w-full h-24 bg-white/50 border-none rounded-xl p-3 text-body-sm placeholder:text-outline focus:ring-2 focus:ring-primary mb-3 outline-none" placeholder="{{ __t('admin.orders.note_placeholder') }}"></textarea>
                <div class="flex items-center justify-between gap-3">
                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                        <input type="checkbox" name="is_customer_note" value="1" class="rounded border-outline-variant text-primary focus:ring-primary">
                        {{ __t('admin.orders.visible_to_customer') }}
                    </label>
                    <button type="submit" class="px-4 py-2 bg-on-secondary-fixed-variant text-white rounded-xl text-sm font-medium hover:opacity-90 transition-all flex items-center gap-2 active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        {{ __t('admin.orders.save') }}
                    </button>
                </div>
            </form>

            <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                @forelse($notes as $note)
                    <div class="p-3 rounded-xl {{ $note->is_customer_note ? 'bg-primary-fixed/20 border border-primary-fixed-dim/30' : 'bg-white/30' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/50 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-on-secondary-fixed-variant text-sm">person</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="font-semibold text-xs text-on-surface">{{ $note->user?->name ?? __t('admin.orders.system_label') }}</span>
                                    @if($note->is_customer_note)
                                        <span class="px-1.5 py-0.5 bg-primary-fixed-dim/30 text-on-secondary-fixed-variant text-[10px] font-bold rounded">{{ __t('admin.orders.visible_to_customer') }}</span>
                                    @endif
                                    <span class="text-xs opacity-70 text-on-surface-variant">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-on-surface">{{ $note->note }}</p>
                            </div>
                            <form action="{{ route('admin.orders.notes.delete', $note) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('{{ __t('admin.orders.delete_note_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="opacity-60 hover:opacity-100 transition-opacity">
                                    <span class="material-symbols-outlined text-[18px] text-error">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 opacity-70 text-sm">
                        <span class="material-symbols-outlined text-2xl mb-2 block">sticky_note_2</span>
                        <p>{{ __t('admin.orders.no_internal_notes') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection