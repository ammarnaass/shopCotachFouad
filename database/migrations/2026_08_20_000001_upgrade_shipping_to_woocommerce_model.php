<?php

use App\Modules\Shipping\Models\ShippingZone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add zone_type to shipping_zones table if not exists
        Schema::table('shipping_zones', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_zones', 'zone_type')) {
                $table->enum('zone_type', ['standard', 'everywhere'])
                    ->default('standard')
                    ->after('name');
            }
        });

        // Add index on (zone_type, sort_order)
        try {
            Schema::table('shipping_zones', function (Blueprint $table) {
                $table->index(['zone_type', 'sort_order'], 'shipping_zones_type_sort_index');
            });
        } catch (\Exception $e) {
            // Index might already exist
        }

        // 2. Create shipping_zone_locations table if not exists
        if (!Schema::hasTable('shipping_zone_locations')) {
            Schema::create('shipping_zone_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipping_zone_id')
                    ->constrained('shipping_zones')
                    ->cascadeOnDelete();
                $table->enum('type', ['country', 'state', 'postcode']);
                $table->string('value', 100);
                $table->timestamps();

                $table->index(['type', 'value']);
                $table->index('shipping_zone_id');
            });
        }

        // 3. Add method_order to shipping_methods table if not exists
        Schema::table('shipping_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_methods', 'method_order')) {
                $table->unsignedInteger('method_order')->default(0)->after('sort_order');
            }
        });

        // 4. Data Migration: convert existing JSON countries/states/cities into shipping_zone_locations
        $existingZones = DB::table('shipping_zones')->get();
        foreach ($existingZones as $zone) {
            // If zone was marked as default in old schema, set its zone_type to everywhere
            if (!empty($zone->is_default)) {
                DB::table('shipping_zones')->where('id', $zone->id)->update([
                    'zone_type'  => 'everywhere',
                    'sort_order' => 2147483647,
                ]);
                continue;
            }

            // Countries
            if (!empty($zone->countries)) {
                $countries = is_string($zone->countries) ? json_decode($zone->countries, true) : $zone->countries;
                if (is_array($countries)) {
                    foreach (collect($countries)->flatten()->filter() as $country) {
                        $c = strtoupper(trim((string)$country));
                        if ($c !== '' && $c !== '*') {
                            DB::table('shipping_zone_locations')->insertOrIgnore([
                                'shipping_zone_id' => $zone->id,
                                'type'             => 'country',
                                'value'            => $c,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);
                        }
                    }
                }
            }

            // States
            if (!empty($zone->states)) {
                $states = is_string($zone->states) ? json_decode($zone->states, true) : $zone->states;
                if (is_array($states)) {
                    foreach (collect($states)->flatten()->filter() as $state) {
                        $s = trim((string)$state);
                        if ($s !== '') {
                            DB::table('shipping_zone_locations')->insertOrIgnore([
                                'shipping_zone_id' => $zone->id,
                                'type'             => 'state',
                                'value'            => $s,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);
                        }
                    }
                }
            }

            // Cities (treated as postcode/state or custom location if applicable)
            if (!empty($zone->cities)) {
                $cities = is_string($zone->cities) ? json_decode($zone->cities, true) : $zone->cities;
                if (is_array($cities)) {
                    foreach (collect($cities)->flatten()->filter() as $city) {
                        $c = trim((string)$city);
                        if ($c !== '' && $c !== '*') {
                            // If numeric, can be postcode/wilaya code
                            $locType = is_numeric($c) ? 'postcode' : 'state';
                            DB::table('shipping_zone_locations')->insertOrIgnore([
                                'shipping_zone_id' => $zone->id,
                                'type'             => $locType,
                                'value'            => $c,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);
                        }
                    }
                }
            }
        }

        // 5. Ensure an everywhere zone exists
        $everywhereCount = DB::table('shipping_zones')->where('zone_type', 'everywhere')->count();
        if ($everywhereCount === 0) {
            DB::table('shipping_zones')->insert([
                'name'        => 'مواقع غير مغطاة بمناطقك الأخرى',
                'zone_type'   => 'everywhere',
                'sort_order'  => 2147483647,
                'status'      => 'active',
                'regions'     => '[]',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_locations');

        Schema::table('shipping_methods', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_methods', 'method_order')) {
                $table->dropColumn('method_order');
            }
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_zones', 'zone_type')) {
                $table->dropColumn('zone_type');
            }
        });
    }
};
