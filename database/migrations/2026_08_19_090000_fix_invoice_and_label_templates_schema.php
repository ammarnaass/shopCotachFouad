<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_templates')) {
            Schema::table('invoice_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('invoice_templates', 'description')) {
                    $table->text('description')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('invoice_templates', 'status')) {
                    $table->boolean('status')->default(true)->after('is_default');
                }
                if (Schema::hasColumn('invoice_templates', 'orientation')) {
                    $table->string('orientation')->nullable()->change();
                }
                if (Schema::hasColumn('invoice_templates', 'notes')) {
                    $table->text('notes')->nullable()->change();
                }
                if (Schema::hasColumn('invoice_templates', 'is_active')) {
                    $table->boolean('is_active')->default(true)->change();
                }
            });
        }

        if (Schema::hasTable('label_templates')) {
            Schema::table('label_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('label_templates', 'description')) {
                    $table->text('description')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('label_templates', 'paper_size')) {
                    $table->string('paper_size')->default('100x150')->after('description');
                }
                if (!Schema::hasColumn('label_templates', 'custom_width')) {
                    $table->unsignedSmallInteger('custom_width')->nullable()->after('paper_size');
                }
                if (!Schema::hasColumn('label_templates', 'custom_height')) {
                    $table->unsignedSmallInteger('custom_height')->nullable()->after('custom_width');
                }
                if (!Schema::hasColumn('label_templates', 'status')) {
                    $table->boolean('status')->default(true)->after('is_default');
                }
                if (Schema::hasColumn('label_templates', 'size')) {
                    $table->string('size')->nullable()->change();
                }
                if (Schema::hasColumn('label_templates', 'size_mm')) {
                    $table->string('size_mm')->nullable()->change();
                }
                if (Schema::hasColumn('label_templates', 'notes')) {
                    $table->text('notes')->nullable()->change();
                }
                if (Schema::hasColumn('label_templates', 'is_active')) {
                    $table->boolean('is_active')->default(true)->change();
                }
            });
        }
    }

    public function down(): void
    {
    }
};
