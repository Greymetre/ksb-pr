<?php

namespace App\Exports;

use App\Models\SecondaryCustomer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RetailerProductivityExport implements 
    FromCollection, 
    WithHeadings, 
    ShouldAutoSize, 
    WithStyles, 
    WithEvents
{
    protected $filters;
    protected $months = [];

    public function __construct($filters)
    {
        $this->filters = $filters;

        // Generate months (Jan to Dec)
        for ($i = 1; $i <= 12; $i++) {
            $this->months[] = Carbon::create()->month($i)->format('M');
        }
    }

    /**
     * Main collection method - FIXED
     */
    public function collection()
    {
        // ✅ Correct eager loading
$query = SecondaryCustomer::with([
'employee',    
'orders' => function ($q) {
        if (!empty($this->filters['year'])) {
        $q->whereYear('order_date', $this->filters['year']);
    }
    
    if (!empty($this->filters['start_date'])) {
        $q->whereDate('order_date', '>=', $this->filters['start_date']);
    }
    if (!empty($this->filters['end_date'])) {
        $q->whereDate('order_date', '<=', $this->filters['end_date']);
    }
}])
->leftJoin('cities', 'cities.id', '=', 'secondary_customers.city_id')
->leftJoin('districts', 'districts.id', '=', 'secondary_customers.district_id')
->leftJoin('states', 'states.id', '=', 'secondary_customers.state_id')
->leftJoin('master_distributors', 'master_distributors.id', '=', 'secondary_customers.distributor_name')
->select(
    'master_distributors.id as distributor_id', 
    'secondary_customers.*',
    'cities.city_name as city_name',
    'districts.district_name as district_name',
    'states.state_name as state_name',
    'master_distributors.trade_name as distributor_name'
);


if (!empty($this->filters['allowed_user_ids'])) {
    $allowedUserIds = $this->filters['allowed_user_ids'];

    $query->whereHas('distributor', function ($dist) use ($allowedUserIds) {

        $dist->where(function ($q) use ($allowedUserIds) {

            // ✅ supervisor_id (direct column in master_distributors)
            $q->whereIn('supervisor_id', $allowedUserIds)

            // ✅ sales_executive_id (JSON array in master_distributors)
            ->orWhere(function ($sub) use ($allowedUserIds) {
                foreach ($allowedUserIds as $id) {
                    $sub->orWhereJsonContains('sales_executive_id', $id);
                }
            });

        });

    });
}


        // Apply filters safely
        if (!empty($this->filters['employee_id'])) {
            $query->where('employee_id', $this->filters['employee_id']);
        }

        // if (!empty($this->filters['retailer_id'])) {
        //     $query->where('buyer_id', $this->filters['retailer_id']);
        // }

        if (!empty($this->filters['retailer_id'])) {
            $query->where('secondary_customers.id', $this->filters['retailer_id']);
        }

        // if (!empty($this->filters['distributor_id'])) {
        //     $query->where('seller_id', $this->filters['distributor_id']);
        // }

        if (!empty($this->filters['distributor_id'])) {
            $query->where('secondary_customers.distributor_name', $this->filters['distributor_id']);
        }

        if (!empty($this->filters['designation_id'])) {

            $designationIds = is_array($this->filters['designation_id'])
                ? $this->filters['designation_id']
                : [$this->filters['designation_id']];

            $query->whereHas('employee', function ($q) use ($designationIds) {
                $q->whereIn('designation_id', $designationIds);
            });
        }

        // if (!empty($this->filters['start_date'])) {
        //     $query->whereDate('order_date', '>=', $this->filters['start_date']);
        // }

        // if (!empty($this->filters['end_date'])) {
        //     $query->whereDate('order_date', '<=', $this->filters['end_date']);
        // }

        $customers = $query->orderBy('secondary_customers.created_at', 'desc')->get();
        $employees = $customers->pluck('employee')->filter();
        // dd($employees[10]);

        $allReportingIds = $employees->pluck('reportingid')
        ->filter()
        ->flatMap(fn($ids) => explode(',', $ids))
        ->map(fn($id) => (int) trim($id))
        ->unique();

        $reportingUsers = \App\Models\User::whereIn('id', $allReportingIds)
        ->pluck('name', 'id');
        
        $data = [];

        foreach ($customers as $customer) {
            $orders = $customer->orders ?? collect();
            $ytdQty = $orders->sum('total_qty');
            $ytdValue = $orders->sum('grand_total');
            $employee = $customer->employee;
            $reportingNames = '-';

            if ($employee && !empty($employee->reportingid)) {
                $ids = explode(',', $employee->reportingid);

                $reportingNames = collect($ids)
                    ->map(function ($id) use ($reportingUsers) {
                        $id = (int) trim($id);
                        return $reportingUsers->get($id);
                    })
                    ->filter()
                    ->implode(', ');
            }


            $row = [
                optional($customer->employee)->name ?? '-',
                $customer->shop_name ?? '-',
                $customer->city_name ?? '-',        // ✅ Direct
                $customer->district_name ?? '-',    // ✅ Direct
                $customer->state_name ?? '-', 
                $customer->distributor_name ?? '-',
                $reportingNames,

            ];

            

            // Add monthly Qty and Value (12 months × 2 columns = 24 columns)
            foreach (range(1, 12) as $month) {
                $monthlyOrders = $orders->filter(function ($order) use ($month) {
                    return Carbon::parse($order->order_date)->month === $month;
                });

                $qty = $monthlyOrders->sum('total_qty') ?? '-';
                $value = $monthlyOrders->sum('grand_total') ?? '-';

$row[] = ($qty == 0) ? '0' : $qty;
$row[] = ($value == 0) ? '0.00' : $value;

            };

    $row[] = $ytdQty == 0 ? '0' : $ytdQty;
    $row[] = $ytdValue == 0 ? '0.00' : $ytdValue;
    $row[] = $customer->id ?? '-';
    $row[] = optional($customer->employee)->employee_codes ?? '-';
    $row[] = $customer->distributor_id ?? '-'; // (fix select earlier)
            
            

            $data[] = $row;
            
        }

        return collect($data);
    }

    public function headings(): array
    {
        $headings = [
            'Employee Name',
            'Customer Name',
            'City',
            'District',
            'State',
            'Distributor Name',
            'Reporting Manager',
        
            
        ];

        foreach ($this->months as $month) {
            $headings[] = $month . '-Qty';
            $headings[] = $month . '-Value';
        };
         $headings[] = 'YTD Order Qty';
    $headings[] = 'YTD Order Value';
    $headings[] = 'Customer Id';
    $headings[] = 'Employee Code';
    $headings[] = 'Distributor Id';
        

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                // Header background color
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'D9E1F2']
                    ]
                ]);

                // Center align all cells
                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // Add borders to all cells
                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Freeze first row
                $sheet->freezePane('A2');

                // Header row height
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}