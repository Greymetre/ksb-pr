<?php

namespace App\Services\Odoo;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pulls products from Odoo into odoo_products.
|--------------------------------------------------------------------------
| Odoo: product.product / get_fieldkonnect_product_list, called once per
| synced category (odoo_categories.category_odoo_id), paginated.
| Each record: product_odoo_id, product_name, category_odoo_id, subcategory_odoo_id,
| subcategory_name, uom_id, uom_name, mrp, standard_price, gst_percent, active,
| is_deleted, created_at, updated_at.
| Run by `php artisan odoo:sync-products` (cron, twice a day, after the
| category and sub-category syncs) and by "Sync now" on Odoo Sync -> Product Master Odoo.
|
| - Only products whose category AND sub-category are both synced
|   (odoo_categories / odoo_subcategories) are kept; the rest are skipped and
|   removed if stored earlier. A product with no sub-category goes under its
|   category's placeholder sub-category (subcategory_odoo_id = category_odoo_id,
|   see SubcategorySync), when there is one.
| - Upsert key is product_odoo_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - Odoo sends no product code, so product_id links by name to
|   products.product_name, then products.display_name. `products` is never changed.
*/
class ProductSync extends OdooPullSync
{
    public const ENTITY = 'products';

    /** Synced categories' category_odoo_id */
    private array $categoryOdooIds = [];
    /** Synced sub-categories: subcategory_odoo_id => category_odoo_id */
    private array $subcategories = [];
    private array $productCache = [];

    public function entity(): string
    {
        return self::ENTITY;
    }

    protected function source(): array
    {
        return ['product.product', 'get_fieldkonnect_product_list'];
    }

    protected function pageSize(): int
    {
        return 500;
    }

    /**
     * One read per synced category (Scenario C: products under a category id).
     */
    protected function requests(): array
    {
        $this->categoryOdooIds = DB::table('odoo_categories')
            ->whereNotNull('category_odoo_id')
            ->whereNotNull('category_id')
            ->pluck('category_odoo_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->subcategories = DB::table('odoo_subcategories')
            ->whereNotNull('subcategory_odoo_id')
            ->pluck('category_odoo_id', 'subcategory_odoo_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_map(fn ($odooId) => ['category_odoo_id' => $odooId], $this->categoryOdooIds);
    }

    /**
     * A product with no sub-category is put under its category's placeholder
     * sub-category (same Odoo id as the category), when SubcategorySync made one.
     */
    protected function transform(array $records): array
    {
        return array_map(function ($record) {
            $categoryOdooId = (int) ($record['category_odoo_id'] ?? 0);
            if (empty($record['subcategory_odoo_id']) && ($this->subcategories[$categoryOdooId] ?? null) === $categoryOdooId) {
                $record['subcategory_odoo_id'] = $categoryOdooId;
            }
            return $record;
        }, $records);
    }

    /**
     * Rows Odoo no longer returns for a synced category are removed (only after Odoo was read fully).
     */
    protected function prepare(array $records): void
    {
        $records = array_filter($records, fn ($r) => $this->isSynced($r));
        $ids = array_values(array_filter(array_map(fn ($r) => (int) ($r['product_odoo_id'] ?? 0), $records)));
        $names = array_values(array_filter(array_map(fn ($r) => mb_strtolower(trim((string) ($r['product_name'] ?? ''))), $records)));

        DB::table('odoo_products')
            ->whereNotNull('product_odoo_id')
            ->whereNotIn('product_odoo_id', $ids ?: [0])
            ->delete();

        // Rows synced before product_odoo_id existed: kept only if Odoo still has that name
        DB::table('odoo_products')
            ->whereNull('product_odoo_id')
            ->whereNotIn(DB::raw('LOWER(TRIM(product_name))'), $names ?: [''])
            ->delete();
    }

    protected function upsert(array $record, string $correlationId): array
    {
        $odooId = (int) ($record['product_odoo_id'] ?? 0);
        $name = trim((string) ($record['product_name'] ?? ''));

        if (!$odooId || $name === '') {
            return ['status' => 'failed', 'errors' => ['record' => ['product_odoo_id and product_name are required.']]];
        }

        // Category or sub-category not synced in FieldKonnect: do not keep this product
        if (!$this->isSynced($record)) {
            DB::table('odoo_products')->where('product_odoo_id', $odooId)->delete();
            return ['status' => 'skipped'];
        }

        $productId = $this->resolveProductId($name);
        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);

        $existing = DB::table('odoo_products')->where('product_odoo_id', $odooId)
            ->first(['id', 'odoo_updated_at', 'product_id'])
            ?? DB::table('odoo_products')->whereNull('product_odoo_id')->where('product_name', $name)
                ->first(['id', 'odoo_updated_at', 'product_id']);

        // Unchanged in Odoo: nothing to do, unless the FieldKonnect link changed
        if ($existing && $this->isUnchanged($existing, $odooUpdatedAt) && (int) $existing->product_id === (int) $productId) {
            return ['status' => 'skipped'];
        }

        $row = [
            'product_odoo_id' => $odooId,
            'product_name' => $name,
            'category_odoo_id' => (int) $record['category_odoo_id'],
            'subcategory_odoo_id' => (int) $record['subcategory_odoo_id'],
            'uom_id' => !empty($record['uom_id']) ? (int) $record['uom_id'] : null,
            'uom_name' => $record['uom_name'] ?? null,
            'mrp' => $record['mrp'] ?? null,
            'standard_price' => $record['standard_price'] ?? null,
            'gst_percent' => $record['gst_percent'] ?? null,
            'active' => filter_var($record['active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_deleted' => filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'product_id' => $productId,
            'odoo_created_at' => $this->toLocal($record['created_at'] ?? null),
            'odoo_updated_at' => $odooUpdatedAt,
            'last_correlation_id' => $correlationId,
            'raw_payload' => json_encode($record),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('odoo_products')->where('id', $existing->id)->update($row);
            return ['status' => 'updated'];
        }

        DB::table('odoo_products')->insert($row + ['created_at' => now()]);

        return ['status' => 'created'];
    }

    /**
     * Category is synced and the sub-category is a synced one of that category.
     */
    private function isSynced(array $record): bool
    {
        $categoryOdooId = (int) ($record['category_odoo_id'] ?? 0);
        $subcategoryOdooId = (int) ($record['subcategory_odoo_id'] ?? 0);

        return in_array($categoryOdooId, $this->categoryOdooIds, true)
            && ($this->subcategories[$subcategoryOdooId] ?? null) === $categoryOdooId;
    }

    /**
     * product_name is matched against products.product_name, then products.display_name.
     */
    private function resolveProductId(string $name): ?int
    {
        $key = mb_strtolower($name);

        if (!array_key_exists($key, $this->productCache)) {
            $id = Product::where('product_name', $name)->value('id')
                ?? Product::where('display_name', $name)->value('id');
            $this->productCache[$key] = $id ? (int) $id : null;
        }

        return $this->productCache[$key];
    }
}
