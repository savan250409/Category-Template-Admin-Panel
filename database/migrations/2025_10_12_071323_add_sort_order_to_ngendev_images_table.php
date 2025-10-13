<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToNgendevImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ngendev_images', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('ai_model');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ngendev_images', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}
