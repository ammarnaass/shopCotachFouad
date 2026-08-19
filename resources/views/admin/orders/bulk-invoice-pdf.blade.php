<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فواتير مجمعة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; }
        .invoice { padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .store-info h1 { font-size: 22px; margin-bottom: 5px; }
        .store-info p { font-size: 11px; color: #666; margin-bottom: 2px; }
        .invoice-meta { text-align: left; }
        .invoice-meta table { border-collapse: collapse; }
        .invoice-meta td { padding: 4px 10px; font-size: 11px; }
        .invoice-meta td:first-child { color: #666; }
        .invoice-meta td:last-child { font-weight: bold; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 13px; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #ddd; }
        .two-col { display: flex; gap: 20px; }
        .two-col > div { flex: 1; }
        .info-row { margin-bottom: 4px; font-size: 11px; }
        .info-row span:first-child { color: #666; display: inline-block; width: 100px; }
        .info-row span:last-child { font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { background: #f5f5f5; padding: 8px; text-align: right; border: 1px solid #ddd; font-size: 11px; }
        table.items td { padding: 8px; border: 1px solid #ddd; font-size: 11px; }
        table.items td:nth-child(4), table.items td:nth-child(5), table.items td:nth-child(6) { text-align: center; }
        .totals { width: 300px; margin-right: auto; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 8px; font-size: 12px; }
        .totals td:last-child { text-align: left; font-weight: bold; }
        .totals .grand-total { border-top: 2px solid #333; font-size: 14px; color: #000; }
        .page-break { page-break-after: always; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    @foreach($orders as $index => $order)
        <div class="invoice">
            <div class="header">
                <div class="store-info">
                    <h1>{{ config('app.name') }}</h1>
                    <p>الهاتف: {{ config('ecommerce.store.phone', '-') }}</p>
                    <p>العنوان: {{ config('ecommerce.store.address', '-') }}</p>
                </div>
                <div class="invoice-meta">
                    <table>
                        <tr><td>رقم الفاتورة</td><td>#{{ $order->order_number }}</td></tr>
                        <tr><td>التاريخ</td><td>{{ $order->created_at->format('Y-m-d') }}</td></tr>
                        <tr><td>الحالة</td><td>{{ $order->status_name }}</td></tr>
                        <tr><td>طريقة الدفع</td><td>{{ $order->payment->first()->method ?? 'COD' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="two-col section">
                <div>
                    <div class="section-title">بيانات العميل</div>
                    <div class="info-row"><span>الاسم:</span><span>{{ $order->user?->name ?? $order->shippingAddress?->name ?? 'ضيف' }}</span></div>
                    <div class="info-row"><span>الهاتف:</span><span>{{ $order->user?->phone ?? $order->guest_phone ?? $order->shippingAddress?->phone ?? '-' }}</span></div>
                    <div class="info-row"><span>البريد:</span><span>{{ $order->user?->email ?? $order->guest_email ?? '-' }}</span></div>
                </div>
                <div>
                    <div class="section-title">عنوان الشحن</div>
                    @if($order->shippingAddress)
                        <div class="info-row"><span>الاسم:</span><span>{{ $order->shippingAddress->name }}</span></div>
                        <div class="info-row"><span>الهاتف:</span><span>{{ $order->shippingAddress->phone }}</span></div>
                        <div class="info-row"><span>العنوان:</span><span>{{ $order->shippingAddress->address }}</span></div>
                        <div class="info-row"><span>المدينة:</span><span>{{ $order->shippingAddress->city }} @if($order->shippingAddress->state_name)- {{ $order->shippingAddress->state_name }}@endif</span></div>
                        <div class="info-row"><span>الدولة:</span><span>{{ $order->shippingAddress->country_name }}</span></div>
                    @else
                        <p style="font-size:11px;color:#666;">لا يوجد عنوان شحن</p>
                    @endif
                </div>
            </div>

            <div class="section">
                <div class="section-title">المنتجات</div>
                <table class="items">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>SKU</th>
                            <th>الخيارات</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->product_sku ?? '—' }}</td>
                                <td>{{ $item->variant_name ?? ($item->options_summary ? implode(', ', collect($item->options_summary)->pluck('value')->toArray()) : '—') }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="totals">
                    <table>
                        <tr><td>الإجمالي الفرعي</td><td>{{ number_format($order->subtotal, 2) }}</td></tr>
                        @if($order->discount > 0)
                            <tr><td>الخصم</td><td style="color:#d32f2f;">-{{ number_format($order->discount, 2) }}</td></tr>
                        @endif
                        <tr><td>الشحن</td><td>{{ number_format($order->shipping_cost, 2) }}</td></tr>
                        @if($order->tax > 0)
                            <tr><td>الضريبة</td><td>{{ number_format($order->tax, 2) }}</td></tr>
                        @endif
                        @if($order->cod_fee > 0)
                            <tr><td>رسوم الدفع عند الاستلام</td><td>{{ number_format($order->cod_fee, 2) }}</td></tr>
                        @endif
                        <tr class="grand-total"><td>الإجمالي النهائي</td><td>{{ number_format($order->grand_total, 2) }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="footer">
                فاتورة رقم #{{ $order->order_number }} • طُبعت في {{ now()->format('Y-m-d H:i') }} • {{ config('app.name') }}
            </div>
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
