<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Safely drop foreign key only if it exists
        Schema::table('master_distributors', function ($table) {
            // Get all foreign keys on the table
            $foreignKeys = $this->getForeignKeys('master_distributors');

            // Check if a foreign key exists on sales_executive_id column
            if (in_array('master_distributors_sales_executive_id_foreign', $foreignKeys)
                || collect($foreignKeys)->search(fn($key) => str_ends_with($key, '_sales_executive_id_foreign')) !== false) {
                $table->dropForeign(['sales_executive_id']);
            }
        });

        // Step 2: Change column to JSON (MySQL)
        DB::statement('ALTER TABLE master_distributors MODIFY sales_executive_id JSON NULL');

        // Step 3: Convert existing single values to JSON array [id]
        DB::statement("
            UPDATE master_distributors 
            SET sales_executive_id = JSON_ARRAY(sales_executive_id) 
            WHERE sales_executive_id IS NOT NULL 
              AND sales_executive_id != '' 
              AND JSON_VALID(CONCAT('[', sales_executive_id, ']')) = 1
        ");
    }

    public function down(): void
    {
        // Step 1: Extract first value from JSON array back to single integer
        DB::statement("
            UPDATE master_distributors 
            SET sales_executive_id = JSON_UNQUOTE(JSON_EXTRACT(sales_executive_id, '$[0]')) 
            WHERE sales_executive_id IS NOT NULL
        ");

        // Step 2: Change column back to unsignedBigInteger
        DB::statement('ALTER TABLE master_distributors MODIFY sales_executive_id BIGINT UNSIGNED NULL');

        // Step 3: Re-add foreign key
        Schema::table('master_distributors', function ($table) {
            $table->foreign('sales_executive_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Helper: Get list of foreign key constraint names on a table (MySQL only)
     */
    private function getForeignKeys(string $table): array
    {
        return collect(DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = ? 
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table]))->pluck('CONSTRAINT_NAME')->toArray();
    }
};