<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| FieldKonnect products come from Odoo
|--------------------------------------------------------------------------
| odoo_product_id is filled by the Odoo product sync (ProductSync) with Odoo's
| product_odoo_id. Product List shows only products that have it.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'odoo_product_id')) {
                $table->unsignedBigInteger('odoo_product_id')->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['odoo_product_id']);
            $table->dropColumn('odoo_product_id');
        });
    }
};
