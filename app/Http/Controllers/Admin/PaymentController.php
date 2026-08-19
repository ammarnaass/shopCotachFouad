<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order\Payment;
use App\Models\Order\PaymentMethod;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::with('order')->latest()->paginate(20);
        $methods = PaymentMethod::ordered()->get();
        $stats = [
            'total' => Payment::count(),
            'paid' => Payment::where('status', 'paid')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'cod_revenue' => Payment::where('method', 'cod')->where('status', 'paid')->sum('amount'),
        ];

        return view('admin.payments.index', compact('payments', 'stats', 'methods'));
    }
}
