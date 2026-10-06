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

class ProductExport implements FromCollection,WithHeadings,ShouldAutoSize,WithMapping
{
    public function __construct($request)
    {
        $this->category_id = $request->input('category_id');
        $this->active = $request->input('active');
    }

    public function collection()
    {
        $data = Product::with('productpriceinfo')->select('id','active','product_name','product_code','new_group','sub_group','expiry_interval','expiry_interval_preiod', 'display_name', 'description', 'subcategory_id', 'category_id', 'brand_id', 'product_image', 'unit_id', 'specification', 'part_no','suc_del', 'product_no', 'model_no','phase','sap_code' , 'branch_id', 'hsn_sac', 'hsn_sac_no');
        if($this->category_id && !empty($this->category_id)){
            $data->where('category_id', $this->category_id);
        }
        if($this->active && !empty($this->active)){
            $data->where('active', $this->active);
        }
        $data = $data->latest()->get();

        return $data;
    }

    public function headings(): array
    {
        return ['Fieldkonnect Id', 'Product Name', 'Duke Code', 'Description', 'Subcategory ID', 'Subcategory', 'Category ID', 'Category', 'Product Image', 'Unit ID', 'Unit of Measure', 'Sales Price', 'GST', 'Discount', 'Model No', 'Status', 'Odoo Code', 'Cost', 'HSN/SAC Code', 'Delete'];
    }

    public function map($data): array
    {
        return [
            $data['id'],
            $data['product_name'],
            $data['product_code'],
            $data['description'],
            $data['subcategory_id'],
            $data['subcategories']?$data['subcategories']['subcategory_name'] : '-',
            $data['category_id'],
            $data['categories']?$data['categories']['category_name'] : '-',
            $data['product_image'],
            $data['unit_id'],
            $data['unitmeasures']?$data['unitmeasures']['unit_name']:'',
            isset($data['productpriceinfo']['mrp']) ? $data['productpriceinfo']['mrp'] :'',
            isset($data['productpriceinfo']['gst']) ? $data['productpriceinfo']['gst'] : '',
            isset($data['productpriceinfo']['discount']) ? $data['productpriceinfo']['discount'] : '',
            isset($data['model_no']) ? $data['model_no'] :'',
            $data['active'],
            $data['sap_code'],
            isset($data['productpriceinfo']['rmc']) ? $data['productpriceinfo']['rmc'] :'',
            isset($data['hsn_sac']) ? $data['hsn_sac'] :'',
            'No', // set to Yes and re-import to delete this product
        ];
    }

}
