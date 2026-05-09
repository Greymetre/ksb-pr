<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('active_customer_process_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('active_customer_process_id');
            $table->unsignedBigInteger('customer_process_step_id');

            $table->enum('status', ['pending', 'active', 'completed'])->default('pending');
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('remark')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('active_customer_process_id')
                ->references('id')
                ->on('active_customer_processes')
                ->onDelete('cascade');

            $table->foreign('customer_process_step_id')
                ->references('id')
                ->on('customer_process_steps')
                ->onDelete('cascade');

            $table->foreign('completed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('active_customer_process_steps');
    }
};
