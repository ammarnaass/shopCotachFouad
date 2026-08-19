<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->string('animation_effect', 20)->default('fade')->after('button_target');
            $table->string('entrance_effect', 20)->default('fade-up')->after('animation_effect');

            $table->index('animation_effect');
            $table->index('entrance_effect');
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropIndex(['animation_effect']);
            $table->dropColumn(['animation_effect', 'entrance_effect']);
        });
    }
};
