<?php

namespace App\Modules\Dashboard\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Catalog\Models\Product;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Shipping\Models\ShippingCompany;
use App\Modules\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_revenue' => Order::where('payment_status', 'paid')->sum('grand_total')
                + Order::where('status', 'delivered')->whereHas('payment', fn ($q) => $q->where('method', 'cod'))->sum('grand_total'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'instant_buy_orders' => Order::where('is_instant_buy', true)->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'new_customers_this_month' => User::where('role', 'customer')
                ->where('created_at', '>=', now()->subDays(30))->count(),
            'total_products' => Product::count(),
            'low_stock' => Product::where('stock', '<', 10)->where('stock', '>', 0)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
        ];

        $current30 = Order::where('created_at', '>=', now()->subDays(30))
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('grand_total');
        $previous30 = Order::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('grand_total');
        $stats['revenue_growth'] = $previous30 > 0
            ? round((($current30 - $previous30) / $previous30) * 100, 1)
            : 100;
        $stats['orders_growth'] = $this->growthPercent(Order::class, 30);

        $weeklySales = Order::where('created_at', '>=', now()->subDays(6))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(grand_total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $weeklyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $row = $weeklySales->firstWhere('date', $date);
            $weeklyChart[] = [
                'date' => $date,
                'day' => now()->subDays($i)->format('D'),
                'orders' => $row ? (int) $row->orders_count : 0,
                'revenue' => $row ? (float) $row->revenue : 0,
            ];
        }

        $statusDistribution = [
            ['status' => 'pending',    'label' => 'قيد الانتظار', 'count' => $stats['pending_orders'],    'color' => '#F59E0B'],
            ['status' => 'processing', 'label' => 'قيد التجهيز',  'count' => $stats['processing_orders'], 'color' => '#6366F1'],
            ['status' => 'shipped',    'label' => 'تم الشحن',     'count' => $stats['shipped_orders'],    'color' => '#8B5CF6'],
            ['status' => 'delivered',  'label' => 'تم التسليم',   'count' => $stats['delivered_orders'],  'color' => '#10B981'],
            ['status' => 'cancelled',  'label' => 'ملغي',         'count' => $stats['cancelled_orders'],  'color' => '#EF4444'],
        ];

        $recentOrders = Order::with(['user', 'shippingAddress', 'items'])
            ->latest()
            ->limit(8)
            ->get();

        $lowStockProducts = Product::with('category')
            ->where('stock', '<', 10)
            ->orderBy('stock', 'asc')
            ->limit(6)
            ->get();

        $topProducts = OrderItem::select('product_id', 'product_name',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(total) as total_revenue'),
            DB::raw('COUNT(DISTINCT order_id) as orders_count'))
            ->whereNotNull('product_id')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $bestRated = Product::whereHas('reviews')
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews')
            ->orderByDesc('avg_rating')
            ->limit(5)
            ->get();

        $activeCoupons = Coupon::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->limit(5)
            ->get();

        $shippingCompanies = ShippingCompany::where('status', 'active')->get();

        $settings = [
            'currency' => config('ecommerce.store.currency').' ('.config('ecommerce.store.currency_symbol').')',
            'shipping_company' => config('ecommerce.shipping.default_company'),
            'payment_method' => 'COD (عند الاستلام)',
            'theme' => 'Light Mode',
            'store_name' => config('app.name'),
        ];

        return view('admin.dashboard', compact(
            'stats', 'weeklyChart', 'statusDistribution', 'recentOrders',
            'lowStockProducts', 'topProducts', 'bestRated', 'activeCoupons',
            'shippingCompanies', 'settings'
        ));
    }

    public function quickSetting(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|in:shipping_company,store_name',
            'value' => 'required|string',
        ]);

        $envKey = match ($data['key']) {
            'shipping_company' => 'SHIPPING_DEFAULT_COMPANY',
            'store_name' => 'APP_NAME',
        };

        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return response()->json(['success' => false, 'message' => '.env غير موجود'], 404);
        }

        $content = file_get_contents($envPath);
        $pattern = '/^'.preg_quote($envKey, '/').'=.*$/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $envKey.'='.$data['value'], $content);
        } else {
            $content .= PHP_EOL.$envKey.'='.$data['value'];
        }
        file_put_contents($envPath, $content);

        \Artisan::call('config:clear');

        return response()->json(['success' => true, 'message' => 'تم التحديث بنجاح']);
    }

    private function growthPercent(string $model, int $days): float
    {
        $current = $model::where('created_at', '>=', now()->subDays($days))->count();
        $previous = $model::whereBetween('created_at', [now()->subDays($days * 2), now()->subDays($days)])->count();
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
