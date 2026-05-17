<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFiltersTable extends Migration
{
    public function up()
    {
        Schema::create('filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('filter_category_id');
            $table->string('name');
            $table->boolean('is_premium')->default(1);
            $table->decimal('saturation', 8, 4)->default(1);
            $table->decimal('brightness', 8, 4)->default(0);
            $table->decimal('contrast',   8, 4)->default(1);
            $table->decimal('red',        8, 4)->default(1);
            $table->decimal('green',      8, 4)->default(1);
            $table->decimal('blue',       8, 4)->default(1);
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('filter_category_id')
                ->references('id')->on('filter_categories')
                ->onDelete('cascade');

            $table->index(['filter_category_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('filters');
    }
}
