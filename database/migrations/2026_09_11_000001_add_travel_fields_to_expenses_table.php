<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('night_halt')->nullable()->after('date');
            $table->string('from_location')->nullable()->after('night_halt');
            $table->string('to_location')->nullable()->after('from_location');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['night_halt', 'from_location', 'to_location']);
        });
    }
};
