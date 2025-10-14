<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageTitleAndNameChangeToSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * This will add two new columns to the subcategories table:
     * 1) image_title -> string, nullable
     * 2) name_change -> string, nullable
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('image_title')->nullable()->after('title'); // store image title
            $table->string('name_change')->nullable()->after('image_title'); // store updated subcategory name
        });
    }

    /**
     * Reverse the migrations.
     *
     * This will drop the columns added in the up() method.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn(['image_title', 'name_change']);
        });
    }
}
