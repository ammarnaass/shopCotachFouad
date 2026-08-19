<?php

namespace App\Modules\Cart\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Services\CartService;
use App\Modules\Orders\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private ?OrderService $orderService = null,
    ) {
        if ($this->orderService === null && class_exists(OrderService::class)) {
            $this->orderService = app(OrderService::class);
        }
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

    public function calculateShipping(Request $request): JsonResponse
    {
        $request->validate([
            'city' => 'required|string',
            'state_code' => 'nullable|string|max:5',
            'method' => 'nullable|in:standard,express',
        ]);
        $cart = $this->cartService->getCart();
        $cost = 0;
        if ($this->orderService) {
            $cost = $this->orderService->calculateShipping(
                $request->city,
                $request->method ?? 'standard',
                $cart->subtotal - $cart->discount,
                null,
                'home',
                0,
                null,
                $request->state_code
            );
        }

        return response()->json(['success' => true, 'shipping_cost' => $cost, 'is_free' => $cost === 0]);
    }
}
