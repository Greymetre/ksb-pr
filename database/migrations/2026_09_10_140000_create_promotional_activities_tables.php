<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promotional_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_status_id');
            $table->date('activity_date');
            $table->string('location_name', 255);
            $table->decimal('company_share', 12, 2)->default(0);
            $table->decimal('distributor_share', 12, 2)->default(0);
            $table->text('remark')->nullable();
            $table->string('approval_status', 20)->default('pending')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('reporting_manager_id')->nullable()->index();
            $table->unsignedBigInteger('approved_rejected_by')->nullable();
            $table->timestamp('approved_rejected_at')->nullable();
            $table->text('approval_remark')->nullable();
            $table->timestamps();
        });

        Schema::create('promotional_activity_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotional_activity_id')->index();
            $table->unsignedBigInteger('promotional_gift_id')->index();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['promotional_activity_id', 'promotional_gift_id'], 'pac_gift_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotional_activity_gifts');
        Schema::dropIfExists('promotional_activities');
    }
};
