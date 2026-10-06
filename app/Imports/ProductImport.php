<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductDetails;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Facades\DB;
use Log;
use Illuminate\Support\Facades\Auth;

class ProductImport implements ToCollection,WithValidation,WithHeadingRow, WithBatchInserts , WithChunkReading
{
    use Importable, SkipsFailures;
    
    public function model(array $row)
    {
        return new Product([
            //
        ]);
    }
    /*
    | Column => [heading aliases, default for a new product, transform].
    | Headings are slugged: the current export uses "Fieldkonnect Id", "Duke Code",
    | "Odoo Code", "Sales Price", "Cost", "HSN/SAC Code" (hsnsac_code); older files and
    | the template use product_id, product_code, sap_code, mrp, rmc, hsn_sac.
    | A column missing from the file is left unchanged on an existing product.
    */
    private function productColumns(): array
    {
        return [
            'active'                 => [['status'], 'Y', 'ucfirst'],
            'product_name'           => [['product_name'], '', 'ucfirst'],
            'product_code'           => [['duke_code', 'product_code'], '', null],
            'new_group'              => [['new_group'], '', null],
            'sub_group'              => [['sub_group'], '', 'ucfirst'],
            'expiry_interval'        => [['expiry_interval'], '', 'ucfirst'],
            'expiry_interval_preiod' => [['expiry_interval_preiod'], 0, 'ucfirst'],
            'display_name'           => [['display_name'], '', 'ucfirst'],
            'description'            => [['description'], '', 'ucfirst'],
            'subcategory_id'         => [['subcategory_id'], null, null],
            'category_id'            => [['category_id'], null, null],
            'brand_id'               => [['brand_id'], null, null],
            'product_image'          => [['product_image'], '', null],
            'unit_id'                => [['unit_id'], null, null],
            'suc_del'                => [['suc_del'], null, null],
            'sap_code'               => [['odoo_code', 'sap_code'], null, null],
            'specification'          => [['hp'], null, null],
            'part_no'                => [['kw'], null, null],
            'product_no'             => [['product_stage'], null, null],
            'model_no'               => [['model_no'], null, null],
            'phase'                  => [['phase'], null, null],
            'hsn_sac'                => [['hsnsac_code', 'hsn_sac'], 0, null],
            'hsn_sac_no'             => [['hsn_sac_no'], null, null],
            'branch_id'              => [['branch_id'], '', null],
        ];
    }

    private function detailColumns(): array
    {
        return [
            'active'             => [['status'], 'Y', 'ucfirst'],
            'detail_title'       => [['detail_title', 'product_name'], '', 'ucfirst'],
            'detail_description' => [['detail_description'], '', 'ucfirst'],
            'detail_image'       => [['detail_image'], '', null],
            'mrp'                => [['sales_price', 'mrp'], 0.00, null],
            'price'              => [['price'], null, null],
            'discount'           => [['discount'], 0.00, null],
            'max_discount'       => [['max_discount'], 0.00, null],
            'rmc'                => [['cost', 'rmc'], 0.00, null],
            'selling_price'      => [['selling_price'], 0.00, null],
            'gst'                => [['gst'], 0.00, null],
            'isprimary'          => [['isprimary'], 1, null],
            'hsn_code'           => [['hsn_code'], null, null],
            'ean_code'           => [['ean_code'], null, null],
            'top_sku'            => [['top_sku'], null, null],
            'budget_for_month'   => [['budget_for_month'], null, null],
        ];
    }

    private function attributes(Collection $row, array $columns, bool $creating): array
    {
        $attributes = [];
        foreach ($columns as $field => [$aliases, $default, $transform]) {
            $alias = collect($aliases)->first(fn ($key) => $row->has($key));
            if ($alias === null) {
                if ($creating) {
                    $attributes[$field] = $default;
                }
                continue;
            }
            $value = $row[$alias];
            $attributes[$field] = ($value === null || $value === '') ? $default : ($transform ? $transform($value) : $value);
        }
        return $attributes;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $productId = $row['fieldkonnect_id'] ?? $row['product_id'] ?? null;

            // delete = Yes: remove the product (same as the Delete button), needs product_delete
            if (in_array(strtolower(trim((string) ($row['delete'] ?? ''))), ['yes', 'y'], true)) {
                if (!empty($productId) && Auth::user()->can('product_delete')) {
                    ProductDetails::where('product_id', $productId)->delete();
                    Product::where('id', $productId)->delete();
                }
                continue;
            }

            $product = !empty($productId) ? Product::find($productId) : null;

            if ($product) {
                $product->update($this->attributes($row, $this->productColumns(), false) + ['updated_at' => getcurentDateTime()]);
            } else {
                $product = Product::create($this->attributes($row, $this->productColumns(), true) + [
                    'created_by' => Auth::user()->id,
                    'created_at' => getcurentDateTime(),
                    'updated_at' => getcurentDateTime(),
                ]);
            }

            $detail = ProductDetails::where('product_id', $product->id)->first();
            $detailAttributes = $this->attributes($row, $this->detailColumns(), !$detail);
            if (array_key_exists('price', $detailAttributes) && $detailAttributes['price'] === null) {
                $detailAttributes['price'] = $detailAttributes['mrp'] ?? $detail->mrp ?? 0.00;
            }

            ProductDetails::updateOrCreate(['product_id' => $product->id], $detailAttributes + [
                'product_id' => $product->id,
                'updated_at' => getcurentDateTime(),
            ] + ($detail ? [] : ['created_at' => getcurentDateTime()]));
        }
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|regex:/[a-zA-Z0-9\s]+/',
            'hsn_sac' => 'nullable|in:HSN,SAC',
            'hsnsac_code' => 'nullable|in:HSN,SAC',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'product_name.required' => 'Product name is required.',
            'product_name.string' => 'Product name must be a string.',
            'product_name.regex' => 'Product name format is invalid.',
            'hsn_sac.in' => 'it should be only from HSN or SAC.',
            'hsnsac_code.in' => 'HSN/SAC Code should be only HSN or SAC.',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function onFailure(Failure ...$failures)
    {
        Log::stack(['import-failure-logs'])->info(json_encode($failures));
    }
}
