<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->string('plivo_status', 50)->nullable()->index();
            $table->string('plivo_call_uuid')->nullable()->unique();
            $table->string('plivo_b_leg_uuid')->nullable()->index();
            $table->text('recording_url')->nullable();
            $table->string('recording_id')->nullable()->index();
            $table->decimal('cost', 12, 6)->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('webhook_token', 64)->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropColumn([
                'plivo_status', 'plivo_call_uuid', 'plivo_b_leg_uuid',
                'recording_url', 'recording_id', 'cost', 'answered_at',
                'completed_at', 'webhook_token',
            ]);
        });
    }
};
