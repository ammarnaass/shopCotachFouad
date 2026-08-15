<?php

namespace App\Services\Documents;

use App\Models\Documents\InvoiceTemplate;
use App\Models\Documents\LabelTemplate;
use App\Models\Order\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class PdfService
{
    public function __construct(
        private InvoiceService $invoiceService,
        private LabelService   $labelService,
    ) {}

    /**
     * Generate and download an invoice PDF for a single order.
     */
    public function generateInvoice(Order $order, ?int $templateId = null): Response
    {
        $order->loadMissing('items.product', 'shippingAddress', 'shippingCompany', 'shippingMethod', 'payment', 'coupon', 'user');

        $template = $this->invoiceService->resolveTemplate($templateId);
        $invoice  = $this->invoiceService->getOrCreate($order, $templateId);
        $data     = $this->invoiceService->getInvoiceData($order, $invoice, $template);

        $view      = $this->resolveInvoiceView($template);
        $html      = view($view, array_merge($data, ['pdf_mode' => true]))->render();
        $paperSize = $template->getDompdfPaper();

        $pdf = Pdf::loadHtml($html)
            ->setPaper($paperSize, 'portrait')
            ->setOption('isFontDirTmp', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Generate and download a label PDF for a single order.
     */
    public function generateLabel(Order $order, ?int $templateId = null): Response
    {
        $order->loadMissing('items', 'shippingAddress', 'shippingMethod', 'payment', 'user');

        $template = $this->labelService->resolveTemplate($templateId);
        $data     = $this->labelService->getLabelData($order, $template);
        $view     = $this->resolveLabelView($template);
        $html     = view($view, array_merge($data, ['pdf_mode' => true]))->render();
        $size     = $template->size_mm;

        $pdf = Pdf::loadHtml($html)
            ->setPaper([0, 0, $this->mmToPt($size['width']), $this->mmToPt($size['height'])], 'portrait')
            ->setOption('isFontDirTmp', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('label-' . $order->order_number . '.pdf');
    }

    /**
     * Generate bulk invoice PDF for multiple orders.
     */
    public function generateBulkInvoices(Collection $orders, ?int $templateId = null): Response
    {
        $template = $this->invoiceService->resolveTemplate($templateId);
        $view     = $this->resolveInvoiceView($template);
        $allData  = [];

        foreach ($orders as $order) {
            $order->loadMissing('items.product', 'shippingAddress', 'shippingCompany', 'shippingMethod', 'payment', 'coupon', 'user');
            $invoice    = $this->invoiceService->getOrCreate($order, $templateId);
            $allData[]  = $this->invoiceService->getInvoiceData($order, $invoice, $template);
        }

        $html = view('documents.layouts.bulk-invoices', [
            'allData'  => $allData,
            'template' => $template,
            'view'     => $view,
            'pdf_mode' => true,
        ])->render();

        $pdf = Pdf::loadHtml($html)
            ->setPaper($template->getDompdfPaper(), 'portrait')
            ->setOption('isFontDirTmp', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('invoices-bulk-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Generate bulk label PDF for multiple orders.
     */
    public function generateBulkLabels(Collection $orders, ?int $templateId = null): Response
    {
        $template = $this->labelService->resolveTemplate($templateId);
        $view     = $this->resolveLabelView($template);
        $allData  = [];

        foreach ($orders as $order) {
            $order->loadMissing('items', 'shippingAddress', 'shippingMethod', 'payment', 'user');
            $allData[] = $this->labelService->getLabelData($order, $template);
        }

        $size = $template->size_mm;
        $html = view('documents.layouts.bulk-labels', [
            'allData'  => $allData,
            'template' => $template,
            'view'     => $view,
            'pdf_mode' => true,
        ])->render();

        $pdf = Pdf::loadHtml($html)
            ->setPaper([0, 0, $this->mmToPt($size['width']), $this->mmToPt($size['height'])], 'portrait')
            ->setOption('isFontDirTmp', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('labels-bulk-' . now()->format('Ymd-His') . '.pdf');
    }

    private function resolveInvoiceView(InvoiceTemplate $template): string
    {
        $view = 'documents.invoices.' . $template->slug;
        if (view()->exists($view)) {
            return $view;
        }
        // Fall back based on paper_size
        if ($template->isThermal()) {
            return 'documents.invoices.thermal';
        }
        return 'documents.invoices.classic';
    }

    private function resolveLabelView(LabelTemplate $template): string
    {
        $view = 'documents.labels.' . $template->slug;
        if (view()->exists($view)) {
            return $view;
        }
        return 'documents.labels.classic';
    }

    /**
     * Convert millimeters to points (1mm = 2.8346 pt).
     */
    private function mmToPt(int $mm): float
    {
        return round($mm * 2.8346, 2);
    }
}
