<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkOrderActionRequest;
use App\Http\Requests\Admin\StoreOrderNoteRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderNote;
use App\Models\Order\OrderStatusHistory;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): View
    {
        $query = Order::with('user', 'items');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhere('guest_phone', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->min_total) {
            $query->where('grand_total', '>=', $request->min_total);
        }
        if ($request->max_total) {
            $query->where('grand_total', '<=', $request->max_total);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::whereIn('status', ['confirmed', 'processing'])->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'today' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())->sum('grand_total'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order): View
    {
        $order->load('user', 'items.product', 'shippingAddress', 'shippingCompany', 'payment', 'coupon', 'notes.user', 'statusHistory.user');
        $notes = $order->notes()->latest()->get();
        $statusHistory = $order->statusHistory()->latest()->get();

        return view('admin.orders.show', compact('order', 'notes', 'statusHistory'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $previousStatus = $order->status;
        $this->orderService->updateStatus($order, $request->status);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => $request->status,
            'previous_status' => $previousStatus,
            'note' => $request->input('note'),
        ]);

        return redirect()->back()->with('success', 'تم تحديث حالة الطلب');
    }

    public function addNote(StoreOrderNoteRequest $request, Order $order): RedirectResponse
    {
        OrderNote::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'note' => $request->note,
            'is_customer_note' => $request->boolean('is_customer_note'),
        ]);

        return redirect()->back()->with('success', 'تم إضافة الملاحظة');
    }

    public function deleteNote(OrderNote $note): RedirectResponse
    {
        $note->delete();

        return redirect()->back()->with('success', 'تم حذف الملاحظة');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'تم حذف الطلب');
    }

    public function bulkAction(BulkOrderActionRequest $request): RedirectResponse
    {
        $action = $request->action;
        $orderIds = $request->order_ids;
        $orders = Order::whereIn('id', $orderIds)->get();
        $count = 0;

        foreach ($orders as $order) {
            if ($action === 'update_status') {
                $previousStatus = $order->status;
                if ($previousStatus !== $request->status) {
                    $this->orderService->updateStatus($order, $request->status);
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'user_id' => auth()->id(),
                        'status' => $request->status,
                        'previous_status' => $previousStatus,
                        'note' => 'تحديث جماعي للحالة',
                    ]);
                    $count++;
                }
            } else {
                $order->delete();
                $count++;
            }
        }

        $messages = [
            'update_status' => "تم تحديث حالة {$count} طلب",
            'delete' => "تم حذف {$count} طلب",
            'print_labels' => "تم تحديد {$count} طلب للطباعة",
            'print_invoices' => "تم تحديد {$count} طلب لطباعة الفواتير",
            'print_customer_labels' => "تم تحديد {$count} طلب لطباعة ملصقات العملاء",
        ];

        return redirect()->route('admin.orders.index')->with('success', $messages[$action] ?? 'تم تنفيذ الإجراء');
    }
}
