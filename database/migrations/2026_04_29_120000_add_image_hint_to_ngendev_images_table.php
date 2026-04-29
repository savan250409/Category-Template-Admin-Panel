<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageHintToNgendevImagesTable extends Migration
{
    public function up()
    {
        Schema::table('ngendev_images', function (Blueprint $table) {
            $table->string('image_hint')->nullable()->after('name_change');
        });
    }

    public function down()
    {
        Schema::table('ngendev_images', function (Blueprint $table) {
            $table->dropColumn('image_hint');
        });
    }
}
