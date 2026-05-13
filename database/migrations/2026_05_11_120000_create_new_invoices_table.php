<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('new_invoices', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to secondary_customers
            $table->foreignId('secondary_customer_id')
                ->constrained('secondary_customers')
                ->onDelete('cascade');
            
            // Invoice details
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('points', 15, 2)->default(0);

            // Approval lifecycle: 0 pending, 1 SS, 2 Sales, 3 HO, 4 rejected
            $table->tinyInteger('approval_status')->default(0)->index();
            $table->text('approval_remark')->nullable();
            $table->unsignedBigInteger('approved_ss_by')->nullable()->index();
            $table->timestamp('approved_ss_at')->nullable();
            $table->unsignedBigInteger('approved_sales_by')->nullable()->index();
            $table->timestamp('approved_sales_at')->nullable();
            $table->unsignedBigInteger('approved_ho_by')->nullable()->index();
            $table->timestamp('approved_ho_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable()->index();
            $table->timestamp('rejected_at')->nullable();
            
            // Tracking
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_invoices');
    }
};
