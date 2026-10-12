<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Odoo products now come from product.product / get_fieldkonnect_product_list
|--------------------------------------------------------------------------
| Called once per synced category (category_odoo_id); each record has
| product_odoo_id, product_name, category/sub-category odoo ids, uom, prices
| (no external_id / product_code), so product_odoo_id becomes the upsert key
| and the old columns become optional.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odoo_products', function (Blueprint $table) {
            if (!Schema::hasColumn('odoo_products', 'product_odoo_id')) {
                $table->unsignedBigInteger('product_odoo_id')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('odoo_products', 'category_odoo_id')) {
                // odoo_categories.category_odoo_id
                $table->unsignedBigInteger('category_odoo_id')->nullable()->index()->after('description');
            }
            if (!Schema::hasColumn('odoo_products', 'subcategory_odoo_id')) {
                // odoo_subcategories.subcategory_odoo_id
                $table->unsignedBigInteger('subcategory_odoo_id')->nullable()->index()->after('category_odoo_id');
            }
            if (!Schema::hasColumn('odoo_products', 'uom_id')) {
                $table->unsignedBigInteger('uom_id')->nullable()->after('uom_name');
            }
            $table->string('external_id', 100)->nullable()->change();
            $table->string('product_code', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('odoo_products', function (Blueprint $table) {
            $table->dropUnique(['product_odoo_id']);
            $table->dropIndex(['category_odoo_id']);
            $table->dropIndex(['subcategory_odoo_id']);
            $table->dropColumn(['product_odoo_id', 'category_odoo_id', 'subcategory_odoo_id', 'uom_id']);
        });
    }
};
