<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('secondary_customers', function (Blueprint $table) {
        if (!Schema::hasColumn('secondary_customers', 'state_id')) {
            $table->bigInteger('state_id')->unsigned()->nullable();
        }

        if (!Schema::hasColumn('secondary_customers', 'district_id')) {
            $table->bigInteger('district_id')->unsigned()->nullable();
        }

        if (!Schema::hasColumn('secondary_customers', 'city_id')) {
            $table->bigInteger('city_id')->unsigned()->nullable();
        }

        if (!Schema::hasColumn('secondary_customers', 'pincode_id')) {
            $table->bigInteger('pincode_id')->unsigned()->nullable();
        }

        if (!Schema::hasColumn('secondary_customers', 'beat_id')) {
            $table->bigInteger('beat_id')->unsigned()->nullable();
        }
    });
}

public function down(): void
{
    Schema::table('secondary_customers', function (Blueprint $table) {
        $table->dropColumn([
            'state_id',
            'district_id',
            'city_id',
            'pincode_id',
            'beat_id',
        ]);
    });

}
};