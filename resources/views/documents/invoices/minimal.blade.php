<style>
    .invoice-container-minimal { font-family: 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.6; }
    .store-name { font-size: 18px; font-weight: 700; color: #0f172a; }
    .store-sub { font-size: 11px; color: #64748b; margin-top: 2px; }
    .store-details { font-size: 10px; color: #64748b; margin-top: 4px; line-height: 1.4; }
    .invoice-title { font-size: 16px; font-weight: 700; color: #0f172a; }
    .invoice-meta-table { font-size: 10px; margin-top: 4px; border-collapse: collapse; }
    .invoice-meta-table td { padding: 2px 6px; }
    .meta-label { color: #64748b; }
    .meta-value { font-weight: 700; color: #0f172a; }
    .invoice-section { margin-top: 16px; }
    .section-title { font-size: 11px; font-weight: 700; color: #0f172a; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; margin-bottom: 8px; }
    .info-table { font-size: 11px; width: 100%; border-collapse: collapse; }
    .info-table td { padding: 3px 4px; vertical-align: top; }
    .info-label { color: #64748b; width: 65px; }
    .info-value { font-weight: 600; color: #0f172a; }
    .items-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .items-table th { color: #64748b; font-size: 10px; font-weight: 600; padding: 6px; border-bottom: 1px solid #0f172a; }
    .items-table td { padding: 6px; font-size: 11px; border-bottom: 1px solid #f1f5f9; }
    .totals-table { font-size: 11px; border-collapse: collapse; }
    .totals-table td { padding: 4px 8px; }
    .total-label { color: #64748b; }
    .total-val { font-weight: 700; color: #0f172a; }
</style>

<div class="invoice-container-minimal">
    <x-documents.invoice-header :store="$store" :invoice="$invoice" :template="$template" :pdfMode="($pdf_mode ?? false)" />
    <x-documents.invoice-customer :customer="$customer" :order="$order" :template="$template" :orderMeta="$orderMeta" />
    <x-documents.invoice-items :items="$items" :template="$template" :pdfMode="($pdf_mode ?? false)" />
    <x-documents.invoice-summary :order="$order" :template="$template" :currencySymbol="$currencySymbol" :orderMeta="$orderMeta" />
    <x-documents.invoice-footer :store="$store" :invoice="$invoice" :template="$template" />
</div>
