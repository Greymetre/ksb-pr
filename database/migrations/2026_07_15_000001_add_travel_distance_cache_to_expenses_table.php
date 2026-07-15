<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('expenses', 'total_distance')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->decimal('total_distance', 15, 3)->nullable()->after('total_km');
            });
        }

        if (!Schema::hasColumn('expenses', 'distance_calculated')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->boolean('distance_calculated')->default(false)->after('total_distance');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('expenses', 'distance_calculated')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('distance_calculated');
            });
        }

        if (Schema::hasColumn('expenses', 'total_distance')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('total_distance');
            });
        }
    }
};
