<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_user_login_details', function (Blueprint $table) {
            $table->string('build_number', 50)->nullable()->after('app_version');
            $table->timestamp('last_seen_at')->nullable()->after('last_login_date');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_user_login_details', function (Blueprint $table) {
            $table->dropColumn(['build_number', 'last_seen_at']);
        });
    }
};
