<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promotional_gifts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->unsignedInteger('quantity')->default(0);
            $table->char('active', 1)->default('Y')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotional_gifts');
    }
};
