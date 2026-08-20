<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملصقات مجمعة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; }
        .label { padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .store-name { font-size: 18px; font-weight: bold; }
        .order-number { font-family: monospace; font-size: 14px; background: #f5f5f5; padding: 6px 12px; border: 1px solid #ccc; }
        .two-col { display: flex; gap: 20px; margin-bottom: 15px; }
        .two-col > div { flex: 1; }
        .section-title { font-weight: bold; font-size: 12px; margin-bottom: 6px; padding-bottom: 3px; border-bottom: 1px solid #ddd; }
        .info-row { margin-bottom: 4px; font-size: 11px; }
        .info-row span:first-child { color: #666; display: inline-block; width: 90px; }
        .info-row span:last-child { font-weight: bold; }
        .products { margin-bottom: 15px; }
        .products table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .products th { background: #f5f5f5; padding: 6px; text-align: right; border: 1px solid #ddd; font-size: 11px; }
        .products td { padding: 6px; border: 1px solid #ddd; font-size: 11px; }
        .footer { border-top: 1px dashed #999; padding-top: 8px; font-size: 10px; color: #666; display: flex; justify-content: space-between; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach($orders as $order)
        <div class="label">
            <div class="header">
                <div class="store-name">{{ config('app.name') }}</div>
                <div class="order-number">#{{ $order->order_number }}</div>
            </div>

            <div class="two-col">
                <div>
                    <div class="section-title">بيانات العميل</div>
                    <div class="info-row"><span>الاسم:</span><span>{{ $order->shippingAddress->name ?? ($order->user?->name ?? 'ضيف') }}</span></div>
                    <div class="info-row"><span>الهاتف:</span><span>{{ $order->shippingAddress->phone ?? $order->user?->phone ?? $order->guest_phone ?? '-' }}</span></div>
                    @if($order->shippingAddress)
                        <div class="info-row"><span>العنوان:</span><span>{{ $order->shippingAddress->address }}</span></div>
                        <div class="info-row"><span>المدينة:</span><span>{{ $order->shippingAddress->city }}</span></div>
                        <div class="info-row"><span>الولاية:</span><span>{{ $order->shippingAddress->state_name ?: ($order->shippingAddress->city ?: '-') }} @if($order->shippingAddress->state_number) (ولاية رقم {{ $order->shippingAddress->state_number }}) @endif</span></div>
                        <div class="info-row"><span>الدولة:</span><span>{{ $order->shippingAddress->country_name ?: 'الجزائر' }}</span></div>
                    @endif
                </div>
                <div>
                    <div class="section-title">ملخص الطلب</div>
                    <div class="products">
                        <table>
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="info-row" style="margin-top:8px;"><span>الإجمالي:</span><span>{{ number_format($order->grand_total, 2) }} {{ currentCurrencySymbol() }}</span></div>
                </div>
            </div>

            <div class="footer">
                <span>طُبعت في {{ now()->format('Y-m-d H:i') }}</span>
                <span>{{ config('app.name') }}</span>
            </div>
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
