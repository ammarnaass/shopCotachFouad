<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Coupon;
use App\Models\Shipping\ShippingOfficePickup;
use App\Models\Shipping\ShippingZone;
use App\Services\DynamicShippingService;
use App\Services\ShippingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingApiController extends Controller
{
    public function zones(): JsonResponse
    {
        $zones = ShippingZone::where('status', 'active')
            ->with(['activeMethods.carrier'])
            ->orderBy('priority')
            ->get()
            ->map(fn ($z) => [
                'id' => $z->id,
                'name' => $z->name,
                'description' => $z->description,
                'countries' => $z->countries,
                'cities' => $z->cities ?? $z->regions,
                'methods' => $z->activeMethods->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'type' => $m->type,
                    'type_label' => $m->getTypeLabel(),
                    'carrier' => $m->carrier?->name,
                    'estimated_days' => $m->estimated_days,
                ]),
            ]);

        return response()->json(['success' => true, 'data' => $zones]);
    }

    public function calculate(Request $request, ShippingCalculator $calculator): JsonResponse
    {
        $request->validate([
            'city' => 'nullable|string',
            'country_id' => 'nullable|integer',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'method' => 'nullable|in:standard,express',
            'subtotal' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'delivery_type' => 'nullable|in:home,office',
            'items' => 'nullable|array',
            'coupon_code' => 'nullable|string',
        ]);

        // Use new system if country_id provided
        if ($request->country_id || $request->city_id || $request->state_id) {
            $coupon = null;
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)->where('status', 'active')->first();
            }

            $result = $calculator->calculate(
                $request->items ?? [],
                $request->country_id,
                $request->state_id,
                $request->city_id,
                $coupon
            );

            return response()->json(array_merge(['success' => true], $result));
        }

        // Legacy: calculate by city name
        $zone = ShippingZone::where('status', 'active')
            ->get()
            ->first(fn ($z) => $z->isCityInZone($request->city ?? '', $request->country_code ?? ''));

        $cost = 0;
        $zoneName = null;
        $estimatedDays = null;
        $options = [];

        if ($zone) {
            $zoneName = $zone->name;
            $method = $request->method ?? 'standard';
            $deliveryType = $request->delivery_type ?? 'home';
            $cost = $zone->calculateCost(
                $request->city ?? '',
                $request->country_code ?? '',
                $method,
                $deliveryType,
                $request->subtotal ?? 0,
                $request->weight ?? 0
            );
            $estimatedDays = $zone->estimatedDays($method);

            // Also include zone methods
            foreach ($zone->activeMethods as $m) {
                $mCost = $m->calculateCost($request->weight ?? 0, $request->subtotal ?? 0, $request->items ?? []);
                if ($mCost !== null) {
                    $options[] = [
                        'id' => $m->id,
                        'name' => $m->name,
                        'type' => $m->type,
                        'type_label' => $m->getTypeLabel(),
                        'carrier' => $m->carrier?->name,
                        'cost' => $mCost,
                        'estimated_days' => $m->estimated_days,
                        'is_free' => $mCost == 0,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'zone' => $zoneName,
            'cost' => $cost,
            'is_free' => $cost === 0,
            'estimated_days' => $estimatedDays,
            'options' => $options,
        ]);
    }

    public function available(Request $request, DynamicShippingService $service): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'country_code' => 'required|string|size:2',
            'city' => 'required|string',
            'delivery_type' => 'nullable|in:home,office',
        ]);

        $supported = $service->getSupportedDeliveryTypes(
            $data['product_id'], $data['country_code'], $data['city']
        );
        $deliveryType = ($data['delivery_type'] && in_array($data['delivery_type'], $supported))
            ? $data['delivery_type']
            : $supported[0];

        $result = $service->getAvailableMethods(
            $data['product_id'], $data['country_code'], $data['city'], $deliveryType
        );

        return response()->json([
            'success' => true,
            'data' => [
                'supported_delivery_types' => $supported,
                'delivery_type' => $deliveryType,
                'available_methods' => $result['available'],
                'unavailable_methods' => $result['unavailable'],
            ],
        ]);
    }

    public function offices(string $carrier): JsonResponse
    {
        $offices = ShippingOfficePickup::where('carrier_id', $carrier)
            ->where('is_active', true)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->name,
                'address' => $o->address,
                'city' => $o->city,
                'state' => $o->state,
                'country_code' => $o->country_code,
                'latitude' => $o->latitude,
                'longitude' => $o->longitude,
                'working_hours' => $o->working_hours,
                'phone' => $o->phone,
            ]);

        return response()->json(['success' => true, 'data' => $offices]);
    }

    public function track(string $number, ShippingCalculator $calculator): JsonResponse
    {
        $tracking = $calculator->trackShipment($number);

        if (! $tracking) {
            return response()->json([
                'success' => false,
                'message' => 'رقم التتبع غير موجود',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tracking,
        ]);
    }
}
