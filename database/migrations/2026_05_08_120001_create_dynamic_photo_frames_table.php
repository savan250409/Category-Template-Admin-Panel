<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicPhotoFramesTable extends Migration
{
    public function up()
    {
        Schema::create('dynamic_photo_frames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dynamic_photo_frame_category_id')
                ->constrained('dynamic_photo_frame_categories')
                ->onDelete('cascade');
            $table->string('zip_file');
            $table->integer('input_count')->default(1);
            $table->string('thumbnail')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dynamic_photo_frames');
    }
}
