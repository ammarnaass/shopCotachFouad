<?php

namespace App\Modules\Shipping\Services;

use App\Modules\Shipping\Exceptions\InvalidShippingConfigurationException;
use App\Modules\Shipping\Exceptions\NoShippingZoneException;
use App\Modules\Shipping\Models\ShippingZone;
use Illuminate\Support\Facades\Log;

/**
 * محرك مطابقة مناطق الشحن بناءً على نموذج WooCommerce Shipping Zones:
 * - فحص تسلسلي للمناطق القياسية (standard) حسب الترتيب اليدوي (sort_order ASC).
 * - أول منطقة تطابق أي موقع من مواقعها (منطق OR) تفوز فورًا ويتوقف البحث.
 * - إذا لم تطابق أي منطقة قياسية -> الرجوع التلقائي لمنطقة النظام المحجوزة (everywhere).
 */
class ShippingZoneMatcher
{
    /**
     * @throws InvalidShippingConfigurationException
     * @throws NoShippingZoneException
     */
    public function match(
        string $countryCode,
        ?string $stateCode = null,
        ?string $postcode = null,
        ?string $city = null
    ): ShippingZone {
        $countryCode = strtoupper(trim($countryCode));

        if ($countryCode === '') {
            throw new InvalidShippingConfigurationException('لا يمكن احتساب الشحن بدون كود دولة صريح.');
        }

        $stateCode = $stateCode !== null ? trim($stateCode) : null;
        $postcode  = $postcode !== null ? trim($postcode) : null;
        $city      = $city !== null ? trim($city) : null;

        // 1. جلب المناطق القياسية الفعّالة مرتبة تصاعدياً حسب الأولوية اليدوية
        $standardZones = ShippingZone::query()
            ->standard()
            ->active()
            ->with(['locations'])
            ->orderBy('sort_order', 'asc')
            ->get();

        // 2. فحص تسلسلي: أول منطقة تطابق عنوان الزبون تفوز فورًا
        foreach ($standardZones as $zone) {
            if ($zone->matchesAddress($countryCode, $stateCode, $postcode, $city)) {
                return $zone;
            }
        }

        // 3. لم تطابق أي منطقة قياسية -> الرجوع لمنطقة النظام المحجوزة (Everywhere)
        Log::warning('shipping.no_standard_zone_matched_using_everywhere', [
            'country_code' => $countryCode,
            'state_code'   => $stateCode,
            'postcode'     => $postcode,
            'city'         => $city,
        ]);

        return $this->fallbackToEverywhereOrFail($countryCode, $stateCode, $postcode, $city);
    }

    /**
     * خط الدفاع الأخير: منطقة النظام المحجوزة (Everywhere / "مواقع غير مغطاة بمناطقك الأخرى").
     *
     * @throws NoShippingZoneException
     * @throws InvalidShippingConfigurationException
     */
    private function fallbackToEverywhereOrFail(
        string $countryCode,
        ?string $stateCode,
        ?string $postcode,
        ?string $city
    ): ShippingZone {
        $everywhere = ShippingZone::getOrCreateEverywhereZone();

        if ($everywhere->status !== 'active') {
            Log::warning('shipping.everywhere_zone_inactive', [
                'country_code' => $countryCode,
                'city'         => $city,
            ]);

            throw new NoShippingZoneException($city ?? 'unknown', $countryCode, $stateCode);
        }

        return $everywhere;
    }
}
