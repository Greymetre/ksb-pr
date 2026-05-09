<?php

namespace App\Exports;

use App\Models\User;
use App\Models\BeatSchedule;
use App\Models\Order;
use App\Models\CheckIn;
use App\Models\SecondaryCustomer;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;           // ← Added
use Maatwebsite\Excel\Events\AfterSheet;             // ← Added
use PhpOffice\PhpSpreadsheet\Style\Alignment;       // ← Added for alignment
use PhpOffice\PhpSpreadsheet\Style\Fill;            // ← Added for background color
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Holiday;

class CounterVisitReportExport implements 
    FromCollection, 
    ShouldAutoSize, 
    WithHeadings,
    WithStrictNullComparison,
    WithEvents                                          // ← Added
{
protected $start_date, $end_date, $employee_id, $division_id, $branch_id, $designation_id;

public function __construct($request)

{
    // dd($request);
    $this->start_date    = $request->start_date;
    $this->end_date      = $request->end_date;
    $this->employee_id   = $request->employee_id;
    $this->division_id   = $request->division_id;
    $this->branch_id     = $request->branch_id;
    $this->designation_id= $request->designation_id;
}

//     private function getWorkingDays($start_date, $end_date)
//     {
//         $start = Carbon::parse($start_date);
//         $end   = Carbon::parse($end_date);

// //         $holidays = Holiday::whereBetween('holiday_date', [$start, $end])
// //             ->pluck('holiday_date')
// // ->flatMap(function ($date) {
// //     return collect(explode(',', $date))
// //         ->map(fn($d) => Carbon::parse(trim($d))->format('Y-m-d'));
// // })            ->toArray();
// $presentDays = CheckIn::where('user_id', $user->id)
//     ->whereBetween('checkin_date', [$this->start_date, $this->end_date])
//     ->select(DB::raw('DATE(checkin_date) as date'))
//     ->distinct()
//     ->count();

//         $workingDays = 0;

//         while ($start <= $end) {
//             $date = $start->format('Y-m-d');

//             if ($start->isSunday() || in_array($date, $holidays)) {
//                 $start->addDay();
//                 continue;
//             }

//             $workingDays++;
//             $start->addDay();
//         }

//         return $workingDays;
//     }

    public function collection()
    {
        $userids = getUsersReportingToAuth();
        
       $users = User::with(['getdivision', 'getbranch', 'getdesignation', 'reportinginfo' ])
    ->whereIn('id', $userids)

    ->when($this->employee_id, function ($q) {
        $q->where('id', $this->employee_id);
    })

    ->when($this->division_id, function ($q) {
        $q->where('division_id', $this->division_id);
    })

    ->when($this->branch_id, function ($q) {
        $q->where('branch_id', $this->branch_id);
    })

    ->when($this->designation_id, function ($q) {
        $q->where('designation_id', $this->designation_id);
    })

    ->get();
        // $users = User::whereIn('id', $userids)
        //     ->select('id', 'name')
        //     ->get();

        $userIds = $users->pluck('id');
        
        $schedules = BeatSchedule::whereBetween('beat_date', [$this->start_date, $this->end_date])
            ->whereIn('user_id', $userIds)
            ->get();

        $orders = Order::whereBetween('order_date', [$this->start_date, $this->end_date])
            ->whereIn('executive_id', $userIds)
            ->get();

        $allReportingIds = $users->pluck('reportingid')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->map(fn($id) => trim($id))
            ->unique();

            $reportingUsers = User::whereIn('id', $allReportingIds)
        ->pluck('name', 'id');

        // dd($allReportingIds);
        // dd($reportingUsers);

        return $users->map(function ($user) use ($schedules, $orders, $reportingUsers ) {


            $presentDays = CheckIn::where('user_id', $user->id)
        ->whereBetween('checkin_date', [$this->start_date, $this->end_date])
        ->select(DB::raw('DATE(checkin_date) as date'))
        ->distinct()
        ->count();
            $visited = CheckIn::where('user_id', $user->id)
                ->whereBetween('checkin_date', [$this->start_date, $this->end_date])
                ->select(DB::raw('COALESCE(entity_id, customer_id) as visit_id'))
                ->distinct()
                ->count();

            $userOrders = $orders->where('executive_id', $user->id);
            $productivityCount = $userOrders->count();

            $workingDays = Attendance::where('user_id', $user->id)
                ->whereBetween('punchin_date', [
                    Carbon::parse($this->start_date)->startOfDay(),
                    Carbon::parse($this->end_date)->endOfDay()
                ])
                ->select(DB::raw('DATE(punchin_date) as date'))
                ->distinct()
                ->count();
                
            $dailyTarget      = 15;
            $totalVisitTarget = $dailyTarget * $workingDays;

            $adherence = $totalVisitTarget > 0 
                ? round(($visited * 100) / $totalVisitTarget, 1) 
                : 0;

            $productivity = $visited > 0 
                ? round(($productivityCount * 100) / $visited, 1) 
                : 0;

            $orderIds = $userOrders->pluck('id');

            $uniqueSkuCount = DB::table('order_details')
                ->whereIn('order_id', $orderIds)
                ->distinct('product_id')
                ->count('product_id');

            $totalOrderQty   = $userOrders->sum('total_qty') ?? 0;
            $totalOrderValue = $userOrders->sum('grand_total') ?? 0;

            $newCounterAdded = SecondaryCustomer::where('created_by', $user->id)
            ->whereBetween('created_at', [
                Carbon::parse($this->start_date)->startOfDay(),
                Carbon::parse($this->end_date)->endOfDay()
            ])
            ->count();

            // $reportingNames = '';

            // if (!empty($user->reporting_id)) {
            //     $reportingIds = explode(',', $user->reporting_id);

            //     $reportingNames = User::whereIn('id', $reportingIds)
            //         ->pluck('name')
            //         ->implode(', ');
            // }

            $reportingNames = '';

                if (!empty($user->reportingid)) {
                    $ids = explode(',', $user->reportingid);
                    // dd($user->reportingids);
                    $reportingNames = collect($ids)
                        ->map(function ($id) use ($reportingUsers) {
                            $id = (int) trim($id);   // 🔥 IMPORTANT FIX
                            return $reportingUsers->get($id);
                        })
                        ->filter()
                        ->implode(', ');

                      
                }

            // dd($reportingNames);

            $totalCumulativeCounter = SecondaryCustomer::where('employee_id', $user->id)
                ->count();

            return [
                (int) ($user->id ?? 0),
                (string) ($user->name ?? ''),
                $dailyTarget,
                $workingDays,
                $totalVisitTarget,
                $visited,
                $adherence . ' %',
                $productivityCount,
                $productivity . ' %',
                $newCounterAdded,
                $totalOrderQty,
                $totalOrderValue,
                $uniqueSkuCount,
                $totalCumulativeCounter,
                (string) ($user->getdivision->division_name ?? ''),
                (string) ($user->getbranch->branch_name ?? ''),
                (string) ($user->getdesignation->designation_name ?? ''),
                // (string) ($user->reportinginfo->name ?? ''),
                (string) $reportingNames,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employees Code',
            'Employees Name',
            'Daily Visit Target',
            'Total Working Days',
            'Total Visit Target',
            'Total Customers Visited',
            'Adherance %',
            'Total Productive Visits',
            'Productivity %',
            'New Counter Added',
            'Total Order Qty',
            'Total Order Value',
            'Unique SKU Ordered',
            'Total Cumulative Counter',
            'Division',
            'Branch',
            'Designation',
            'Reporting Manager', 
        ];
    }

    /**
     * Register Events for Styling
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Style the Heading Row (Row 1)
                $cellRange = 'A1:R1';   // Adjust N if you add/remove columns
                $sheet->getStyle($cellRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],   // White text
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E88E5'], // Nice blue color (change as you like)
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // 2. Center align from Column C (3rd column) to the last column
                $lastColumn = 'Q';   // Change to your last column letter if needed
                $dataRange = 'C2:' . $lastColumn . ($event->sheet->getHighestRow());

                $sheet->getStyle($dataRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Optional: Make header row height a bit taller
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}