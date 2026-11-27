<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Calling remarks: the admin calls an employee from User List > call icon,
| reviews that employee's work for one day and saves a remark / issue.
| One row per employee per call date.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_calling_remarks')) {
            Schema::create('user_calling_remarks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->date('call_date')->index();
                $table->text('calling_remark')->nullable();
                $table->text('issue')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'call_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_calling_remarks');
    }
};
