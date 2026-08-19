@props([
    'customer',   // array: name, phone, email, address, city, wilaya, commune, country
    'order',
    'template',
    'orderMeta' => [],
])
@php
    $showCustomer = $template->getSetting('show_customer_info', true);
@endphp
@if($showCustomer)
<div class="invoice-section">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:50%; vertical-align:top; padding-left:12px;">
                <div class="section-title">بيانات العميل</div>
                <table class="info-table" cellpadding="0" cellspacing="0">
                    <tr><td class="info-label">الاسم</td><td class="info-value">{{ $customer['name'] }}</td></tr>
                    <tr><td class="info-label">الهاتف</td><td class="info-value" dir="ltr">{{ $customer['phone'] }}</td></tr>
                    @if($customer['email'] !== '—')
                    <tr><td class="info-label">البريد</td><td class="info-value">{{ $customer['email'] }}</td></tr>
                    @endif
                </table>
            </td>
            <td style="width:50%; vertical-align:top;">
                <div class="section-title">عنوان الشحن</div>
                <table class="info-table" cellpadding="0" cellspacing="0">
                    @if($customer['wilaya'] && $customer['wilaya'] !== '—')
                    <tr><td class="info-label">الولاية</td><td class="info-value">{{ $customer['wilaya'] }}</td></tr>
                    @endif
                    @if($customer['commune'] && $customer['commune'] !== '—' && $customer['commune'] !== $customer['city'])
                    <tr><td class="info-label">الدوّارة</td><td class="info-value">{{ $customer['commune'] }}</td></tr>
                    @endif
                    <tr><td class="info-label">المدينة</td><td class="info-value">{{ $customer['city'] }}</td></tr>
                    <tr><td class="info-label">العنوان</td><td class="info-value">{{ $customer['address'] }}</td></tr>
                    @if(($customer['zip'] ?? '—') !== '—')
                    <tr><td class="info-label">الرمز البريدي</td><td class="info-value">{{ $customer['zip'] }}</td></tr>
                    @endif
                    @if(($orderMeta['delivery_type'] ?? '—') !== '—')
                    <tr><td class="info-label">نوع التوصيل</td><td class="info-value">{{ $orderMeta['delivery_type'] }}</td></tr>
                    @endif
                    @if(($orderMeta['shipping_method'] ?? '—') !== '—')
                    <tr><td class="info-label">طريقة الشحن</td><td class="info-value">{{ $orderMeta['shipping_method'] }}</td></tr>
                    @endif
                    @if(($orderMeta['shipping_company'] ?? '—') !== '—')
                    <tr><td class="info-label">شركة الشحن</td><td class="info-value">{{ $orderMeta['shipping_company'] }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
</div>
@endif
