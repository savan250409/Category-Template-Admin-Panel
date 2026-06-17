<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail: one row per FCM API call (per project, per send).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_notification_id')->nullable();
            $table->unsignedBigInteger('firebase_project_id')->nullable();
            $table->string('module')->nullable();
            $table->string('event')->nullable();
            $table->string('target_type')->nullable(); // topic | token
            $table->string('target')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('image_url', 1000)->nullable();
            $table->json('data')->nullable();
            $table->boolean('success')->default(false);
            $table->text('response')->nullable();
            $table->timestamps();

            $table->index('scheduled_notification_id');
            $table->index('firebase_project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
