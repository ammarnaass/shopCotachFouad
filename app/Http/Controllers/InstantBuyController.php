<?php

namespace App\Http\Controllers;

use App\Actions\Order\CreateInstantOrder;
use App\Data\Repositories\Contracts\CouponRepositoryInterface;
use App\Http\Requests\Web\InstantBuyRequest;
use App\Models\Catalog\Product;
use App\Models\Order\Order;
use App\Models\Shipping\ShippingCompany;
use App\Services\CouponService;
use App\Services\DynamicShippingService;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstantBuyController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PricingService $pricing,
        private CouponService $coupons,
    ) {}

    public function create(Request $request, ?string $slug = null): View
    {
        $product = null;
        $productJson = 'null';
        if ($slug) {
            $product = Product::active()->where('slug', $slug)
                ->with(['images', 'options.values', 'customFields', 'primaryImage', 'shippingCompany'])
                ->firstOrFail();
            $productJson = json_encode([
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'sale_price' => (float) ($product->sale_price ?? 0),
                'discount_percent' => (int) ($product->discount_percent ?? 0),
                'stock' => (int) $product->stock,
                'image' => $product->primaryImage ? asset('storage/'.$product->primaryImage->image) : null,
                'options' => $product->options->mapWithKeys(fn ($o) => [$o->id => ['label' => $o->name, 'values' => $o->values->pluck('value', 'id')->toArray()]])->toArray(),
                'option_adjustments' => $product->options->flatMap(fn ($o) => $o->values->pluck('price_adjustment', 'id'))->toArray(),
                'custom_fields' => $product->customFields->map(fn ($f) => ['label' => $f->label, 'type' => $f->type, 'price_effect' => (float) $f->price_effect, 'required' => (bool) $f->required])->toArray(),
                'shipping_company_id' => $product->shipping_company_id,
                'shipping_company_name' => $product->shippingCompany?->name,
            ], JSON_UNESCAPED_UNICODE);
        }

        $countries = config('ecommerce.countries', []);
        $defaultCountry = session('selected_country', config('ecommerce.default_country', 'DZ'));
        $popularProducts = Product::active()->with('primaryImage')->latest()->take(8)->get();
        $shippingCompanies = ShippingCompany::where('status', 'active')->orderBy('name')->get();

        return view('frontend.instant.buy', compact('product', 'productJson', 'countries', 'defaultCountry', 'popularProducts', 'shippingCompanies'));
    }

    public function shippingOptions(Request $request, DynamicShippingService $shippingService): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'country_code' => 'required|string|size:2',
            'city' => 'required|string',
            'delivery_type' => 'nullable|in:home,office',
        ]);

        $product = Product::with('shippingCompany')->findOrFail($data['product_id']);
        $companyId = $product->shipping_company_id;
        $fixedCompany = $companyId ? ShippingCompany::find($companyId) : null;

        $supportedDeliveryTypes = $shippingService->getSupportedDeliveryTypes(
            $data['product_id'], $data['country_code'], $data['city']
        );

        $reqDeliveryType = $data['delivery_type'] ?? null;
        $deliveryType = ($reqDeliveryType && in_array($reqDeliveryType, $supportedDeliveryTypes))
            ? $reqDeliveryType
            : $supportedDeliveryTypes[0];

        $result = $shippingService->getAvailableMethods(
            $data['product_id'], $data['country_code'], $data['city'],
            $deliveryType, $companyId
        );

        $options = [];
        $companies = [];

        foreach ($result['available'] as $item) {
            $options[] = [
                'type' => $item['id'] ? 'method_'.$item['id'] : $item['type'],
                'label' => $item['name'],
                'method_id' => $item['id'],
                'company_id' => $item['carrier_id'],
                'company_name' => $item['carrier'],
                'zone_id' => $item['zone_id'],
                'delivery_type' => $item['delivery_type'],
                'cost' => $item['cost'],
                'is_free' => $item['is_free'],
                'estimated_days' => $item['estimated_days'],
                'is_cod_available' => $item['is_cod_available'],
                'pickup_location' => $item['pickup_location'],
            ];

            if ($item['carrier_id'] && ! isset($companies[$item['carrier_id']])) {
                $companies[$item['carrier_id']] = [
                    'id' => $item['carrier_id'],
                    'name' => $item['carrier'],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'options' => $options,
            'companies' => array_values($companies),
            'fixed_company' => $fixedCompany ? ['id' => $fixedCompany->id, 'name' => $fixedCompany->name] : null,
            'supported_delivery_types' => $supportedDeliveryTypes,
            'zone_delivery_type' => $supportedDeliveryTypes[0] ?? 'home',
        ]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'options' => 'nullable|array',
            'custom_text' => 'nullable|string|max:500',
            'country_code' => 'required|string|size:2',
            'city' => 'required|string',
            'state_code' => 'nullable|string|max:5',
            'shipping_method' => 'nullable|in:standard,express',
            'delivery_type' => 'nullable|in:home,office',
            'shipping_company_id' => 'nullable|exists:shipping_companies,id',
            'coupon_code' => 'nullable|string',
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        $qty = (int) ($data['quantity'] ?? 1);
        $method = $data['shipping_method'] ?? 'standard';

        $price = $this->pricing->calculateProductPrice($product, $data['options'] ?? [], $data['custom_text'] ?? null);
        $subtotal = $this->pricing->calculateSubtotal($price['unit_price'], $qty);
        $weight = $this->pricing->calculateWeight((float) ($product->weight ?? 0), $qty);

        $shippingCost = 0;
        $shippingFree = false;
        try {
            $shippingCost = $this->orderService->calculateShipping(
                $data['city'], $method, $subtotal, $data['country_code'],
                $data['delivery_type'] ?? 'home', $weight,
                $data['shipping_company_id'] ?? null, $data['state_code'] ?? null
            );
            $shippingFree = $shippingCost === 0.0 && $subtotal > 0;
        } catch (\Throwable $e) {
            $shippingCost = 0;
        }

        $couponResult = $this->coupons->validate($data['coupon_code'] ?? null, $subtotal);

        $total = max(0, $subtotal + $shippingCost - ($couponResult['discount'] ?? 0));

        $countrySymbol = config("ecommerce.countries.{$data['country_code']}.currency_symbol")
            ?? config('ecommerce.store.currency_symbol');

        return response()->json([
            'success' => true,
            'base_price' => $price['base_price'],
            'unit_price' => $price['unit_price'],
            'options_adjustment' => $price['options_adjustment'],
            'options_summary' => $price['options_summary'],
            'custom_field_price' => $price['custom_field_price'],
            'quantity' => $qty,
            'subtotal' => round($subtotal, 2),
            'shipping_cost' => round($shippingCost, 2),
            'shipping_free' => $shippingFree,
            'weight' => round($weight, 2),
            'discount' => round($couponResult['discount'] ?? 0, 2),
            'coupon' => $couponResult['coupon'] ? [
                'code' => $couponResult['coupon']->code,
                'type' => $couponResult['coupon']->type,
                'value' => (float) $couponResult['coupon']->value,
                'description' => $couponResult['coupon']->type === 'percent'
                    ? "خصم {$couponResult['coupon']->value}%"
                    : "خصم {$couponResult['coupon']->value}",
            ] : null,
            'total' => round($total, 2),
            'currency_symbol' => $countrySymbol,
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $couponResult = $this->coupons->validate($data['code'], (float) $data['subtotal']);

        if (! $couponResult) {
            $coupon = app(CouponRepositoryInterface::class)->findByCode($data['code']);
            $msg = 'كوبون غير صالح';
            if ($coupon) {
                if ($coupon->expiry_date && $coupon->expiry_date->isPast()) {
                    $msg = 'الكوبون منتهي الصلاحية';
                } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                    $msg = 'تم استنفاد الكوبون';
                } elseif ($coupon->min_order && $data['subtotal'] < $coupon->min_order) {
                    $msg = "الحد الأدنى للطلب {$coupon->min_order}";
                }
            }

            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        return response()->json([
            'success' => true,
            'coupon' => [
                'code' => $couponResult['coupon']->code,
                'type' => $couponResult['coupon']->type,
                'value' => (float) $couponResult['coupon']->value,
                'discount' => round($couponResult['discount'], 2),
                'description' => $couponResult['coupon']->type === 'percent'
                    ? "خصم {$couponResult['coupon']->value}%"
                    : "خصم {$couponResult['coupon']->value}",
            ],
        ]);
    }

    public function submit(InstantBuyRequest $request): JsonResponse|RedirectResponse
    {
        $product = Product::active()->findOrFail($request->product_id);

        if ($product->stock < (int) $request->quantity) {
            return $this->errorResponse($request, 'الكمية المطلوبة غير متوفرة في المخزون');
        }

        $order = app(CreateInstantOrder::class)->execute(
            $request->validated(),
            $product,
            $request->file('custom_file'),
        );

        $isGuest = ! Auth::check();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect' => $isGuest
                    ? route('instant.thankyou', $order->order_number)
                    : route('orders.show', $order->id),
            ]);
        }

        $redirectRoute = $isGuest
            ? route('instant.thankyou', $order->order_number)
            : route('orders.show', $order->id);

        return redirect($redirectRoute)->with('success', 'تم إنشاء الطلب بنجاح. رقم الطلب: '.$order->order_number);
    }

    public function thankyou(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with(['items', 'shippingAddress', 'payment'])->firstOrFail();
        $countrySymbol = config("ecommerce.countries.{$order->shippingAddress?->country_code}.currency_symbol")
            ?? config('ecommerce.store.currency_symbol');

        return view('frontend.instant.thankyou', compact('order', 'countrySymbol'));
    }

    private function errorResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return back()->withErrors(['order' => $message])->withInput();
    }
}
