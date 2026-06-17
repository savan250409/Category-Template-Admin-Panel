<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            // NULL = broadcast to all active projects (existing behaviour).
            // A JSON array of integers = only those specific firebase_projects.id values.
            $table->json('target_project_ids')->nullable()->after('extra_data');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            $table->dropColumn('target_project_ids');
        });
    }
};
