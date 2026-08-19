<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة - {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'IBM Plex Sans Arabic', Arial, sans-serif;
            font-size: 12px;
            color: #191b23;
            line-height: 1.7;
        }
        table { border-collapse: collapse; }
        .header {
            background-color: #004ac6;
            color: #ffffff;
            padding: 25px 30px;
        }
        .header table { width: 100%; }
        .header td { vertical-align: top; }
        .header .store-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header .store-details {
            font-size: 11px;
            opacity: 0.9;
            margin-top: 6px;
            line-height: 1.8;
        }
        .header .invoice-meta {
            text-align: left;
            direction: ltr;
        }
        .header .invoice-meta table {
            border-collapse: collapse;
        }
        .header .invoice-meta td {
            padding: 4px 12px;
            font-size: 11px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .header .invoice-meta td:first-child {
            opacity: 0.8;
            font-weight: 500;
        }
        .header .invoice-meta td:last-child {
            font-weight: 700;
        }
        .section { padding: 20px 30px; }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #004ac6;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #dbe1ff;
        }
        .info-table { width: 100%; }
        .info-table td {
            padding: 5px 8px;
            font-size: 11px;
            vertical-align: top;
        }
        .info-table td:first-child {
            color: #434655;
            width: 100px;
            font-weight: 500;
        }
        .info-table td:last-child {
            font-weight: 700;
            color: #191b23;
        }
        .items-table { width: 100%; }
        .items-table th {
            background-color: #dbe1ff;
            color: #003ea8;
            padding: 10px 12px;
            font-size: 11px;
            font-weight: 700;
            text-align: right;
            border: 1px solid #c3c6d7;
        }
        .items-table td {
            padding: 9px 12px;
            font-size: 11px;
            border: 1px solid #c3c6d7;
            text-align: right;
        }
        .items-table td:nth-child(4),
        .items-table td:nth-child(5),
        .items-table td:nth-child(6) {
            text-align: center;
            direction: ltr;
        }
        .totals-table {
            width: 320px;
            margin-left: auto;
            direction: ltr;
            text-align: left;
        }
        .totals-table td {
            padding: 7px 10px;
            font-size: 12px;
        }
        .totals-table td:first-child {
            color: #434655;
        }
        .totals-table td:last-child {
            font-weight: 700;
            text-align: left;
        }
        .totals-table .grand-total {
            border-top: 3px solid #004ac6;
            font-size: 15px;
            font-weight: 700;
            color: #004ac6;
            background-color: #f3f3fe;
        }
        .footer {
            padding: 15px 30px;
            border-top: 1px dashed #c3c6d7;
            text-align: center;
            font-size: 10px;
            color: #737686;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width:50%;">
                    <div class="store-name">{{ config('app.name') }}</div>
                    <div class="store-details">
                        الهاتف: {{ config('ecommerce.store.phone', '-') }}<br>
                        العنوان: {{ config('ecommerce.store.address', '-') }}
                    </div>
                </td>
                <td style="width:50%;" class="invoice-meta">
                    <table>
                        <tr><td>رقم الفاتورة</td><td>#{{ $order->order_number }}</td></tr>
                        <tr><td>التاريخ</td><td>{{ $order->created_at->format('Y-m-d') }}</td></tr>
                        <tr><td>الحالة</td><td>{{ $order->status_name }}</td></tr>
                        <tr><td>طريقة الدفع</td><td>{{ $order->payment->first()->method ?? 'COD' }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="info-table">
            <tr>
                <td style="width:50%; vertical-align: top;">
                    <div class="section-title">بيانات العميل</div>
                    <table class="info-table">
                        <tr><td>الاسم</td><td>{{ $order->user?->name ?? $order->shippingAddress?->name ?? 'ضيف' }}</td></tr>
                        <tr><td>الهاتف</td><td>{{ $order->user?->phone ?? $order->guest_phone ?? $order->shippingAddress?->phone ?? '-' }}</td></tr>
                        <tr><td>البريد</td><td>{{ $order->user?->email ?? $order->guest_email ?? '-' }}</td></tr>
                    </table>
                </td>
                <td style="width:50%; vertical-align: top;">
                    <div class="section-title">عنوان الشحن</div>
                    @if($order->shippingAddress)
                        <table class="info-table">
                            <tr><td>الاسم</td><td>{{ $order->shippingAddress->name }}</td></tr>
                            <tr><td>الهاتف</td><td>{{ $order->shippingAddress->phone }}</td></tr>
                            <tr><td>العنوان</td><td>{{ $order->shippingAddress->address }}</td></tr>
                            <tr><td>المدينة</td><td>{{ $order->shippingAddress->city }} @if($order->shippingAddress->state_name)- {{ $order->shippingAddress->state_name }}@endif</td></tr>
                            <tr><td>الدولة</td><td>{{ $order->shippingAddress->country_name }}</td></tr>
                        </table>
                    @else
                        <p style="font-size:11px; color:#737686;">لا يوجد عنوان شحن</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">المنتجات</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:35%;">المنتج</th>
                    <th style="width:15%;">SKU</th>
                    <th style="width:20%;">الخيارات</th>
                    <th style="width:10%;">الكمية</th>
                    <th style="width:10%;">السعر</th>
                    <th style="width:10%;">الإجمالي</th>
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
        <table class="totals-table">
            <tr><td>الإجمالي الفرعي</td><td>{{ number_format($order->subtotal, 2) }}</td></tr>
            @if($order->discount > 0)
                <tr><td>الخصم</td><td style="color:#ba1a1a;">-{{ number_format($order->discount, 2) }}</td></tr>
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

    <div class="footer">
        فاتورة رقم #{{ $order->order_number }} • طُبعت في {{ now()->format('Y-m-d H:i') }} • {{ config('app.name') }}
    </div>
</body>
</html>
