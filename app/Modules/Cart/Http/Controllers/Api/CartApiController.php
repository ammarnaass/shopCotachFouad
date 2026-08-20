<?php

namespace App\Modules\Cart\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Services\CartService;
use App\Modules\Shipping\Exceptions\InvalidShippingConfigurationException;
use App\Modules\Shipping\Exceptions\NoShippingZoneException;
use App\Modules\Shipping\Services\ShippingCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartApiController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private ShippingCalculationService $shippingService,
    ) {
    }

    public function index(): JsonResponse
    {
        $cart = $this->cartService->getCart();

        return response()->json(['success' => true, 'data' => $cart->load('items.product', 'items.variant', 'coupon')]);
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $item = $this->cartService->addItem(
            $request->product_id,
            $request->quantity ?? 1,
            $request->variant_id
        );

        return response()->json(['success' => true, 'data' => $item, 'message' => 'تمت الإضافة'], 201);
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:0']);
        $item = $this->cartService->updateQuantity($itemId, $request->quantity);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(int $itemId): JsonResponse
    {
        $this->cartService->removeItem($itemId);

        return response()->json(['success' => true]);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        $coupon = $this->cartService->applyCoupon($request->code);
        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'كود غير صالح'], 422);
        }

        return response()->json(['success' => true, 'message' => 'تم التطبيق', 'data' => $coupon]);
    }

    /**
     * احتساب تكلفة الشحن عبر المسار الجديد الموحّد.
     *
     * يقبل: country_code (ISO alpha-2) + city
     * يُرجع: success: true مع التكلفة، أو success: false مع error_code صريح.
     * الفرونت-إند يجب أن يمنع زر "إتمام الطلب" عند success: false.
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_code' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'city'         => ['required', 'string', 'max:150'],
            'state'        => ['nullable', 'string', 'max:100'],
            'method_code'  => ['nullable', 'string', 'max:50'],
        ]);

        $cart = $this->cartService->getCart();
        $subtotal = max(0, ($cart->subtotal ?? 0) - ($cart->discount ?? 0));

        try {
            $quote = $this->shippingService->calculate(
                countryCode: $validated['country_code'],
                city:        $validated['city'],
                subtotal:    $subtotal,
                stateName:   $validated['state'] ?? null,
                methodCode:  $validated['method_code'] ?? null,
            );

            return response()->json([
                'success'       => true,
                'shipping_cost' => $quote->cost,
                'is_free'       => $quote->isFreeShipping,
                'method_code'   => $quote->methodCode,
                'method_name'   => $quote->methodName,
                'delivery_days' => [
                    'min' => $quote->minDeliveryDays,
                    'max' => $quote->maxDeliveryDays,
                ],
            ]);
        } catch (NoShippingZoneException $e) {
            // رفض صريح — الفرونت-إند يجب أن يُعطّل زر الدفع
            return response()->json([
                'success'    => false,
                'error_code' => 'SHIPPING_UNAVAILABLE',
                'message'    => 'عذرًا، الشحن لمنطقتك غير متاح حاليًا. يرجى التواصل معنا.',
            ], 422);
        } catch (InvalidShippingConfigurationException $e) {
            Log::error('cart.shipping_configuration_error', [
                'message' => $e->getMessage(),
                'city'    => $validated['city'],
                'country' => $validated['country_code'],
            ]);

            return response()->json([
                'success'    => false,
                'error_code' => 'SHIPPING_MISCONFIGURED',
                'message'    => 'حدث خطأ بإعدادات الشحن. تم إبلاغ الفريق التقني.',
            ], 500);
        }
    }
}
