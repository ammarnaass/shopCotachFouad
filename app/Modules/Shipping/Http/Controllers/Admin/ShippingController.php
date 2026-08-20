<?php

namespace App\Modules\Shipping\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\Orders\Models\Order;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Shipping\Models\ShippingCompany;
use App\Modules\Shipping\Models\ShippingLabel;
use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Shipping\Models\ShippingOfficePickup;
use App\Modules\Shipping\Models\ShippingTracking;
use App\Modules\Shipping\Models\ShippingZone;
use App\Modules\Shipping\Services\ShippingCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ShippingController extends Controller
{
    // ============================================
    //  MAIN INDEX - Show all shipping tabs
    // ============================================
    public function index(): View
    {
        $activeTab = request('tab', 'zones');
        $companies = ShippingCompany::latest()->paginate(20, ['*'], 'c_page');
        $zones = ShippingZone::with(['company', 'methods'])->orderBy('priority')->paginate(20, ['*'], 'z_page');
        $methods = ShippingMethod::with(['zone', 'carrier'])->orderBy('sort_order')->paginate(20, ['*'], 'm_page');
        $labels = ShippingLabel::with(['order', 'carrier'])->latest()->paginate(20, ['*'], 'l_page');
        $pickupOffices = ShippingOfficePickup::with('carrier')->latest()->paginate(20, ['*'], 'p_page');

        $stats = [
            'zones_count' => ShippingZone::count(),
            'methods_count' => ShippingMethod::count(),
            'carriers_count' => ShippingCompany::count(),
            'labels_count' => ShippingLabel::count(),
            'pending_labels' => ShippingLabel::where('status', 'pending')->count(),
            'shipped_labels' => ShippingLabel::where('status', 'shipped')->count(),
            'pickup_offices_count' => ShippingOfficePickup::count(),
        ];

        return view('admin.shipping.index', compact(
            'activeTab', 'companies', 'zones', 'methods', 'labels', 'stats', 'pickupOffices'
        ));
    }

    // ============================================
    //  COMPANIES CRUD
    // ============================================
    public function createCompany(): View
    {
        return view('admin.shipping.company-form', ['company' => null]);
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        $data = $this->validateCompany($request);
        ShippingCompany::create($data);
        Cache::forget('shipping_companies');

        return redirect()->route('admin.shipping.index', ['tab' => 'companies'])->with('success', 'تم إضافة شركة الشحن بنجاح');
    }

    public function editCompany(ShippingCompany $company): View
    {
        return view('admin.shipping.company-form', compact('company'));
    }

    public function updateCompany(Request $request, ShippingCompany $company): RedirectResponse
    {
        $data = $this->validateCompany($request);
        $company->update($data);
        Cache::forget('shipping_companies');

        return redirect()->route('admin.shipping.index', ['tab' => 'companies'])->with('success', 'تم تحديث شركة الشحن بنجاح');
    }

    public function destroyCompany(ShippingCompany $company): RedirectResponse
    {
        $company->delete();
        Cache::forget('shipping_companies');

        return redirect()->route('admin.shipping.index', ['tab' => 'companies'])->with('success', 'تم حذف شركة الشحن بنجاح');
    }

    // ============================================
    //  ZONES CRUD
    // ============================================
    public function createZone(): View
    {
        $companies = ShippingCompany::where('status', 'active')->get();
        $countries = config('ecommerce.countries', []);

        return view('admin.shipping.zone-form', ['zone' => null, 'companies' => $companies, 'countries' => $countries]);
    }

    public function storeZone(Request $request): RedirectResponse
    {
        $data = $this->validateZone($request);
        $data['regions'] = $data['regions'] ?? [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            if (!empty($data['is_default'])) {
                ShippingZone::where('is_default', true)->update(['is_default' => false]);
            }
            ShippingZone::create($data);
        });

        return redirect()->route('admin.shipping.index', ['tab' => 'zones'])->with('success', 'تم إضافة منطقة الشحن بنجاح');
    }

    public function editZone(ShippingZone $zone): View
    {
        $companies = ShippingCompany::where('status', 'active')->get();
        $countries = config('ecommerce.countries', []);

        return view('admin.shipping.zone-form', compact('zone', 'companies', 'countries'));
    }

    public function updateZone(Request $request, ShippingZone $zone): RedirectResponse
    {
        $data = $this->validateZone($request);
        $data['regions'] = $data['regions'] ?? [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $zone) {
            if (!empty($data['is_default'])) {
                ShippingZone::where('is_default', true)->where('id', '!=', $zone->id)->update(['is_default' => false]);
            }
            $zone->update($data);
        });

        return redirect()->route('admin.shipping.index', ['tab' => 'zones'])->with('success', 'تم تحديث منطقة الشحن بنجاح');
    }

    public function destroyZone(ShippingZone $zone): RedirectResponse
    {
        if ($zone->isEverywhere()) {
            return redirect()->route('admin.shipping.index', ['tab' => 'zones'])
                ->with('error', 'لا يمكن حذف منطقة النظام المحجوزة (مواقع غير مغطاة بمناطقك الأخرى).');
        }

        $zone->delete();

        return redirect()->route('admin.shipping.index', ['tab' => 'zones'])->with('success', 'تم حذف منطقة الشحن بنجاح');
    }

    public function reorderZones(Request $request): \Illuminate\Http\JsonResponse
    {
        $order = $request->input('order', $request->input('ids', []));
        if (!is_array($order) || empty($order)) {
            return response()->json(['success' => false, 'message' => 'الترتيب غير صالح'], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                ShippingZone::where('id', $id)
                    ->where('zone_type', 'standard')
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ ترتيب المناطق بنجاح',
        ]);
    }

    // ============================================
    //  METHODS CRUD
    // ============================================
    public function createMethod(): View
    {
        $zones = ShippingZone::where('status', 'active')->get();
        $carriers = ShippingCompany::where('status', 'active')->get();
        $productClass = class_exists(Product::class) ? Product::class : \App\Models\Catalog\Product::class;
        $products = $productClass::where('status', 'active')->select('id', 'name')->get();

        $defaultZoneId = request('zone_id');

        return view('admin.shipping.method-form', [
            'method' => null, 'zones' => $zones, 'carriers' => $carriers, 'products' => $products, 'defaultZoneId' => $defaultZoneId,
        ]);
    }

    public function storeMethod(Request $request): RedirectResponse
    {
        $data = $this->validateMethod($request);
        $data['shipping_zone_id'] = $data['zone_id'] ?? null;
        $data['base_cost'] = (float) ($data['flat_rate_amount'] ?? $data['base_cost'] ?? 0);
        $data['calculation_type'] = match ($data['type'] ?? 'flat_rate') {
            'weight_based' => 'weight_based',
            'price_based'  => 'price_based',
            default        => 'flat',
        };
        $data['status'] = $request->has('status') ? (bool)$request->status : true;

        ShippingMethod::create($data);

        if ($request->filled('return_to_zone')) {
            return redirect()->route('admin.shipping.zone.edit', $request->input('return_to_zone'))->with('success', 'تم إضافة طريقة الشحن بنجاح');
        }

        return redirect()->route('admin.shipping.index', ['tab' => 'methods'])->with('success', 'تم إضافة طريقة الشحن بنجاح');
    }

    public function editMethod(ShippingMethod $method): View
    {
        $zones = ShippingZone::where('status', 'active')->get();
        $carriers = ShippingCompany::where('status', 'active')->get();
        $productClass = class_exists(Product::class) ? Product::class : \App\Models\Catalog\Product::class;
        $products = $productClass::where('status', 'active')->select('id', 'name')->get();
        $defaultZoneId = $method->shipping_zone_id ?? $method->zone_id;

        return view('admin.shipping.method-form', compact('method', 'zones', 'carriers', 'products', 'defaultZoneId'));
    }

    public function updateMethod(Request $request, ShippingMethod $method): RedirectResponse
    {
        $data = $this->validateMethod($request);
        $data['shipping_zone_id'] = $data['zone_id'] ?? $method->shipping_zone_id;
        if (isset($data['flat_rate_amount'])) {
            $data['base_cost'] = (float) $data['flat_rate_amount'];
        }
        if (isset($data['type'])) {
            $data['calculation_type'] = match ($data['type']) {
                'weight_based' => 'weight_based',
                'price_based'  => 'price_based',
                default        => 'flat',
            };
        }

        $method->update($data);

        if ($request->filled('return_to_zone')) {
            return redirect()->route('admin.shipping.zone.edit', $request->input('return_to_zone'))->with('success', 'تم تحديث طريقة الشحن بنجاح');
        }

        return redirect()->route('admin.shipping.index', ['tab' => 'methods'])->with('success', 'تم تحديث طريقة الشحن بنجاح');
    }

    public function toggleMethodStatus(ShippingMethod $method): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $currentStatus = ($method->status == 1 || $method->status === 'active' || $method->status === true);
        $newStatus = $currentStatus ? 0 : 1;
        $method->update(['status' => $newStatus]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'status'  => $newStatus,
                'message' => $newStatus ? 'تم تفعيل طريقة الشحن بنجاح' : 'تم إخفاء/تعطيل طريقة الشحن بنجاح',
            ]);
        }

        return redirect()->back()->with('success', $newStatus ? 'تم تفعيل طريقة الشحن بنجاح' : 'تم إخفاء/تعطيل طريقة الشحن بنجاح');
    }

    public function destroyMethod(ShippingMethod $method): RedirectResponse
    {
        $method->delete();

        return redirect()->back()->with('success', 'تم حذف طريقة الشحن بنجاح');
    }

    // Quick add method to a zone (from zone card)
    public function storeMethodForZone(Request $request, ShippingZone $zone): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:flat_rate,free_shipping,weight_based,zone_based,product_based,courier_api',
            'carrier_id' => 'nullable|exists:shipping_companies,id',
            'flat_rate_amount' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|string|max:50',
        ], [
            'name.required' => 'اسم طريقة الشحن مطلوب',
        ]);

        $data['shipping_zone_id'] = $zone->id;
        $data['zone_id'] = $zone->id;
        $data['base_cost'] = (float) ($data['flat_rate_amount'] ?? 0);
        $data['calculation_type'] = match ($data['type']) {
            'weight_based' => 'weight_based',
            'price_based'  => 'price_based',
            default        => 'flat',
        };
        $data['status'] = true;
        $data['sort_order'] = $request->input('sort_order', 0);
        $data['method_order'] = $request->input('method_order', 0);

        ShippingMethod::create($data);

        return redirect()->route('admin.shipping.index', ['tab' => 'zones'])->with('success', 'تم إضافة طريقة الشحن للمنطقة بنجاح');
    }

    // ============================================
    //  LABELS (Shipping Labels / Waybills)
    // ============================================
    public function createLabel(): View
    {
        $carriers = ShippingCompany::where('status', 'active')->get();
        $orderClass = class_exists(Order::class) ? Order::class : \App\Models\Order\Order::class;
        $orders = $orderClass::whereIn('status', ['confirmed', 'processing', 'shipped'])
            ->latest()->select('id', 'order_number', 'grand_total')->get();

        return view('admin.shipping.label-form', ['label' => null, 'carriers' => $carriers, 'orders' => $orders]);
    }

    public function storeLabel(Request $request, ShippingCalculator $calculator): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'carrier_id' => 'required|exists:shipping_companies,id',
            'weight' => 'nullable|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'tracking_number' => 'nullable|string|max:100',
        ], [
            'order_id.required' => 'الطلب مطلوب',
            'carrier_id.required' => 'شركة الشحن مطلوبة',
            'cost.required' => 'تكلفة الشحن مطلوبة',
        ]);

        if (empty($data['tracking_number'])) {
            $data['tracking_number'] = 'SH'.strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
        }

        ShippingLabel::create($data);

        return redirect()->route('admin.shipping.index', ['tab' => 'labels'])->with('success', 'تم إنشاء بوليصة الشحن بنجاح');
    }

    public function showLabel(ShippingLabel $label): View
    {
        $label->load(['order', 'carrier', 'trackingUpdates']);
        $statusOptions = ShippingTracking::getStatuses();

        return view('admin.shipping.label-show', compact('label', 'statusOptions'));
    }

    public function updateLabelStatus(Request $request, ShippingLabel $label): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,printed,shipped,delivered,returned',
        ]);

        $status = $request->status;
        $updateData = ['status' => $status];

        if ($status === 'shipped' && ! $label->shipped_at) {
            $updateData['shipped_at'] = now();
        }
        if ($status === 'delivered' && ! $label->delivered_at) {
            $updateData['delivered_at'] = now();
        }

        $label->update($updateData);

        // Also update the order status
        if ($status === 'shipped') {
            $label->order?->update(['status' => 'shipped', 'shipping_company_id' => $label->carrier_id]);
        }
        if ($status === 'delivered') {
            $label->order?->update(['status' => 'delivered']);
        }

        return back()->with('success', 'تم تحديث حالة البوليصة');
    }

    public function addTrackingUpdate(Request $request, ShippingLabel $label, ShippingCalculator $calculator): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|string|max:50',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $calculator->addTrackingUpdate($label->id, $data['status'], $data['location'], $data['description']);

        return back()->with('success', 'تم إضافة تحديث التتبع');
    }

    // ============================================
    //  TRACKING (Public)
    // ============================================
    public function track(string $number, ShippingCalculator $calculator)
    {
        $tracking = $calculator->trackShipment($number);
        if (! $tracking) {
            return response()->json(['error' => 'رقم التتبع غير موجود'], 404);
        }

        return response()->json($tracking);
    }

    // ============================================
    //  API: Calculate shipping
    // ============================================
    public function calculateShipping(Request $request, ShippingCalculator $calculator)
    {
        $request->validate([
            'country_id' => 'nullable|integer',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'items' => 'nullable|array',
            'coupon_code' => 'nullable|string',
        ]);

        $coupon = null;
        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)->where('status', 'active')->first();
        }

        $result = $calculator->calculate(
            $request->items ?? [],
            $request->country_id,
            $request->state_id,
            $request->city_id,
            $coupon
        );

        return response()->json($result);
    }

    // ============================================
    //  PRINT LABEL (PDF)
    // ============================================
    public function printLabel(ShippingLabel $label)
    {
        $label->load(['order.items', 'order.shippingAddress', 'carrier']);

        $html = view('admin.shipping.label-pdf', compact('label'))->render();

        return app(\App\Services\LabelPdfService::class)
            ->download($html, 'label-'.$label->tracking_number.'.pdf', 'rtl', 'A5-L');
    }

    // ============================================
    //  BULK SHIP (create labels for multiple orders)
    // ============================================
    public function bulkShip(Request $request, ShippingCalculator $calculator): RedirectResponse
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'carrier_id' => 'required|exists:shipping_companies,id',
        ], [
            'order_ids.required' => 'اختر طلباتاً واحداً على الأقل',
            'carrier_id.required' => 'اختر شركة الشحن',
        ]);

        $orderClass = class_exists(Order::class) ? Order::class : \App\Models\Order\Order::class;
        $orders = $orderClass::whereIn('id', $request->order_ids)
            ->whereIn('status', ['confirmed', 'processing'])
            ->get();

        $created = 0;
        foreach ($orders as $order) {
            $exists = ShippingLabel::where('order_id', $order->id)->exists();
            if (! $exists) {
                $calculator->createLabel(
                    $order->id,
                    $request->carrier_id,
                    $order->items->sum(fn ($item) => ($item->weight ?? 0) * $item->quantity),
                    $order->shipping_cost ?? 0
                );
                $created++;
            }
        }

        return redirect()->route('admin.shipping.index', ['tab' => 'labels'])
            ->with('success', "تم إنشاء {$created} بوليصة شحن بنجاح");
    }

    // ============================================
    //  PICKUP OFFICES CRUD
    // ============================================
    public function createPickup(): View
    {
        $carriers = ShippingCompany::where('is_active', true)->orderBy('name')->get();

        return view('admin.shipping.pickup-form', ['pickup' => null, 'carriers' => $carriers]);
    }

    public function storePickup(Request $request): RedirectResponse
    {
        $data = $this->validatePickup($request);
        ShippingOfficePickup::create($data);

        return redirect()->route('admin.shipping.index', ['tab' => 'pickups'])
            ->with('success', 'تم إضافة مكتب الاستلام');
    }

    public function editPickup(ShippingOfficePickup $pickup): View
    {
        $carriers = ShippingCompany::where('is_active', true)->orderBy('name')->get();
        $pickup->load('carrier');

        return view('admin.shipping.pickup-form', compact('pickup', 'carriers'));
    }

    public function updatePickup(Request $request, ShippingOfficePickup $pickup): RedirectResponse
    {
        $data = $this->validatePickup($request);
        $pickup->update($data);

        return redirect()->route('admin.shipping.index', ['tab' => 'pickups'])
            ->with('success', 'تم تحديث مكتب الاستلام');
    }

    public function destroyPickup(ShippingOfficePickup $pickup): RedirectResponse
    {
        $pickup->delete();

        return redirect()->route('admin.shipping.index', ['tab' => 'pickups'])
            ->with('success', 'تم حذف مكتب الاستلام');
    }

    private function validatePickup(Request $request): array
    {
        return $request->validate([
            'carrier_id' => 'required|exists:shipping_companies,id',
            'name' => 'required|string|max:100',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country_code' => 'required|string|size:2',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'working_hours' => 'nullable|array',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ], [
            'carrier_id.required' => 'شركة الشحن مطلوبة',
            'name.required' => 'اسم المكتب مطلوب',
            'address.required' => 'العنوان مطلوب',
            'city.required' => 'المدينة مطلوبة',
            'country_code.required' => 'الدولة مطلوبة',
        ]);
    }

    // ============================================
    //  VALIDATION HELPERS
    // ============================================
    private function validateCompany(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|url',
            'website' => 'nullable|url',
            'tracking_url' => 'required|url',
            'api_endpoint' => 'nullable|url',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'اسم الشركة مطلوب',
            'tracking_url.required' => 'رابط التتبع مطلوب',
            'tracking_url.url' => 'رابط التتبع غير صحيح',
        ]);
    }

    private function validateZone(Request $request): array
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:shipping_companies,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'countries' => 'nullable|array',
            'states' => 'nullable|array',
            'cities' => 'nullable|array',
            'delivery_type' => 'required|in:home,office,both',
            'cost' => 'nullable|numeric|min:0',
            'express_cost' => 'nullable|numeric|min:0',
            'home_cost' => 'nullable|numeric|min:0',
            'home_express_cost' => 'nullable|numeric|min:0',
            'office_cost' => 'nullable|numeric|min:0',
            'office_express_cost' => 'nullable|numeric|min:0',
            'cost_per_kg' => 'nullable|numeric|min:0',
            'free_threshold' => 'nullable|numeric|min:0',
            'estimated_days_standard' => 'nullable|string|max:30',
            'estimated_days_express' => 'nullable|string|max:30',
            'is_default' => 'boolean',
            'priority' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'اسم المنطقة مطلوب',
        ]);

        $validated['cost'] = (float)($validated['cost'] ?? $validated['home_cost'] ?? $validated['office_cost'] ?? 0);
        if (isset($validated['home_express_cost']) || isset($validated['office_express_cost'])) {
            $validated['express_cost'] = (float)($validated['express_cost'] ?? $validated['home_express_cost'] ?? $validated['office_express_cost'] ?? 0);
        }

        return $validated;
    }

    private function validateMethod(Request $request): array
    {
        $rules = [
            'zone_id' => 'required|exists:shipping_zones,id',
            'name' => 'required|string|max:100',
            'type' => 'required|in:flat_rate,free_shipping,weight_based,zone_based,product_based,courier_api',
            'carrier_id' => 'nullable|exists:shipping_companies,id',
            'estimated_days' => 'nullable|string|max:50',
            'tax_status' => 'nullable|in:taxable,none',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ];

        // Type-specific rules
        if ($request->type === 'flat_rate' || $request->type === 'courier_api') {
            $rules['flat_rate_amount'] = 'required|numeric|min:0';
        }
        if ($request->type === 'free_shipping') {
            $rules['free_shipping_min'] = 'nullable|numeric|min:0';
            $rules['free_shipping_requires'] = 'nullable|in:min_amount,coupon,both';
        }
        if ($request->type === 'weight_based') {
            $rules['weight_ranges'] = 'nullable|array';
        }
        if ($request->type === 'product_based') {
            $rules['product_ids'] = 'nullable|array';
        }
        if ($request->type === 'courier_api') {
            $rules['api_settings'] = 'nullable|array';
        }
        if ($request->type === 'zone_based') {
            $rules['zone_rates'] = 'nullable|array';
        }

        $messages = [
            'name.required' => 'اسم طريقة الشحن مطلوب',
            'zone_id.required' => 'المنطقة مطلوبة',
            'zone_id.exists' => 'المنطقة غير موجودة',
            'flat_rate_amount.required' => 'مبلغ الشحن مطلوب',
        ];

        return $request->validate($rules, $messages);
    }
}
