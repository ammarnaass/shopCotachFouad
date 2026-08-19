<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documents\InvoiceTemplate;
use App\Models\Documents\LabelTemplate;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintingSettingsController extends Controller
{
    public function __construct(private SettingsService $settings) {}

    public function index(): View
    {
        $invoiceTemplates = InvoiceTemplate::active()->get();
        $labelTemplates   = LabelTemplate::active()->get();

        $printing = [
            'default_invoice_template' => $this->settings->get('printing_default_invoice_template'),
            'default_label_template'   => $this->settings->get('printing_default_label_template'),
            'default_invoice_size'     => $this->settings->get('printing_default_invoice_size', 'a4'),
            'default_label_size'       => $this->settings->get('printing_default_label_size', '100x150'),
            'show_logo'                => $this->settings->get('printing_show_logo', '1'),
            'show_barcode'             => $this->settings->get('printing_show_barcode', '1'),
            'show_qr'                  => $this->settings->get('printing_show_qr', '0'),
            'auto_generate_invoice'    => $this->settings->get('printing_auto_generate_invoice', '1'),
        ];

        return view('admin.settings.printing', compact('printing', 'invoiceTemplates', 'labelTemplates'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_invoice_template' => 'nullable|exists:invoice_templates,id',
            'default_label_template'   => 'nullable|exists:label_templates,id',
            'default_invoice_size'     => 'required|in:a4,a5,thermal_80,thermal_58',
            'default_label_size'       => 'required|in:100x150,100x100,80x50,a6,custom',
            'show_logo'                => 'nullable|boolean',
            'show_barcode'             => 'nullable|boolean',
            'show_qr'                  => 'nullable|boolean',
            'auto_generate_invoice'    => 'nullable|boolean',
        ]);

        foreach (['show_logo', 'show_barcode', 'show_qr', 'auto_generate_invoice'] as $field) {
            $validated[$field] = $request->boolean($field) ? '1' : '0';
        }

        foreach ($validated as $key => $value) {
            $this->settings->set('printing_' . $key, (string) $value, 'printing');
        }

        // Also update default flags on InvoiceTemplate and LabelTemplate if chosen
        if (! empty($validated['default_invoice_template'])) {
            InvoiceTemplate::where('is_default', true)->update(['is_default' => false]);
            InvoiceTemplate::where('id', $validated['default_invoice_template'])->update(['is_default' => true]);
        }

        if (! empty($validated['default_label_template'])) {
            LabelTemplate::where('is_default', true)->update(['is_default' => false]);
            LabelTemplate::where('id', $validated['default_label_template'])->update(['is_default' => true]);
        }

        $this->settings->flush();

        return back()->with('success', 'تم حفظ إعدادات الطباعة بنجاح');
    }
}
