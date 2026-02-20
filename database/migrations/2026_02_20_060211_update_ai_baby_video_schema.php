<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAiBabyVideoSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Rename tables
        if (Schema::hasTable('ai_video_categories')) {
            Schema::rename('ai_video_categories', 'ai_baby_video_categories');
        }
        if (Schema::hasTable('ai_video_subcategories')) {
            Schema::rename('ai_video_subcategories', 'ai_baby_videos');
        }

        // Update ai_baby_video_categories
        Schema::table('ai_baby_video_categories', function (Blueprint $table) {
            if (Schema::hasColumn('ai_baby_video_categories', 'name')) {
                $table->renameColumn('name', 'category_name');
            }
            if (!Schema::hasColumn('ai_baby_video_categories', 'category_image')) {
                $table->string('category_image')->nullable()->after('category_name');
            }
            if (!Schema::hasColumn('ai_baby_video_categories', 'type')) {
                $table->string('type')->nullable()->after('category_image');
            }
            if (!Schema::hasColumn('ai_baby_video_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('type');
            }
        });

        // Update ai_baby_videos
        Schema::table('ai_baby_videos', function (Blueprint $table) {
            // Drop old columns if they exist
            $columns = ['category_name', 'category_thumbnail_image', 'title', 'videos', 'description', 'trending'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ai_baby_videos', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Add new columns
            if (!Schema::hasColumn('ai_baby_videos', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('ai_baby_videos', 'video_path')) {
                $table->string('video_path')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('ai_baby_videos', 'ai_prompt')) {
                $table->text('ai_prompt')->nullable()->after('video_path');
            }
            if (!Schema::hasColumn('ai_baby_videos', 'ai_model')) {
                $table->string('ai_model')->nullable()->after('ai_prompt');
            }
            if (!Schema::hasColumn('ai_baby_videos', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('ai_model');
            }
            if (!Schema::hasColumn('ai_baby_videos', 'no_of_video')) {
                $table->integer('no_of_video')->default(1)->after('sort_order');
            }
            // name_change already exists
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse operations
        if (Schema::hasTable('ai_baby_video_categories')) {
            Schema::rename('ai_baby_video_categories', 'ai_video_categories');
            Schema::table('ai_video_categories', function (Blueprint $table) {
                if (Schema::hasColumn('ai_video_categories', 'category_name')) {
                    $table->renameColumn('category_name', 'name');
                }
                $table->dropColumn(['category_image', 'type', 'sort_order']);
            });
        }
        if (Schema::hasTable('ai_baby_videos')) {
            Schema::rename('ai_baby_videos', 'ai_video_subcategories');
        }
    }
}
