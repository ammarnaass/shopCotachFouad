<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملصق العميل - {{ $order->order_number }}</title>
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
            padding: 18px 25px;
        }
        .header table { width: 100%; }
        .header td { vertical-align: middle; }
        .header .store-name {
            font-size: 18px;
            font-weight: 700;
        }
        .header .order-number {
            background-color: rgba(255,255,255,0.15);
            padding: 6px 14px;
            border-radius: 6px;
            font-family: 'IBM Plex Sans Arabic', Arial, monospace;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            direction: ltr;
        }
        .section { padding: 18px 25px; }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #004ac6;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #dbe1ff;
        }
        .info-table { width: 100%; }
        .info-table td {
            padding: 4px 6px;
            font-size: 11px;
            vertical-align: top;
        }
        .info-table td:first-child {
            color: #434655;
            width: 90px;
            font-weight: 500;
        }
        .info-table td:last-child {
            font-weight: 700;
            color: #191b23;
        }
        .customer-box {
            background-color: #f3f3fe;
            border: 1px solid #c3c6d7;
            border-radius: 8px;
            padding: 12px 15px;
        }
        .products-table { width: 100%; }
        .products-table th {
            background-color: #dbe1ff;
            color: #003ea8;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 700;
            text-align: right;
            border: 1px solid #c3c6d7;
        }
        .products-table td {
            padding: 6px 10px;
            font-size: 11px;
            border: 1px solid #c3c6d7;
            text-align: right;
        }
        .products-table td:last-child {
            text-align: center;
            direction: ltr;
        }
        .footer {
            padding: 12px 25px;
            border-top: 1px dashed #c3c6d7;
            font-size: 10px;
            color: #737686;
        }
        .footer table { width: 100%; }
        .footer td { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="store-name">{{ config('app.name') }}</div>
                </td>
                <td style="text-align: left; direction: ltr;">
                    <div class="order-number">#{{ $order->order_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">بيانات العميل</div>
        <div class="customer-box">
            <table class="info-table">
                <tr><td>الاسم</td><td>{{ $order->shippingAddress->name ?? ($order->user?->name ?? 'ضيف') }}</td></tr>
                <tr><td>الهاتف</td><td>{{ $order->shippingAddress->phone ?? $order->user?->phone ?? $order->guest_phone ?? '-' }}</td></tr>
                @if($order->shippingAddress)
                    <tr><td>العنوان</td><td>{{ $order->shippingAddress->address }}</td></tr>
                    <tr><td>المدينة</td><td>{{ $order->shippingAddress->city }}</td></tr>
                    <tr><td>الولاية</td><td>{{ $order->shippingAddress->state_name ?? '-' }}</td></tr>
                    <tr><td>الدولة</td><td>{{ $order->shippingAddress->country_name ?? '-' }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">ملخص الطلب</div>
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width:70%;">المنتج</th>
                    <th style="width:30%;">الكمية</th>
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
        <table class="info-table" style="margin-top: 10px; direction: ltr; text-align: left;">
            <tr><td style="width: auto;">الإجمالي</td><td style="font-weight: 700; color: #004ac6; font-size: 13px;">{{ number_format($order->grand_total, 2) }} {{ currentCurrencySymbol() }}</td></tr>
        </table>
    </div>

    <div class="footer">
        <table>
            <tr>
                <td>طُبعت في {{ now()->format('Y-m-d H:i') }}</td>
                <td style="width: 30%;">{{ config('app.name') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
