<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('customer_details', 'bank_account_type')) {
            Schema::table('customer_details', function (Blueprint $table) {
                $table->string('bank_account_type', 20)->nullable()->after('account_holder');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customer_details', 'bank_account_type')) {
            Schema::table('customer_details', function (Blueprint $table) {
                $table->dropColumn('bank_account_type');
            });
        }
    }
};
