<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropFilterAiImageSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('filter_ai_image_settings');
    }

    public function down()
    {
        Schema::create('filter_ai_image_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
}
