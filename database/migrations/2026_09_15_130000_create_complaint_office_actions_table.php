<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('complaint_office_actions')) {
            return;
        }
        Schema::create('complaint_office_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id')->unique();
            $table->string('material_provided', 10)->nullable();
            $table->string('quantity_provided', 50)->nullable();
            $table->string('service_engineer_provided', 10)->nullable();
            $table->string('visit_report_path')->nullable();
            $table->string('replacement', 10)->nullable();
            $table->string('replacement_quantity', 50)->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->text('points_discussed')->nullable();
            $table->string('customer_care_name', 150)->nullable();
            $table->string('department_head_name', 150)->nullable();
            $table->string('manager_name', 150)->nullable();
            $table->text('final_decision')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_office_actions');
    }
};
