<?php

namespace App\Services\Odoo;

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
| category and sub-category syncs) and by "Sync now" on Product Management -> Product List.
|
| - Only products whose category AND sub-category are both synced
|   (odoo_categories / odoo_subcategories) are kept; the rest are skipped and
|   removed if stored earlier. A product with no sub-category goes under its
|   category's placeholder sub-category (subcategory_odoo_id = category_odoo_id,
|   see SubcategorySync), when there is one.
| - Upsert key is product_odoo_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - Odoo is the master for FieldKonnect `products`: the matching product (by
|   odoo_product_id, else by name) gets odoo_product_id, Odoo's name, category,
|   sub-category, UOM (unit_measures, created when missing) and active flag; a
|   missing one is created. Its primary product_details row gets MRP and GST.
|   Product List shows only these.
*/
class ProductSync extends OdooPullSync
{
    public const ENTITY = 'products';

    /** Synced categories' category_odoo_id */
    private array $categoryOdooIds = [];
    /** Synced sub-categories: subcategory_odoo_id => category_odoo_id */
    private array $subcategories = [];
    /** FieldKonnect ids: category_odoo_id => categories.id, subcategory_odoo_id => subcategories.id */
    private array $fkCategories = [];
    private array $fkSubcategories = [];
    private array $unitCache = [];

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

        $this->fkCategories = DB::table('categories')->whereNotNull('odoo_category_id')->pluck('id', 'odoo_category_id')->all();
        $this->fkSubcategories = DB::table('subcategories')->whereNotNull('odoo_subcategory_id')->pluck('id', 'odoo_subcategory_id')->all();

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

        // FieldKonnect products no longer in Odoo (or no longer synced) drop off Product List
        DB::table('products')
            ->whereNotNull('odoo_product_id')
            ->whereNotIn('odoo_product_id', $ids ?: [0])
            ->update(['odoo_product_id' => null, 'updated_at' => now()]);
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

        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);

        $existing = DB::table('odoo_products')->where('product_odoo_id', $odooId)
            ->first(['id', 'odoo_updated_at', 'product_id'])
            ?? DB::table('odoo_products')->whereNull('product_odoo_id')->where('product_name', $name)
                ->first(['id', 'odoo_updated_at', 'product_id']);
        $linkedId = DB::table('products')->where('odoo_product_id', $odooId)->value('id');

        // Unchanged in Odoo and already in FieldKonnect: nothing to do
        if ($existing && $linkedId && $this->isUnchanged($existing, $odooUpdatedAt) && (int) $existing->product_id === (int) $linkedId) {
            return ['status' => 'skipped'];
        }

        $productId = $this->syncFieldKonnectProduct($odooId, $name, $record, $linkedId ? (int) $linkedId : null);

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
     * FieldKonnect product for this Odoo product: by odoo_product_id, else by name (not
     * deleted, not yet linked), else created. Name, category, sub-category, UOM and active
     * flag follow Odoo; the primary product_details row gets MRP (also price / selling
     * price) and GST.
     */
    private function syncFieldKonnectProduct(int $odooId, string $name, array $record, ?int $productId): int
    {
        $productId ??= DB::table('products')->whereNull('deleted_at')->whereNull('odoo_product_id')
            ->where(fn ($q) => $q->where('product_name', $name)->orWhere('display_name', $name))
            ->value('id');

        $row = [
            'odoo_product_id' => $odooId,
            'product_name' => $name,
            'display_name' => $name,
            'category_id' => $this->fkCategories[(int) $record['category_odoo_id']] ?? null,
            'subcategory_id' => $this->fkSubcategories[(int) $record['subcategory_odoo_id']] ?? null,
            'unit_id' => $this->resolveUnitId($record['uom_name'] ?? null),
            'active' => filter_var($record['active'] ?? true, FILTER_VALIDATE_BOOLEAN)
                && !filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'Y' : 'N',
            'updated_at' => now(),
        ];

        if ($productId) {
            DB::table('products')->where('id', $productId)->update($row);
        } else {
            $productId = (int) DB::table('products')->insertGetId($row + ['created_at' => now()]);
        }

        $mrp = isset($record['mrp']) ? (float) $record['mrp'] : null;
        $detail = [
            'mrp' => $mrp,
            'price' => $mrp,
            'selling_price' => $mrp,
            'gst' => isset($record['gst_percent']) ? (float) $record['gst_percent'] : null,
            'updated_at' => now(),
        ];
        $detailId = DB::table('product_details')->where('product_id', $productId)->whereNull('deleted_at')
            ->orderByDesc('isprimary')->orderBy('id')->value('id');

        if ($detailId) {
            DB::table('product_details')->where('id', $detailId)->update($detail);
        } else {
            DB::table('product_details')->insert($detail + [
                'product_id' => $productId,
                'active' => 'Y',
                'isprimary' => 1,
                'created_at' => now(),
            ]);
        }

        return (int) $productId;
    }

    /**
     * Odoo uom_name against unit_measures.unit_name / unit_code (case-insensitive); created when missing.
     */
    private function resolveUnitId(?string $uomName): ?int
    {
        $uomName = trim((string) $uomName);
        if ($uomName === '') {
            return null;
        }

        $key = mb_strtolower($uomName);
        if (!array_key_exists($key, $this->unitCache)) {
            $id = DB::table('unit_measures')->whereNull('deleted_at')
                ->where(fn ($q) => $q->whereRaw('LOWER(unit_name) = ?', [$key])->orWhereRaw('LOWER(unit_code) = ?', [$key]))
                ->value('id')
                ?? DB::table('unit_measures')->insertGetId([
                    'active' => 'Y',
                    'unit_name' => $uomName,
                    'unit_code' => $uomName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            $this->unitCache[$key] = (int) $id;
        }

        return $this->unitCache[$key];
    }
}
