<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Odoo product categories (Category Master Odoo)
|--------------------------------------------------------------------------
| Pulled from Odoo (product.category / get_fieldkonnect_categories) by
| `php artisan odoo:sync-categories` twice a day, or "Sync now" on the page.
| category_id links to the FieldKonnect `categories` row with the same name;
| on every sync the linked `categories.sap_code` is set to Odoo's category_code.
| See odoo-integration-docs/README.md
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('odoo_categories')) {
            return;
        }

        Schema::create('odoo_categories', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('category_code', 50)->index();
            $table->string('category_name', 250)->index();
            $table->text('description')->nullable();
            $table->integer('ranking')->default(1);
            $table->string('image_url', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_deleted')->default(false);
            // Resolved FieldKonnect category (null when no category with the same name)
            $table->unsignedBigInteger('category_id')->nullable()->index();

            $table->dateTime('odoo_created_at')->nullable();
            $table->dateTime('odoo_updated_at')->nullable();
            $table->string('last_correlation_id', 100)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odoo_categories');
    }
};
