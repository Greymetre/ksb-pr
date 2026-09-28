<?php

namespace App\Services\Odoo;

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pulls product categories from Odoo into odoo_categories.
|--------------------------------------------------------------------------
| Odoo: product.category / get_fieldkonnect_categories (paginated).
| Run by `php artisan odoo:sync-categories` (cron, twice a day) and by the
| "Sync now" button on Odoo Sync -> Category Master Odoo.
|
| - Upsert key is external_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
*/
class CategorySync extends OdooPullSync
{
    public const ENTITY = 'categories';

    private array $categoryCache = [];

    public function entity(): string
    {
        return self::ENTITY;
    }

    protected function source(): array
    {
        return ['product.category', 'get_fieldkonnect_categories'];
    }

    protected function upsert(array $record, string $correlationId): array
    {
        if (empty($record['external_id']) || empty($record['category_name'])) {
            return ['status' => 'failed', 'errors' => ['record' => ['external_id and category_name are required.']]];
        }

        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);
        $categoryId = $this->resolveCategoryId($record['category_name']);

        $existing = DB::table('odoo_categories')
            ->where('external_id', $record['external_id'])
            ->first(['id', 'odoo_updated_at', 'category_id']);

        // Unchanged in Odoo: nothing to do, unless the FieldKonnect link changed
        if ($this->isUnchanged($existing, $odooUpdatedAt) && (int) $existing->category_id === (int) $categoryId) {
            return ['status' => 'skipped'];
        }

        $row = [
            'category_code' => (string) ($record['category_code'] ?? ''),
            'category_name' => $record['category_name'],
            'description' => $record['description'] ?? null,
            'ranking' => (int) ($record['ranking'] ?? 1),
            'image_url' => $record['image_url'] ?? null,
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

        DB::table('odoo_categories')->insert($row + [
            'external_id' => $record['external_id'],
            'created_at' => now(),
        ]);

        return ['status' => 'created'];
    }

    /**
     * category_name is matched against FieldKonnect categories.category_name (not deleted).
     */
    private function resolveCategoryId(string $name): ?int
    {
        $key = mb_strtolower(trim($name));

        if (!array_key_exists($key, $this->categoryCache)) {
            $this->categoryCache[$key] = DB::table('categories')
                ->whereNull('deleted_at')
                ->where('category_name', trim($name))
                ->value('id');
        }

        return $this->categoryCache[$key];
    }
}
