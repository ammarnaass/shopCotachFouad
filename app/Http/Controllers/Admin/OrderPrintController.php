<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documents\Invoice;
use App\Models\Order\Order;
use App\Services\Documents\InvoiceService;
use App\Services\Documents\LabelService;
use App\Services\Documents\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderPrintController extends Controller
{
    public function __construct(
        private PdfService     $pdfService,
        private InvoiceService $invoiceService,
        private LabelService   $labelService,
    ) {}

    /**
     * Download or preview single invoice.
     */
    public function invoice(Order $order)
    {
        $templateId = request()->input('template_id');

        // Check if user requested HTML print preview instead of PDF download
        if (request()->has('preview') || request()->has('print')) {
            $template = $this->invoiceService->resolveTemplate($templateId);
            $invoice  = $this->invoiceService->getOrCreate($order, $templateId);
            $data     = $this->invoiceService->getInvoiceData($order, $invoice, $template);
            $view     = 'documents.invoices.' . $template->slug;
            if (! view()->exists($view)) {
                $view = $template->isThermal() ? 'documents.invoices.thermal' : 'documents.invoices.classic';
            }

            return view('documents.layouts.print', array_merge($data, [
                'doc_title' => 'فاتورة - ' . $invoice->invoice_number,
                'pdf_link'  => '<a href="' . route('admin.orders.invoice', ['order' => $order->id, 'template_id' => $templateId]) . '" class="btn-pdf">📥 تحميل PDF</a>',
            ]))->with('view', $view);
        }

        return $this->pdfService->generateInvoice($order, $templateId);
    }

    /**
     * Download or preview single label.
     */
    public function customerLabel(Order $order)
    {
        $templateId = request()->input('template_id');

        if (request()->has('preview') || request()->has('print')) {
            $template = $this->labelService->resolveTemplate($templateId);
            $data     = $this->labelService->getLabelData($order, $template);
            $view     = 'documents.labels.' . $template->slug;
            if (! view()->exists($view)) {
                $view = 'documents.labels.classic';
            }

            return view('documents.layouts.print', array_merge($data, [
                'doc_title' => 'ملصق طلب - #' . $order->order_number,
                'pdf_link'  => '<a href="' . route('admin.orders.label', ['order' => $order->id, 'template_id' => $templateId]) . '" class="btn-pdf">📥 تحميل PDF</a>',
            ]))->with('view', $view);
        }

        return $this->pdfService->generateLabel($order, $templateId);
    }

    /**
     * Download or preview bulk invoices.
     */
    public function bulkInvoice(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
        ]);

        $templateId = $request->input('template_id');
        $orders = Order::whereIn('id', $request->order_ids)
            ->with('items.product', 'shippingAddress', 'shippingCompany', 'payment', 'coupon', 'user')
            ->get();

        if ($request->has('print')) {
            $template = $this->invoiceService->resolveTemplate($templateId);
            $view     = 'documents.invoices.' . $template->slug;
            if (! view()->exists($view)) {
                $view = $template->isThermal() ? 'documents.invoices.thermal' : 'documents.invoices.classic';
            }

            $allData = [];
            foreach ($orders as $o) {
                $inv = $this->invoiceService->getOrCreate($o, $templateId);
                $allData[] = $this->invoiceService->getInvoiceData($o, $inv, $template);
            }

            return view('documents.layouts.bulk-invoices', [
                'allData'  => $allData,
                'template' => $template,
                'view'     => $view,
            ]);
        }

        return $this->pdfService->generateBulkInvoices($orders, $templateId);
    }

    /**
     * Download or preview bulk labels.
     */
    public function bulkLabel(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
        ]);

        $templateId = $request->input('template_id');
        $orders = Order::whereIn('id', $request->order_ids)
            ->with('items', 'shippingAddress', 'payment', 'user')
            ->get();

        if ($request->has('print')) {
            $template = $this->labelService->resolveTemplate($templateId);
            $view     = 'documents.labels.' . $template->slug;
            if (! view()->exists($view)) {
                $view = 'documents.labels.classic';
            }

            $allData = [];
            foreach ($orders as $o) {
                $allData[] = $this->labelService->getLabelData($o, $template);
            }

            return view('documents.layouts.bulk-labels', [
                'allData'  => $allData,
                'template' => $template,
                'view'     => $view,
            ]);
        }

        return $this->pdfService->generateBulkLabels($orders, $templateId);
    }
}
