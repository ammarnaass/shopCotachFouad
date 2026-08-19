<?php

namespace App\Services\Documents;

use App\Models\Documents\Invoice;
use App\Models\Documents\InvoiceTemplate;
use App\Models\Order\Order;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Get existing invoice or create a new one for the order.
     */
    public function getOrCreate(Order $order, ?int $templateId = null): Invoice
    {
        return DB::transaction(function () use ($order, $templateId) {
            $invoice = Invoice::where('order_id', $order->id)->first();

            if (! $invoice) {
                $template = $this->resolveTemplate($templateId);
                $invoice = Invoice::create([
                    'order_id'           => $order->id,
                    'invoice_number'     => Invoice::generateNumber(),
                    'status'             => 'issued',
                    'invoice_template_id'=> $template?->id,
                    'issued_at'          => now(),
                    'metadata'           => $this->buildStoreSnapshot(),
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Resolve which template to use. Falls back to the default template.
     */
    public function resolveTemplate(?int $templateId = null): ?InvoiceTemplate
    {
        if ($templateId) {
            $template = InvoiceTemplate::find($templateId);
            if ($template) return $template;
        }

        // Try printing settings default
        $defaultId = SiteSettings::get('printing_default_invoice_template');
        if ($defaultId) {
            $template = InvoiceTemplate::find($defaultId);
            if ($template) return $template;
        }

        return InvoiceTemplate::where('is_default', true)->where('status', true)->first()
            ?? InvoiceTemplate::where('status', true)->first();
    }

    /**
     * Build the complete data array for rendering an invoice Blade template.
     */
    public function getInvoiceData(Order $order, Invoice $invoice, InvoiceTemplate $template): array
    {
        // Store info — use snapshot if available, otherwise live settings
        $snapshot = $invoice->metadata ?? [];
        $storeInfo = [
            'name'            => $snapshot['store_name']            ?? SiteSettings::get('store_name', config('app.name')),
            'logo'            => SiteSettings::get('store_logo'),
            'email'           => $snapshot['store_email']           ?? SiteSettings::get('store_email'),
            'phone'           => $snapshot['store_phone']           ?? SiteSettings::get('store_phone'),
            'phone_secondary' => $snapshot['store_phone_secondary'] ?? SiteSettings::get('store_phone_secondary'),
            'address'         => $snapshot['store_address']         ?? SiteSettings::get('store_address'),
            'website'         => $snapshot['store_website']         ?? SiteSettings::get('store_website'),
            'wilaya'          => $snapshot['store_wilaya']          ?? SiteSettings::get('store_wilaya'),
            'commune'         => $snapshot['store_commune']         ?? SiteSettings::get('store_commune'),
            'postal_code'     => $snapshot['store_postal_code']    ?? SiteSettings::get('store_postal_code'),
            // Invoice / legal info
            'business_name'   => $snapshot['invoice_business_name'] ?? SiteSettings::get('invoice_business_name'),
            'legal_name'      => $snapshot['invoice_legal_name']    ?? SiteSettings::get('invoice_legal_name'),
            'rc'              => $snapshot['invoice_rc']            ?? SiteSettings::get('invoice_rc'),
            'nif'             => $snapshot['invoice_nif']           ?? SiteSettings::get('invoice_nif'),
            'nis'             => $snapshot['invoice_nis']            ?? SiteSettings::get('invoice_nis'),
            'invoice_phone'   => $snapshot['invoice_phone']         ?? SiteSettings::get('invoice_phone'),
            'invoice_email'   => $snapshot['invoice_email']         ?? SiteSettings::get('invoice_email'),
            'invoice_address'=> $snapshot['invoice_address']       ?? SiteSettings::get('invoice_address'),
            'invoice_notes'   => $snapshot['invoice_notes']         ?? SiteSettings::get('invoice_notes'),
        ];

        // Customer info
        $addr = $order->shippingAddress;
        $customerInfo = [
            'name'    => $addr?->name ?? $order->user?->name ?? $order->guest_email ?? 'ضيف',
            'phone'   => $addr?->phone ?? $order->user?->phone ?? $order->guest_phone ?? '—',
            'email'   => $order->user?->email ?? $order->guest_email ?? '—',
            'address' => $addr?->address ?? '—',
            'city'    => $addr?->city ?? '—',
            'wilaya'  => $addr?->state_name ?? '—',
            'commune' => $addr?->district ?? $addr?->city ?? '—',
            'country' => $addr?->country_name ?? '—',
            'zip'     => $addr?->zip ?? '—',
        ];

        // Payment
        $payment = $order->payment?->first();
        $orderMeta = [
            'delivery_type' => $order->delivery_type_label,
            'shipping_method' => $order->shipping_method_label,
            'shipping_company' => $order->shippingCompany?->name ?? '—',
            'payment_status' => Order::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status,
            'notes' => $order->notes,
        ];

        return [
            'order'        => $order,
            'invoice'      => $invoice,
            'template'     => $template,
            'store'        => $storeInfo,
            'customer'     => $customerInfo,
            'orderMeta'    => $orderMeta,
            'items'        => $order->items,
            'payment'      => $payment,
            'currencySymbol' => currentCurrencySymbol(),
        ];
    }

    /**
     * Capture a snapshot of current store settings for archiving on the invoice.
     */
    public function buildStoreSnapshot(): array
    {
        return [
            'store_name'            => SiteSettings::get('store_name'),
            'store_email'           => SiteSettings::get('store_email'),
            'store_phone'           => SiteSettings::get('store_phone'),
            'store_phone_secondary' => SiteSettings::get('store_phone_secondary'),
            'store_address'         => SiteSettings::get('store_address'),
            'store_website'         => SiteSettings::get('store_website'),
            'store_wilaya'          => SiteSettings::get('store_wilaya'),
            'store_commune'         => SiteSettings::get('store_commune'),
            'store_postal_code'     => SiteSettings::get('store_postal_code'),
            'invoice_business_name' => SiteSettings::get('invoice_business_name'),
            'invoice_legal_name'    => SiteSettings::get('invoice_legal_name'),
            'invoice_rc'            => SiteSettings::get('invoice_rc'),
            'invoice_nif'           => SiteSettings::get('invoice_nif'),
            'invoice_nis'           => SiteSettings::get('invoice_nis'),
            'invoice_phone'         => SiteSettings::get('invoice_phone'),
            'invoice_address'       => SiteSettings::get('invoice_address'),
            'invoice_email'         => SiteSettings::get('invoice_email'),
            'invoice_notes'         => SiteSettings::get('invoice_notes'),
        ];
    }
}
