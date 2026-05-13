<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStickersTable extends Migration
{
    public function up()
    {
        Schema::create('stickers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sticker_category_id')
                ->constrained('sticker_categories')
                ->onDelete('cascade');
            $table->string('image');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stickers');
    }
}
