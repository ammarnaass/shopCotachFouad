<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration آمن للتحديث — يضيف الحقول الناقصة فقط دون المساس بالجداول أو البيانات الموجودة.
 *
 * السياق: جداول shipping_zones و shipping_methods و shipping_companies موجودة بالفعل
 * من migrations سابقة. هذا الملف يضيف فقط الحقول التي يتطلبها الموديول الجديد:
 *   - shipping_zones: shipping_company_id (FK للجدول الجديد), is_default index
 *   - shipping_methods: code, calculation_type, base_cost, cost_per_kg,
 *                       free_shipping_threshold, min/max_delivery_days (بالصيغة الجديدة)
 *   - shipping_companies: code (unique identifier للكود البرمجي)
 */
return new class extends Migration
{
    public function up(): void
    {
        // === shipping_companies: إضافة حقل code لو غير موجود ===
        Schema::table('shipping_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_companies', 'code')) {
                $table->string('code')->unique()->nullable()->after('name');
            }
            if (!Schema::hasColumn('shipping_companies', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('status');
            }
        });

        // === shipping_zones: ربط اختياري بشركة الشحن الجديدة + فهرسة is_default ===
        Schema::table('shipping_zones', function (Blueprint $table) {
            // shipping_company_id (FK للجدول الجديد) — مختلف عن company_id القديم
            if (!Schema::hasColumn('shipping_zones', 'shipping_company_id')) {
                $table->foreignId('shipping_company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('shipping_companies')
                    ->nullOnDelete();
            }

            // is_default موجود (أُضيف بـ 2026_06_15) لكن يحتاج فهرسة لضمان أداء < 200ms
            if (Schema::hasColumn('shipping_zones', 'is_default')) {
                // نُضيف الفهرس فقط إن لم يكن موجودًا
                try {
                    $table->index('is_default', 'shipping_zones_is_default_index');
                } catch (\Exception $e) {
                    // الفهرس موجود بالفعل — تجاهل بصمت
                }
            }
        });

        // === shipping_methods: إضافة حقول النموذج الجديد بجانب الحقول القديمة ===
        // الجدول القديم يستخدم: zone_id, type (enum), flat_rate_amount, ...
        // الجديد يتطلب:        shipping_zone_id, code, calculation_type, base_cost, ...
        // نضيف الحقول الجديدة فقط ونترك القديمة — قطع الدعم يكون في PR منفصل
        Schema::table('shipping_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_methods', 'shipping_zone_id')) {
                $table->foreignId('shipping_zone_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('shipping_zones')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('shipping_methods', 'code')) {
                $table->string('code')->nullable()->after('name');
            }

            if (!Schema::hasColumn('shipping_methods', 'calculation_type')) {
                $table->enum('calculation_type', ['flat', 'weight_based', 'price_based'])
                    ->default('flat')
                    ->after('code');
            }

            if (!Schema::hasColumn('shipping_methods', 'base_cost')) {
                $table->decimal('base_cost', 10, 2)->default(0)->after('calculation_type');
            }

            if (!Schema::hasColumn('shipping_methods', 'cost_per_kg')) {
                $table->decimal('cost_per_kg', 10, 2)->default(0)->after('base_cost');
            }

            if (!Schema::hasColumn('shipping_methods', 'free_shipping_threshold')) {
                $table->decimal('free_shipping_threshold', 10, 2)->nullable()->after('cost_per_kg');
            }

            if (!Schema::hasColumn('shipping_methods', 'min_delivery_days')) {
                $table->unsignedTinyInteger('min_delivery_days')->nullable()->after('free_shipping_threshold');
            }

            if (!Schema::hasColumn('shipping_methods', 'max_delivery_days')) {
                $table->unsignedTinyInteger('max_delivery_days')->nullable()->after('min_delivery_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $columns = ['max_delivery_days', 'min_delivery_days', 'free_shipping_threshold',
                        'cost_per_kg', 'base_cost', 'calculation_type', 'code'];

            foreach ($columns as $col) {
                if (Schema::hasColumn('shipping_methods', $col)) {
                    $table->dropColumn($col);
                }
            }

            if (Schema::hasColumn('shipping_methods', 'shipping_zone_id')) {
                $table->dropForeign(['shipping_zone_id']);
                $table->dropColumn('shipping_zone_id');
            }
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_zones', 'shipping_company_id')) {
                $table->dropForeign(['shipping_company_id']);
                $table->dropColumn('shipping_company_id');
            }
            try {
                $table->dropIndex('shipping_zones_is_default_index');
            } catch (\Exception $e) {
                // الفهرس غير موجود — تجاهل
            }
        });

        Schema::table('shipping_companies', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_companies', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('shipping_companies', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
