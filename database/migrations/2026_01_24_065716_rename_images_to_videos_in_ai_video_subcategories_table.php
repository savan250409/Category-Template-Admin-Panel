<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameImagesToVideosInAiVideoSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_video_subcategories', function (Blueprint $table) {
            $table->renameColumn('images', 'videos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ai_video_subcategories', function (Blueprint $table) {
            $table->renameColumn('videos', 'images');
        });
    }
}
