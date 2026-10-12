<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Odoo categories now come from product.category / get_fieldkonnect_category_list
|--------------------------------------------------------------------------
| That method returns category_odoo_id + category_name only (no external_id,
| no category_code), so category_odoo_id becomes the upsert key and the old
| columns become optional. Rows synced earlier keep their external_id, which
| odoo_subcategories / odoo_products still join on.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odoo_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('odoo_categories', 'category_odoo_id')) {
                $table->unsignedBigInteger('category_odoo_id')->nullable()->unique()->after('id');
            }
            $table->string('external_id', 100)->nullable()->change();
            $table->string('category_code', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('odoo_categories', function (Blueprint $table) {
            $table->dropUnique(['category_odoo_id']);
            $table->dropColumn('category_odoo_id');
        });
    }
};
