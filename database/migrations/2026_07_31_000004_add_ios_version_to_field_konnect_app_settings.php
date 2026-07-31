<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('field_konnect_app_settings')
            && !Schema::hasColumn('field_konnect_app_settings', 'app_ios_version')) {
            Schema::table('field_konnect_app_settings', function (Blueprint $table) {
                $table->string('app_ios_version', 50)->default('')->after('app_version');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('field_konnect_app_settings')
            && Schema::hasColumn('field_konnect_app_settings', 'app_ios_version')) {
            Schema::table('field_konnect_app_settings', function (Blueprint $table) {
                $table->dropColumn('app_ios_version');
            });
        }
    }
};
