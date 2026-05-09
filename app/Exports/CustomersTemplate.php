<?php

namespace App\Exports;

use App\Models\Customers;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class CustomersTemplate implements FromCollection,WithHeadings,ShouldAutoSize
{
    public function collection()
    {
        return Customers::limit(0)->get();   
    }

    public function headings(): array
    {
return [
            'Distributor Code',
            'Legal Name',
            'Trade Name',
            'Contact Person',
            'Mobile',
            'Billing City',
            'Billing State',
            'Status',
            
        ];    
    }

}