<?php

use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CouponApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ShippingApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\WishlistApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/auth/register', [AuthApiController::class, 'register']);
Route::post('/auth/login', [AuthApiController::class, 'login']);

Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{slug}', [ProductApiController::class, 'show']);
Route::get('/shipping/zones', [ShippingApiController::class, 'zones']);
Route::post('/shipping/calculate', [\App\Modules\Shipping\Http\Controllers\Api\ShippingCalculationController::class, 'calculate'])->name('api.shipping.calculate');
Route::get('/shipping/tracking/{number}', [ShippingApiController::class, 'track']);
Route::post('/coupons/validate', [CouponApiController::class, 'validate']);
Route::post('/shipping/available', [ShippingApiController::class, 'available']);
Route::get('/shipping/offices/{carrier}', [ShippingApiController::class, 'offices']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart', [CartApiController::class, 'add']);
    Route::patch('/cart/{item}', [CartApiController::class, 'update']);
    Route::delete('/cart/{item}', [CartApiController::class, 'destroy']);
    Route::post('/cart/coupon', [CartApiController::class, 'applyCoupon']);
    Route::post('/cart/calculate-shipping', [CartApiController::class, 'calculateShipping']);

    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::post('/orders', [OrderApiController::class, 'store']);
    Route::get('/orders/{id}', [OrderApiController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderApiController::class, 'cancel']);

    Route::get('/wishlist', [WishlistApiController::class, 'index']);
    Route::post('/wishlist', [WishlistApiController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistApiController::class, 'destroy']);
});
