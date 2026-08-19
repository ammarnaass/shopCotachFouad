@php
    $showBarcode = $template->getSetting('show_barcode', true);
    $showQr      = $template->getSetting('show_qr', false);
    $showTotal   = $template->getSetting('show_total', true);
    $showPayment = $template->getSetting('show_payment_method', true);
@endphp
<style>
    .label-classic {
        width: 100%;
        max-width: 380px;
        margin: 0 auto;
        border: 2px solid #000;
        padding: 12px;
        background: #fff;
        font-family: 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif;
        color: #000;
    }
    .label-products-table { width: 100%; border-collapse: collapse; font-size: 10px; margin: 8px 0; }
    .label-products-table th { border-bottom: 1px solid #000; padding: 3px 4px; text-align: right; background: #f0f0f0; }
    .label-products-table td { padding: 4px; border-bottom: 1px solid #eee; }
    .label-total-bar { background: #000; color: #fff; padding: 6px 10px; font-size: 13px; font-weight: bold; text-align: center; border-radius: 4px; margin-top: 8px; }
</style>

<div class="label-classic">
    <x-documents.label-header :store="$store" :order="$order" :template="$template" :pdfMode="($pdf_mode ?? false)" />
    <x-documents.label-customer :customer="$customer" :order="$order" :template="$template" :orderMeta="$orderMeta" />

    <table class="label-products-table">
        <thead>
            <tr>
                <th style="width: 75%;">المنتج</th>
                <th style="width: 25%; text-align: center;">الكمية</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    <div>{{ $item->product_name }}</div>
                    @php
                        $formattedOptions = is_array($item->options_summary)
                            ? collect($item->options_summary)
                                ->map(fn (array $option) => ($option['label'] ?? 'خيار').': '.($option['value'] ?? '—'))
                                ->implode(' | ')
                            : null;
                    @endphp
                    @if($formattedOptions)
                        <div style="font-size: 9px; color: #475569; margin-top: 2px;">{{ $formattedOptions }}</div>
                    @elseif($item->custom_text)
                        <div style="font-size: 9px; color: #475569; margin-top: 2px;">{{ $item->custom_text }}</div>
                    @endif
                </td>
                <td style="text-align: center; font-weight: bold;">{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($showTotal || $showPayment)
        <div class="label-total-bar">
            @if($showTotal)
                المبلغ المطلوب: {{ number_format($order->grand_total, 0) }} {{ $currencySymbol }}
            @endif
            @if($showPayment && $payment)
                <span style="font-size: 10px; font-weight: normal; opacity: 0.9;">({{ $payment->method ?? 'COD' }})</span>
            @endif
        </div>
    @endif

    @if($showBarcode)
        <div style="margin-top: 10px;">
            <x-documents.barcode :value="$barcode_value" height="40" :showValue="true" />
        </div>
    @endif

    @if($showQr)
        <div style="margin-top: 6px;">
            <x-documents.qr-code :value="route('admin.orders.show', $order->id)" :size="60" />
        </div>
    @endif
</div>
