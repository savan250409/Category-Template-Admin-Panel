<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoodlesTable extends Migration
{
    public function up()
    {
        Schema::create('doodles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('image')->nullable();
            $table->enum('doodle_type', ['image', 'line'])->default('image');
            $table->boolean('is_premium')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('doodles');
    }
}
