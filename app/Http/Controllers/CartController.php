<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index(): View
    {
        $cart = $this->cartService->getCart();

        return view('frontend.cart.index', compact('cart'));
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
            'options' => 'nullable|array',
            'custom_text' => 'nullable|string|max:500',
        ]);

        $item = $this->cartService->addItem(
            $request->product_id,
            $request->quantity ?? 1,
            $request->variant_id,
            $request->options ?? [],
            $request->custom_text
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة المنتج إلى السلة',
                'cart_count' => $this->cartService->getCart()->total_items,
                'item' => $item,
            ]);
        }

        return redirect()->back()->with('success', 'تمت إضافة المنتج إلى السلة');
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:0']);

        $item = $this->cartService->updateQuantity($itemId, $request->quantity);

        return response()->json([
            'success' => true,
            'cart' => $this->cartService->getSummary(),
            'item' => $item,
        ]);
    }

    public function destroy(int $itemId): JsonResponse
    {
        $this->cartService->removeItem($itemId);

        return response()->json([
            'success' => true,
            'cart' => $this->cartService->getSummary(),
        ]);
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clear();

        return response()->json(['success' => true]);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $coupon = $this->cartService->applyCoupon($request->code);
        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'كود غير صالح'], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تطبيق الكوبون بنجاح',
            'cart' => $this->cartService->getSummary(),
        ]);
    }

    public function removeCoupon(): JsonResponse
    {
        $this->cartService->removeCoupon();

        return response()->json([
            'success' => true,
            'cart' => $this->cartService->getSummary(),
        ]);
    }
}
