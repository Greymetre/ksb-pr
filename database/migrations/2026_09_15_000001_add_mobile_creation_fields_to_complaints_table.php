<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->unsignedBigInteger('dealer_id')->nullable()->after('complaint_date');
            $table->string('alternate_number', 10)->nullable();
            $table->string('end_user_name', 150)->nullable();
            $table->string('end_user_mobile', 10)->nullable();
            $table->string('technician_mobile', 10)->nullable();
            $table->unsignedBigInteger('product_category_id')->nullable();
            $table->string('product_size', 50)->nullable();
            $table->string('size_unit', 10)->nullable();
            $table->string('batch_no_dom', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['dealer_id', 'alternate_number', 'end_user_name', 'end_user_mobile', 'technician_mobile', 'product_category_id', 'product_size', 'size_unit', 'batch_no_dom']);
        });
    }
};
