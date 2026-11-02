<?php

namespace App\Services\Odoo;

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pulls product sub-categories from Odoo into odoo_subcategories.
|--------------------------------------------------------------------------
| Odoo: product.category / get_fieldkonnect_subcategory_list, called once per
| Odoo category (odoo_categories.category_odoo_id), paginated.
| Each record: subcategory_odoo_id, subcategory_name, active, is_deleted, created_at, updated_at.
| Run by `php artisan odoo:sync-subcategories` (cron, twice a day, 10 minutes
| after the category sync) and by "Sync now" on Sub Category List and
| Odoo Sync -> Sub Category Master Odoo.
|
| - Sub-categories of every Odoo category are stored (listed on Odoo Sync ->
|   Sub Category Master Odoo), but only those of the synced categories get a
|   FieldKonnect sub-category (subcategory_id); the rest keep it null.
| - Upsert key is subcategory_odoo_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - Odoo returns nested sub-categories (PVC Pipes > Column Pipe > Heavy Pipe) as one
|   flat list with no parent sub-category, so all are stored under the category.
| - Odoo is the master for FieldKonnect `subcategories`: the matching row (by
|   odoo_subcategory_id, else by name inside the category) gets
|   odoo_subcategory_id, Odoo's name, category and active flag; a missing one is
|   created. Sub Category List shows only these.
| - A synced category for which Odoo sends no sub-category (today: Column Coupler)
|   gets one placeholder sub-category with the category's name, keyed by the
|   category's Odoo id (subcategory_odoo_id = category_odoo_id), so its products
|   can sync. Once Odoo sends real sub-categories for it, the placeholder drops
|   off the list and the real ones are synced.
*/
class SubcategorySync extends OdooPullSync
{
    public const ENTITY = 'subcategories';

    /** odoo category_odoo_id => FieldKonnect category_id, for the synced categories */
    private array $categories = [];

    public function entity(): string
    {
        return self::ENTITY;
    }

    protected function source(): array
    {
        return ['product.category', 'get_fieldkonnect_subcategory_list'];
    }

    /**
     * One read per Odoo category (Scenario C: sub-categories under a category id).
     */
    protected function requests(): array
    {
        $this->categories = DB::table('odoo_categories')
            ->whereNotNull('category_odoo_id')
            ->whereNotNull('category_id')
            ->pluck('category_id', 'category_odoo_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return DB::table('odoo_categories')
            ->whereNotNull('category_odoo_id')
            ->orderBy('category_odoo_id')
            ->pluck('category_odoo_id')
            ->map(fn ($odooId) => ['category_odoo_id' => (int) $odooId])
            ->all();
    }

    /**
     * Placeholder sub-category (same name and Odoo id as the category) for every
     * synced category that came back from Odoo without sub-categories.
     */
    protected function transform(array $records): array
    {
        // A sub-category returned under more than one category is kept once, preferring a synced category
        $unique = [];
        foreach ($records as $record) {
            $key = (int) ($record['subcategory_odoo_id'] ?? 0);
            if (!$key || !isset($unique[$key]) || (!isset($this->categories[(int) ($unique[$key]['category_odoo_id'] ?? 0)])
                && isset($this->categories[(int) ($record['category_odoo_id'] ?? 0)]))) {
                $unique[$key ?: 'invalid_' . count($unique)] = $record;
            }
        }
        $records = array_values($unique);

        $withSubcategories = array_flip(array_map(fn ($r) => (int) ($r['category_odoo_id'] ?? 0), $records));

        $empty = DB::table('odoo_categories')
            ->whereIn('category_odoo_id', array_keys($this->categories) ?: [0])
            ->get(['category_odoo_id', 'category_name', 'active', 'is_deleted', 'odoo_created_at', 'odoo_updated_at']);

        foreach ($empty as $category) {
            if (isset($withSubcategories[(int) $category->category_odoo_id])) {
                continue;
            }
            $records[] = [
                'subcategory_odoo_id' => (int) $category->category_odoo_id,
                'subcategory_name' => $category->category_name,
                'category_odoo_id' => (int) $category->category_odoo_id,
                'active' => (bool) $category->active,
                'is_deleted' => (bool) $category->is_deleted,
                'created_at' => $category->odoo_created_at,
                'updated_at' => $category->odoo_updated_at,
                'is_placeholder' => true,
            ];
        }

        return $records;
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

        // FieldKonnect sub-categories no longer in Odoo, or whose category is no longer synced, drop off Sub Category List
        $syncedIds = array_values(array_filter(array_map(
            fn ($r) => isset($this->categories[(int) ($r['category_odoo_id'] ?? 0)]) ? (int) ($r['subcategory_odoo_id'] ?? 0) : 0,
            $records
        )));

        DB::table('subcategories')
            ->whereNotNull('odoo_subcategory_id')
            ->whereNotIn('odoo_subcategory_id', $syncedIds ?: [0])
            ->update(['odoo_subcategory_id' => null, 'updated_at' => now()]);
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

        // Only sub-categories of synced categories reach Sub Category List; the rest are stored for Odoo Sync only
        $subcategoryId = $categoryId ? $this->syncFieldKonnectSubcategory($odooId, $name, $categoryId, $record) : null;
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

    /**
     * FieldKonnect sub-category for this Odoo sub-category: matched by odoo_subcategory_id,
     * else by name inside the category (not deleted), else created. Name, category,
     * active flag and odoo_subcategory_id follow Odoo.
     */
    private function syncFieldKonnectSubcategory(int $odooId, string $name, int $categoryId, array $record): int
    {
        $subcategoryId = DB::table('subcategories')->where('odoo_subcategory_id', $odooId)->value('id')
            ?? DB::table('subcategories')->whereNull('deleted_at')->whereNull('odoo_subcategory_id')
                ->where('category_id', $categoryId)->where('subcategory_name', $name)->value('id');

        $row = [
            'subcategory_name' => $name,
            'category_id' => $categoryId,
            'odoo_subcategory_id' => $odooId,
            'active' => filter_var($record['active'] ?? true, FILTER_VALIDATE_BOOLEAN)
                && !filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'Y' : 'N',
            'updated_at' => now(),
        ];

        if ($subcategoryId) {
            DB::table('subcategories')->where('id', $subcategoryId)->update($row);
            return (int) $subcategoryId;
        }

        return (int) DB::table('subcategories')->insertGetId($row + ['created_at' => now()]);
    }
}
