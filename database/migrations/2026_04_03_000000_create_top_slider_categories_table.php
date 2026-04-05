<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_slider_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('thumbnail_image')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('file_type', ['image', 'video'])->default('image');
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->string('video_thumbnail')->nullable();
            $table->boolean('top_slider_is_on')->default(1);
            $table->boolean('status')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('top_slider_categories');
    }
};
