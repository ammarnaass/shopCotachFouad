<?php

namespace App\Modules\Shipping\Services;

use App\Modules\Shipping\Exceptions\InvalidShippingConfigurationException;
use App\Modules\Shipping\Exceptions\NoShippingZoneException;
use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Shipping\Models\ShippingZone;

/**
 * نقطة دخول وحيدة لاحتساب الشحن بكل النظام (ويب، API، لوحة الإدارة).
 * متوافقة مع نموذج WooCommerce Shipping Zones.
 */
class ShippingCalculationService
{
    public function __construct(
        private readonly ShippingZoneMatcher $matcher,
    ) {
    }

    /**
     * @param  string|null  $methodCode  كود طريقة شحن محددة يطلبها الزبون (اختياري)
     *
     * @throws InvalidShippingConfigurationException
     * @throws NoShippingZoneException
     */
    public function calculate(
        string $countryCode,
        ?string $city = null,
        float $subtotal = 0,
        float $weightKg = 0,
        ?string $stateName = null,
        ?string $methodCode = null,
        ?string $postcode = null,
        ?string $stateCode = null,
    ): ShippingQuote {
        // إذا مُرِّر stateName نستخدمه كـ stateCode إن لم يُمرَّر stateCode صراحة
        $resolvedState = $stateCode ?? $stateName;

        $zone = $this->matcher->match(
            countryCode: $countryCode,
            stateCode:   $resolvedState,
            postcode:    $postcode,
            city:        $city,
        );

        $methodsQuery = $zone->methods()->active();

        if ($methodCode !== null) {
            $methodsQuery->where('code', $methodCode);
        }

        $method = $methodsQuery->orderBy('method_order')->orderBy('sort_order')->first();

        if (!$method) {
            // منطقة موجودة لكن بدون أي طريقة شحن فعالة — هذا خطأ إعداد بالإدارة
            throw new InvalidShippingConfigurationException(
                "منطقة الشحن #{$zone->id} ({$zone->name}) لا تحتوي على أي طريقة شحن فعّالة."
            );
        }

        $cost = $method->calculateCost($subtotal, $weightKg);

        return new ShippingQuote(
            zoneId: $zone->id,
            zoneName: $zone->name,
            methodId: $method->id,
            methodCode: $method->code,
            methodName: $method->name,
            cost: $cost,
            minDeliveryDays: $method->min_delivery_days,
            maxDeliveryDays: $method->max_delivery_days,
            isFreeShipping: $cost === 0.0,
        );
    }

    /**
     * كل طرق الشحن المتاحة لمنطقة الزبون مرتبة حسب method_order — يُستخدم لخيارات صفحة الـ checkout.
     *
     * @return \Illuminate\Support\Collection<int, ShippingMethod>
     *
     * @throws InvalidShippingConfigurationException
     * @throws NoShippingZoneException
     */
    public function availableMethods(
        string $countryCode,
        ?string $city = null,
        ?string $stateName = null,
        ?string $postcode = null,
        ?string $stateCode = null,
    ): \Illuminate\Support\Collection {
        $resolvedState = $stateCode ?? $stateName;

        $zone = $this->matcher->match(
            countryCode: $countryCode,
            stateCode:   $resolvedState,
            postcode:    $postcode,
            city:        $city,
        );

        $methods = $zone->methods()
            ->active()
            ->orderBy('method_order')
            ->orderBy('sort_order')
            ->get();

        if ($methods->isEmpty()) {
            throw new InvalidShippingConfigurationException(
                "منطقة الشحن #{$zone->id} ({$zone->name}) لا تحتوي على أي طريقة شحن فعّالة."
            );
        }

        return $methods;
    }
}
