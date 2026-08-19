<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices')) { return; }
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('invoice_number')->unique()->comment('e.g. INV-2026-000001');
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('issued');
            $table->foreignId('invoice_template_id')->nullable()->constrained('invoice_templates')->nullOnDelete();
            $table->json('metadata')->nullable()->comment('Snapshot of store info at time of invoice creation');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

