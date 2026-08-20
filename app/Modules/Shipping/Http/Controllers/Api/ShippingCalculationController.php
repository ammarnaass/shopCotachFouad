<?php

namespace App\Modules\Shipping\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Shipping\Exceptions\InvalidShippingConfigurationException;
use App\Modules\Shipping\Exceptions\NoShippingZoneException;
use App\Modules\Shipping\Services\ShippingCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingCalculationController extends Controller
{
    public function __construct(
        private readonly ShippingCalculationService $shippingService,
    ) {
    }

    public function calculate(Request $request): JsonResponse
    {
        // country_code فقط — لا نقبل أي country_id رقمي إطلاقًا
        $validated = $request->validate([
            'country_code' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'city'         => ['nullable', 'string', 'max:150'],
            'state'        => ['nullable', 'string', 'max:100'],
            'state_code'   => ['nullable', 'string', 'max:100'],
            'postcode'     => ['nullable', 'string', 'max:50'],
            'subtotal'     => ['required', 'numeric', 'min:0'],
            'weight_kg'    => ['nullable', 'numeric', 'min:0'],
            'method_code'  => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $quote = $this->shippingService->calculate(
                countryCode: $validated['country_code'],
                city:        $validated['city'] ?? null,
                subtotal:    (float) $validated['subtotal'],
                weightKg:    (float) ($validated['weight_kg'] ?? 0),
                stateName:   $validated['state'] ?? null,
                methodCode:  $validated['method_code'] ?? null,
                postcode:    $validated['postcode'] ?? null,
                stateCode:   $validated['state_code'] ?? null,
            );

            return response()->json([
                'success' => true,
                'data'    => $quote->toArray(),
            ]);
        } catch (NoShippingZoneException $e) {
            // لا تُتمم الطلب أبدًا بشحن = 0 صامت — رفض صريح مع رسالة واضحة للزبون
            return response()->json([
                'success'    => false,
                'error_code' => 'SHIPPING_UNAVAILABLE',
                'message'    => 'عذرًا، الشحن لمنطقتك غير متاح حاليًا. يرجى التواصل معنا.',
            ], 422);
        } catch (InvalidShippingConfigurationException $e) {
            Log::error('shipping.configuration_error', [
                'message' => $e->getMessage(),
                'input'   => $validated,
            ]);

            return response()->json([
                'success'    => false,
                'error_code' => 'SHIPPING_MISCONFIGURED',
                'message'    => 'حدث خطأ بإعدادات الشحن. تم إبلاغ الفريق التقني.',
            ], 500);
        }
    }

    public function availableMethods(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_code' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'city'         => ['nullable', 'string', 'max:150'],
            'state'        => ['nullable', 'string', 'max:100'],
            'state_code'   => ['nullable', 'string', 'max:100'],
            'postcode'     => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $methods = $this->shippingService->availableMethods(
                countryCode: $validated['country_code'],
                city:        $validated['city'] ?? null,
                stateName:   $validated['state'] ?? null,
                postcode:    $validated['postcode'] ?? null,
                stateCode:   $validated['state_code'] ?? null,
            );

            return response()->json([
                'success' => true,
                'data'    => $methods->map(fn ($m) => [
                    'code'              => $m->code,
                    'name'              => $m->name,
                    'min_delivery_days' => $m->min_delivery_days,
                    'max_delivery_days' => $m->max_delivery_days,
                ]),
            ]);
        } catch (NoShippingZoneException $e) {
            return response()->json([
                'success'    => false,
                'error_code' => 'SHIPPING_UNAVAILABLE',
                'message'    => 'عذرًا، الشحن لمنطقتك غير متاح حاليًا.',
            ], 422);
        } catch (InvalidShippingConfigurationException $e) {
            Log::error('shipping.configuration_error', ['message' => $e->getMessage()]);

            return response()->json([
                'success'    => false,
                'error_code' => 'SHIPPING_MISCONFIGURED',
                'message'    => 'حدث خطأ بإعدادات الشحن.',
            ], 500);
        }
    }
}
