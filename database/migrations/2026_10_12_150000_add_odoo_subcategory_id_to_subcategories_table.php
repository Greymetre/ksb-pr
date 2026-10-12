<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| FieldKonnect sub-categories come from Odoo
|--------------------------------------------------------------------------
| odoo_subcategory_id is filled by the Odoo sub-category sync (SubcategorySync)
| with Odoo's subcategory_odoo_id. Sub Category List shows only these.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            if (!Schema::hasColumn('subcategories', 'odoo_subcategory_id')) {
                $table->unsignedBigInteger('odoo_subcategory_id')->nullable()->unique()->after('category_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropUnique(['odoo_subcategory_id']);
            $table->dropColumn('odoo_subcategory_id');
        });
    }
};
