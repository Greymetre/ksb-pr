<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductExport implements FromCollection,WithHeadings,ShouldAutoSize,WithMapping
{
    private $costs = [];

    public function __construct($request)
    {
        $this->category_id = $request->input('category_id');
        $this->active = $request->input('active');
    }

    public function collection()
    {
        $data = Product::with(['categories', 'subcategories', 'unitmeasures', 'productpriceinfo'])
            ->select('id', 'odoo_product_id', 'active', 'product_name', 'category_id', 'subcategory_id', 'unit_id')
            ->whereNotNull('odoo_product_id');
        if($this->category_id && !empty($this->category_id)){
            $data->where('category_id', $this->category_id);
        }
        if($this->active && !empty($this->active)){
            $data->where('active', $this->active);
        }
        $data = $data->latest()->get();

        // Odoo cost price (standard_price) per Odoo product id
        $this->costs = DB::table('odoo_products')
            ->whereIn('product_odoo_id', $data->pluck('odoo_product_id'))
            ->pluck('standard_price', 'product_odoo_id');

        return $data;
    }

    /**
     * Only the fields the Odoo product sync fills.
     */
    public function headings(): array
    {
        return ['Fieldkonnect Id', 'Odoo Product Id', 'Product Name', 'Category', 'Sub Category', 'Unit of Measure', 'MRP', 'Cost Price', 'GST %', 'Status'];
    }

    public function map($data): array
    {
        return [
            $data->id,
            $data->odoo_product_id,
            $data->product_name,
            $data->categories->category_name ?? '',
            $data->subcategories->subcategory_name ?? '',
            $data->unitmeasures->unit_name ?? '',
            $data->productpriceinfo->mrp ?? '',
            $this->costs[$data->odoo_product_id] ?? '',
            $data->productpriceinfo->gst ?? '',
            $data->active === 'Y' ? 'Active' : 'Inactive',
        ];
    }

}
