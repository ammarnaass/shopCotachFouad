<?php

use App\Modules\Shipping\Http\Controllers\Admin\ShippingZoneController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'can:manage-shipping'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('shipping-zones', ShippingZoneController::class);
        // Route::resource('shipping-companies', ShippingCompanyController::class);
        // Route::resource('shipping-methods', ShippingMethodController::class);
    });
