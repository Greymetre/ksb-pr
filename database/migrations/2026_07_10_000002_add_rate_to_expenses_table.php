<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('expenses', 'rate')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->decimal('rate', 12, 2)->nullable()->after('expenses_type');
            });
        }

        DB::statement('
            UPDATE expenses
            LEFT JOIN expenses_types ON expenses.expenses_type = expenses_types.id
            SET expenses.rate = COALESCE(expenses.rate, expenses_types.rate, 0)
        ');
    }

    public function down()
    {
        if (Schema::hasColumn('expenses', 'rate')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('rate');
            });
        }
    }
};
