<?php

namespace App\Actions\Order;

use App\Models\Catalog\Product;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\Payment;
use App\Models\Shipping\ShippingAddress;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateInstantOrder
{
    public function __construct(
        private PricingService $pricing,
        private CouponService $coupons,
        private OrderService $orderService,
    ) {}

    public function execute(array $data, Product $product, ?UploadedFile $customFile = null): Order
    {
        $qty = (int) ($data['quantity'] ?? 1);
        $isGuest = ! Auth::check();

        $price = $this->pricing->calculateProductPrice(
            $product,
            $data['options'] ?? [],
            $data['custom_text'] ?? null,
        );

        $subtotal = $this->pricing->calculateSubtotal($price['unit_price'], $qty);
        $weight = $this->pricing->calculateWeight((float) ($product->weight ?? 0), $qty);

        $shippingCost = 0;
        try {
            $shippingCost = $this->orderService->calculateShipping(
                $data['city'],
                $data['shipping_method'] ?? 'standard',
                $subtotal,
                $data['country_code'],
                $data['delivery_type'] ?? 'home',
                $weight,
                $data['shipping_company_id'] ?? null,
                $data['state_code'] ?? null
            );
        } catch (\Throwable $e) {
            $shippingCost = 0;
        }

        $couponResult = $this->coupons->apply($data['coupon_code'] ?? null, $subtotal);
        $discount = $couponResult['discount'];
        $couponId = $couponResult['coupon_id'];

        $codFee = in_array($data['payment_method'] ?? 'cod', ['cod'], true)
            ? (float) config('ecommerce.cod.extra_fee', 0) : 0;
        $tax = 0;
        $grandTotal = max(0, $subtotal + $shippingCost + $codFee + $tax - $discount);

        $customFilePath = null;
        if ($customFile && $customFile->isValid()) {
            $customFilePath = $customFile->store('order_files', 'public');
        }

        $countries = config('ecommerce.countries', []);
        $dial = $countries[$data['country_code']]['dial_code'] ?? '';
        $fullPhone = str_starts_with($data['phone'], '+') ? $data['phone'] : ($dial.$data['phone']);

        return DB::transaction(function () use (
            $data, $product, $qty, $subtotal, $shippingCost, $couponId, $discount,
            $codFee, $tax, $grandTotal, $price, $customFilePath, $fullPhone, $isGuest, $couponResult
        ) {
            $addressEmail = $isGuest
                ? ($data['email'] ?? ('guest_'.str_replace('+', '', $fullPhone).'@amarstore.com'))
                : null;

            $address = ShippingAddress::create([
                'user_id' => $isGuest ? null : Auth::id(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'name' => $data['first_name'].' '.$data['last_name'],
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
                'shipping_method' => $data['shipping_method'] ?? 'standard',
                'shipping_method_id' => Order::extractShippingMethodId($data['shipping_method'] ?? 'standard'),
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
                'price' => $price['unit_price'],
                'total' => $subtotal,
                'options' => $price['options_summary']
                    ? collect($price['options_summary'])->pluck('value')->implode(', ')
                    : null,
                'options_summary' => $price['options_summary'],
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

            $this->coupons->incrementUsage($couponResult['coupon']);

            return $order->fresh(['items', 'shippingAddress', 'payment']);
        });
    }
}
