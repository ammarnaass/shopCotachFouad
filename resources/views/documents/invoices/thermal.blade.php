@php
    $paperWidth = $template->paper_size === 'thermal_58' ? '58mm' : '80mm';
@endphp
<style>
    .thermal-receipt {
        width: {{ $paperWidth }};
        margin: 0 auto;
        font-family: 'DejaVu Sans', 'Arial Unicode MS', 'Courier New', Courier, monospace;
        font-size: 10px;
        color: #000;
        line-height: 1.3;
        background: #fff;
        padding: 5px;
    }
    .thermal-header { text-align: center; margin-bottom: 8px; border-bottom: 1px dashed #000; padding-bottom: 6px; }
    .thermal-title { font-size: 14px; font-weight: bold; }
    .thermal-meta { font-size: 9px; margin-top: 3px; }
    .thermal-divider { border-top: 1px dashed #000; margin: 6px 0; }
    .thermal-table { width: 100%; border-collapse: collapse; font-size: 9px; }
    .thermal-table th { border-bottom: 1px solid #000; padding: 2px 0; text-align: right; }
    .thermal-table td { padding: 3px 0; border-bottom: 1px dotted #ccc; }
    .thermal-totals { width: 100%; margin-top: 6px; font-size: 10px; }
    .thermal-totals td { padding: 2px 0; }
    .thermal-grand { font-size: 12px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px double #000; padding: 4px 0 !important; }
    .thermal-footer { text-align: center; font-size: 9px; margin-top: 8px; border-top: 1px dashed #000; padding-top: 6px; }
</style>

<div class="thermal-receipt">
    <div class="thermal-header">
        <div class="thermal-title">{{ $store['name'] }}</div>
        @if($store['phone']) <div>{{ $store['phone'] }}</div> @endif
        <div class="thermal-meta">
            فاتورة #: {{ $invoice->invoice_number }}<br>
            الطلب #: {{ $order->order_number }}<br>
            التاريخ: {{ $invoice->issued_at?->format('Y-m-d H:i') ?? $invoice->created_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <div style="margin-bottom: 6px; font-size: 9px;">
        <strong>العميل:</strong> {{ $customer['name'] }}<br>
        <strong>الهاتف:</strong> {{ $customer['phone'] }}<br>
        @if($customer['wilaya'] !== '—') <strong>الولاية:</strong> {{ $customer['wilaya'] }}<br>@endif
        @if($customer['commune'] && $customer['commune'] !== '—' && $customer['commune'] !== $customer['city']) <strong>الدوّارة:</strong> {{ $customer['commune'] }}<br>@endif
        @if($customer['address'] && $customer['address'] !== '—') <strong>العنوان:</strong> {{ $customer['address'] }}@endif
    </div>

    <div class="thermal-divider"></div>

    <table class="thermal-table">
        <thead>
            <tr>
                <th style="width: 50%; text-align: right;">المنتج</th>
                <th style="width: 15%; text-align: center;">ك</th>
                <th style="width: 35%; text-align: left; direction: ltr;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="text-align: right;">{{ $item->product_name }}</td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td style="text-align: left; direction: ltr;">{{ number_format($item->total, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="thermal-totals">
        <tr>
            <td>الإجمالي الفرعي:</td>
            <td style="text-align: left; direction: ltr;">{{ number_format($order->subtotal, 0) }} {{ $currencySymbol }}</td>
        </tr>
        @if($order->discount > 0)
        <tr>
            <td>الخصم:</td>
            <td style="text-align: left; direction: ltr;">-{{ number_format($order->discount, 0) }} {{ $currencySymbol }}</td>
        </tr>
        @endif
        @if($order->shipping_cost > 0)
        <tr>
            <td>الشحن:</td>
            <td style="text-align: left; direction: ltr;">{{ number_format($order->shipping_cost, 0) }} {{ $currencySymbol }}</td>
        </tr>
        @endif
        <tr class="thermal-grand">
            <td>الإجمالي النهائي:</td>
            <td style="text-align: left; direction: ltr;">{{ number_format($order->grand_total, 0) }} {{ $currencySymbol }}</td>
        </tr>
    </table>

    <div class="thermal-footer">
        <x-documents.barcode :value="$order->order_number" height="30" :showValue="true" />
        <div style="margin-top: 4px;">{{ $template->getSetting('thank_you_message', 'شكراً لتسوقكم معنا!') }}</div>
    </div>
</div>
