<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Odoo sub-categories now come from product.category / get_fieldkonnect_subcategory_list
|--------------------------------------------------------------------------
| Called once per synced category (category_odoo_id); each record has
| subcategory_odoo_id + subcategory_name only (no external_id / code), so
| subcategory_odoo_id becomes the upsert key and the old columns become optional.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odoo_subcategories', function (Blueprint $table) {
            if (!Schema::hasColumn('odoo_subcategories', 'subcategory_odoo_id')) {
                $table->unsignedBigInteger('subcategory_odoo_id')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('odoo_subcategories', 'category_odoo_id')) {
                // Parent odoo_categories.category_odoo_id
                $table->unsignedBigInteger('category_odoo_id')->nullable()->index()->after('subcategory_name');
            }
            $table->string('external_id', 100)->nullable()->change();
            $table->string('subcategory_code', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('odoo_subcategories', function (Blueprint $table) {
            $table->dropUnique(['subcategory_odoo_id']);
            $table->dropIndex(['category_odoo_id']);
            $table->dropColumn(['subcategory_odoo_id', 'category_odoo_id']);
        });
    }
};
