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
| - The category must also exist in FieldKonnect (matched by name).
*/
class CategorySync extends OdooPullSync
{
    public const ENTITY = 'categories';

    // Odoo categories FieldKonnect syncs for now (matched by name, case-insensitive)
    public const SYNCED_CATEGORIES = ['Column Coupler', 'HDPE Pipe', 'PVC Pipes'];

    private array $categoryCache = [];

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
        DB::table('odoo_categories')
            ->whereNotIn(DB::raw('LOWER(TRIM(category_name))'), array_map('mb_strtolower', self::SYNCED_CATEGORIES))
            ->delete();
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

        $categoryId = $this->resolveCategoryId($name);

        // Not a FieldKonnect category: do not keep it (its sub-categories and products are skipped too)
        if (!$categoryId) {
            DB::table('odoo_categories')->where('category_odoo_id', $odooId)->delete();
            return ['status' => 'skipped', 'errors' => ['category_name' => ["{$name} does not exist in FieldKonnect categories."]]];
        }

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
     * category_name is matched against FieldKonnect categories.category_name (not deleted).
     */
    private function resolveCategoryId(string $name): ?int
    {
        $key = mb_strtolower($name);

        if (!array_key_exists($key, $this->categoryCache)) {
            $id = DB::table('categories')
                ->whereNull('deleted_at')
                ->where('category_name', $name)
                ->value('id');
            $this->categoryCache[$key] = $id ? (int) $id : null;
        }

        return $this->categoryCache[$key];
    }
}
