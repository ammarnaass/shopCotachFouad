<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string', 'order_total' => 'required|numeric']);

        $coupon = Coupon::where('code', $request->code)->first();
        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'كود غير موجود'], 404);
        }

        if (! $coupon->isValid($request->order_total)) {
            return response()->json(['success' => false, 'message' => 'الكوبون غير صالح أو منتهي'], 422);
        }

        $discount = $coupon->calculateDiscount($request->order_total);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount' => $discount,
            ],
        ]);
    }
}
