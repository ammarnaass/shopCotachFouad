<?php

namespace App\Services\Documents;

use App\Models\Documents\InvoiceTemplate;
use App\Models\Documents\LabelTemplate;
use App\Models\Order\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfService
{
    public function __construct(
        private InvoiceService $invoiceService,
        private LabelService   $labelService,
    ) {}

    /**
     * Create and configure an mPDF instance with full native Arabic, BiDi, and RTL support.
     */
    private function createMpdf(array|string $format = 'A4', string $orientation = 'P', array $margins = [8, 8, 8, 8]): Mpdf
    {
        $tmp = storage_path('app/mpdf');
        if (! is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'                     => 'utf-8',
            'format'                   => $format,
            'orientation'              => $orientation,
            'tempDir'                  => $tmp,
            'default_font'             => 'dejavusanscondensed',
            'margin_left'              => $margins[0] ?? 8,
            'margin_right'             => $margins[1] ?? 8,
            'margin_top'               => $margins[2] ?? 8,
            'margin_bottom'            => $margins[3] ?? 8,
            'autoArabic'               => true,
            'autoLangToFont'           => true,
            'allow_charset_conversion' => true,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;

        return $mpdf;
    }

    /**
     * Generate and download an invoice PDF for a single order.
     */
    public function generateInvoice(Order $order, ?int $templateId = null): Response
    {
        $order->loadMissing('items.product', 'shippingAddress', 'shippingCompany', 'shippingMethod', 'payment', 'coupon', 'user');

        $template = $this->invoiceService->resolveTemplate($templateId);
        $invoice  = $this->invoiceService->getOrCreate($order, $templateId);
        $data     = $this->invoiceService->getInvoiceData($order, $invoice, $template);

        $view     = $this->resolveInvoiceView($template);
        $html     = view($view, array_merge($data, ['pdf_mode' => true]))->render();

        $format = match ($template->paper_size) {
            'a5'         => 'A5',
            'thermal_80' => [80, 297],
            'thermal_58' => [58, 297],
            default      => 'A4',
        };

        $margins = match ($template->paper_size) {
            'thermal_80', 'thermal_58' => [3, 3, 3, 3],
            default                    => [8, 8, 8, 8],
        };

        $mpdf = $this->createMpdf($format, 'P', $margins);
        $mpdf->WriteHTML($html);

        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';
        $pdfOutput = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($pdfOutput, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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

        $format   = [$size['width'], $size['height']];
        $mpdf     = $this->createMpdf($format, 'P', [4, 4, 4, 4]);
        $mpdf->WriteHTML($html);

        $filename = 'label-' . $order->order_number . '.pdf';
        $pdfOutput = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($pdfOutput, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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

        $format = match ($template->paper_size) {
            'a5'         => 'A5',
            'thermal_80' => [80, 297],
            'thermal_58' => [58, 297],
            default      => 'A4',
        };

        $margins = match ($template->paper_size) {
            'thermal_80', 'thermal_58' => [3, 3, 3, 3],
            default                    => [8, 8, 8, 8],
        };

        $mpdf = $this->createMpdf($format, 'P', $margins);
        $mpdf->WriteHTML($html);

        $filename = 'invoices-bulk-' . now()->format('Ymd-His') . '.pdf';
        $pdfOutput = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($pdfOutput, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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

        $format = [$size['width'], $size['height']];
        $mpdf   = $this->createMpdf($format, 'P', [4, 4, 4, 4]);
        $mpdf->WriteHTML($html);

        $filename = 'labels-bulk-' . now()->format('Ymd-His') . '.pdf';
        $pdfOutput = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($pdfOutput, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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
}
