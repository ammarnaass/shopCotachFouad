<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['links', 'categories', 'custom_html', 'contact', 'social', 'store_info'])->default('links');
            $table->text('custom_html')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('footer_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('footer_section_id')->constrained('footer_sections')->cascadeOnDelete();
            $table->string('title');
            $table->string('url');
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('footer_socials', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['facebook', 'instagram', 'tiktok', 'youtube', 'whatsapp', 'telegram', 'snapchat', 'twitter'])->unique();
            $table->string('url');
            $table->string('icon')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_links');
        Schema::dropIfExists('footer_sections');
        Schema::dropIfExists('footer_socials');
    }
};
