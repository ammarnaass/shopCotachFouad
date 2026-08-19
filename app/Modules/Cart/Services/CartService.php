<?php

namespace App\Modules\Cart\Services;

use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Coupons\Models\Coupon;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Models\ProductOptionValue;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart(): Cart
    {
        if (auth()->check()) {
            $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        } else {
            $sessionId = Session::getId();
            $cart = Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        $cart->load('items.product', 'items.variant', 'coupon');
        return $cart;
    }

    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null, array $options = [], ?string $customText = null): CartItem
    {
        $product = Product::active()->findOrFail($productId);

        $price = (float) $product->final_price;
        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            $price = (float) $variant->price;
        }

        if (!empty($options)) {
            foreach ($options as $optionId => $valueId) {
                $optionValue = ProductOptionValue::find($valueId) 
                    ?? (\App\Models\Catalog\ProductOptionValue::class ? \App\Models\Catalog\ProductOptionValue::find($valueId) : null);
                if ($optionValue) {
                    $price += (float) $optionValue->price_adjustment;
                }
            }
        }

        $cart = $this->getCart();

        $existing = $cart->items()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->where('options', json_encode($options))
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);
            $existing->refresh();
            return $existing;
        }

        return $cart->items()->create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'options' => $options,
            'custom_text' => $customText,
            'price' => $price,
        ]);
    }

    public function updateQuantity(int $itemId, int $quantity): CartItem
    {
        $item = CartItem::findOrFail($itemId);
        if ($quantity <= 0) {
            $item->delete();
            return $item;
        }
        $item->update(['quantity' => $quantity]);
        return $item->fresh();
    }

    public function removeItem(int $itemId): bool
    {
        return CartItem::destroy($itemId) > 0;
    }

    public function clear(): void
    {
        $this->getCart()->items()->delete();
    }

    public function applyCoupon(string $code): ?Coupon
    {
        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon) return null;

        $cart = $this->getCart();
        $cart->update(['coupon_id' => $coupon->id]);
        return $coupon;
    }

    public function removeCoupon(): void
    {
        $this->getCart()->update(['coupon_id' => null]);
    }

    public function getSummary(): array
    {
        $cart = $this->getCart();
        $subtotal = $cart->subtotal;
        $discount = $cart->discount;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'items_count' => $cart->total_items,
            'coupon' => $cart->coupon,
        ];
    }
}
