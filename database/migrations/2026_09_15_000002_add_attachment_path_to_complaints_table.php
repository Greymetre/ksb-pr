<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('complaints', 'attachment_path')) {
            Schema::table('complaints', function (Blueprint $table) {
                $table->string('attachment_path')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('complaints', 'attachment_path')) {
            Schema::table('complaints', function (Blueprint $table) {
                $table->dropColumn('attachment_path');
            });
        }
    }
};
