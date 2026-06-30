<?php

namespace App\Exports;

use App\Models\SalesTargetUsers;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class SalesTargetUsersTemplate implements FromCollection,WithHeadings,ShouldAutoSize
{
    public function collection()
    {
        return SalesTargetUsers::select('user_id', 'month', 'year', 'target')->limit(0)->get();   
    }

    public function headings(): array
    {
        return [['User Id', 'Branch Id', 'User Name', 'Type','01/26','01/26_qty','02/26','02/26_qty','03/26','03/26_qty','04/26','04/26_qty','05/26','05/26_qty','06/26','06/26_qty','07/26','07/26_qty','08/26','08/26_qty','09/26','09/26_qty','10/26','10/26_qty','11/26','11/26_qty','12/26','12/26_qty'],['','','','Add primary or secondary value only.please remove this row before upload.']];
    }

}