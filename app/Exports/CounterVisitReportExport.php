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

    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */
    $users = User::with([
            'getdivision',
            'getbranch',
            'getdesignation',
            'reportinginfo'
        ])
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

    /*
    |--------------------------------------------------------------------------
    | SORT USERS BRANCH WISE
    |--------------------------------------------------------------------------
    */
    $users = $users->sortBy(function ($u) {
        return ($u->getdivision->division_name ?? '') . '_' . ($u->getbranch->branch_name ?? '');
    });

    $userIds = $users->pluck('id');

    /*
    |--------------------------------------------------------------------------
    | ORDERS
    |--------------------------------------------------------------------------
    */
    $orders = Order::whereBetween('order_date', [$this->start_date, $this->end_date])
        ->whereIn('executive_id', $userIds)
        ->get();

    /*
    |--------------------------------------------------------------------------
    | REPORTING USERS
    |--------------------------------------------------------------------------
    */
    $allReportingIds = $users->pluck('reportingid')
        ->filter()
        ->flatMap(fn($ids) => explode(',', $ids))
        ->map(fn($id) => trim($id))
        ->unique();

    $reportingUsers = User::whereIn('id', $allReportingIds)
        ->pluck('name', 'id');

    /*
    |--------------------------------------------------------------------------
    | ROWS COLLECTION
    |--------------------------------------------------------------------------
    */
    $rows = collect();

    $currentBranch = null;
    $currentDivision = null;

    /*
    |--------------------------------------------------------------------------
    | BRANCH TOTALS
    |--------------------------------------------------------------------------
    */
    $branchTotals = [
        'workingDays' => 0,
        'visitTarget' => 0,
        'visited' => 0,
        'productive' => 0,
        'newCounter' => 0,
        'orderQty' => 0,
        'orderValue' => 0,
        'sku' => 0,
        'cumulative' => 0,
    ];

    $divisionTotals = [
    'workingDays' => 0,
    'visitTarget' => 0,
    'visited' => 0,
    'productive' => 0,
    'newCounter' => 0,
    'orderQty' => 0,
    'orderValue' => 0,
    'sku' => 0,
    'cumulative' => 0,
];

    /*
    |--------------------------------------------------------------------------
    | GRAND TOTALS
    |--------------------------------------------------------------------------
    */
    $grandTotals = [
        'workingDays' => 0,
        'visitTarget' => 0,
        'visited' => 0,
        'productive' => 0,
        'newCounter' => 0,
        'orderQty' => 0,
        'orderValue' => 0,
        'sku' => 0,
        'cumulative' => 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | LOOP USERS
    |--------------------------------------------------------------------------
    */
    foreach ($users as $user) {
        $divisionName = $user->getdivision->division_name ?? 'No Zone';
        $branchName = $user->getbranch->branch_name ?? 'No Branch';

        /*
        |--------------------------------------------------------------------------
        | ADD SUBTOTAL ROW ON BRANCH CHANGE
        |--------------------------------------------------------------------------
        */
        // if ($currentBranch !== null && $currentBranch != $branchName) {

        //     $rows->push([
                
        //     '',
        //     'SUBTOTAL - ' . $currentBranch,
        //     '',

        //     $branchTotals['workingDays'],

        //     $branchTotals['visitTarget'],

        //     $branchTotals['visited'],

        //     $branchTotals['visitTarget'] > 0
        //         ? round(($branchTotals['visited'] * 100) / $branchTotals['visitTarget'], 1) . ' %'
        //         : '0 %',

        //     $branchTotals['productive'],

        //     $branchTotals['visited'] > 0
        //     ? round(($branchTotals['productive'] * 100) / $branchTotals['visited'], 1) . ' %'
        //     : '0 %',
        //         $branchTotals['newCounter'],
        //         $branchTotals['orderQty'],
        //         $branchTotals['orderValue'],
        //         $branchTotals['sku'],
        //         $branchTotals['cumulative'],
        //         '',
        //         '',
        //         '',
        //         '',
        //     ]);

        //     // RESET BRANCH TOTALS
        //     $branchTotals = [
        //         'workingDays' => 0,
        //         'visitTarget' => 0,
        //         'visited' => 0,
        //         'productive' => 0,
        //         'newCounter' => 0,
        //         'orderQty' => 0,
        //         'orderValue' => 0,
        //         'sku' => 0,
        //         'cumulative' => 0,
        //     ];
        // }


        /*
        |--------------------------------------------------------------------------
        | DIVISION CHANGE
        |--------------------------------------------------------------------------
        */
        if ($currentDivision !== null && $currentDivision != $divisionName) {

            /*
            |--------------------------------------------------------------------------
            | LAST BRANCH SUBTOTAL
            |--------------------------------------------------------------------------
            */
            $rows->push([
                '',
                'SUBTOTAL - ' . $currentBranch,
                '',
                $branchTotals['workingDays'],
                $branchTotals['visitTarget'],
                $branchTotals['visited'],

                $branchTotals['visitTarget'] > 0
                    ? round(($branchTotals['visited'] * 100) / $branchTotals['visitTarget'], 1) . ' %'
                    : '0 %',

                $branchTotals['productive'],

                $branchTotals['visited'] > 0
                    ? round(($branchTotals['productive'] * 100) / $branchTotals['visited'], 1) . ' %'
                    : '0 %',

                $branchTotals['newCounter'],
                $branchTotals['orderQty'],
                $branchTotals['orderValue'],
                $branchTotals['sku'],
                $branchTotals['cumulative'],
                '',
                '',
                '',
                '',
            ]);

            /*
            |--------------------------------------------------------------------------
            | DIVISION TOTAL
            |--------------------------------------------------------------------------
            */
            $rows->push([
                '',
                'ZONE TOTAL - ' . $currentDivision,
                '',
                $divisionTotals['workingDays'],
                $divisionTotals['visitTarget'],
                $divisionTotals['visited'],

                $divisionTotals['visitTarget'] > 0
                    ? round(($divisionTotals['visited'] * 100) / $divisionTotals['visitTarget'], 1) . ' %'
                    : '0 %',

                $divisionTotals['productive'],

                $divisionTotals['visited'] > 0
                    ? round(($divisionTotals['productive'] * 100) / $divisionTotals['visited'], 1) . ' %'
                    : '0 %',

                $divisionTotals['newCounter'],
                $divisionTotals['orderQty'],
                $divisionTotals['orderValue'],
                $divisionTotals['sku'],
                $divisionTotals['cumulative'],
                '',
                '',
                '',
                '',
            ]);

            /*
            |--------------------------------------------------------------------------
            | RESET BRANCH TOTALS
            |--------------------------------------------------------------------------
            */
            $branchTotals = [
                'workingDays' => 0,
                'visitTarget' => 0,
                'visited' => 0,
                'productive' => 0,
                'newCounter' => 0,
                'orderQty' => 0,
                'orderValue' => 0,
                'sku' => 0,
                'cumulative' => 0,
            ];

            /*
            |--------------------------------------------------------------------------
            | RESET DIVISION TOTALS
            |--------------------------------------------------------------------------
            */
            $divisionTotals = [
                'workingDays' => 0,
                'visitTarget' => 0,
                'visited' => 0,
                'productive' => 0,
                'newCounter' => 0,
                'orderQty' => 0,
                'orderValue' => 0,
                'sku' => 0,
                'cumulative' => 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ONLY BRANCH CHANGE
        |--------------------------------------------------------------------------
        */
        elseif ($currentBranch !== null && $currentBranch != $branchName) {

            $rows->push([
                '',
                'SUBTOTAL - ' . $currentBranch,
                '',
                $branchTotals['workingDays'],
                $branchTotals['visitTarget'],
                $branchTotals['visited'],

                $branchTotals['visitTarget'] > 0
                    ? round(($branchTotals['visited'] * 100) / $branchTotals['visitTarget'], 1) . ' %'
                    : '0 %',

                $branchTotals['productive'],

                $branchTotals['visited'] > 0
                    ? round(($branchTotals['productive'] * 100) / $branchTotals['visited'], 1) . ' %'
                    : '0 %',

                $branchTotals['newCounter'],
                $branchTotals['orderQty'],
                $branchTotals['orderValue'],
                $branchTotals['sku'],
                $branchTotals['cumulative'],
                '',
                '',
                '',
                '',
            ]);

            /*
            |--------------------------------------------------------------------------
            | RESET BRANCH TOTALS
            |--------------------------------------------------------------------------
            */
            $branchTotals = [
                'workingDays' => 0,
                'visitTarget' => 0,
                'visited' => 0,
                'productive' => 0,
                'newCounter' => 0,
                'orderQty' => 0,
                'orderValue' => 0,
                'sku' => 0,
                'cumulative' => 0,
            ];
        }

        $currentDivision = $divisionName;

        $currentBranch = $branchName;

        /*
        |--------------------------------------------------------------------------
        | VISITED
        |--------------------------------------------------------------------------
        */
        $visited = CheckIn::where('user_id', $user->id)
            ->whereBetween('checkin_date', [$this->start_date, $this->end_date])
            ->select(DB::raw('COALESCE(entity_id, customer_id) as visit_id'))
            ->distinct()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | USER ORDERS
        |--------------------------------------------------------------------------
        */
        $userOrders = $orders->where('executive_id', $user->id);

        $productivityCount = $userOrders->count();

        /*
        |--------------------------------------------------------------------------
        | WORKING DAYS
        |--------------------------------------------------------------------------
        */
        $workingDays = Attendance::where('user_id', $user->id)
            ->whereBetween('punchin_date', [
                Carbon::parse($this->start_date)->startOfDay(),
                Carbon::parse($this->end_date)->endOfDay()
            ])
            ->select(DB::raw('DATE(punchin_date) as date'))
            ->distinct()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | TARGETS
        |--------------------------------------------------------------------------
        */
        $dailyTarget = 15;

        $totalVisitTarget = $dailyTarget * $workingDays;

        /*
        |--------------------------------------------------------------------------
        | ADHERENCE
        |--------------------------------------------------------------------------
        */
        $adherence = $totalVisitTarget > 0
            ? round(($visited * 100) / $totalVisitTarget, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | PRODUCTIVITY %
        |--------------------------------------------------------------------------
        */
        $productivity = $visited > 0
            ? round(($productivityCount * 100) / $visited, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | ORDER DETAILS
        |--------------------------------------------------------------------------
        */
        $orderIds = $userOrders->pluck('id');

        $uniqueSkuCount = DB::table('order_details')
            ->whereIn('order_id', $orderIds)
            ->distinct('product_id')
            ->count('product_id');

        $totalOrderQty = $userOrders->sum('total_qty') ?? 0;

        $totalOrderValue = $userOrders->sum('grand_total') ?? 0;

        /*
        |--------------------------------------------------------------------------
        | NEW COUNTER
        |--------------------------------------------------------------------------
        */
        $newCounterAdded = SecondaryCustomer::where('created_by', $user->id)
            ->whereBetween('created_at', [
                Carbon::parse($this->start_date)->startOfDay(),
                Carbon::parse($this->end_date)->endOfDay()
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | REPORTING NAMES
        |--------------------------------------------------------------------------
        */
        $reportingNames = '';

        if (!empty($user->reportingid)) {

            $ids = explode(',', $user->reportingid);

            $reportingNames = collect($ids)
                ->map(function ($id) use ($reportingUsers) {

                    $id = (int) trim($id);

                    return $reportingUsers->get($id);

                })
                ->filter()
                ->implode(', ');
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL COUNTER
        |--------------------------------------------------------------------------
        */
        $totalCumulativeCounter = SecondaryCustomer::where('employee_id', $user->id)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | USER ROW
        |--------------------------------------------------------------------------
        */
        $rows->push([
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
            (string) ($branchName ?? ''),
            (string) ($user->getdesignation->designation_name ?? ''),
            (string) $reportingNames,
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE BRANCH TOTALS
        |--------------------------------------------------------------------------
        */
        $branchTotals['workingDays'] += $workingDays;
        $branchTotals['visitTarget'] += $totalVisitTarget;
        $branchTotals['visited'] += $visited;
        $branchTotals['productive'] += $productivityCount;
        $branchTotals['newCounter'] += $newCounterAdded;
        $branchTotals['orderQty'] += $totalOrderQty;
        $branchTotals['orderValue'] += $totalOrderValue;
        $branchTotals['sku'] += $uniqueSkuCount;
        $branchTotals['cumulative'] += $totalCumulativeCounter;


        $divisionTotals['workingDays'] += $workingDays;
        $divisionTotals['visitTarget'] += $totalVisitTarget;
        $divisionTotals['visited'] += $visited;
        $divisionTotals['productive'] += $productivityCount;
        $divisionTotals['newCounter'] += $newCounterAdded;
        $divisionTotals['orderQty'] += $totalOrderQty;
        $divisionTotals['orderValue'] += $totalOrderValue;
        $divisionTotals['sku'] += $uniqueSkuCount;
        $divisionTotals['cumulative'] += $totalCumulativeCounter;

        /*
        |--------------------------------------------------------------------------
        | UPDATE GRAND TOTALS
        |--------------------------------------------------------------------------
        */
        $grandTotals['workingDays'] += $workingDays;
        $grandTotals['visitTarget'] += $totalVisitTarget;
        $grandTotals['visited'] += $visited;
        $grandTotals['productive'] += $productivityCount;
        $grandTotals['newCounter'] += $newCounterAdded;
        $grandTotals['orderQty'] += $totalOrderQty;
        $grandTotals['orderValue'] += $totalOrderValue;
        $grandTotals['sku'] += $uniqueSkuCount;
        $grandTotals['cumulative'] += $totalCumulativeCounter;
    }

    /*
    |--------------------------------------------------------------------------
    | LAST BRANCH SUBTOTAL
    |--------------------------------------------------------------------------
    */
    $rows->push([
        '',
        'SUBTOTAL - ' . $currentBranch,
        '',
        $branchTotals['workingDays'],
        $branchTotals['visitTarget'],
        $branchTotals['visited'],

        $branchTotals['visitTarget'] > 0
            ? round(($branchTotals['visited'] * 100) / $branchTotals['visitTarget'], 1) . ' %'
            : '0 %',

        $branchTotals['productive'],

        $branchTotals['visited'] > 0
            ? round(($branchTotals['productive'] * 100) / $branchTotals['visited'], 1) . ' %'
            : '0 %',

        $branchTotals['newCounter'],
        $branchTotals['orderQty'],
        $branchTotals['orderValue'],
        $branchTotals['sku'],
        $branchTotals['cumulative'],
        '',
        '',
        '',
        '',
    ]);

    /*
    |--------------------------------------------------------------------------
    | LAST DIVISION TOTAL
    |--------------------------------------------------------------------------
    */
    $rows->push([
        '',
        'ZONE TOTAL - ' . $currentDivision,
        '',
        $divisionTotals['workingDays'],
        $divisionTotals['visitTarget'],
        $divisionTotals['visited'],

        $divisionTotals['visitTarget'] > 0
            ? round(($divisionTotals['visited'] * 100) / $divisionTotals['visitTarget'], 1) . ' %'
            : '0 %',

        $divisionTotals['productive'],

        $divisionTotals['visited'] > 0
            ? round(($divisionTotals['productive'] * 100) / $divisionTotals['visited'], 1) . ' %'
            : '0 %',

        $divisionTotals['newCounter'],
        $divisionTotals['orderQty'],
        $divisionTotals['orderValue'],
        $divisionTotals['sku'],
        $divisionTotals['cumulative'],
        '',
        '',
        '',
        '',
    ]);

    /*
    |--------------------------------------------------------------------------
    | GRAND TOTAL ROW
    |--------------------------------------------------------------------------
    */
    $rows->push(
        [
        '',
        'GRAND TOTAL',
        '',

        $grandTotals['workingDays'],

        $grandTotals['visitTarget'],

        $grandTotals['visited'],

        $grandTotals['visitTarget'] > 0
            ? round(($grandTotals['visited'] * 100) / $grandTotals['visitTarget'], 1) . ' %'
            : '0 %',

        $grandTotals['productive'],

        $grandTotals['visited'] > 0
            ? round(($grandTotals['productive'] * 100) / $grandTotals['visited'], 1) . ' %'
            : '0 %',
        $grandTotals['newCounter'],
        $grandTotals['orderQty'],
        $grandTotals['orderValue'],
        $grandTotals['sku'],
        $grandTotals['cumulative'],
        '',
        '',
        '',
        '',
    ]);

    return $rows;
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
            'ZONE',
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

            /*
            |--------------------------------------------------------------------------
            | HEADER STYLE
            |--------------------------------------------------------------------------
            */
            $cellRange = 'A1:R1';

            $sheet->getStyle($cellRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E88E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | CENTER ALIGN DATA
            |--------------------------------------------------------------------------
            */
            $lastColumn = 'R';

            $dataRange = 'C2:' . $lastColumn . ($sheet->getHighestRow());

            $sheet->getStyle($dataRange)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | ROW HEIGHT
            |--------------------------------------------------------------------------
            */
            $sheet->getRowDimension(1)->setRowHeight(25);

            /*
            |--------------------------------------------------------------------------
            | HIGHLIGHT SUBTOTAL & GRAND TOTAL ROWS
            |--------------------------------------------------------------------------
            */
            $highestRow = $sheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {

                $cellValue = $sheet->getCell('B' . $row)->getValue();

                /*
                |--------------------------------------------------------------------------
                | DIVISION TOTAL ROW → RED
                |--------------------------------------------------------------------------
                */
                if (str_contains($cellValue, 'ZONE TOTAL')) {

                    $sheet->getStyle('A' . $row . ':R' . $row)
                        ->applyFromArray([

                            'font' => [
                                'bold' => true,
                                'color' => [
                                    'rgb' => 'FFFFFF'
                                ],
                            ],

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,

                                // RED COLOR
                                'startColor' => [
                                    'rgb' => 'E53935'
                                ],
                            ],

                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | GRAND TOTAL ROW → GREEN
                |--------------------------------------------------------------------------
                */
                elseif (str_contains($cellValue, 'GRAND TOTAL')) {

                    $sheet->getStyle('A' . $row . ':R' . $row)
                        ->applyFromArray([

                            'font' => [
                                'bold' => true,
                                'color' => [
                                    'rgb' => 'FFFFFF'
                                ],
                            ],

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,

                                // GREEN COLOR
                                'startColor' => [
                                    'rgb' => '43A047'
                                ],
                            ],

                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | SUBTOTAL ROW → YELLOW
                |--------------------------------------------------------------------------
                */
                elseif (str_contains($cellValue, 'SUBTOTAL')) {

                    $sheet->getStyle('A' . $row . ':R' . $row)
                        ->applyFromArray([

                            'font' => [
                                'bold' => true,
                            ],

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,

                                // YELLOW COLOR
                                'startColor' => [
                                    'rgb' => 'FFF59D'
                                ],
                            ],

                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                }
            }
        },
    ];
}
}