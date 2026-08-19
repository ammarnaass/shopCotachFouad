@php
    $primaryColor = $template->getSetting('primary_color', '#004ac6');
@endphp
<style>
    .invoice-container { font-family: 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.6; }
    .store-name { font-size: 20px; font-weight: 700; color: {{ $primaryColor }}; }
    .store-sub { font-size: 11px; color: #64748b; margin-top: 2px; }
    .store-details { font-size: 10px; color: #475569; margin-top: 6px; line-height: 1.5; }
    .invoice-title { font-size: 18px; font-weight: 700; color: {{ $primaryColor }}; letter-spacing: -0.5px; }
    .invoice-meta-table { font-size: 10px; margin-top: 6px; border-collapse: collapse; }
    .invoice-meta-table td { padding: 3px 8px; }
    .meta-label { color: #64748b; font-weight: 500; }
    .meta-value { font-weight: 700; color: #0f172a; }
    .invoice-section { margin-top: 18px; }
    .section-title { font-size: 12px; font-weight: 700; color: {{ $primaryColor }}; border-bottom: 2px solid {{ $primaryColor }}30; padding-bottom: 4px; margin-bottom: 8px; }
    .info-table { font-size: 11px; width: 100%; border-collapse: collapse; }
    .info-table td { padding: 3px 6px; vertical-align: top; }
    .info-label { color: #64748b; width: 70px; font-weight: 500; }
    .info-value { font-weight: 700; color: #0f172a; }
    .items-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .items-table th { background: {{ $primaryColor }}15; color: {{ $primaryColor }}; font-size: 10px; font-weight: 700; padding: 7px 10px; border: 1px solid #cbd5e1; }
    .items-table td { padding: 7px 10px; font-size: 11px; border: 1px solid #e2e8f0; }
    .totals-table { font-size: 11px; border-collapse: collapse; }
    .totals-table td { padding: 5px 10px; }
    .total-label { color: #64748b; }
    .total-val { font-weight: 700; color: #0f172a; }
</style>

<div class="invoice-container">
    <x-documents.invoice-header :store="$store" :invoice="$invoice" :template="$template" :pdfMode="($pdf_mode ?? false)" />
    <x-documents.invoice-customer :customer="$customer" :order="$order" :template="$template" :orderMeta="$orderMeta" />
    <x-documents.invoice-items :items="$items" :template="$template" :pdfMode="($pdf_mode ?? false)" />
    <x-documents.invoice-summary :order="$order" :template="$template" :currencySymbol="$currencySymbol" :orderMeta="$orderMeta" />
    <x-documents.invoice-footer :store="$store" :invoice="$invoice" :template="$template" />
</div>
