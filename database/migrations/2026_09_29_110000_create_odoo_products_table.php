<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Odoo products (Product Master Odoo)
|--------------------------------------------------------------------------
| Pulled from Odoo (product.product / get_fieldkonnect_products) by
| `php artisan odoo:sync-products` twice a day, or "Sync now" on the page.
| category_external_id / subcategory_external_id point to odoo_categories /
| odoo_subcategories. product_id links to the FieldKonnect `products` row with
| the same product_code (or sap_code); `products` is only read, never changed.
| See odoo-integration-docs/README.md
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('odoo_products')) {
            return;
        }

        Schema::create('odoo_products', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('product_code', 100)->index();
            $table->string('product_name', 250)->index();
            $table->string('display_name', 300)->nullable();
            $table->text('description')->nullable();

            $table->string('category_external_id', 100)->nullable()->index();
            $table->string('category_code', 50)->nullable();
            $table->string('subcategory_external_id', 100)->nullable()->index();
            $table->string('subcategory_code', 50)->nullable();
            $table->string('brand_code', 50)->nullable();
            $table->string('brand_name', 150)->nullable();
            $table->string('uom_code', 50)->nullable();
            $table->string('uom_name', 100)->nullable();

            $table->string('hsn_code', 50)->nullable();
            $table->string('ean_barcode', 100)->nullable();
            $table->decimal('mrp', 19, 4)->nullable();
            $table->decimal('standard_price', 19, 4)->nullable();
            $table->decimal('gst_percent', 8, 4)->nullable();
            $table->char('currency_code', 3)->nullable();

            $table->string('model_no', 250)->nullable();
            $table->string('part_no', 250)->nullable();
            $table->string('product_group', 150)->nullable();
            $table->string('product_subgroup', 150)->nullable();
            $table->text('specification')->nullable();
            $table->string('image_url', 500)->nullable();

            $table->boolean('orderable')->default(true);
            $table->boolean('active')->default(true);
            $table->boolean('is_deleted')->default(false);
            // Resolved FieldKonnect product (null when product_code not found)
            $table->unsignedBigInteger('product_id')->nullable()->index();

            $table->dateTime('odoo_created_at')->nullable();
            $table->dateTime('odoo_updated_at')->nullable();
            $table->string('last_correlation_id', 100)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odoo_products');
    }
};
