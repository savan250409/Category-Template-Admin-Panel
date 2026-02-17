<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ngendev_categories', function (Blueprint $table) {
            $table->enum('type', ['Solo', 'Couple'])->default('Solo')->after('category_image');
        });

        Schema::table('ai_image_ngd_setting', function (Blueprint $table) {
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
        Schema::table('ngendev_categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('ai_image_ngd_setting', function (Blueprint $table) {
            $table->dropColumn('couple_active');
        });
    }
};
