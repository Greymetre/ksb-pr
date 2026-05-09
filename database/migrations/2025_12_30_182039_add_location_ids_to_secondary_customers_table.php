<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('secondary_customers', function (Blueprint $table) {
        // Drop foreign keys aur ID columns
        $table->dropForeign(['country_id', 'state_id', 'district_id', 'city_id', 'pincode_id']);
        $table->dropColumn(['country_id', 'state_id', 'district_id', 'city_id', 'pincode_id']);

        // Wapas string columns add karo
        $table->string('country')->nullable();
        $table->string('state')->nullable();
        $table->string('district')->nullable();
        $table->string('city')->nullable();
        $table->string('pincode')->nullable();
    });
}

public function down()
{
    Schema::table('secondary_customers', function (Blueprint $table) {
        $table->dropColumn(['country', 'state', 'district', 'city', 'pincode']);

        $table->foreignId('country_id')->nullable()->constrained();
        $table->foreignId('state_id')->nullable()->constrained();
        $table->foreignId('district_id')->nullable()->constrained();
        $table->foreignId('city_id')->nullable()->constrained();
        $table->foreignId('pincode_id')->nullable()->constrained();
    });
}
};
