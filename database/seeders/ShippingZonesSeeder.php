<?php

namespace Database\Seeders;

use App\Models\Shipping\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZonesSeeder extends Seeder
{
    public function run(): void
    {
        $zones = config('ecommerce.shipping.zones', []);
        $states = config('ecommerce.countries.DZ.states', []);

        foreach ($zones as $z) {
            // Convert state codes to state names
            $cities = $z['cities'] ?? ['*'];
            if ($cities !== ['*']) {
                $cities = array_map(fn($code) => $states[$code] ?? $code, $cities);
            }

            ShippingZone::updateOrCreate(
                ['name' => $z['name']],
                [
                    'regions' => $z['cities'] ?? ['*'],
                    'countries' => $z['countries'] ?? null,
                    'cities' => $cities,
                    'cost' => $z['cost'],
                    'express_cost' => $z['express_cost'],
                    'free_threshold' => $z['free_threshold'] ?? null,
                    'status' => 'active',
                    'delivery_type' => 'both',
                    'is_default' => false,
                    'priority' => 0,
                    'sort_order' => 0,
                ]
            );
        }
        $this->command->info('Seeded ' . count($zones) . ' shipping zones');
    }
}
