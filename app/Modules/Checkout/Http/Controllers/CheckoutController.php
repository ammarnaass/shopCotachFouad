<?php

namespace App\Modules\Checkout\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Services\CartService;
use App\Modules\Orders\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private ?CartService $cartService = null,
        private ?OrderService $orderService = null
    ) {
        if ($this->cartService === null && class_exists(CartService::class)) {
            $this->cartService = app(CartService::class);
        }
        if ($this->orderService === null && class_exists(OrderService::class)) {
            $this->orderService = app(OrderService::class);
        }
    }

    public function index(Request $request): View
    {
        $cart = $this->cartService ? $this->cartService->getCart() : null;
        return view('frontend.cart.index', compact('cart'));
    }
}
