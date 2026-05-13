<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStickerCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('sticker_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('image')->nullable();
            $table->boolean('is_premium')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sticker_categories');
    }
}
