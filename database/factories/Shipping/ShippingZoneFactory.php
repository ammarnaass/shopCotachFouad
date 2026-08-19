<?php

namespace Database\Factories\Shipping;

use App\Modules\Shipping\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingZoneFactory extends Factory
{
    protected $model = ShippingZone::class;

    public function definition(): array
    {
        return [
            'name'                   => $this->faker->words(3, true),
            'description'            => null,
            'countries'              => ['*'],
            'cities'                 => ['*'],
            'delivery_type'          => 'both',
            'cost'                   => $this->faker->randomFloat(2, 100, 1000),
            'express_cost'           => $this->faker->randomFloat(2, 200, 2000),
            'home_cost'              => null,
            'home_express_cost'      => null,
            'office_cost'            => null,
            'office_express_cost'    => null,
            'cost_per_kg'            => null,
            'free_threshold'         => null,
            'estimated_days_standard'=> '3-5',
            'estimated_days_express' => '1-2',
            'is_default'             => false,
            'priority'               => 10,
            'sort_order'             => 0,
            'status'                 => 'active',
        ];
    }

    /** منطقة افتراضية */
    public function asDefault(): static
    {
        return $this->state(['is_default' => true]);
    }

    /** منطقة غير نشطة */
    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
