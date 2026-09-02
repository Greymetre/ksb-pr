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
use Carbon\Carbon;

class SalesTargetUsersTemplate implements FromCollection,WithHeadings,ShouldAutoSize
{
    public function collection()
    {
        return SalesTargetUsers::select('user_id', 'month', 'year', 'target')->limit(0)->get();   
    }

    public function headings(): array
    {
        $startYear = Carbon::now()->month >= 4 ? Carbon::now()->year : Carbon::now()->year - 1;
        $headings = ['User Id', 'Branch Id', 'User Name', 'Type'];

        for ($offset = 0; $offset < 12; $offset++) {
            $date = Carbon::create($startYear, 4, 1)->addMonths($offset);
            $headings[] = $date->format('m/y');
        }

        return [
            $headings,
            ['', '', '', 'Add primary or secondary value only. Please remove this row before upload.'],
        ];
    }

}
