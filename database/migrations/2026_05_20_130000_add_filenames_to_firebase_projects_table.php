<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remember the original names of the uploaded JSON files so the edit
 * screen can show which files are currently configured.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firebase_projects', function (Blueprint $table) {
            $table->string('service_account_filename')->nullable()->after('credentials');
            $table->string('google_services_filename')->nullable()->after('service_account_filename');
        });
    }

    public function down(): void
    {
        Schema::table('firebase_projects', function (Blueprint $table) {
            $table->dropColumn(['service_account_filename', 'google_services_filename']);
        });
    }
};
