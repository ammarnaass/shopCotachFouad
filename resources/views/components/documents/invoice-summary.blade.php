@props([
    'order',
    'template',
    'currencySymbol' => 'د.ج',
    'orderMeta' => [],
])
@php
    $showDiscount = $template->getSetting('show_discount', true);
    $showShipping = $template->getSetting('show_shipping', true);
    $showPayment  = $template->getSetting('show_payment_method', true);
    $showNotes    = $template->getSetting('show_notes', true);
    $primaryColor = $template->getSetting('primary_color', '#004ac6');
@endphp
<div class="invoice-section">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:55%; vertical-align:top; padding-left:15px;">
                @if($showPayment && $order->payment?->first())
                    <div style="margin-bottom:10px; font-size:11px;">
                        <strong>طريقة الدفع:</strong> {{ $order->payment->first()->method ?? 'الدفع عند الاستلام' }}
                    </div>
                @endif

                @if(($orderMeta['delivery_type'] ?? '—') !== '—')
                    <div style="margin-bottom:8px; font-size:11px;">
                        <strong>نوع التوصيل:</strong> {{ $orderMeta['delivery_type'] }}
                    </div>
                @endif

                @if(($orderMeta['shipping_company'] ?? '—') !== '—')
                    <div style="margin-bottom:8px; font-size:11px;">
                        <strong>شركة الشحن:</strong> {{ $orderMeta['shipping_company'] }}
                    </div>
                @endif

                @if($showNotes && !empty($orderMeta['notes']))
                    <div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:6px; padding:8px 12px; font-size:11px;">
                        <strong>ملاحظات:</strong> {{ $orderMeta['notes'] }}
                    </div>
                @endif
            </td>
            <td style="width:45%; vertical-align:top;">
                <table class="totals-table" cellpadding="0" cellspacing="0" width="100%" style="direction:ltr; text-align:left;">
                    <tr>
                        <td class="total-label">الإجمالي الفرعي</td>
                        <td class="total-val">{{ number_format($order->subtotal, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @if($showDiscount && $order->discount > 0)
                    <tr>
                        <td class="total-label">الخصم</td>
                        <td class="total-val" style="color:#d9534f;">-{{ number_format($order->discount, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @endif
                    @if($showShipping)
                    <tr>
                        <td class="total-label">الشحن</td>
                        <td class="total-val">{{ number_format($order->shipping_cost, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @endif
                    @if($order->tax > 0)
                    <tr>
                        <td class="total-label">الضريبة</td>
                        <td class="total-val">{{ number_format($order->tax, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @endif
                    @if($order->cod_fee > 0)
                    <tr>
                        <td class="total-label">رسوم الدفع عند الاستلام</td>
                        <td class="total-val">{{ number_format($order->cod_fee, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row" style="background-color: {{ $primaryColor }}10; border-top: 2px solid {{ $primaryColor }};">
                        <td class="grand-total-label" style="color: {{ $primaryColor }}; font-weight:700; font-size:14px; padding:8px 10px;">الإجمالي النهائي</td>
                        <td class="grand-total-val" style="color: {{ $primaryColor }}; font-weight:700; font-size:14px; padding:8px 10px; text-align:left;">{{ number_format($order->grand_total, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
