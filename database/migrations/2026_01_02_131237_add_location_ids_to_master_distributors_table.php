<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('master_distributors', function (Blueprint $table) {
            // Billing IDs
            $table->unsignedBigInteger('country_id')->nullable()->after('billing_pincode');
            $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
            $table->unsignedBigInteger('district_id')->nullable()->after('state_id');
            $table->unsignedBigInteger('city_id')->nullable()->after('district_id');
            $table->unsignedBigInteger('pincode_id')->nullable()->after('city_id');

            // Shipping IDs
            $table->unsignedBigInteger('shipping_country_id')->nullable()->after('shipping_pincode');
            $table->unsignedBigInteger('shipping_state_id')->nullable()->after('shipping_country_id');
            $table->unsignedBigInteger('shipping_district_id')->nullable()->after('shipping_state_id');
            $table->unsignedBigInteger('shipping_city_id')->nullable()->after('shipping_district_id');
            $table->unsignedBigInteger('shipping_pincode_id')->nullable()->after('shipping_city_id');

            // Optional: Foreign keys (agar strict relations chahiye)
            // $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('master_distributors', function (Blueprint $table) {
            $table->dropColumn([
                'country_id', 'state_id', 'district_id', 'city_id', 'pincode_id',
                'shipping_country_id', 'shipping_state_id', 'shipping_district_id',
                'shipping_city_id', 'shipping_pincode_id'
            ]);
        });
    }
};