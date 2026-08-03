<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->string('show_in_mobile', 1)->default('Y')->after('active')->index();
        });
    }

    public function down()
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropColumn('show_in_mobile');
        });
    }
};
