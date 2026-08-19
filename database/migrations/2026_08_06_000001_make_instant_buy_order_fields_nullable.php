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
            Schema::create('instant_buy_orders_new', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 50)->unique();
                $table->unsignedBigInteger('user_id')->nullable();

                // Customer info — now nullable
                $table->string('first_name', 100)->nullable();
                $table->string('last_name', 100)->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('email', 255)->nullable();

                // Address
                $table->string('country_code', 2)->nullable()->index();
                $table->string('state_code', 20)->nullable();
                $table->string('city', 100)->nullable();
                $table->text('address')->nullable();
                $table->text('notes')->nullable();

                // Product info
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->json('options')->nullable();
                $table->string('custom_text', 500)->nullable();
                $table->string('custom_file', 255)->nullable();

                // Pricing
                $table->decimal('product_price', 10, 2);
                $table->decimal('options_price', 10, 2)->default(0);
                $table->decimal('shipping_cost', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->string('coupon_code', 50)->nullable();
                $table->decimal('grand_total', 10, 2);

                // Shipping
                $table->string('shipping_method_type', 50)->nullable();
                $table->string('shipping_method_name', 100)->nullable();
                $table->string('delivery_type', 20)->default('home');
                $table->unsignedBigInteger('shipping_company_id')->nullable();

                // Status
                $table->string('status', 30)->default('new')->index();
                $table->string('payment_status', 20)->default('pending');
                $table->string('payment_method', 50)->default('cod');

                // Security
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();

                // Notifications
                $table->timestamp('notified_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });

            // Copy data (safe even on fresh DBs)
            $existing = DB::table('instant_buy_orders')->get();
            foreach ($existing as $row) {
                DB::table('instant_buy_orders_new')->insert((array) $row);
            }

            Schema::drop('instant_buy_orders');
            Schema::rename('instant_buy_orders_new', 'instant_buy_orders');
        } else {
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN first_name VARCHAR(100) NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN last_name VARCHAR(100) NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN phone VARCHAR(100) NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN country_code VARCHAR(2) NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN city VARCHAR(100) NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN address TEXT NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('instant_buy_orders_new', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 50)->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('phone', 20);
                $table->string('email', 255)->nullable();
                $table->string('country_code', 2)->index();
                $table->string('state_code', 20)->nullable();
                $table->string('city', 100);
                $table->text('address');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->json('options')->nullable();
                $table->string('custom_text', 500)->nullable();
                $table->string('custom_file', 255)->nullable();
                $table->decimal('product_price', 10, 2);
                $table->decimal('options_price', 10, 2)->default(0);
                $table->decimal('shipping_cost', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->string('coupon_code', 50)->nullable();
                $table->decimal('grand_total', 10, 2);
                $table->string('shipping_method_type', 50)->nullable();
                $table->string('shipping_method_name', 100)->nullable();
                $table->string('delivery_type', 20)->default('home');
                $table->unsignedBigInteger('shipping_company_id')->nullable();
                $table->string('status', 30)->default('new')->index();
                $table->string('payment_status', 20)->default('pending');
                $table->string('payment_method', 50)->default('cod');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('notified_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            $existing = DB::table('instant_buy_orders')->get();
            foreach ($existing as $row) {
                DB::table('instant_buy_orders_new')->insert((array) $row);
            }

            Schema::drop('instant_buy_orders');
            Schema::rename('instant_buy_orders_new', 'instant_buy_orders');
        } else {
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN first_name VARCHAR(100) NOT NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN last_name VARCHAR(100) NOT NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN phone VARCHAR(20) NOT NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN country_code VARCHAR(2) NOT NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN city VARCHAR(100) NOT NULL');
            DB::statement('ALTER TABLE instant_buy_orders MODIFY COLUMN address TEXT NOT NULL');
        }
    }
};
