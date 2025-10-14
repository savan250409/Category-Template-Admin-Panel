<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop `image_title` and `name_change` columns from subcategories table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            if (Schema::hasColumn('subcategories', 'image_title')) {
                $table->dropColumn('image_title');
            }
            if (Schema::hasColumn('subcategories', 'name_change')) {
                $table->dropColumn('name_change');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Re-add the columns if needed.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('image_title')->nullable()->after('title');
            $table->string('name_change')->nullable()->after('image_title');
        });
    }
};
