<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('new_dealer_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('target_month')->index();
            $table->unsignedInteger('target');
            $table->unsignedInteger('achievement')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->unique(['user_id', 'target_month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('new_dealer_targets');
    }
};
