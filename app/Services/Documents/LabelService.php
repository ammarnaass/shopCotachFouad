<?php

namespace App\Services\Documents;

use App\Models\Documents\LabelTemplate;
use App\Models\Order\Order;
use App\Support\SiteSettings;

class LabelService
{
    /**
     * Resolve which label template to use.
     */
    public function resolveTemplate(?int $templateId = null): ?LabelTemplate
    {
        if ($templateId) {
            $template = LabelTemplate::find($templateId);
            if ($template) return $template;
        }

        $defaultId = SiteSettings::get('printing_default_label_template');
        if ($defaultId) {
            $template = LabelTemplate::find($defaultId);
            if ($template) return $template;
        }

        return LabelTemplate::where('is_default', true)->where('status', true)->first()
            ?? LabelTemplate::where('status', true)->first();
    }

    /**
     * Build data array for rendering a label Blade template.
     */
    public function getLabelData(Order $order, LabelTemplate $template): array
    {
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

        $storeInfo = [
            'name'            => SiteSettings::get('store_name', config('app.name')),
            'logo'            => SiteSettings::get('store_logo'),
            'phone'           => SiteSettings::get('store_phone'),
            'phone_secondary' => SiteSettings::get('store_phone_secondary'),
            'address'         => SiteSettings::get('store_address'),
            'wilaya'          => SiteSettings::get('store_wilaya'),
            'commune'         => SiteSettings::get('store_commune'),
            'website'         => SiteSettings::get('store_website'),
        ];

        $payment = $order->payment?->first();
        $itemCount = $order->items->sum('quantity');
        $orderMeta = [
            'delivery_type' => $order->delivery_type_label,
            'shipping_method' => $order->shipping_method_label,
            'shipping_company' => $order->shippingCompany?->name ?? '—',
            'notes' => $order->notes,
        ];

        return [
            'order'          => $order,
            'template'       => $template,
            'store'          => $storeInfo,
            'customer'       => $customerInfo,
            'orderMeta'      => $orderMeta,
            'items'          => $order->items,
            'item_count'     => $itemCount,
            'payment'        => $payment,
            'barcode_value'  => $order->order_number,
            'currencySymbol' => currentCurrencySymbol(),
        ];
    }
}
