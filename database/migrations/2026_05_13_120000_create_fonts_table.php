<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFontsTable extends Migration
{
    public function up()
    {
        Schema::create('fonts', function (Blueprint $table) {
            $table->id();
            $table->string('font_name')->unique();
            $table->string('font_file')->nullable();
            $table->string('preview_image')->nullable();
            $table->boolean('is_premium')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fonts');
    }
}
