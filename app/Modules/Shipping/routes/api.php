<?php

use App\Modules\Shipping\Http\Controllers\Api\ShippingCalculationController;
use Illuminate\Support\Facades\Route;

Route::prefix('shipping')
    ->name('api.shipping.')
    ->group(function () {
        Route::post('calculate', [ShippingCalculationController::class, 'calculate'])->name('calculate');
        Route::post('methods', [ShippingCalculationController::class, 'availableMethods'])->name('methods');
    });

// ملاحظة مهمة عند الدمج:
// هذا المسار يجب أن يكون البديل الوحيد والنهائي لكل من:
//   - CartApiController::calculateShipping()  (كان يستخدم OrderService::calculateShipping القديمة)
//   - ShippingApiController::calculate()      (كان يستخدم country_id رقمي)
// احذف الاثنين بعد تأكيد أن الفرونت-إند (صفحة السلة + أي عميل موبايل) يستدعي هذا المسار فقط،
// لتفادي وجود مسارين مختلفين لنفس الوظيفة بمنطقين مختلفين مستقبلًا.
