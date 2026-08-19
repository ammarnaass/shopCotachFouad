<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): View
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('frontend.orders.index', compact('orders'));
    }

    public function show(int $id): View
    {
        $order = Order::with(['items.product', 'shippingAddress', 'payment', 'shippingCompany'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('frontend.orders.show', compact('order'));
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        try {
            $this->orderService->cancelOrder($order, $request->reason);

            return redirect()->back()->with('success', 'تم إلغاء الطلب');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
