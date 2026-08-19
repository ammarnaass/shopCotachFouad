@props([
    'customer',
    'order',
    'template',
    'orderMeta' => [],
])
<div class="label-customer-box" style="background: #f8f9fa; border: 1px solid #ddd; border-radius: 6px; padding: 8px 10px; margin-bottom: 8px;">
    <div style="font-size: 13px; font-weight: bold; color: #111; margin-bottom: 3px;">
        👤 {{ $customer['name'] }}
    </div>
    <div style="font-size: 13px; font-weight: bold; color: #004ac6; direction: ltr; text-align: right; margin-bottom: 4px;">
        📞 {{ $customer['phone'] }}
    </div>
    <div style="font-size: 11px; color: #333; line-height: 1.4;">
        @if(($customer['email'] ?? '—') !== '—')
        <strong>البريد:</strong> {{ $customer['email'] }}<br>
        @endif
        <strong>الولاية:</strong> {{ $customer['wilaya'] }}<br>
        @if($customer['commune'] && $customer['commune'] !== '—' && $customer['commune'] !== $customer['city'])
        <strong>الدوّارة:</strong> {{ $customer['commune'] }}<br>
        @endif
        @if($customer['city'] && $customer['city'] !== '—' && $customer['city'] !== $customer['commune'])
        <strong>البلدية:</strong> {{ $customer['city'] }}<br>
        @endif
        <strong>العنوان:</strong> {{ $customer['address'] }}<br>
        @if(($customer['zip'] ?? '—') !== '—')
        <strong>الرمز البريدي:</strong> {{ $customer['zip'] }}<br>
        @endif
        @if(($orderMeta['delivery_type'] ?? '—') !== '—')
        <strong>نوع التوصيل:</strong> {{ $orderMeta['delivery_type'] }}<br>
        @endif
        @if(($orderMeta['shipping_company'] ?? '—') !== '—')
        <strong>شركة الشحن:</strong> {{ $orderMeta['shipping_company'] }}<br>
        @endif
        @if(!empty($orderMeta['notes']))
        <strong>ملاحظات:</strong> {{ $orderMeta['notes'] }}
        @endif
    </div>
</div>
