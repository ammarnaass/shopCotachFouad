<style>
    .label-compact {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        border: 1.5px solid #111;
        padding: 8px;
        background: #fff;
        font-family: 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif;
        font-size: 10px;
        color: #000;
    }
    .compact-header { font-size: 13px; font-weight: bold; border-bottom: 1px solid #111; padding-bottom: 4px; margin-bottom: 6px; display: flex; justify-content: space-between; }
</style>

<div class="label-compact">
    <div class="compact-header">
        <span>{{ $store['name'] }}</span>
        <span style="direction: ltr;">#{{ $order->order_number }}</span>
    </div>

    <div style="margin-bottom: 6px;">
        <div style="font-size: 12px; font-weight: bold;">👤 {{ $customer['name'] }}</div>
        <div style="font-size: 12px; font-weight: bold; color: #004ac6; direction: ltr; text-align: right;">📞 {{ $customer['phone'] }}</div>
        @if(($customer['email'] ?? '—') !== '—')
            <div>✉️ {{ $customer['email'] }}</div>
        @endif
        <div>📍 {{ $customer['wilaya'] }}@if($customer['commune'] && $customer['commune'] !== '—' && $customer['commune'] !== $customer['city']) - {{ $customer['commune'] }}@endif - {{ $customer['address'] }}</div>
        @if(($customer['zip'] ?? '—') !== '—')
            <div>ZIP: {{ $customer['zip'] }}</div>
        @endif
        @if(($orderMeta['delivery_type'] ?? '—') !== '—')
            <div>🚚 {{ $orderMeta['delivery_type'] }}</div>
        @endif
        @if(($orderMeta['shipping_company'] ?? '—') !== '—')
            <div>🏷️ {{ $orderMeta['shipping_company'] }}</div>
        @endif
    </div>

    <div style="background: #f0f0f0; padding: 4px 6px; border-radius: 4px; font-size: 10px; margin-bottom: 6px;">
        عدد المنتجات: <strong>{{ $item_count }}</strong> | المطلوب: <strong>{{ number_format($order->grand_total, 0) }} {{ $currencySymbol }}</strong>
    </div>

    <div style="font-size: 9px; line-height: 1.5; margin-bottom: 6px;">
        @foreach($items as $item)
            @php
                $formattedOptions = is_array($item->options_summary)
                    ? collect($item->options_summary)
                        ->map(fn (array $option) => ($option['label'] ?? 'خيار').': '.($option['value'] ?? '—'))
                        ->implode(' | ')
                    : null;
            @endphp
            <div>
                <strong>{{ $item->product_name }}</strong> × {{ $item->quantity }}
                @if($formattedOptions)
                    <div style="color: #475569;">{{ $formattedOptions }}</div>
                @elseif($item->custom_text)
                    <div style="color: #475569;">{{ $item->custom_text }}</div>
                @endif
            </div>
        @endforeach
    </div>

    @if(!empty($orderMeta['notes']))
        <div style="font-size: 9px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 4px 6px; margin-bottom: 6px;">
            <strong>ملاحظات:</strong> {{ $orderMeta['notes'] }}
        </div>
    @endif

    <x-documents.barcode :value="$barcode_value" height="30" :showValue="true" />
</div>
