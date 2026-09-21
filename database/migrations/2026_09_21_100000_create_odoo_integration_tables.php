<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Odoo -> FieldKonnect push integration (Party-wise pricing)
|--------------------------------------------------------------------------
| odoo_api_clients       : API keys given to Odoo (mode = test | live)
| odoo_sync_logs         : one row per API request (audit / debugging)
| odoo_stg_party_prices  : TEST key data lands here (never used by the app)
| party_product_prices   : LIVE key data lands here (real party-wise prices)
|
| Both price tables have exactly the same structure so switching Odoo from
| the test key to the live key needs no change on the Odoo side.
| See odoo-integration-docs/README.md
*/
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('odoo_api_clients')) {
            Schema::create('odoo_api_clients', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('key_prefix', 20);
                $table->string('api_key_hash', 64)->unique();
                $table->enum('mode', ['test', 'live'])->default('test');
                $table->boolean('active')->default(true);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('odoo_sync_logs')) {
            Schema::create('odoo_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->string('correlation_id', 100)->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->enum('mode', ['test', 'live']);
                $table->string('entity', 50)->index();
                $table->string('method', 10);
                $table->string('ip', 45)->nullable();
                $table->unsignedInteger('received_count')->default(0);
                $table->unsignedInteger('created_count')->default(0);
                $table->unsignedInteger('updated_count')->default(0);
                $table->unsignedInteger('skipped_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->unsignedSmallInteger('status_code');
                $table->json('errors')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }

        foreach (['odoo_stg_party_prices', 'party_product_prices'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                continue;
            }

            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('company_code', 50)->default('');
                $table->string('external_id', 100);

                $table->string('price_list_code', 50)->index();
                $table->string('price_list_name', 150)->nullable();

                $table->string('party_external_id', 100)->index();
                $table->string('party_code', 100)->index();
                // Resolved FieldKonnect party (null when party_code not found yet)
                $table->enum('party_type', ['customer', 'master_distributor'])->nullable();
                $table->unsignedBigInteger('party_id')->nullable();

                $table->string('product_external_id', 100)->index();
                $table->string('product_code', 100)->index();
                // Resolved FieldKonnect product (null when product_code not found yet)
                $table->unsignedBigInteger('product_id')->nullable()->index();

                $table->char('currency_code', 3);
                $table->decimal('base_price', 19, 4);
                $table->decimal('party_price', 19, 4);
                $table->decimal('discount_percent', 8, 4)->nullable();
                $table->boolean('tax_inclusive')->default(false);
                $table->decimal('minimum_quantity', 19, 4)->default(1);
                $table->decimal('maximum_quantity', 19, 4)->nullable();
                $table->string('uom_code', 50);
                $table->dateTime('valid_from');
                $table->dateTime('valid_to')->nullable();
                $table->integer('priority')->default(0);
                $table->boolean('active')->default(true);
                $table->boolean('is_deleted')->default(false);

                $table->dateTime('odoo_updated_at');
                $table->string('last_correlation_id', 100)->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamps();

                $table->unique(['company_code', 'external_id']);
                $table->index(['party_type', 'party_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('party_product_prices');
        Schema::dropIfExists('odoo_stg_party_prices');
        Schema::dropIfExists('odoo_sync_logs');
        Schema::dropIfExists('odoo_api_clients');
    }
};
