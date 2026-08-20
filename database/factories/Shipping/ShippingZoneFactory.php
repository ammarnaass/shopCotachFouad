<?php

namespace Database\Factories\Shipping;

use App\Modules\Shipping\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory مُحدَّثة للإصدار الجديد من ShippingZone.
 * الحقول القديمة (cost, express_cost, delivery_type, priority...) محذوفة —
 * الآن التسعير يُدار من ShippingMethod المرتبطة بكل منطقة.
 */
class ShippingZoneFactory extends Factory
{
    protected $model = ShippingZone::class;

    public function definition(): array
    {
        return [
            'name'                   => $this->faker->words(3, true),
            'shipping_company_id'    => null,
            // أكواد ISO alpha-2 دائمًا — يُطبَّع تلقائيًا Uppercase عند الحفظ (ShippingZone::booted)
            'countries'              => ['DZ'],
            'states'                 => null,
            'cities'                 => null,
            'is_default'             => false,
            'status'                 => 'active',
            'sort_order'             => 0,
            // حقل قديم موجود بالـ schema — مطلوب بالاختبارات (NOT NULL constraint)
            // الكود الجديد لا يستخدمه (ShippingZoneMatcher يعتمد countries/cities/states)
            'regions'                => [],
        ];
    }

    /** منطقة افتراضية — خط الدفاع الأخير */
    public function asDefault(): static
    {
        return $this->state(['is_default' => true]);
    }

    /** منطقة غير نشطة */
    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }

    /** منطقة تغطي دولة بعينها */
    public function forCountry(string ...$codes): static
    {
        return $this->state([
            'countries' => array_map('strtoupper', $codes),
        ]);
    }

    /** منطقة بمدن محددة */
    public function forCities(string ...$cities): static
    {
        return $this->state([
            'cities' => array_map('mb_strtolower', $cities),
        ]);
    }
}
