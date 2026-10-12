<?php

namespace App\Exports;

use App\Models\Subcategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class SubcategoryExport implements FromCollection,WithHeadings,ShouldAutoSize,WithMapping
{
    public function collection()
    {
        return Subcategory::with('categories')->select('id','subcategory_name', 'odoo_subcategory_id','category_id', 'service_category_id')->whereNotNull('odoo_subcategory_id')->latest()->get();   
    }

    public function headings(): array
    {
        return ['id','subcategory_name', 'Odoo Sub-category ID','category_id','category_name', 'Odoo Category ID', 'service_category_id'];
    }

    public function map($data): array
    {
        return [
            $data['id'],
            $data['subcategory_name'],
            $data['odoo_subcategory_id'],
            $data['category_id'],
            $data['categories']['category_name'] ?? '',
            $data['categories']['odoo_category_id'] ?? '',
            $data['service_category_id'],
        ];
    }

}