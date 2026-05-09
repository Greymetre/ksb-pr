<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secondary_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('secondary_customers', 'saathi_awareness')) {
                $table->tinyInteger('saathi_awareness')->default(1);
            }
        });
    }

    public function down(): void
    {
        Schema::table('secondary_customers', function (Blueprint $table) {
            if (Schema::hasColumn('secondary_customers', 'saathi_awareness')) {
                $table->dropColumn('saathi_awareness');
            }
        });
    }
};