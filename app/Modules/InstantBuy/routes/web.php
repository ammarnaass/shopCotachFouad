<?php

use App\Modules\InstantBuy\Http\Controllers\InstantBuyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/instant', [InstantBuyController::class, 'create'])->name('instant.create');
    Route::get('/instant/{slug}', [InstantBuyController::class, 'create'])->name('instant.buy');
    Route::post('/instant/calculate', [InstantBuyController::class, 'calculate'])->name('instant.calculate');
    Route::post('/instant/shipping-options', [InstantBuyController::class, 'calculate'])->name('instant.shipping-options');
    Route::post('/instant/coupon', [InstantBuyController::class, 'validateCoupon'])->name('instant.coupon');
    Route::post('/instant/submit', [InstantBuyController::class, 'submit'])->name('instant.submit');
    Route::get('/order/{orderNumber}/thanks', [InstantBuyController::class, 'thankyou'])->name('instant.thankyou');
});
