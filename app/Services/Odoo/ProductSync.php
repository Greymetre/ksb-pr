<?php

namespace App\Services\Odoo;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Pulls products from Odoo into odoo_products.
|--------------------------------------------------------------------------
| Odoo: product.product / get_fieldkonnect_products (paginated, ~2900 rows).
| Run by `php artisan odoo:sync-products` (cron, twice a day, after the
| category and sub-category syncs) and by "Sync now" on Odoo Sync -> Product Master Odoo.
|
| - Upsert key is external_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - product_id links by product_code to products.product_code, then
|   products.sap_code (same rule as party prices). `products` is never changed.
*/
class ProductSync extends OdooPullSync
{
    public const ENTITY = 'products';

    private array $productCache = [];

    public function entity(): string
    {
        return self::ENTITY;
    }

    protected function source(): array
    {
        return ['product.product', 'get_fieldkonnect_products'];
    }

    protected function pageSize(): int
    {
        return 500;
    }

    protected function upsert(array $record, string $correlationId): array
    {
        if (empty($record['external_id']) || empty($record['product_code']) || empty($record['product_name'])) {
            return ['status' => 'failed', 'errors' => ['record' => ['external_id, product_code and product_name are required.']]];
        }

        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);
        $productId = $this->resolveProductId($record['product_code']);

        $existing = DB::table('odoo_products')
            ->where('external_id', $record['external_id'])
            ->first(['id', 'odoo_updated_at', 'product_id']);

        // Unchanged in Odoo: nothing to do, unless the FieldKonnect link changed
        if ($this->isUnchanged($existing, $odooUpdatedAt) && (int) $existing->product_id === (int) $productId) {
            return ['status' => 'skipped'];
        }

        $row = [
            'product_code' => $record['product_code'],
            'product_name' => $record['product_name'],
            'display_name' => $record['display_name'] ?? null,
            'description' => $record['description'] ?? null,
            'category_external_id' => $record['category_external_id'] ?? null,
            'category_code' => $record['category_code'] ?? null,
            'subcategory_external_id' => $record['subcategory_external_id'] ?? null,
            'subcategory_code' => $record['subcategory_code'] ?? null,
            'brand_code' => $record['brand_code'] ?? null,
            'brand_name' => $record['brand_name'] ?? null,
            'uom_code' => $record['uom_code'] ?? null,
            'uom_name' => $record['uom_name'] ?? null,
            'hsn_code' => $record['hsn_code'] ?? null,
            'ean_barcode' => $record['ean_barcode'] ?? null,
            'mrp' => $record['mrp'] ?? null,
            'standard_price' => $record['standard_price'] ?? null,
            'gst_percent' => $record['gst_percent'] ?? null,
            'currency_code' => isset($record['currency_code']) ? strtoupper($record['currency_code']) : null,
            'model_no' => $record['model_no'] ?? null,
            'part_no' => $record['part_no'] ?? null,
            'product_group' => $record['product_group'] ?? null,
            'product_subgroup' => $record['product_subgroup'] ?? null,
            'specification' => $record['specification'] ?? null,
            'image_url' => $record['image_url'] ?? null,
            'orderable' => filter_var($record['orderable'] ?? true, FILTER_VALIDATE_BOOLEAN),
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

        DB::table('odoo_products')->insert($row + [
            'external_id' => $record['external_id'],
            'created_at' => now(),
        ]);

        return ['status' => 'created'];
    }

    /**
     * product_code is matched against products.product_code, then products.sap_code.
     */
    private function resolveProductId(string $productCode): ?int
    {
        if (!array_key_exists($productCode, $this->productCache)) {
            $this->productCache[$productCode] = Product::where('product_code', $productCode)->value('id')
                ?? Product::where('sap_code', $productCode)->value('id');
        }

        return $this->productCache[$productCode];
    }
}
