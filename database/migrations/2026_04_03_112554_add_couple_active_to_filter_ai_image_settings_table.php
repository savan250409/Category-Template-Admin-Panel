<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoupleActiveToFilterAiImageSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('filter_ai_image_settings', function (Blueprint $table) {
            $table->boolean('couple_active')->default(1)->after('model');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('filter_ai_image_settings', function (Blueprint $table) {
            $table->dropColumn('couple_active');
        });
    }
}
