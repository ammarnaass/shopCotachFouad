<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_templates')) {
            return;
        }
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('paper_size', ['a4', 'a5', 'thermal_80', 'thermal_58'])->default('a4');
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->json('settings')->nullable()->comment('show_logo, show_sku, colors, header/footer text, etc.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
};
