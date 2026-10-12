<?php

namespace App\Services\Odoo;

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pulls product categories from Odoo into odoo_categories.
|--------------------------------------------------------------------------
| Odoo: product.category / get_fieldkonnect_category_list (paginated).
| Each record: category_odoo_id, category_name, active, is_deleted, created_at, updated_at.
| Run by `php artisan odoo:sync-categories` (cron, twice a day) and by the
| "Sync now" button on Odoo Sync -> Category Master Odoo.
|
| - Only the categories in SYNCED_CATEGORIES are kept; the rest are skipped
|   and removed if stored earlier.
| - Upsert key is category_odoo_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - Odoo is the master for FieldKonnect `categories`: the matching category
|   (by odoo_category_id, else by name) gets odoo_category_id, Odoo's name and
|   active flag; a missing one is created. Category List shows only these.
*/
class CategorySync extends OdooPullSync
{
    public const ENTITY = 'categories';

    // Odoo categories FieldKonnect syncs for now (matched by name, case-insensitive)
    public const SYNCED_CATEGORIES = ['Column Coupler', 'HDPE Pipe', 'PVC Pipes'];

    public function entity(): string
    {
        return self::ENTITY;
    }

    protected function source(): array
    {
        return ['product.category', 'get_fieldkonnect_category_list'];
    }

    /**
     * Rows of categories no longer synced are removed (only after Odoo was read fully).
     */
    protected function prepare(array $records): void
    {
        $synced = array_map('mb_strtolower', self::SYNCED_CATEGORIES);

        DB::table('odoo_categories')
            ->whereNotIn(DB::raw('LOWER(TRIM(category_name))'), $synced)
            ->delete();

        // Categories no longer synced drop off Category List
        DB::table('categories')
            ->whereNotNull('odoo_category_id')
            ->whereNotIn(DB::raw('LOWER(TRIM(category_name))'), $synced)
            ->update(['odoo_category_id' => null, 'updated_at' => now()]);
    }

    protected function upsert(array $record, string $correlationId): array
    {
        $odooId = (int) ($record['category_odoo_id'] ?? 0);
        $name = trim((string) ($record['category_name'] ?? ''));

        if (!$odooId || $name === '') {
            return ['status' => 'failed', 'errors' => ['record' => ['category_odoo_id and category_name are required.']]];
        }

        if (!$this->isSynced($name)) {
            return ['status' => 'skipped'];
        }

        $categoryId = $this->syncFieldKonnectCategory($odooId, $name, $record);

        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);

        // Rows synced before category_odoo_id existed are matched by their FieldKonnect category
        $existing = DB::table('odoo_categories')->where('category_odoo_id', $odooId)
            ->first(['id', 'odoo_updated_at', 'category_id'])
            ?? DB::table('odoo_categories')->whereNull('category_odoo_id')->where('category_id', $categoryId)
                ->first(['id', 'odoo_updated_at', 'category_id']);

        // Unchanged in Odoo: nothing to do, unless the FieldKonnect link changed
        if ($existing && $this->isUnchanged($existing, $odooUpdatedAt) && (int) $existing->category_id === $categoryId) {
            DB::table('odoo_categories')->where('id', $existing->id)->update(['category_odoo_id' => $odooId]);
            return ['status' => 'skipped'];
        }

        $row = [
            'category_odoo_id' => $odooId,
            'category_name' => $name,
            'active' => filter_var($record['active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_deleted' => filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'category_id' => $categoryId,
            'odoo_created_at' => $this->toLocal($record['created_at'] ?? null),
            'odoo_updated_at' => $odooUpdatedAt,
            'last_correlation_id' => $correlationId,
            'raw_payload' => json_encode($record),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('odoo_categories')->where('id', $existing->id)->update($row);
            return ['status' => 'updated'];
        }

        DB::table('odoo_categories')->insert($row + ['created_at' => now()]);

        return ['status' => 'created'];
    }

    private function isSynced(string $name): bool
    {
        return in_array(mb_strtolower($name), array_map('mb_strtolower', self::SYNCED_CATEGORIES), true);
    }

    /**
     * FieldKonnect category for this Odoo category: matched by odoo_category_id, else by
     * name (not deleted), else created. Its name, active flag and odoo_category_id follow Odoo.
     */
    private function syncFieldKonnectCategory(int $odooId, string $name, array $record): int
    {
        $categoryId = DB::table('categories')->where('odoo_category_id', $odooId)->value('id')
            ?? DB::table('categories')->whereNull('deleted_at')->where('category_name', $name)->value('id');

        $row = [
            'category_name' => $name,
            'odoo_category_id' => $odooId,
            'active' => filter_var($record['active'] ?? true, FILTER_VALIDATE_BOOLEAN)
                && !filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'Y' : 'N',
            'updated_at' => now(),
        ];

        if ($categoryId) {
            DB::table('categories')->where('id', $categoryId)->update($row);
            return (int) $categoryId;
        }

        return (int) DB::table('categories')->insertGetId($row + ['created_at' => now()]);
    }
}
