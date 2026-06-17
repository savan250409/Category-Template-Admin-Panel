<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per Android app this admin panel serves (the panel drives 3 apps).
 * Each project stores its own FCM service-account JSON and the topic every
 * install of that app subscribes to — so we broadcast by topic and never
 * need to store per-device FCM tokens.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firebase_projects', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();            // lowercase slug used internally (e.g. asm074_demo)
            $table->string('display_name')->nullable(); // human label (e.g. "Apero Art Drawing")
            $table->string('project_id')->nullable();   // Firebase project id
            $table->string('sender_id')->nullable();    // FCM sender id
            $table->string('topic')->nullable();        // topic every install subscribes to (usually the package name)
            $table->longText('credentials')->nullable();// full service-account JSON (source of truth — no .env needed)
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firebase_projects');
    }
};
