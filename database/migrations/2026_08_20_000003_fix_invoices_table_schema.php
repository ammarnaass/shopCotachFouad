<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'status')) {
                $table->enum('status', ['draft', 'issued', 'cancelled'])->default('issued')->after('invoice_number');
            }
            if (!Schema::hasColumn('invoices', 'invoice_template_id')) {
                $table->foreignId('invoice_template_id')->nullable()->after('status')->constrained('invoice_templates')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'metadata')) {
                $table->json('metadata')->nullable()->after('invoice_template_id');
            }
            if (!Schema::hasColumn('invoices', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('issued_at');
            }

            // Make all legacy columns nullable
            if (Schema::hasColumn('invoices', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0)->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->default(0)->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'total')) {
                $table->decimal('total', 12, 2)->default(0)->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency', 10)->default('DZD')->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'snapshot')) {
                $table->json('snapshot')->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'due_at')) {
                $table->timestamp('due_at')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('invoices', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('invoices', 'invoice_template_id')) {
                $table->dropForeign(['invoice_template_id']);
                $table->dropColumn('invoice_template_id');
            }
            if (Schema::hasColumn('invoices', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
