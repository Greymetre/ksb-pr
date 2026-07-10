<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('expenses_types', 'payroll_id')) {
            Schema::table('expenses_types', function (Blueprint $table) {
                $table->string('payroll_id')->nullable()->after('allowance_type_id');
            });

            return;
        }

        DB::statement('ALTER TABLE expenses_types MODIFY payroll_id VARCHAR(255) NULL');
    }

    public function down()
    {
        if (Schema::hasColumn('expenses_types', 'payroll_id')) {
            DB::statement('ALTER TABLE expenses_types MODIFY payroll_id BIGINT NULL');
        }
    }
};
