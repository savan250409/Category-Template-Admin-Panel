<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNameChangeAndModelFromFilterAiImageTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('filter_ai_images', function (Blueprint $table) {
            $table->dropColumn(['name_change', 'ai_model']);
        });

        Schema::table('filter_ai_image_settings', function (Blueprint $table) {
            $table->dropColumn('model');
        });
    }

    public function down()
    {
        Schema::table('filter_ai_images', function (Blueprint $table) {
            $table->tinyInteger('name_change')->default(0)->after('no_of_image');
            $table->string('ai_model')->nullable()->after('ai_prompt');
        });

        Schema::table('filter_ai_image_settings', function (Blueprint $table) {
            $table->string('model')->nullable()->after('id');
        });
    }
}
