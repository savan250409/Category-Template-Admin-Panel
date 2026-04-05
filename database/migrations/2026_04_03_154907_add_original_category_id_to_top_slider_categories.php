<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalCategoryIdToTopSliderCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('top_slider_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('original_category_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('top_slider_categories', function (Blueprint $table) {
            $table->dropColumn('original_category_id');
        });
    }
}
