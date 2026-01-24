<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiVideoSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_video_subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('category_thumbnail_image')->nullable();
            $table->string('title');
            $table->longText('images')->nullable();
            $table->text('description')->nullable();
            $table->boolean('name_change')->default(false);
            $table->boolean('trending')->default(false);
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
        Schema::dropIfExists('ai_video_subcategories');
    }
}
