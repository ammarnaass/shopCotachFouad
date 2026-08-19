@props([
    'store',       // array: name, logo, email, phone, phone_secondary, address, website, wilaya, commune, postal_code, business_name, legal_name, rc, nif, nis, invoice_phone, invoice_email, invoice_address, invoice_notes
    'invoice',     // Invoice model
    'template',    // InvoiceTemplate model
    'pdfMode' => false,
])
@php
    $primaryColor = $template->getSetting('primary_color', '#004ac6');
    $showLogo = $template->getSetting('show_logo', true);
    $logoSrc = $pdfMode ? pdf_image_src($store['logo'] ?? null) : ($store['logo'] ?? null);

    $store['commune'] = $store['commune'] ?? '';
    $store['postal_code'] = $store['postal_code'] ?? '';

    $addressParts = array_filter([$store['address'] ?? '', $store['wilaya'] ?? '', $store['commune'], $store['postal_code']]);
    $fullAddress = implode(' - ', $addressParts);
@endphp
<div class="invoice-header">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:55%; vertical-align:top;">
                @if($showLogo && $logoSrc)
                    <img src="{{ $logoSrc }}" alt="{{ $store['name'] }}" style="max-height:60px; max-width:160px; object-fit:contain; margin-bottom:8px;">
                @endif
                <div class="store-name">{{ $store['business_name'] ?: $store['name'] }}</div>
                @if($store['legal_name'])
                    <div class="store-sub">{{ $store['legal_name'] }}</div>
                @endif
                <div class="store-details">
                    @if($store['phone']) الهاتف: {{ $store['phone'] }}<br>@endif
                    @if($store['phone_secondary']) الهاتف الثاني: {{ $store['phone_secondary'] }}<br>@endif
                    @if($fullAddress) العنوان: {{ $fullAddress }}<br>@endif
                    @if($store['invoice_address']) العنوان القانوني: {{ $store['invoice_address'] }}<br>@endif
                    @if($store['website']) الموقع: {{ $store['website'] }}<br>@endif
                    @if($store['email']) البريد: {{ $store['email'] }}<br>@endif
                    @if($store['invoice_email']) بريد الفاتورة: {{ $store['invoice_email'] }}<br>@endif
                    @if($store['invoice_phone']) هاتف الفاتورة: {{ $store['invoice_phone'] }}<br>@endif
                    @if($store['rc']) السجل التجاري: {{ $store['rc'] }}<br>@endif
                    @if($store['nif']) NIF: {{ $store['nif'] }}<br>@endif
                    @if($store['nis']) NIS: {{ $store['nis'] }}<br>@endif
                    @if($store['invoice_notes']) {{ $store['invoice_notes'] }}<br>@endif
                </div>
            </td>
            <td style="width:45%; vertical-align:top; text-align:left; direction:ltr;">
                <div class="invoice-title">INVOICE / فاتورة</div>
                <table class="invoice-meta-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="meta-label">رقم الفاتورة</td>
                        <td class="meta-value">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">رقم الطلب</td>
                        <td class="meta-value">#{{ $invoice->order->order_number }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">التاريخ</td>
                        <td class="meta-value">{{ $invoice->issued_at?->format('Y-m-d') ?? $invoice->created_at->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">الحالة</td>
                        <td class="meta-value">{{ $invoice->order->status_name }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
