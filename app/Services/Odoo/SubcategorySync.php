<?php

namespace App\Services\Odoo;

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pulls product sub-categories from Odoo into odoo_subcategories.
|--------------------------------------------------------------------------
| Odoo: product.category / get_fieldkonnect_subcategories (paginated).
| Run by `php artisan odoo:sync-subcategories` (cron, twice a day, 10 minutes
| after the category sync) and by "Sync now" on Odoo Sync -> Sub Category Master Odoo.
|
| - Upsert key is external_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - Odoo nests sub-categories (PVC Pipes > Column Pipe > Heavy Pipe), so the
|   parent chain is walked up: only sub-categories under a category linked to
|   FieldKonnect are kept; the rest are skipped (and removed if stored earlier).
| - subcategory_id links to FieldKonnect `subcategories` by name: inside the
|   parent's linked FieldKonnect category. `subcategories` is never changed.
*/
class SubcategorySync extends OdooPullSync
{
    public const ENTITY = 'subcategories';

    private array $parentCache = [];
    /** sub-category external_id => parent external_id, for the whole run */
    private array $parentOf = [];
    private array $subcategoryCache = [];

    public function entity(): string
    {
        return self::ENTITY;
    }

    protected function source(): array
    {
        return ['product.category', 'get_fieldkonnect_subcategories'];
    }

    protected function prepare(array $records): void
    {
        $this->parentOf = [];
        foreach ($records as $record) {
            if (!empty($record['external_id'])) {
                $this->parentOf[$record['external_id']] = $record['category_external_id'] ?? null;
            }
        }
    }

    protected function upsert(array $record, string $correlationId): array
    {
        if (empty($record['external_id']) || empty($record['subcategory_name'])) {
            return ['status' => 'failed', 'errors' => ['record' => ['external_id and subcategory_name are required.']]];
        }

        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);
        $parentCategoryId = $this->resolveParentCategoryId($record['category_external_id'] ?? null);

        // Parent category not synced/linked in FieldKonnect: do not keep this sub-category
        if (!$parentCategoryId) {
            DB::table('odoo_subcategories')->where('external_id', $record['external_id'])->delete();
            return ['status' => 'skipped'];
        }

        $subcategoryId = $this->resolveSubcategoryId($record['subcategory_name'], $parentCategoryId);

        $existing = DB::table('odoo_subcategories')
            ->where('external_id', $record['external_id'])
            ->first(['id', 'odoo_updated_at', 'subcategory_id']);

        // Unchanged in Odoo: nothing to do, unless the FieldKonnect link changed
        if ($this->isUnchanged($existing, $odooUpdatedAt) && (int) $existing->subcategory_id === (int) $subcategoryId) {
            return ['status' => 'skipped'];
        }

        $row = [
            'subcategory_code' => (string) ($record['subcategory_code'] ?? ''),
            'subcategory_name' => $record['subcategory_name'],
            'category_external_id' => $record['category_external_id'] ?? null,
            'category_code' => $record['category_code'] ?? null,
            'description' => $record['description'] ?? null,
            'ranking' => (int) ($record['ranking'] ?? 1),
            'image_url' => $record['image_url'] ?? null,
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

        DB::table('odoo_subcategories')->insert($row + [
            'external_id' => $record['external_id'],
            'created_at' => now(),
        ]);

        return ['status' => 'created'];
    }

    /**
     * FieldKonnect category of the nearest linked ancestor (odoo_categories), walking
     * up through parent sub-categories. Null when no ancestor is a linked category.
     */
    private function resolveParentCategoryId(?string $categoryExternalId): ?int
    {
        $seen = [];

        while ($categoryExternalId && !isset($seen[$categoryExternalId])) {
            $seen[$categoryExternalId] = true;

            if (!array_key_exists($categoryExternalId, $this->parentCache)) {
                $this->parentCache[$categoryExternalId] = DB::table('odoo_categories')
                    ->where('external_id', $categoryExternalId)
                    ->value('category_id');
            }

            if ($this->parentCache[$categoryExternalId]) {
                return (int) $this->parentCache[$categoryExternalId];
            }

            $categoryExternalId = $this->parentOf[$categoryExternalId] ?? null;
        }

        return null;
    }

    private function resolveSubcategoryId(string $name, int $parentCategoryId): ?int
    {
        $key = mb_strtolower(trim($name)) . '|' . $parentCategoryId;

        if (!array_key_exists($key, $this->subcategoryCache)) {
            $this->subcategoryCache[$key] = DB::table('subcategories')
                ->whereNull('deleted_at')
                ->where('subcategory_name', trim($name))
                ->where('category_id', $parentCategoryId)
                ->value('id');
        }

        return $this->subcategoryCache[$key];
    }
}
