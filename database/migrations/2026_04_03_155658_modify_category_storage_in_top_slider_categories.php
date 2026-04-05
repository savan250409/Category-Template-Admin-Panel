<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCategoryStorageInTopSliderCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('top_slider_categories', function (Blueprint $table) {
            $table->renameColumn('original_category_id', 'category_id');
            $table->dropColumn('category_name');
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
            $table->renameColumn('category_id', 'original_category_id');
            $table->string('category_name')->nullable();
        });
    }
}
