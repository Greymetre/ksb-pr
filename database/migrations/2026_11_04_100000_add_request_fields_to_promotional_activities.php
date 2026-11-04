<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotional_activities', function (Blueprint $table) {
            $table->string('request_no', 20)->nullable()->unique()->after('id');
            $table->text('purpose')->nullable()->after('location_name');
            $table->decimal('estimated_budget', 12, 2)->nullable()->after('purpose');
            $table->json('attachments')->nullable()->after('remark');
        });

        // Existing activities get their request ID from their id, same as new ones.
        DB::table('promotional_activities')->whereNull('request_no')
            ->update(['request_no' => DB::raw("CONCAT('PA-', LPAD(id, 5, '0'))")]);
    }

    public function down(): void
    {
        Schema::table('promotional_activities', function (Blueprint $table) {
            $table->dropUnique(['request_no']);
            $table->dropColumn(['request_no', 'purpose', 'estimated_budget', 'attachments']);
        });
    }
};
