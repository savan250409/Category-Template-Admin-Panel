<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToNgendevImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ngendev_images', function (Blueprint $table) {
            $table->integer('no_of_image')->default(1);
            $table->boolean('name_change')->default(0);
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
            $table->dropColumn(['no_of_image', 'name_change']);
        });
    }
}
