<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotional_activities', function (Blueprint $table) {
            $table->text('pop_delivery_address')->nullable()->after('estimated_budget');
            $table->json('sample_materials')->nullable()->after('pop_delivery_address');
            $table->text('sample_delivery_address')->nullable()->after('sample_materials');
        });
    }

    public function down(): void
    {
        Schema::table('promotional_activities', function (Blueprint $table) {
            $table->dropColumn(['pop_delivery_address', 'sample_materials', 'sample_delivery_address']);
        });
    }
};
