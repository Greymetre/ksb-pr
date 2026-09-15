<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('complaint_office_actions') || Schema::hasColumn('complaint_office_actions', 'review_decision')) {
            return;
        }
        Schema::table('complaint_office_actions', function (Blueprint $table) {
            $table->string('department_head_decision', 10)->nullable()->after('department_head_name');
            $table->string('manager_decision', 10)->nullable()->after('manager_name');
            $table->string('review_decision', 10)->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('complaint_office_actions')) {
            return;
        }
        Schema::table('complaint_office_actions', function (Blueprint $table) {
            $table->dropColumn(['department_head_decision', 'manager_decision', 'review_decision', 'reviewed_by', 'reviewed_at']);
        });
    }
};
