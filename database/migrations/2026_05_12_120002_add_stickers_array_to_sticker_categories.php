<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStickersArrayToStickerCategories extends Migration
{
    public function up()
    {
        Schema::table('sticker_categories', function (Blueprint $table) {
            $table->json('stickers')->nullable()->after('image');
        });

        if (Schema::hasTable('stickers')) {
            $cats = DB::table('sticker_categories')->select('id')->get();
            foreach ($cats as $cat) {
                $imgs = DB::table('stickers')
                    ->where('sticker_category_id', $cat->id)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'desc')
                    ->pluck('image')
                    ->values()
                    ->all();
                DB::table('sticker_categories')->where('id', $cat->id)->update([
                    'stickers' => json_encode($imgs),
                ]);
            }

            Schema::dropIfExists('stickers');
        }
    }

    public function down()
    {
        Schema::create('stickers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sticker_category_id')
                ->constrained('sticker_categories')
                ->onDelete('cascade');
            $table->string('image');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $cats = DB::table('sticker_categories')->select('id', 'stickers')->get();
        foreach ($cats as $cat) {
            $arr = json_decode($cat->stickers ?? '[]', true) ?: [];
            foreach ($arr as $i => $filename) {
                DB::table('stickers')->insert([
                    'sticker_category_id' => $cat->id,
                    'image'               => $filename,
                    'sort_order'          => $i,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }

        Schema::table('sticker_categories', function (Blueprint $table) {
            $table->dropColumn('stickers');
        });
    }
}
