<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Queue of pending / sent pushes. A background dispatcher picks up rows
 * whose scheduled_at is due and broadcasts them to every active Firebase
 * project's topic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('module')->nullable();        // registry slug, or "global"
            $table->string('type')->nullable();          // app bucket: img | vid | global
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url', 1000)->nullable();
            $table->string('click_action')->nullable();
            $table->string('screen')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending'); // pending | sent | failed
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_notifications');
    }
};
