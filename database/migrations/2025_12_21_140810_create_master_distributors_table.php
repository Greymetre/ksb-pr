<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_distributors', function (Blueprint $table) {
            $table->id();

            /* ================= BASIC INFO ================= */
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('distributor_code')->unique();
            $table->string('category');
            $table->string('business_status');
            $table->date('business_start_date');

            $table->string('shop_image')->nullable();
            $table->string('profile_image')->nullable();

            /* ================= CONTACT ================= */
            $table->string('contact_person');
            $table->string('c')->nullable();
            $table->string('mobile');
            $table->string('alternate_mobile')->nullable();
            $table->string('email');
            $table->string('secondary_email')->nullable();

            /* ================= BILLING ADDRESS ================= */
            $table->string('billing_address');
            $table->string('billing_city');
            $table->string('billing_district');
            $table->string('billing_state');
            $table->string('billing_country');
            $table->string('billing_pincode');

            /* ================= SHIPPING ADDRESS ================= */
            $table->string('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_district');
            $table->string('shipping_state');
            $table->string('shipping_country');
            $table->string('shipping_pincode');

            /* ================= BUSINESS & OPERATION ================= */
            $table->string('sales_zone');
            $table->string('area_territory');
            $table->string('beat_route');
            $table->string('market_classification');
            $table->text('competitor_brands')->nullable();

            /* ================= COMPLIANCE / KYC ================= */
            $table->string('gst_number');
            $table->string('pan_number');
            $table->string('registration_type');
            $table->string('documents')->nullable();

            /* ================= BANKING ================= */
            $table->string('bank_name');
            $table->string('account_holder');
            $table->string('account_number');
            $table->string('ifsc');
            $table->string('branch_name')->nullable();
            $table->decimal('credit_limit', 15, 2);
            $table->integer('credit_days');
            $table->decimal('avg_monthly_purchase', 15, 2)->nullable();
            $table->decimal('outstanding_balance', 15, 2)->nullable();
            $table->string('preferred_payment_method')->nullable();
            $table->string('cancelled_cheque');

            /* ================= SALES ================= */
            $table->decimal('monthly_sales', 15, 2);
            $table->string('product_categories');
            $table->string('secondary_sales_required')->nullable();
            $table->string('last_12_months_sales')->nullable();
            $table->unsignedBigInteger('sales_executive_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->string('customer_segment');

            /* ================= ADDITIONAL ================= */
            $table->string('weekly_tai_alert');
            $table->string('target_vs_achievement');
            $table->string('schemes_updates');
            $table->string('new_launch_update');
            $table->string('payment_alert');
            $table->string('pending_orders');
            $table->string('inventory_status');

            /* ================= CAPACITY ================= */
            $table->decimal('turnover', 15, 2);
            $table->string('staff_strength');
            $table->string('vehicles_capacity');
            $table->string('area_coverage');
            $table->string('other_brands_handled');
            $table->string('warehouse_size');

            $table->timestamps();
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('master_distributors');
    }
};
