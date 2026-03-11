<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNgendevVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ngendev_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('ngendev_video_categories')->onDelete('cascade');
            $table->string('video_thumbnail')->nullable();
            $table->string('video_path');
            $table->text('ai_prompt');
            $table->string('ai_model')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('no_of_video')->default(1);
            $table->boolean('name_change')->default(0);
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
        Schema::dropIfExists('ngendev_videos');
    }
}
