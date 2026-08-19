<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update users table
        DB::table('users')
            ->where('country_code', 'SD')
            ->update(['country_code' => 'DZ']);

        // Update shipping_addresses table
        if (Schema::hasTable('shipping_addresses')) {
            DB::table('shipping_addresses')
                ->where('country_code', 'SD')
                ->update(['country_code' => 'DZ']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert users table
        DB::table('users')
            ->where('country_code', 'DZ')
            ->update(['country_code' => 'SD']);

        // Revert shipping_addresses table
        if (Schema::hasTable('shipping_addresses')) {
            DB::table('shipping_addresses')
                ->where('country_code', 'DZ')
                ->update(['country_code' => 'SD']);
        }
    }
};
