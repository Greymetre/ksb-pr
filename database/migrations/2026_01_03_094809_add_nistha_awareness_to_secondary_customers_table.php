<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('secondary_customers', function (Blueprint $table) {
        $table->enum('nistha_awareness_status', ['Done', 'Not Done'])
              ->nullable()
              ->default(null)
              ->after('saathi_awareness_status'); // optional positioning
    });
}

public function down(): void
{
    Schema::table('secondary_customers', function (Blueprint $table) {
        $table->dropColumn('nistha_awareness_status');
    });
}
};
