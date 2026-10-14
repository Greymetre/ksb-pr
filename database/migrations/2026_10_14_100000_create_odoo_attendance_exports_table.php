<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Attendance for Odoo (FieldKonnect -> Odoo, read by Odoo over the API)
|--------------------------------------------------------------------------
| odoo_attendance_exports : frozen copy of one full month of attendance, one
|                           row per employee per day (same status as the CRM
|                           Attendance Summary report), built on the 1st at
|                           01:00 IST for the previous month by
|                           `php artisan odoo:export-attendance`.
|
| Odoo reads it with GET /api/v1/odoo/attendance. The live attendances
| table is only read, never changed. See odoo-integration-docs/README.md
*/
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('odoo_attendance_exports')) {
            Schema::create('odoo_attendance_exports', function (Blueprint $table) {
                $table->id();
                $table->char('period', 7)->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('employee_code', 250)->nullable()->index();
                $table->string('employee_name', 250)->nullable();
                $table->date('attendance_date');
                $table->string('status', 50);
                $table->string('status_code', 30);
                $table->unsignedBigInteger('attendance_id')->nullable();
                $table->time('punchin_time')->nullable();
                $table->date('punchout_date')->nullable();
                $table->time('punchout_time')->nullable();
                $table->string('total_working_time', 20)->nullable();
                $table->string('time_zone', 50);
                $table->string('working_type', 400)->nullable();
                $table->timestamp('generated_at')->nullable();

                $table->unique(['period', 'user_id', 'attendance_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('odoo_attendance_exports');
    }
};
