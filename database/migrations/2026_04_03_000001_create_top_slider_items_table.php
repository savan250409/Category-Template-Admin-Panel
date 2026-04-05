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
        Schema::create('top_slider_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('top_slider_category_id')->constrained('top_slider_categories')->onDelete('cascade');
            $table->text('prompt')->nullable();
            $table->string('file_type')->default('image'); // 'image' or 'video'
            $table->string('file')->nullable();
            $table->string('video_thumbnail')->nullable();
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
        Schema::dropIfExists('top_slider_items');
    }
};
