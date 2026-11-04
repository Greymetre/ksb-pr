<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotional_activities', function (Blueprint $table) {
            $table->decimal('actual_expense', 12, 2)->nullable()->after('execution_remark');
            $table->json('bills')->nullable()->after('actual_expense');
            $table->timestamp('execution_submitted_at')->nullable()->after('bills');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('execution_submitted_at');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_remark')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('promotional_activities', function (Blueprint $table) {
            $table->dropColumn(['actual_expense', 'bills', 'execution_submitted_at', 'reviewed_by', 'reviewed_at', 'review_remark']);
        });
    }
};
