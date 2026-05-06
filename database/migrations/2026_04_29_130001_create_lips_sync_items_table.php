<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLipsSyncItemsTable extends Migration
{
    public function up()
    {
        Schema::create('lips_sync_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lips_sync_category_id')
                ->constrained('lips_sync_categories')
                ->onDelete('cascade');
            $table->string('title');
            $table->string('song');
            $table->string('video');
            $table->string('video_thumbnail')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lips_sync_items');
    }
}
