<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| FieldKonnect categories come from Odoo
|--------------------------------------------------------------------------
| odoo_category_id is filled by the Odoo category sync (CategorySync) with
| Odoo's category_odoo_id. Category List shows only categories that have it.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'odoo_category_id')) {
                $table->unsignedBigInteger('odoo_category_id')->nullable()->unique()->after('sap_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['odoo_category_id']);
            $table->dropColumn('odoo_category_id');
        });
    }
};
