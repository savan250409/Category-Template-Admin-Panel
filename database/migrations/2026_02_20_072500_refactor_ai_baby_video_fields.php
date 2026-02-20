<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_baby_videos', function (Blueprint $table) {
            $table->dropColumn(['ai_model', 'no_of_video']);
            $table->string('video_title')->nullable();
            $table->string('video_thumbnail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_baby_videos', function (Blueprint $table) {
            $table->string('ai_model')->nullable();
            $table->integer('no_of_video')->default(1);
            $table->dropColumn(['video_title', 'video_thumbnail']);
        });
    }
};
