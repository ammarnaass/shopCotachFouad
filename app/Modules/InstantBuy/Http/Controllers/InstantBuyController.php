<?php

namespace App\Modules\InstantBuy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Payments\Models\Payment;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductCustomField;
use App\Modules\Catalog\Models\ProductOptionValue;
use App\Modules\Shipping\Models\ShippingAddress;
use App\Modules\Shipping\Models\ShippingCompany;
use App\Modules\Shipping\Models\ShippingZone;
use App\Modules\Orders\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InstantBuyController extends Controller
{
    public function __construct(private ?OrderService $orderService = null)
    {
        if ($this->orderService === null && class_exists(OrderService::class)) {
            $this->orderService = app(OrderService::class);
        }
    }

    /**
     * Show the instant-buy form for a product (or product picker if no slug given).
     */
    public function create(Request $request, ?string $slug = null): View
    {
        $product = null;
        $productJson = 'null';
        if ($slug) {
            $product = Product::active()->where('slug', $slug)->with(['images', 'options.values', 'customFields', 'primaryImage'])->firstOrFail();
            $productJson = json_encode([
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'sale_price' => (float) ($product->sale_price ?? 0),
                'discount_percent' => (int) ($product->discount_percent ?? 0),
                'stock' => (int) $product->stock,
                'image' => $product->primaryImage ? asset('storage/' . $product->primaryImage->image) : null,
                'options' => $product->options->mapWithKeys(fn($o) => [$o->id => ['label' => $o->name, 'values' => $o->values->pluck('value', 'id')->toArray()]])->toArray(),
                'option_adjustments' => $product->options->flatMap(fn($o) => $o->values->pluck('price_adjustment', 'id'))->toArray(),
                'custom_fields' => $product->customFields->map(fn($f) => ['label' => $f->label, 'type' => $f->type, 'price_effect' => (float) $f->price_effect, 'required' => (bool) $f->required])->toArray(),
            ], JSON_UNESCAPED_UNICODE);
        }

        $countries = config('ecommerce.countries', []);
        $defaultCountry = session('selected_country', config('ecommerce.default_country', 'SD'));
        $popularProducts = Product::active()->with('primaryImage')->latest()->take(8)->get();
        $shippingCompanies = ShippingCompany::where('status', 'active')->orderBy('name')->get();

        return view('frontend.instant.buy', compact('product', 'productJson', 'countries', 'defaultCountry', 'popularProducts', 'shippingCompanies'));
    }

    /**
     * Live price calculation for the instant-buy form.
     * Returns JSON with subtotal, options adjustments, custom-field price, shipping, discount, total.
     */
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

        // Base price
        $base = (float) $product->final_price;

        // Options adjustments (size, color, etc.)
        $optionsAdjustment = 0;
        $optionsSummary = [];
        if (!empty($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $optionId => $valueId) {
                $value = ProductOptionValue::with('option')->find($valueId);
                if ($value && $value->option && $value->option->product_id == $product->id) {
                    $optionsAdjustment += (float) $value->price_adjustment;
                    $optionsSummary[] = [
                        'option' => $value->option->name,
                        'value' => $value->value,
                        'adjustment' => (float) $value->price_adjustment,
                    ];
                }
            }
        }

        // Custom text price (if any custom field has price_effect)
        $customFieldPrice = 0;
        if (!empty($data['custom_text']) && $product->customFields->count() > 0) {
            // The first custom field with type text/textarea determines price for custom text
            $textField = $product->customFields->firstWhere('type', 'text') ??
                         $product->customFields->firstWhere('type', 'textarea');
            if ($textField) {
                $customFieldPrice = (float) $textField->price_effect;
            }
        }

        $unitPrice = $base + $optionsAdjustment + $customFieldPrice;
        $subtotal = $unitPrice * $qty;
        $weight = (float) ($product->weight ?? 0) * $qty;

        // Shipping
        $shippingCost = 0;
        $shippingFree = false;
        try {
            if ($this->orderService) {
                $shippingCost = $this->orderService->calculateShipping(
                    $data['city'],
                    $method,
                    $subtotal,
                    $data['country_code'],
                    $data['delivery_type'] ?? 'home',
                    $weight,
                    $data['shipping_company_id'] ?? null
                );
            }
            $shippingFree = $shippingCost === 0.0 && $subtotal > 0;
        } catch (\Throwable $e) {
            $shippingCost = 0;
        }

        // Coupon discount
        $discount = 0;
        $coupon = null;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                $coupon = null;
            }
        }

        $total = max(0, $subtotal + $shippingCost - $discount);

        $countrySymbol = config("ecommerce.countries.{$data['country_code']}.currency_symbol")
            ?? config('ecommerce.store.currency_symbol');

        return response()->json([
            'success' => true,
            'base_price' => round($base, 2),
            'unit_price' => round($unitPrice, 2),
            'options_adjustment' => round($optionsAdjustment, 2),
            'options_summary' => $optionsSummary,
            'custom_field_price' => round($customFieldPrice, 2),
            'quantity' => $qty,
            'subtotal' => round($subtotal, 2),
            'shipping_cost' => round($shippingCost, 2),
            'shipping_free' => $shippingFree,
            'weight' => round($weight, 2),
            'discount' => round($discount, 2),
            'coupon' => $coupon ? [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'description' => $coupon->type === 'percent'
                    ? "خصم {$coupon->value}%"
                    : "خصم {$coupon->value}",
            ] : null,
            'total' => round($total, 2),
            'currency_symbol' => $countrySymbol,
        ]);
    }

    /**
     * Validate a coupon code in isolation (used in instant-buy before submit).
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $data['code'])->first();
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'كود غير صالح'], 404);
        }
        if (!$coupon->isValid((float) $data['subtotal'])) {
            $msg = 'كوبون غير صالح';
            if ($coupon->expiry_date && $coupon->expiry_date->isPast()) $msg = 'الكوبون منتهي الصلاحية';
            elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) $msg = 'تم استنفاد الكوبون';
            elseif ($coupon->min_order && $data['subtotal'] < $coupon->min_order) $msg = "الحد الأدنى للطلب {$coupon->min_order}";
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        $discount = $coupon->calculateDiscount((float) $data['subtotal']);

        return response()->json([
            'success' => true,
            'coupon' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'discount' => round($discount, 2),
                'description' => $coupon->type === 'percent'
                    ? "خصم {$coupon->value}%"
                    : "خصم {$coupon->value}",
            ],
        ]);
    }

    /**
     * Submit instant-buy order (handles both guests and authenticated users).
     */
    public function submit(Request $request): JsonResponse|RedirectResponse
    {
        $isGuest = !Auth::check();

        $rules = [
            'product_id' => 'required|exists:products,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'city' => 'required|string|max:100',
            'address' => 'required|string',
            'shipping_method' => 'required|in:standard,express',
            'delivery_type' => 'nullable|in:home,office',
            'shipping_company_id' => 'nullable|exists:shipping_companies,id',
            'quantity' => 'required|integer|min:1',
            'options' => 'nullable|array',
            'custom_text' => 'nullable|string|max:500',
        ];

        $settingClass = class_exists(\App\Modules\CMS\Models\Setting::class) 
            ? \App\Modules\CMS\Models\Setting::class 
            : (class_exists(\App\Models\Settings\Setting::class) ? \App\Models\Settings\Setting::class : null);

        // Conditional validation rules based on admin customize settings
        if ($settingClass && $settingClass::get('instant_show_email', '1') === '1' && $settingClass::get('instant_req_email', '0') === '1') {
            $rules['email'] = 'required|email|max:255';
        } else {
            $rules['email'] = 'nullable|email|max:255';
        }

        if ($settingClass && $settingClass::get('instant_show_state', '1') === '1' && $settingClass::get('instant_req_state', '0') === '1') {
            $rules['state_code'] = 'required|string|max:5';
        } else {
            $rules['state_code'] = 'nullable|string|max:5';
        }

        if ($settingClass && $settingClass::get('instant_show_district', '1') === '1' && $settingClass::get('instant_req_district', '0') === '1') {
            $rules['district'] = 'required|string|max:100';
        } else {
            $rules['district'] = 'nullable|string|max:100';
        }

        if ($settingClass && $settingClass::get('instant_show_zip', '1') === '1' && $settingClass::get('instant_req_zip', '0') === '1') {
            $rules['zip'] = 'required|string|max:20';
        } else {
            $rules['zip'] = 'nullable|string|max:20';
        }

        if ($settingClass && $settingClass::get('instant_show_notes', '1') === '1') {
            $rules['notes'] = 'nullable|string|max:500';
        }

        if ($settingClass && $settingClass::get('instant_show_coupon', '1') === '1') {
            $rules['coupon_code'] = 'nullable|string';
        }

        $allowedPaymentMethods = ['cod'];
        if ($settingClass && $settingClass::get('instant_enable_bank_transfer', '0') === '1') {
            $allowedPaymentMethods[] = 'bank';
            $allowedPaymentMethods[] = 'bank_transfer';
        }
        $rules['payment_method'] = 'required|in:' . implode(',', $allowedPaymentMethods);

        $data = $request->validate($rules, [
            'first_name.required' => 'الاسم الأول مطلوب',
            'last_name.required' => 'اللقب مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'country_code.required' => 'الدولة مطلوبة',
            'city.required' => 'المدينة مطلوبة',
            'address.required' => 'العنوان التفصيلي مطلوب',
        ]);

        $product = Product::active()->findOrFail($data['product_id']);
        if ($product->stock < (int) $data['quantity']) {
            return $this->errorResponse($request, 'الكمية المطلوبة غير متوفرة في المخزون');
        }

        // Compute final price on the server
        $qty = (int) $data['quantity'];
        $base = (float) $product->final_price;
        $optionsAdjustment = 0;
        $optionsSummary = [];
        if (!empty($data['options'])) {
            foreach ($data['options'] as $optionId => $valueId) {
                $value = ProductOptionValue::with('option')->find($valueId);
                if ($value && $value->option && $value->option->product_id == $product->id) {
                    $optionsAdjustment += (float) $value->price_adjustment;
                    $optionsSummary[] = [
                        'option' => $value->option->name,
                        'value' => $value->value,
                        'adjustment' => (float) $value->price_adjustment,
                    ];
                }
            }
        }

        $customFieldPrice = 0;
        if (!empty($data['custom_text']) && $product->customFields->count() > 0) {
            $textField = $product->customFields->firstWhere('type', 'text') ??
                         $product->customFields->firstWhere('type', 'textarea');
            if ($textField) {
                $customFieldPrice = (float) $textField->price_effect;
            }
        }

        $weight = (float) ($product->weight ?? 0) * $qty;
        $subtotal = ($base + $optionsAdjustment + $customFieldPrice) * $qty;

        $shippingCost = 0;
        try {
            if ($this->orderService) {
                $shippingCost = $this->orderService->calculateShipping(
                    $data['city'],
                    $data['shipping_method'] ?? 'standard',
                    $subtotal,
                    $data['country_code'],
                    $data['delivery_type'] ?? 'home',
                    $weight,
                    $data['shipping_company_id'] ?? null
                );
            }
        } catch (\Throwable $e) {
            $shippingCost = 0;
        }

        $couponId = null;
        $discount = 0;
        $coupon = null;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
                $couponId = $coupon->id;
            } else {
                $coupon = null;
            }
        }

        $codFee = in_array($data['payment_method'] ?? 'cod', ['cod'], true) ? (float) config('ecommerce.cod.extra_fee', 0) : 0;
        $tax = 0;
        $grandTotal = max(0, $subtotal + $shippingCost + $codFee + $tax - $discount);

        // Handle custom file upload
        $customFilePath = null;
        if ($request->hasFile('custom_file')) {
            $customFilePath = $request->file('custom_file')->store('order_files', 'public');
        }

        // Build full phone with dial code
        $countries = config('ecommerce.countries', []);
        $dial = $countries[$data['country_code']]['dial_code'] ?? '';
        $fullPhone = str_starts_with($data['phone'], '+') ? $data['phone'] : ($dial . $data['phone']);

        $order = DB::transaction(function () use (
            $data, $product, $qty, $subtotal, $shippingCost, $couponId, $discount, $codFee, $tax, $grandTotal,
            $optionsSummary, $customFilePath, $fullPhone, $isGuest, $base, $optionsAdjustment, $customFieldPrice
        ) {
            $addressEmail = $isGuest ? ($data['email'] ?? ('guest_' . str_replace('+', '', $fullPhone) . '@amarstore.com')) : null;
            $address = ShippingAddress::create([
                'user_id' => $isGuest ? null : Auth::id(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'phone' => $fullPhone,
                'email' => $addressEmail,
                'country_code' => $data['country_code'],
                'state_code' => $data['state_code'] ?? null,
                'city' => $data['city'],
                'district' => $data['district'] ?? null,
                'address' => $data['address'],
                'zip' => $data['zip'] ?? null,
                'is_default' => false,
            ]);

            $order = Order::create([
                'user_id' => $isGuest ? null : Auth::id(),
                'guest_email' => $addressEmail,
                'guest_phone' => $isGuest ? $fullPhone : null,
                'is_instant_buy' => true,
                'shipping_address_id' => $address->id,
                'shipping_method' => $data['shipping_method'],
                'shipping_company_id' => $data['shipping_company_id'] ?? null,
                'delivery_type' => $data['delivery_type'] ?? 'home',
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'tax' => $tax,
                'cod_fee' => $codFee,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'coupon_id' => $couponId,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'variant_id' => null,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $qty,
                'price' => ($base + $optionsAdjustment + $customFieldPrice),
                'total' => $subtotal,
                'options' => $optionsSummary ? collect($optionsSummary)->pluck('value')->implode(', ') : null,
                'options_summary' => $optionsSummary,
                'custom_text' => $data['custom_text'] ?? null,
                'custom_file' => $customFilePath,
            ]);

            Payment::create([
                'order_id' => $order->id,
                'method' => $data['payment_method'] ?? 'cod',
                'status' => 'pending',
                'amount' => $grandTotal,
            ]);

            if ($product->type === 'simple') {
                $product->decrement('stock', $qty);
            }

            if ($couponId) {
                Coupon::where('id', $couponId)->increment('used_count');
            }

            return $order->fresh(['items', 'shippingAddress', 'payment']);
        });

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

        return redirect($redirectRoute)->with('success', 'تم إنشاء الطلب بنجاح. رقم الطلب: ' . $order->order_number);
    }

    /**
     * Guest thank-you page (looks up the order by order_number).
     */
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
