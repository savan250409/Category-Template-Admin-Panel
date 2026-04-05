<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNoOfImageFromFilterAiImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('filter_ai_images', function (Blueprint $table) {
            $table->dropColumn('no_of_image');
        });
    }

    public function down()
    {
        Schema::table('filter_ai_images', function (Blueprint $table) {
            $table->integer('no_of_image')->default(1)->after('ai_prompt');
        });
    }
}
