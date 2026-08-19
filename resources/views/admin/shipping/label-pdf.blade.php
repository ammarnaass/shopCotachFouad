<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ __t('admin.shipping.label') }} - {{ $label->tracking_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'dejavusanscondensed', 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        .label { width: 100%; padding: 15px; border: 2px solid #333; }
        .header-table { width: 100%; margin-bottom: 10px; }
        .header-table td { border-bottom: 2px solid #333; padding-bottom: 10px; }
        .store-name { font-size: 18px; font-weight: bold; }
        .tracking-box { display: inline-block; background: #f0f0f0; padding: 8px 15px; border: 1px solid #999; text-align: center; }
        .tracking-box-label { font-size: 9px; color: #666; }
        .tracking-number { font-size: 16px; font-weight: bold; font-family: 'dejavusansmono', monospace; }
        .cols-table { width: 100%; margin-bottom: 10px; }
        .cols-table td { width: 50%; vertical-align: top; padding: 0 6px; }
        .section { margin-bottom: 10px; }
        .section-title { font-weight: bold; font-size: 10px; color: #666; border-bottom: 1px solid #ddd; padding-bottom: 3px; margin-bottom: 5px; }
        .kv { width: 100%; border-collapse: collapse; }
        .kv td { padding: 1px 0; vertical-align: top; }
        .label-col { width: 80px; color: #666; font-size: 10px; }
        .value-col { font-weight: bold; }
        .details-table { width: 100%; border-collapse: collapse; }
        .details-table td { padding: 2px 4px; font-size: 10px; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 3px 6px; font-size: 10px; text-align: right; }
        .items-table th { background: #f5f5f5; }
        .barcode { text-align: center; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #999; }
        .barcode-text { font-family: 'dejavusansmono', monospace; font-size: 12px; letter-spacing: 2px; margin-top: 3px; }
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="label">
        <table class="header-table">
            <tr>
                <td style="text-align: right; vertical-align: middle;">
                    <span class="store-name">{{ config('app.name') }}</span>
                </td>
                <td style="text-align: left; vertical-align: middle;">
                    <span class="tracking-box">
                        <span class="tracking-box-label">{{ __t('admin.shipping.tracking_number') }}</span><br>
                        <span class="tracking-number">{{ $label->tracking_number }}</span>
                    </span>
                </td>
            </tr>
        </table>

        <table class="cols-table">
            <tr>
                <td>
                    <div class="section">
                        <div class="section-title">{{ __t('admin.shipping.sender_info') }}</div>
                        <table class="kv">
                            <tr><td class="label-col">{{ __t('admin.shipping.store') }}:</td><td class="value-col">{{ config('app.name') }}</td></tr>
                            <tr><td class="label-col">{{ __t('admin.shipping.phone') }}:</td><td class="value-col" dir="ltr">{{ config('ecommerce.store.phone', '-') }}</td></tr>
                            <tr><td class="label-col">{{ __t('admin.shipping.address') }}:</td><td class="value-col">{{ config('ecommerce.store.address', '-') }}</td></tr>
                        </table>
                    </div>
                </td>
                <td>
                    <div class="section">
                        <div class="section-title">{{ __t('admin.shipping.recipient_info') }}</div>
                        @if($label->order?->shippingAddress)
                            <table class="kv">
                                <tr><td class="label-col">{{ __t('admin.shipping.name') }}:</td><td class="value-col">{{ $label->order->shippingAddress->name }}</td></tr>
                                <tr><td class="label-col">{{ __t('admin.shipping.phone') }}:</td><td class="value-col" dir="ltr">{{ $label->order->shippingAddress->phone }}</td></tr>
                                <tr><td class="label-col">{{ __t('admin.shipping.address') }}:</td><td class="value-col">{{ $label->order->shippingAddress->address }}</td></tr>
                                <tr><td class="label-col">{{ __t('admin.shipping.city') }}:</td><td class="value-col">{{ $label->order->shippingAddress->city }}</td></tr>
                            </table>
                        @else
                            <div class="value-col">-</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">{{ __t('admin.shipping.shipment_details') }}</div>
            <table class="details-table">
                <tr>
                    <td><span class="label-col">{{ __t('admin.shipping.carrier') }}:</span> <span class="value-col">{{ $label->carrier?->name ?? '-' }}</span></td>
                    <td><span class="label-col">{{ __t('admin.shipping.weight') }}:</span> <span class="value-col">{{ $label->weight ? $label->weight . ' كغ' : '-' }}</span></td>
                    <td><span class="label-col">{{ __t('admin.shipping.cost') }}:</span> <span class="value-col">{{ number_format($label->cost, 2) }} {{ currentCurrencySymbol() }}</span></td>
                    <td><span class="label-col">{{ __t('admin.shipping.order_number') }}:</span> <span class="value-col" dir="ltr">#{{ $label->order?->order_number ?? '-' }}</span></td>
                </tr>
            </table>
        </div>

        @if($label->order?->items?->count())
            <div class="section">
                <div class="section-title">{{ __t('admin.shipping.products') }}</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>{{ __t('admin.shipping.product') }}</th>
                            <th>{{ __t('admin.shipping.quantity') }}</th>
                            <th>{{ __t('admin.shipping.price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($label->order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="barcode" dir="ltr">
            <barcode code="{{ $label->tracking_number }}" type="C128" size="1" height="1" />
            <div class="barcode-text">{{ $label->tracking_number }}</div>
        </div>

        <div class="footer">
            {{ __t('admin.shipping.printed_at') }} {{ now()->format('Y-m-d H:i') }} | {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
