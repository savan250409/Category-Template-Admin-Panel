<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryThumbnailImageToSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('category_thumbnail_image')->nullable()->after('category_name')->comment('Thumbnail image for the category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn('category_thumbnail_image');
        });
    }
}
