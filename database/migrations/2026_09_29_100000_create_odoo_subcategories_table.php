<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Odoo product sub-categories (Sub Category Master Odoo)
|--------------------------------------------------------------------------
| Pulled from Odoo (product.category / get_fieldkonnect_subcategories) by
| `php artisan odoo:sync-subcategories` twice a day, or "Sync now" on the page.
| category_external_id points to odoo_categories.external_id.
| subcategory_id links to the FieldKonnect `subcategories` row with the same
| name; the live `subcategories` table is only read, never changed.
| See odoo-integration-docs/README.md
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('odoo_subcategories')) {
            return;
        }

        Schema::create('odoo_subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('subcategory_code', 50)->index();
            $table->string('subcategory_name', 250)->index();
            // Parent Odoo category
            $table->string('category_external_id', 100)->nullable()->index();
            $table->string('category_code', 50)->nullable();
            $table->text('description')->nullable();
            $table->integer('ranking')->default(1);
            $table->string('image_url', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_deleted')->default(false);
            // Resolved FieldKonnect sub-category (null when not matched)
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();

            $table->dateTime('odoo_created_at')->nullable();
            $table->dateTime('odoo_updated_at')->nullable();
            $table->string('last_correlation_id', 100)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odoo_subcategories');
    }
};
