<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->string('mobile_image')->nullable()->after('image');
            $table->text('description')->nullable()->after('subtitle');
            $table->enum('button_target', ['_same', '_blank'])->default('_same')->after('btn_text');
            $table->timestamp('starts_at')->nullable()->after('is_active');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->index(['is_active', 'starts_at', 'ends_at', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'starts_at', 'ends_at', 'sort_order']);
            $table->dropColumn(['mobile_image', 'description', 'button_target', 'starts_at', 'ends_at']);
        });
    }
};