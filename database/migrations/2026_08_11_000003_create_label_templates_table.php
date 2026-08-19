<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('label_templates')) { return; }
        Schema::create('label_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('paper_size', ['100x150', '100x100', '80x50', 'a6', 'custom'])->default('100x150');
            $table->unsignedSmallInteger('custom_width')->nullable()->comment('in mm');
            $table->unsignedSmallInteger('custom_height')->nullable()->comment('in mm');
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->json('settings')->nullable()->comment('show_barcode, show_qr, show_logo, etc.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_templates');
    }
};

