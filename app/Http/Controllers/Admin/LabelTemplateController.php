<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documents\LabelTemplate;
use App\Models\Order\Order;
use App\Services\Documents\LabelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LabelTemplateController extends Controller
{
    public function __construct(private LabelService $labelService) {}

    public function index(): View
    {
        $templates = LabelTemplate::latest()->get();
        return view('admin.labels.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.labels.templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'paper_size'    => 'required|in:100x150,100x100,80x50,a6,custom',
            'custom_width'  => 'nullable|required_if:paper_size,custom|integer|min:20|max:500',
            'custom_height' => 'nullable|required_if:paper_size,custom|integer|min:20|max:500',
            'description'   => 'nullable|string',
            'status'        => 'nullable|boolean',
            'settings'      => 'nullable|array',
        ]);

        $validated['slug']       = Str::slug($request->input('name')) . '-' . Str::random(5);
        $validated['status']     = $request->boolean('status', true);
        $validated['is_default'] = false;

        LabelTemplate::create($validated);

        return redirect()->route('admin.order-labels.templates.index')->with('success', 'تم إنشاء قالب الملصق بنجاح');
    }

    public function edit(LabelTemplate $template): View
    {
        return view('admin.labels.templates.edit', compact('template'));
    }

    public function update(Request $request, LabelTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'paper_size'    => 'required|in:100x150,100x100,80x50,a6,custom',
            'custom_width'  => 'nullable|required_if:paper_size,custom|integer|min:20|max:500',
            'custom_height' => 'nullable|required_if:paper_size,custom|integer|min:20|max:500',
            'description'   => 'nullable|string',
            'status'        => 'nullable|boolean',
            'settings'      => 'nullable|array',
        ]);

        $validated['status'] = $request->boolean('status');
        $template->update($validated);

        return redirect()->route('admin.order-labels.templates.index')->with('success', 'تم تحديث قالب الملصق بنجاح');
    }

    public function destroy(LabelTemplate $template): RedirectResponse
    {
        if ($template->is_default) {
            return back()->with('error', 'لا يمكن حذف القالب الافتراضي');
        }

        $template->delete();
        return back()->with('success', 'تم حذف قالب الملصق بنجاح');
    }

    public function setDefault(LabelTemplate $template): RedirectResponse
    {
        LabelTemplate::where('is_default', true)->update(['is_default' => false]);
        $template->update(['is_default' => true, 'status' => true]);

        return back()->with('success', 'تم تعيين القالب كافتراضي');
    }

    public function preview(LabelTemplate $template)
    {
        $order = Order::with('items', 'shippingAddress', 'payment', 'user')->latest()->first();

        if (! $order) {
            $order = new Order([
                'id'            => 1,
                'order_number'  => 'ORD-2026-000125',
                'status'        => 'confirmed',
                'grand_total'   => 4500,
                'created_at'    => now(),
            ]);
        }

        $data = $this->labelService->getLabelData($order, $template);
        $view = 'documents.labels.' . $template->slug;
        if (! view()->exists($view)) {
            $view = 'documents.labels.classic';
        }

        if (request()->has('print')) {
            return view('documents.layouts.print', array_merge($data, [
                'doc_title' => 'معاينة ملصق: ' . $template->name,
            ]))->with('view', $view);
        }

        return view($view, $data);
    }
}
