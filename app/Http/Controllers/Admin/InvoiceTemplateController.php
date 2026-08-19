<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documents\Invoice;
use App\Models\Documents\InvoiceTemplate;
use App\Models\Order\Order;
use App\Services\Documents\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\View\View;

class InvoiceTemplateController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(): View
    {
        $templates = InvoiceTemplate::latest()->get();
        return view('admin.invoices.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.invoices.templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'paper_size'  => 'required|in:a4,a5,thermal_80,thermal_58',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
            'settings'    => 'nullable|array',
        ]);

        $validated['slug']       = Str::slug($request->input('name')) . '-' . Str::random(5);
        $validated['status']     = $request->boolean('status', true);
        $validated['is_default'] = false;

        InvoiceTemplate::create($validated);

        return redirect()->route('admin.invoices.templates.index')->with('success', 'تم إنشاء القالب بنجاح');
    }

    public function edit(InvoiceTemplate $template): View
    {
        return view('admin.invoices.templates.edit', compact('template'));
    }

    public function update(Request $request, InvoiceTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'paper_size'  => 'required|in:a4,a5,thermal_80,thermal_58',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
            'settings'    => 'nullable|array',
        ]);

        $validated['status'] = $request->boolean('status');
        $template->update($validated);

        return redirect()->route('admin.invoices.templates.index')->with('success', 'تم تحديث القالب بنجاح');
    }

    public function destroy(InvoiceTemplate $template): RedirectResponse
    {
        if ($template->is_default) {
            return back()->with('error', 'لا يمكن حذف القالب الافتراضي');
        }

        $template->delete();
        return back()->with('success', 'تم حذف القالب بنجاح');
    }

    public function setDefault(InvoiceTemplate $template): RedirectResponse
    {
        InvoiceTemplate::where('is_default', true)->update(['is_default' => false]);
        $template->update(['is_default' => true, 'status' => true]);

        return back()->with('success', 'تم تعيين القالب كافتراضي');
    }

    public function preview(InvoiceTemplate $template)
    {
        // Try getting a real order first, or build dummy data
        $order = Order::with('items', 'shippingAddress', 'payment', 'user')->latest()->first();

        if (! $order) {
            // Build dummy order for previewing when no orders exist yet
            $order = new Order([
                'id'            => 1,
                'order_number'  => 'ORD-2026-000001',
                'status'        => 'delivered',
                'subtotal'      => 7500,
                'shipping_cost' => 500,
                'discount'      => 500,
                'tax'           => 0,
                'cod_fee'       => 0,
                'grand_total'   => 7500,
                'created_at'    => now(),
            ]);
        }

        $invoice = Invoice::firstOrNew(['order_id' => $order->id], [
            'invoice_number' => 'INV-2026-000001',
            'issued_at'      => now(),
        ]);

        $data = $this->invoiceService->getInvoiceData($order, $invoice, $template);
        $view = 'documents.invoices.' . $template->slug;
        if (! view()->exists($view)) {
            $view = $template->isThermal() ? 'documents.invoices.thermal' : 'documents.invoices.classic';
        }

        if (request()->has('print')) {
            return view('documents.layouts.print', array_merge($data, [
                'contentView' => $view,
                'doc_title'   => 'معاينة القالب: ' . $template->name,
            ]))->with('view', $view);
        }

        return view($view, $data);
    }
}
