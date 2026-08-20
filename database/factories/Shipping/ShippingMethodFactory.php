<?php

namespace Database\Factories\Shipping;

use App\Modules\Shipping\Models\ShippingMethod;
use App\Modules\Shipping\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory لـ ShippingMethod — مطلوبة لكل اختبارات ShippingCalculationTest.
 */
class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    public function definition(): array
    {
        return [
            'shipping_zone_id'        => fn (array $attributes) => $attributes['zone_id'] ?? ShippingZone::factory(),
            'zone_id'                 => fn (array $attributes) => $attributes['shipping_zone_id'] ?? ShippingZone::factory(),
            'name'                    => $this->faker->randomElement(['توصيل منزلي', 'توصيل سريع', 'نقطة استلام']),
            'code'                    => $this->faker->unique()->slug(2),
            'calculation_type'        => 'flat',
            'base_cost'               => $this->faker->randomFloat(2, 100, 1000),
            'cost_per_kg'             => 0,
            'free_shipping_threshold' => null,
            'min_delivery_days'       => 2,
            'max_delivery_days'       => 5,
            'status'                  => 'active',
            'sort_order'              => 0,
        ];
    }

    /** طريقة شحن بسعر ثابت */
    public function flat(float $cost = 500): static
    {
        return $this->state([
            'calculation_type' => 'flat',
            'base_cost'        => $cost,
        ]);
    }

    /** طريقة شحن حسب الوزن */
    public function weightBased(float $baseCost = 200, float $costPerKg = 50): static
    {
        return $this->state([
            'calculation_type' => 'weight_based',
            'base_cost'        => $baseCost,
            'cost_per_kg'      => $costPerKg,
        ]);
    }

    /** شحن مجاني فوق حد معيّن */
    public function withFreeThreshold(float $threshold): static
    {
        return $this->state(['free_shipping_threshold' => $threshold]);
    }

    /** طريقة شحن معطّلة */
    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
