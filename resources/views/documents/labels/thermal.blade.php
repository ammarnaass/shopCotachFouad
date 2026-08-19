<style>
    .label-thermal {
        width: 100%;
        max-width: 280px;
        margin: 0 auto;
        padding: 6px;
        background: #fff;
        font-family: 'DejaVu Sans', 'Arial Unicode MS', 'Courier New', Courier, monospace;
        font-size: 10px;
        color: #000;
        border: 1px dashed #000;
    }
</style>

<div class="label-thermal">
    <div style="text-align: center; font-size: 14px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 6px;">
        {{ $store['name'] }}
    </div>

    <div style="font-size: 11px; margin-bottom: 6px;">
        <strong>الطلب:</strong> #{{ $order->order_number }}<br>
        <strong>العميل:</strong> {{ $customer['name'] }}<br>
        <strong>الهاتف:</strong> {{ $customer['phone'] }}<br>
        @if(($customer['email'] ?? '—') !== '—')
        <strong>البريد:</strong> {{ $customer['email'] }}<br>
        @endif
        <strong>الولاية:</strong> {{ $customer['wilaya'] }}<br>
        @if($customer['commune'] && $customer['commune'] !== '—' && $customer['commune'] !== $customer['city'])
        <strong>الدوّارة:</strong> {{ $customer['commune'] }}<br>
        @endif
        <strong>العنوان:</strong> {{ $customer['address'] }}<br>
        @if(($customer['zip'] ?? '—') !== '—')
        <strong>الرمز:</strong> {{ $customer['zip'] }}<br>
        @endif
        @if(($orderMeta['delivery_type'] ?? '—') !== '—')
        <strong>نوع التوصيل:</strong> {{ $orderMeta['delivery_type'] }}<br>
        @endif
        @if(($orderMeta['shipping_company'] ?? '—') !== '—')
        <strong>شركة الشحن:</strong> {{ $orderMeta['shipping_company'] }}<br>
        @endif
    </div>

    <div style="font-size: 10px; margin-bottom: 6px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0;">
        @foreach($items as $item)
            @php
                $formattedOptions = is_array($item->options_summary)
                    ? collect($item->options_summary)
                        ->map(fn (array $option) => ($option['label'] ?? 'خيار').': '.($option['value'] ?? '—'))
                        ->implode(' | ')
                    : null;
            @endphp
            <div style="margin-bottom: 3px;">
                <strong>{{ $item->product_name }}</strong> × {{ $item->quantity }}<br>
                @if($formattedOptions)
                    {{ $formattedOptions }}<br>
                @elseif($item->custom_text)
                    {{ $item->custom_text }}<br>
                @endif
            </div>
        @endforeach
    </div>

    <div style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 4px 0; font-size: 11px; font-weight: bold; text-align: center; margin-bottom: 6px;">
        المطلوب: {{ number_format($order->grand_total, 0) }} {{ $currencySymbol }}
    </div>

    @if(!empty($orderMeta['notes']))
    <div style="font-size: 10px; margin-bottom: 6px;">
        <strong>ملاحظات:</strong> {{ $orderMeta['notes'] }}
    </div>
    @endif

    <x-documents.barcode :value="$barcode_value" height="35" :showValue="true" />
</div>
