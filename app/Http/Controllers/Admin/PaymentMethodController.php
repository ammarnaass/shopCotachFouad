<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $methods = PaymentMethod::ordered()->get();

        return view('admin.payment-methods.index', compact('methods'));
    }

    public function create(): View
    {
        return view('admin.payment-methods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_methods,code',
            'icon' => 'required|string|max:60',
            'color' => 'required|string|max:30',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:manual,gateway,wallet',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'fees_type' => 'required|in:fixed,percent',
            'fees_value' => 'nullable|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_order' => 'nullable|numeric|min:0',
            'instructions' => 'nullable|string|max:2000',
        ]);

        $data['is_active'] = $request->boolean('is_active', false);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['fees_value'] = $data['fees_value'] ?? 0;

        PaymentMethod::create($data);

        return redirect()->route('admin.payment-methods.index')->with('success', __t('admin.payment_methods.created'));
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.edit', ['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_methods,code,'.$paymentMethod->id,
            'icon' => 'required|string|max:60',
            'color' => 'required|string|max:30',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:manual,gateway,wallet',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'fees_type' => 'required|in:fixed,percent',
            'fees_value' => 'nullable|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_order' => 'nullable|numeric|min:0',
            'instructions' => 'nullable|string|max:2000',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['fees_value'] = $data['fees_value'] ?? 0;

        $paymentMethod->update($data);

        return redirect()->route('admin.payment-methods.index')->with('success', __t('admin.payment_methods.updated'));
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')->with('success', __t('admin.payment_methods.deleted'));
    }

    public function toggleActive(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);

        return back()->with('success', __t('admin.payment_methods.updated'));
    }
}
