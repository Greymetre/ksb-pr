<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('new_dealer_targets') && ! Schema::hasColumn('new_dealer_targets', 'achievement')) {
            Schema::table('new_dealer_targets', function (Blueprint $table) {
                $table->unsignedInteger('achievement')->nullable()->after('target');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('new_dealer_targets') && Schema::hasColumn('new_dealer_targets', 'achievement')) {
            Schema::table('new_dealer_targets', function (Blueprint $table) {
                $table->dropColumn('achievement');
            });
        }
    }
};
