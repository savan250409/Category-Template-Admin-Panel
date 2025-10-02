<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ngendev_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('ngendev_categories')->onDelete('cascade');
            $table->string('image_path');
            $table->text('ai_prompt');
            $table->string('ai_model')->nullable(); // AI Image / AI Figure
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngendev_images');
    }
};
