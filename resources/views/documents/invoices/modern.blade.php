@php
    $primaryColor = $template->getSetting('primary_color', '#0f172a');
@endphp
<style>
    .invoice-container-modern { font-family: 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif; font-size: 11px; color: #334155; line-height: 1.6; }
    .modern-header-bg { background: linear-gradient(135deg, {{ $primaryColor }} 0%, #1e293b 100%); color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 18px; }
    .modern-header-bg .store-name { font-size: 22px; font-weight: 800; color: #fff; }
    .modern-header-bg .store-sub { font-size: 11px; opacity: 0.85; margin-top: 2px; }
    .modern-header-bg .store-details { font-size: 10px; opacity: 0.8; margin-top: 6px; line-height: 1.5; }
    .modern-header-bg .invoice-title { font-size: 16px; font-weight: 700; color: #93c5fd; }
    .modern-header-bg .meta-label { color: #cbd5e1; }
    .modern-header-bg .meta-value { color: #fff; font-weight: 700; }
    .invoice-section { margin-top: 16px; }
    .section-title { font-size: 12px; font-weight: 700; color: {{ $primaryColor }}; border-bottom: 2px dashed #cbd5e1; padding-bottom: 4px; margin-bottom: 8px; }
    .info-table { font-size: 11px; width: 100%; border-collapse: collapse; }
    .info-table td { padding: 3px 6px; vertical-align: top; }
    .info-label { color: #64748b; width: 70px; font-weight: 500; }
    .info-value { font-weight: 700; color: #0f172a; }
    .items-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .items-table th { background: #f1f5f9; color: #0f172a; font-size: 10px; font-weight: 700; padding: 8px 10px; border-bottom: 2px solid #cbd5e1; }
    .items-table td { padding: 8px 10px; font-size: 11px; border-bottom: 1px solid #f1f5f9; }
    .totals-table { font-size: 11px; border-collapse: collapse; }
    .totals-table td { padding: 5px 10px; }
    .total-label { color: #64748b; }
    .total-val { font-weight: 700; color: #0f172a; }
</style>

<div class="invoice-container-modern">
    <div class="modern-header-bg">
        <x-documents.invoice-header :store="$store" :invoice="$invoice" :template="$template" :pdfMode="($pdf_mode ?? false)" />
    </div>
    <x-documents.invoice-customer :customer="$customer" :order="$order" :template="$template" :orderMeta="$orderMeta" />
    <x-documents.invoice-items :items="$items" :template="$template" :pdfMode="($pdf_mode ?? false)" />
    <x-documents.invoice-summary :order="$order" :template="$template" :currencySymbol="$currencySymbol" :orderMeta="$orderMeta" />
    <x-documents.invoice-footer :store="$store" :invoice="$invoice" :template="$template" />
</div>
