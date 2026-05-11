<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBabyAiHomeSlidersTable extends Migration
{
    public function up()
    {
        Schema::create('baby_ai_home_sliders', function (Blueprint $table) {
            $table->id();
            $table->enum('source_type', ['image', 'video', 'dynamic_frame']);
            $table->unsignedBigInteger('source_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->string('video_thumbnail')->nullable();
            $table->boolean('is_on')->default(1);
            $table->boolean('status')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique('source_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('baby_ai_home_sliders');
    }
}
