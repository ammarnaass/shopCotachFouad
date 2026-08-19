<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays((int) $period);

        // Revenue
        $revenueData = Order::where('created_at', '>=', $startDate)
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->selectRaw('DATE(created_at) as date, SUM(grand_total) as revenue, COUNT(*) as orders_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Daily chart data
        $chartData = [];
        for ($i = (int) $period; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $row = $revenueData->firstWhere('date', $date);
            $chartData[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('d M'),
                'revenue' => $row ? (float) $row->revenue : 0,
                'orders' => $row ? (int) $row->orders_count : 0,
            ];
        }

        // Status breakdown
        $statusBreakdown = Order::where('created_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count, SUM(grand_total) as total')
            ->groupBy('status')
            ->get();

        // Top products
        $topProducts = Order::where('orders.created_at', '>=', $startDate)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.quantity) as qty, SUM(order_items.total) as revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Top categories
        $topCategories = Order::where('orders.created_at', '>=', $startDate)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.id, categories.name, SUM(order_items.total) as revenue, COUNT(DISTINCT orders.id) as orders_count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Country revenue
        $countryRevenue = Order::where('orders.created_at', '>=', $startDate)
            ->join('shipping_addresses', 'orders.shipping_address_id', '=', 'shipping_addresses.id')
            ->selectRaw('shipping_addresses.country_code, COUNT(*) as orders_count, SUM(orders.grand_total) as revenue')
            ->groupBy('shipping_addresses.country_code')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($row) {
                $countries = config('ecommerce.countries');
                $info = $countries[$row->country_code] ?? null;
                $row->country_name = $info['name'] ?? $row->country_code;

                return $row;
            });

        // Summary
        $summary = [
            'total_revenue' => $revenueData->sum('revenue'),
            'total_orders' => $revenueData->sum('orders_count'),
            'avg_order_value' => $revenueData->sum('orders_count') > 0 ? round($revenueData->sum('revenue') / $revenueData->sum('orders_count'), 2) : 0,
            'new_customers' => User::where('created_at', '>=', $startDate)->count(),
            'completed_orders' => Order::where('created_at', '>=', $startDate)->where('status', 'delivered')->count(),
        ];

        return view('admin.reports.index', compact('chartData', 'statusBreakdown', 'topProducts', 'topCategories', 'countryRevenue', 'summary', 'period'));
    }
}
