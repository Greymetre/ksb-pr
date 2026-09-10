<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('promotional_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('distributor_id')->nullable()->after('reporting_manager_id');
            $table->json('activity_photos')->nullable()->after('approval_remark');
            $table->json('participants')->nullable()->after('activity_photos');
            $table->timestamp('completed_at')->nullable()->after('participants');
        });
    }

    public function down()
    {
        Schema::table('promotional_activities', function (Blueprint $table) {
            $table->dropColumn(['distributor_id', 'activity_photos', 'participants', 'completed_at']);
        });
    }
};
