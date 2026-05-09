<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('secondary_customers', function (Blueprint $table) {
            $table->id();

            /* ================= CUSTOMER TYPE ================= */
            $table->string('type'); // RETAILER, WORKSHOP, MECHANIC, GARAGE
            $table->string('sub_type')->nullable(); // Two-Wheeler Mechanic, Car/4W Mechanic, etc.

            /* ================= BASIC INFO ================= */
            $table->string('owner_name');
            $table->string('shop_name');
            $table->string('mobile_number')->unique();
            $table->string('whatsapp_number')->nullable();

            /* ================= PHOTOS ================= */
            $table->string('owner_photo')->nullable();    // Path to uploaded owner/ID photo
            $table->string('shop_photo')->nullable();      // Path to uploaded shop photo

            /* ================= VEHICLE SEGMENT ================= */
            $table->string('vehicle_segment')->nullable(); 
            // Can store single value like "2W", "HCV", etc.
            // If multiple needed later, change to JSON: $table->json('vehicle_segments')->nullable();

            /* ================= ADDRESS ================= */
            $table->text('address_line');
            $table->foreignId('country_id')->default(1)->constrained('countries');
$table->foreignId('state_id')->constrained('states');
$table->foreignId('district_id')->nullable()->constrained('districts');
$table->foreignId('city_id')->constrained('cities');
$table->foreignId('pincode_id')->nullable()->constrained('pincodes');
$table->foreignId('beat_id')->nullable()->constrained('beats');
            $table->string('belt_area_market_name')->nullable(); // e.g., KODIGAIYUR, MOOLAKADI

            /* ================= STATUS ================= */
            $table->enum('saathi_awareness_status', ['Done', 'Not Done']);
            $table->enum('opportunity_status', ['HOT', 'WARM', 'COLD', 'LOST'])
                  ->default('COLD');

            /* ================= LOCATION ================= */
            $table->string('gps_location')->nullable(); 
            // Recommended format: "lat,lng" or store as POINT type if using spatial
            // Alternative (better for maps): $table->point('gps_location')->nullable();

            /* ================= ADDITIONAL INFO ================= */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secondary_customers');
    }
};