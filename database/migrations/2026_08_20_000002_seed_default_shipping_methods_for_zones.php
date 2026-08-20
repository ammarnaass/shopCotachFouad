<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sync any existing methods where shipping_zone_id was null but zone_id existed
        if (Schema::hasColumn('shipping_methods', 'shipping_zone_id') && Schema::hasColumn('shipping_methods', 'zone_id')) {
            DB::statement("UPDATE shipping_methods SET shipping_zone_id = zone_id WHERE shipping_zone_id IS NULL AND zone_id IS NOT NULL");
        }

        // 2. Generate standard shipping methods for all zones that currently have no methods
        $zones = DB::table('shipping_zones')->get();

        foreach ($zones as $zone) {
            $existingCount = DB::table('shipping_methods')
                ->where('shipping_zone_id', $zone->id)
                ->orWhere('zone_id', $zone->id)
                ->count();

            if ($existingCount === 0) {
                $baseCost = isset($zone->cost) && (float)$zone->cost > 0
                    ? (float)$zone->cost
                    : (isset($zone->home_cost) && (float)$zone->home_cost > 0 ? (float)$zone->home_cost : 500.0);

                $threshold = isset($zone->free_threshold) && (float)$zone->free_threshold > 0
                    ? (float)$zone->free_threshold
                    : 5000.0;

                // Standard delivery method
                DB::table('shipping_methods')->insert([
                    'shipping_zone_id'        => $zone->id,
                    'zone_id'                 => $zone->id,
                    'name'                    => 'توصيل قياسي',
                    'code'                    => 'standard-' . $zone->id,
                    'calculation_type'        => 'flat',
                    'type'                    => 'flat_rate',
                    'base_cost'               => $baseCost,
                    'flat_rate_amount'        => $baseCost,
                    'free_shipping_threshold' => $threshold,
                    'free_shipping_min'       => $threshold,
                    'min_delivery_days'       => 2,
                    'max_delivery_days'       => 5,
                    'estimated_days'          => $zone->estimated_days_standard ?? '2-5 أيام',
                    'status'                  => 1,
                    'sort_order'              => 1,
                    'method_order'            => 1,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);

                // Express delivery method if express_cost exists
                if (isset($zone->express_cost) && (float)$zone->express_cost > 0) {
                    DB::table('shipping_methods')->insert([
                        'shipping_zone_id'        => $zone->id,
                        'zone_id'                 => $zone->id,
                        'name'                    => 'توصيل سريع',
                        'code'                    => 'express-' . $zone->id,
                        'calculation_type'        => 'flat',
                        'type'                    => 'flat_rate',
                        'base_cost'               => (float)$zone->express_cost,
                        'flat_rate_amount'        => (float)$zone->express_cost,
                        'free_shipping_threshold' => null,
                        'free_shipping_min'       => null,
                        'min_delivery_days'       => 1,
                        'max_delivery_days'       => 2,
                        'estimated_days'          => $zone->estimated_days_express ?? '1-2 يوم',
                        'status'                  => 1,
                        'sort_order'              => 2,
                        'method_order'            => 2,
                        'created_at'              => now(),
                        'updated_at'              => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No down needed as we don't want to wipe merchant methods
    }
};
