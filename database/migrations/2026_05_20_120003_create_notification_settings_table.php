<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Singleton settings row for the Notify module. Currently just the
 * default delay applied when an admin sends without picking a date/time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('default_delay_minutes')->default(3);
            $table->timestamps();
        });

        DB::table('notification_settings')->insert([
            'default_delay_minutes' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
