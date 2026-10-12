<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Odoo primary customers (Primary Customer Odoo)
|--------------------------------------------------------------------------
| Pulled from Odoo (res.partner / get_fieldkonnect_customer_list, paginated) by
| `php artisan odoo:sync-customers` twice a day, or "Sync now" on the page.
| customer_odoo_id is Odoo's odoo_parent_id (party_odoo_id).
| customer_id links to the FieldKonnect `customers` row with the same GSTIN
| (customer_details.gstin_no), else the same mobile; `customers` is only read.
| See odoo-integration-docs/README.md
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('odoo_customers')) {
            return;
        }

        Schema::create('odoo_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_odoo_id')->unique();
            $table->string('firm_name', 250)->index();
            $table->string('contact_person_name', 250)->nullable();
            $table->string('mobile', 50)->nullable()->index();
            $table->string('phone', 50)->nullable();
            $table->string('email', 250)->nullable();
            $table->string('street', 500)->nullable();
            $table->string('street2', 500)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('district', 150)->nullable();
            $table->string('state', 150)->nullable()->index();
            $table->string('pin_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('gstin', 20)->nullable()->index();
            $table->string('salesperson', 250)->nullable();
            $table->string('customer_type', 100)->nullable();
            $table->unsignedBigInteger('customer_type_id')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_deleted')->default(false);
            // Resolved FieldKonnect customer (null when not matched)
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            $table->dateTime('odoo_created_at')->nullable();
            $table->dateTime('odoo_updated_at')->nullable();
            $table->string('last_correlation_id', 100)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odoo_customers');
    }
};
