<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Coupon;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductOptionValue;
use App\Models\InstantBuy\InstantBuyOrder;
use App\Models\InstantBuy\InstantBuySetting;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Shipping\ShippingAddress;
use App\Services\DynamicShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InstantBuyOrderController extends Controller
{
    public function __construct(
        protected DynamicShippingService $shippingService
    ) {}

    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:100',
            'selected_options' => 'nullable|array',
            'selected_options.*' => 'exists:product_option_values,id',
            'country_code' => 'nullable|string|size:2',
            'city' => 'nullable|string|max:100',
            'shipping_method_type' => 'nullable|string|max:50',
            'shipping_cost' => 'nullable|numeric|min:0',
            'delivery_type' => 'nullable|string|max:20',
            'coupon_code' => 'nullable|string|max:50',
            'custom_text' => 'nullable|string|max:500',
        ]);

        $product = Product::with(['options.values'])->findOrFail($data['product_id']);
        $quantity = (int) $data['quantity'];

        $basePrice = $product->final_price;
        $optionsPrice = 0;

        if (! empty($data['selected_options'])) {
            $values = ProductOptionValue::whereIn('id', $data['selected_options'])->get();
            foreach ($values as $val) {
                $optionsPrice += (float) ($val->price_adjustment ?? 0);
            }
        }

        $customFieldPrice = 0;
        if (! empty($data['custom_text'])) {
            $customFields = $product->customFields;
            $textField = $customFields->firstWhere('type', 'text') ?? $customFields->firstWhere('type', 'textarea');
            if ($textField) {
                $customFieldPrice = (float) $textField->price_effect;
            }
        }

        $unitPrice = $basePrice + $optionsPrice + $customFieldPrice;
        $subtotal = $unitPrice * $quantity;

        // Use the shipping cost from the frontend (selected from DynamicShippingService options)
        $shippingCost = (float) ($data['shipping_cost'] ?? 0);
        $shippingFree = $shippingCost === 0.0 && $subtotal > 0 && $shippingCost !== null;

        // Coupon discount
        $discount = 0;
        $coupon = null;
        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                $coupon = null;
            }
        }

        $total = max(0, $subtotal + $shippingCost - $discount);

        $countrySymbol = $data['country_code']
            ? (config("ecommerce.countries.{$data['country_code']}.currency_symbol") ?? config('ecommerce.store.currency_symbol'))
            : config('ecommerce.store.currency_symbol');

        $weight = (float) ($product->weight ?? 0) * $quantity;

        return response()->json([
            'success' => true,
            'base_price' => round($basePrice, 2),
            'unit_price' => round($unitPrice, 2),
            'options_adjustment' => round($optionsPrice, 2),
            'quantity' => $quantity,
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

    public function shippingOptions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'country_code' => 'required|string|size:2',
            'city' => 'required|string',
            'delivery_type' => 'nullable|in:home,office',
        ]);

        $supported = $this->shippingService->getSupportedDeliveryTypes(
            $data['product_id'], $data['country_code'], $data['city']
        );

        if (empty($supported)) {
            return response()->json([
                'success' => true,
                'options' => [],
                'companies' => [],
                'supported_delivery_types' => [],
            ]);
        }

        $deliveryType = (!empty($data['delivery_type']) && in_array($data['delivery_type'], $supported))
            ? $data['delivery_type']
            : ($supported[0] ?? 'home');

        $result = $this->shippingService->getAvailableMethods(
            $data['product_id'], $data['country_code'], $data['city'], $deliveryType
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
            'supported_delivery_types' => $supported,
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $data['code'])->first();
        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'كود غير صالح'], 404);
        }
        if (! $coupon->isValid((float) $data['subtotal'])) {
            $msg = 'كوبون غير صالح';
            if ($coupon->expiry_date && $coupon->expiry_date->isPast()) {
                $msg = 'الكوبون منتهي الصلاحية';
            } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                $msg = 'تم استنفاد الكوبون';
            } elseif ($coupon->min_order && $data['subtotal'] < $coupon->min_order) {
                $msg = "الحد الأدنى للطلب {$coupon->min_order}";
            }

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

    public function submit(Request $request): JsonResponse
    {
        $settings = InstantBuySetting::firstOrCreate([], []);

        $rules = [
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'selected_options' => 'nullable|array',
            'selected_options.*' => 'exists:product_option_values,id',
            'custom_text' => 'nullable|string|max:500',
            'state_code' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string|max:50',
            'shipping_method_type' => 'nullable|string|max:50',
            'shipping_cost' => 'nullable|numeric|min:0',
            'delivery_type' => 'nullable|string|max:20',
            'shipping_company_id' => 'nullable|exists:shipping_companies,id',
        ];

        // Core customer/address fields are only validated when enabled (or marked required) in settings.
        $addFieldRule = function (string $field, string $base) use (&$rules, $settings): void {
            $enabled = $settings->{'field_'.$field.'_enabled'};
            $required = $settings->{'field_'.$field.'_required'};
            if ($enabled || $required) {
                $rules[$field] = ($required ? 'required|' : 'nullable|').$base;
            }
        };

        $addFieldRule('phone', 'string|max:20');
        $addFieldRule('country_code', 'string|size:2');
        $addFieldRule('city', 'string|max:100');
        $addFieldRule('address', 'string');
        $addFieldRule('first_name', 'string|max:100');
        $addFieldRule('last_name', 'string|max:100');

        $data = $request->validate($rules);

        $product = Product::findOrFail($data['product_id']);
        $quantity = (int) ($data['quantity'] ?? 1);

        // Calculate prices server-side
        $basePrice = $product->final_price;
        $optionsPrice = 0;
        if (! empty($data['selected_options'])) {
            $values = ProductOptionValue::whereIn('id', $data['selected_options'])->get();
            foreach ($values as $val) {
                $optionsPrice += (float) ($val->price_adjustment ?? 0);
            }
        }

        $subtotal = ($basePrice + $optionsPrice) * $quantity;
        $shippingCost = (float) ($data['shipping_cost'] ?? 0);
        $discount = 0;

        // Validate coupon
        $couponCode = $data['coupon_code'] ?? null;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->first();
            if ($coupon) {
                $discount = $coupon->discount($subtotal + $shippingCost);
            }
        }

        $grandTotal = max(0, $subtotal + $shippingCost - $discount);

        // Generate order number
        $orderNumber = 'IB-'.strtoupper(Str::random(8));

        try {
            DB::beginTransaction();

            $order = InstantBuyOrder::create([
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
                'first_name' => $data['first_name'] ?? 'ضيف',
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? null,
                'email' => auth()->user()?->email,
                'country_code' => $data['country_code'] ?? null,
                'state_code' => $data['state_code'] ?? null,
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'product_id' => $product->id,
                'variant_id' => $data['variant_id'] ?? null,
                'quantity' => $quantity,
                'options' => ! empty($data['selected_options']) ? $data['selected_options'] : null,
                'custom_text' => $data['custom_text'] ?? null,
                'product_price' => round($basePrice * $quantity, 2),
                'options_price' => round($optionsPrice * $quantity, 2),
                'shipping_cost' => $shippingCost,
                'discount' => round($discount, 2),
                'coupon_code' => $couponCode,
                'grand_total' => round($grandTotal, 2),
                'shipping_method_type' => $data['shipping_method_type'] ?? null,
                'delivery_type' => $data['delivery_type'] ?? 'home',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payment_method' => 'cod',
                'status' => 'new',
                'payment_status' => 'pending',
            ]);

            // Create a unified Order record so the purchase appears in the main admin orders list
            // (the orders view already supports the instant-buy "type" badge via is_instant_buy).
            $shippingAddress = ShippingAddress::create([
                'user_id' => auth()->id(),
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')) ?: null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? auth()->user()?->email,
                'country_code' => $data['country_code'] ?? null,
                'state_code' => $data['state_code'] ?? null,
                'city' => $data['city'] ?? null,
                'district' => $data['district'] ?? null,
                'address' => $data['address'] ?? null,
                'zip' => $data['zip'] ?? null,
            ]);

            $orderRecord = Order::create([
                'user_id' => auth()->id(),
                'guest_email' => $data['email'] ?? auth()->user()?->email,
                'guest_phone' => $data['phone'] ?? null,
                'is_instant_buy' => true,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'tax' => 0,
                'cod_fee' => 0,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'shipping_address_id' => $shippingAddress->id,
                'shipping_company_id' => $data['shipping_company_id'] ?? null,
                'shipping_method' => $data['shipping_method_type'] ?? 'standard',
                'shipping_method_id' => Order::extractShippingMethodId($data['shipping_method_type'] ?? 'standard'),
                'delivery_type' => $data['delivery_type'] ?? 'home',
                'coupon_id' => ($couponCode && isset($coupon)) ? $coupon->id : null,
            ]);

            OrderItem::create([
                'order_id' => $orderRecord->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $quantity,
                'price' => $basePrice + $optionsPrice,
                'total' => ($basePrice + $optionsPrice) * $quantity,
                'options' => $data['selected_options'] ?? null,
                'custom_text' => $data['custom_text'] ?? null,
            ]);

            // Decrement stock
            $product->decrement('stock', $quantity);

            // Track coupon usage
            if ($couponCode && isset($coupon)) {
                $coupon->increment('used_count');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'order_number' => $order->order_number,
                    'grand_total' => $order->grand_total,
                    'product_name' => $product->name,
                    'whatsapp_url' => $this->whatsappUrl($settings, $order),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('InstantBuyOrder failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الطلب. حاول مرة أخرى.',
            ], 500);
        }
    }

    private function whatsappUrl(InstantBuySetting $settings, InstantBuyOrder $order): ?string
    {
        if (! $settings->success_show_whatsapp_button) {
            return null;
        }
        $phone = config('ecommerce.store.phone', '');
        if (! $phone) {
            return null;
        }
        $message = str_replace(
            ['{order_number}', '{customer_name}', '{total}'],
            [$order->order_number, $order->first_name.' '.$order->last_name, $order->grand_total],
            $settings->success_whatsapp_message
        );
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }
}
