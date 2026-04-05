<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_ai_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name')->nullable();
            $table->text('ai_prompt');
            $table->string('ai_model')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('no_of_image')->default(1);
            $table->tinyInteger('name_change')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('filter_ai_image_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_ai_images');
    }
};
