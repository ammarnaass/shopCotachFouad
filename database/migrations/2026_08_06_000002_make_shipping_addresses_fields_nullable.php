<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite can't ALTER COLUMN — rebuild the table with nullable columns
            Schema::create('shipping_addresses_new', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('country_code', 2)->default('SD')->nullable();
                $table->string('state_code', 5)->nullable();
                $table->string('city')->nullable();
                $table->string('district')->nullable();
                $table->text('address')->nullable();
                $table->string('zip', 20)->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });

            $existing = DB::table('shipping_addresses')->get();
            foreach ($existing as $row) {
                DB::table('shipping_addresses_new')->insert((array) $row);
            }

            Schema::drop('shipping_addresses');
            Schema::rename('shipping_addresses_new', 'shipping_addresses');
        } else {
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN first_name VARCHAR(255) NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN last_name VARCHAR(255) NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN name VARCHAR(255) NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN phone VARCHAR(255) NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN country_code VARCHAR(2) NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN city VARCHAR(255) NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN address TEXT NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('shipping_addresses_new', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('name');
                $table->string('phone');
                $table->string('email')->nullable();
                $table->string('country_code', 2)->default('SD');
                $table->string('state_code', 5)->nullable();
                $table->string('city');
                $table->string('district')->nullable();
                $table->text('address');
                $table->string('zip', 20)->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });

            $existing = DB::table('shipping_addresses')->get();
            foreach ($existing as $row) {
                DB::table('shipping_addresses_new')->insert((array) $row);
            }

            Schema::drop('shipping_addresses');
            Schema::rename('shipping_addresses_new', 'shipping_addresses');
        } else {
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN first_name VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN last_name VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN name VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN phone VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN country_code VARCHAR(2) NOT NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN city VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE shipping_addresses MODIFY COLUMN address TEXT NOT NULL');
        }
    }
};
