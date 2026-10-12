<?php

namespace App\Services\Odoo;

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pulls product sub-categories from Odoo into odoo_subcategories.
|--------------------------------------------------------------------------
| Odoo: product.category / get_fieldkonnect_subcategory_list, called once per
| synced category (odoo_categories.category_odoo_id), paginated.
| Each record: subcategory_odoo_id, subcategory_name, active, is_deleted, created_at, updated_at.
| Run by `php artisan odoo:sync-subcategories` (cron, twice a day, 10 minutes
| after the category sync) and by "Sync now" on Odoo Sync -> Sub Category Master Odoo.
|
| - Only sub-categories of the synced categories are kept; the rest are removed.
| - Upsert key is subcategory_odoo_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - Odoo returns nested sub-categories (PVC Pipes > Column Pipe > Heavy Pipe) as one
|   flat list with no parent sub-category, so all are stored under the category.
| - subcategory_id links to FieldKonnect `subcategories` by name, inside the
|   category's linked FieldKonnect category. `subcategories` is never changed.
*/
class SubcategorySync extends OdooPullSync
{
    public const ENTITY = 'subcategories';

    /** odoo category_odoo_id => FieldKonnect category_id, for the synced categories */
    private array $categories = [];
    private array $subcategoryCache = [];

    public function entity(): string
    {
        return self::ENTITY;
    }

    protected function source(): array
    {
        return ['product.category', 'get_fieldkonnect_subcategory_list'];
    }

    /**
     * One read per synced category (Scenario C: sub-categories under a category id).
     */
    protected function requests(): array
    {
        $this->categories = DB::table('odoo_categories')
            ->whereNotNull('category_odoo_id')
            ->whereNotNull('category_id')
            ->pluck('category_id', 'category_odoo_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_map(fn ($odooId) => ['category_odoo_id' => (int) $odooId], array_keys($this->categories));
    }

    /**
     * Rows Odoo no longer returns for a synced category are removed (only after Odoo was read fully).
     */
    protected function prepare(array $records): void
    {
        $ids = array_values(array_filter(array_map(fn ($r) => (int) ($r['subcategory_odoo_id'] ?? 0), $records)));
        $names = array_values(array_filter(array_map(fn ($r) => mb_strtolower(trim((string) ($r['subcategory_name'] ?? ''))), $records)));

        DB::table('odoo_subcategories')
            ->whereNotNull('subcategory_odoo_id')
            ->whereNotIn('subcategory_odoo_id', $ids ?: [0])
            ->delete();

        // Rows synced before subcategory_odoo_id existed: kept only if Odoo still has that name
        DB::table('odoo_subcategories')
            ->whereNull('subcategory_odoo_id')
            ->whereNotIn(DB::raw('LOWER(TRIM(subcategory_name))'), $names ?: [''])
            ->delete();
    }

    protected function upsert(array $record, string $correlationId): array
    {
        $odooId = (int) ($record['subcategory_odoo_id'] ?? 0);
        $name = trim((string) ($record['subcategory_name'] ?? ''));
        $categoryOdooId = (int) ($record['category_odoo_id'] ?? 0);

        if (!$odooId || $name === '') {
            return ['status' => 'failed', 'errors' => ['record' => ['subcategory_odoo_id and subcategory_name are required.']]];
        }

        $categoryId = $this->categories[$categoryOdooId] ?? null;

        // Category not synced/linked in FieldKonnect: do not keep this sub-category
        if (!$categoryId) {
            DB::table('odoo_subcategories')->where('subcategory_odoo_id', $odooId)->delete();
            return ['status' => 'skipped'];
        }

        $subcategoryId = $this->resolveSubcategoryId($name, $categoryId);
        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);

        $existing = DB::table('odoo_subcategories')->where('subcategory_odoo_id', $odooId)
            ->first(['id', 'odoo_updated_at', 'subcategory_id', 'category_odoo_id'])
            ?? DB::table('odoo_subcategories')->whereNull('subcategory_odoo_id')->where('subcategory_name', $name)
                ->first(['id', 'odoo_updated_at', 'subcategory_id', 'category_odoo_id']);

        // Unchanged in Odoo: nothing to do, unless the category or FieldKonnect link changed
        if ($existing && $this->isUnchanged($existing, $odooUpdatedAt)
            && (int) $existing->subcategory_id === (int) $subcategoryId
            && (int) $existing->category_odoo_id === $categoryOdooId) {
            return ['status' => 'skipped'];
        }

        $row = [
            'subcategory_odoo_id' => $odooId,
            'subcategory_name' => $name,
            'category_odoo_id' => $categoryOdooId,
            'active' => filter_var($record['active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_deleted' => filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'subcategory_id' => $subcategoryId,
            'odoo_created_at' => $this->toLocal($record['created_at'] ?? null),
            'odoo_updated_at' => $odooUpdatedAt,
            'last_correlation_id' => $correlationId,
            'raw_payload' => json_encode($record),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('odoo_subcategories')->where('id', $existing->id)->update($row);
            return ['status' => 'updated'];
        }

        DB::table('odoo_subcategories')->insert($row + ['created_at' => now()]);

        return ['status' => 'created'];
    }

    private function resolveSubcategoryId(string $name, int $categoryId): ?int
    {
        $key = mb_strtolower($name) . '|' . $categoryId;

        if (!array_key_exists($key, $this->subcategoryCache)) {
            $id = DB::table('subcategories')
                ->whereNull('deleted_at')
                ->where('subcategory_name', $name)
                ->where('category_id', $categoryId)
                ->value('id');
            $this->subcategoryCache[$key] = $id ? (int) $id : null;
        }

        return $this->subcategoryCache[$key];
    }
}
