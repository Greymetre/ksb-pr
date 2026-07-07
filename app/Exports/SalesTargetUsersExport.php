<?php

namespace App\Exports;

use App\Models\Services;
use App\Models\Branch;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use App\Models\SalesTargetUsers;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesTargetUsersExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{

    private $rowIndex = 3;

    public function __construct($request)
    {
        $this->user_id = $request->input('user');
        $this->month = $request->input('month');
        $this->financial_year = $request->input('financial_year');
        $this->target = $request->input('target');
        $this->branch_id = $request->input('branch_id');
        $this->user_id = $request->input('user_id');
        $this->division = $request->input('division');
        $this->type = $request->input('type');
    }

    public function collection()
    {
        $f_year_array = explode('-', $this->financial_year);


        // $data = SalesTargetUsers::with(['user'])->whereBetween('year', $f_year_array)->toSql();
        $userid = auth()->user()->id;
        $all_users = User::whereDoesntHave('roles', function ($query) {
            $query->where('id', 29);
        })->get();
        if (!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('subAdmin') && !auth()->user()->hasRole('HR_Admin') && !auth()->user()->hasRole('HO_Account')  && !auth()->user()->hasRole('Sub_Support') && !auth()->user()->hasRole('Accounts Order') && !auth()->user()->hasRole('Service Admin') && !auth()->user()->hasRole('All Customers') && !auth()->user()->hasRole('Sub billing') && !auth()->user()->hasRole('Sales Admin')) {
            $all_ids_array = array($userid);
            $test = getAllChild(array($userid), $all_users);
            while (count($test) > 0) {
                $all_ids_array = array_merge($all_ids_array, $test);
                $test = getAllChild($test, $all_users);
            }
        } elseif (auth()->user()->hasRole('Accounts Order')) {
            $all_ids_array = User::where('active', 'Y')->whereIn('branch_id', explode(',', auth()->user()->branch_show))->pluck('id')->toArray();
            $test = getAllChild(array($userid), $all_users);
            while (count($test) > 0) {
                $all_ids_array = array_merge($all_ids_array, $test);
                $test = getAllChild($test, $all_users);
            }
        } else {
            $all_ids_array = User::pluck('id')->toArray();
        }
        $data = SalesTargetUsers::with(['user', 'user.getdesignation', 'user.getdivision', 'branch'])->whereIn('user_id', $all_ids_array)->select([
           DB::raw('GROUP_CONCAT(month ORDER BY year, FIELD(month,"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")) as months'),

DB::raw('GROUP_CONCAT(target ORDER BY year, FIELD(month,"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")) as targets'),

DB::raw('GROUP_CONCAT(achievement ORDER BY year, FIELD(month,"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")) as achievements'),

DB::raw('GROUP_CONCAT(achievement_percent ORDER BY year, FIELD(month,"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")) as achievement_percents'),

DB::raw('GROUP_CONCAT(COALESCE(qunatity_target,0) ORDER BY year, FIELD(month,"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")) as quantity_targets'),
DB::raw('GROUP_CONCAT(COALESCE(qunatity_achievement,0) ORDER BY year, FIELD(month,"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")) as quantity_achievements'),

DB::raw('GROUP_CONCAT(COALESCE(qunatity_achievement_percent,0) ORDER BY year, FIELD(month,"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")) as quantity_achievement_percents'),

DB::raw('GROUP_CONCAT(year ORDER BY year, FIELD(month,"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")) as years'),
            DB::raw('user_id'),
            DB::raw('branch_id'),
            DB::raw('type'),
        ]);


        $data->where(function ($query) use ($f_year_array) {
            $query->where(function ($query) use ($f_year_array) {
                if ($this->month == '' && empty($this->month)) {
                    $query->where(function ($query) use ($f_year_array) {
                        $query->where('year', '=', $f_year_array[0])
                            ->where('month', '>=', 'Apr');
                    })->orWhere(function ($query) use ($f_year_array) {
                        $query->where('year', '=', $f_year_array[0])
                            ->where('month', '<=', 'Mar');
                    });
                } else {
                    $query->where(function ($query) use ($f_year_array) {
                        $query->where('year', '=', $f_year_array[0])
                            ->where('month', '>=', $this->month);
                    })->orWhere(function ($query) use ($f_year_array) {
                        $query->where('year', '=', $f_year_array[0])
                            ->where('month', '<=', $this->month);
                    });
                }
            });
        });


        if ($this->branch_id && $this->branch_id != '' && $this->branch_id != null) {
            $userIds = User::where('branch_id', $this->branch_id)->pluck('id');
            $data->whereIn('user_id', $userIds);
        }

        if ($this->user_id && $this->user_id != '' && $this->user_id != null) {
            $userIds = User::where('id', $this->user_id)->pluck('id');
            $data->whereIn('user_id', $userIds);
        }

        if ($this->division && $this->division != '' && $this->division != null) {
            $divisionIds = User::where('division_id', $this->division)->pluck('id');
            $data->whereIn('user_id', $divisionIds);
        }

        if ($this->type && $this->type != '' && $this->type != null) {
            $data->where('type', $this->type);
        }
        
        // dd(
        //     $data->groupBy('user_id', 'branch_id')
        //          ->first()
        // );
        // dd($data->toSql(), $f_year_array,  $this->type);
        $data = $data->groupBy('user_id', 'branch_id')->orderBy('month')->get();

        return $data;
    }


   public function headings(): array
{
    $f_year_array = explode('-', $this->financial_year);

    $startYear = $f_year_array[0];

    // ===============================
    // FIRST HEADER ROW
    // ===============================

    $headings = [
        'Emp Code',
        'User Name',
        'Date Of Joining',
        'Designation',
        'Branch Id',
        'Branch Name',
        'Zone',
        'Sales Type'
    ];

    // JAN → DEC

    $months = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];

    foreach ($months as $month) {

        $headings[] = $month . '/' . $startYear;

        // Remaining merged columns
        $headings[] = '';
        $headings[] = '';
        $headings[] = '';
        $headings[] = '';
        $headings[] = '';
    }

    // TOTAL SECTION

    $headings[] = 'Total';
    $headings[] = '';
    $headings[] = '';
    $headings[] = '';
    $headings[] = '';
    $headings[] = '';
    $headings[] = 'User Active';

    // ===============================
    // SECOND HEADER ROW
    // ===============================

    $sub_headings = [
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        ''
    ];

    // 12 MONTHS SUB HEADINGS

    for ($i = 0; $i < 12; $i++) {

        $sub_headings[] = 'Tgt';
        $sub_headings[] = 'Ach';
        $sub_headings[] = 'Ach%';

        $sub_headings[] = 'QTgt';
        $sub_headings[] = 'QAch';
        $sub_headings[] = 'QAch%';
    }

    // TOTAL SUB HEADINGS

    $sub_headings[] = 'Tgt';
    $sub_headings[] = 'Ach';
    $sub_headings[] = 'Ach%';

    $sub_headings[] = 'QTgt';
    $sub_headings[] = 'QAch';
    $sub_headings[] = 'QAch%';

    $sub_headings[] = '';

    return [
        $headings,
        $sub_headings
    ];
}


public function map($data): array
{
    $response = [];

    // ===============================
    // BASIC USER INFO
    // ===============================

    $response[0] = $data['user']['employee_codes'] ?? '';
    $response[1] = $data['user']['name'] ?? '';

    $response[2] = $data['user']['userinfo']
        ? (
            $data['user']['userinfo']['date_of_joining']
                ? date(
                    'd-M-Y',
                    strtotime($data['user']['userinfo']['date_of_joining'])
                )
                : ''
        )
        : '';

    $response[3] =
        $data['user']['getdesignation']['designation_name'] ?? '';

    $response[4] = $data['branch_id'] ?? '';

    $response[5] =
        $data['branch']['branch_name'] ?? '';

    $response[6] =
        $data['user']['getdivision']['division_name'] ?? '';

    $response[7] = $data['type'] ?? '';

    // ===============================
    // PREPARE DATA
    // ===============================

    $f_year_array = explode('-', $this->financial_year);

    $data['months'] = explode(',', $data['months']);
    $data['targets'] = explode(',', $data['targets']);
    $data['achievements'] = explode(',', $data['achievements']);
    $data['achievement_percents'] = explode(',', $data['achievement_percents']);
    $data['quantity_targets'] = explode(',', $data['quantity_targets']);
    $data['quantity_achievements'] = explode(',', $data['quantity_achievements']);
    $data['quantity_achievement_percents'] = explode(',', $data['quantity_achievement_percents']);
    $years = explode(',', $data['years']);

    // ===============================
    // MONTHS JAN → DEC
    // ===============================

    $months = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec'
    ];

    $startColumn = 8;

    $totalTarget = 0;
    $totalAchievement = 0;

    $totalQtyTarget = 0;
    $totalQtyAchievement = 0;

    foreach ($months as $monthIndex => $monthName) {

        $baseIndex = $startColumn + ($monthIndex * 6);

        // DEFAULT EMPTY VALUES

        $response[$baseIndex] = '';
        $response[$baseIndex + 1] = '';
        $response[$baseIndex + 2] = '';

        $response[$baseIndex + 3] = '';
        $response[$baseIndex + 4] = '';
        $response[$baseIndex + 5] = '';

        foreach ($data['months'] as $key => $month) {

            if (
                $month == $monthName &&
                $f_year_array[0] == $years[$key]
            ) {

                // ===============================
                // TARGET
                // ===============================

                $response[$baseIndex] =
                    $data['targets'][$key] ?? '';

                // ===============================
                // ACHIEVEMENT
                // ===============================

                if ($data->user->sales_type == 'Primary') {

                    $monthNumber = Carbon::parse("1 $month")->month;

                    $firstDate = Carbon::createFromDate(
                        $years[$key],
                        $monthNumber,
                        1
                    )->startOfMonth()->toDateString();

                    $lastDate = Carbon::createFromDate(
                        $years[$key],
                        $monthNumber,
                        1
                    )->endOfMonth()->toDateString();

                    $achievement = (
                        $data->user->primarySales
                            ->where('branch_id', $data['branch_id'])
                            ->where('invoice_date', '>=', $firstDate)
                            ->where('invoice_date', '<=', $lastDate)
                            ->sum('net_amount')
                    ) / 100000;

                    $response[$baseIndex + 1] = number_format(
                        $achievement,
                        2,
                        '.',
                        ''
                    );

                } else {

                    $response[$baseIndex + 1] =
                        $data['achievements'][$key] ?? '';
                }

                // ===============================
                // ACH %
                // ===============================

                if (
                    !empty($response[$baseIndex]) &&
                    $response[$baseIndex] != 0
                ) {

                    $response[$baseIndex + 2] = number_format(
                        (
                            $response[$baseIndex + 1] * 100
                        ) / $response[$baseIndex],
                        2,
                        '.',
                        ''
                    );

                } else {

                    $response[$baseIndex + 2] = '';
                }

                // ===============================
                // QTY TARGET
                // ===============================

                $response[$baseIndex + 3] =
                    $data['quantity_targets'][$key] ?? '';

                // ===============================
                // QTY ACHIEVEMENT
                // ===============================

                $response[$baseIndex + 4] =
                    $data['quantity_achievements'][$key] ?? '';

                // ===============================
                // QTY %
                // ===============================

                if (
                    !empty($response[$baseIndex + 3]) &&
                    $response[$baseIndex + 3] != 0
                ) {

                    $response[$baseIndex + 5] = number_format(
                        (
                            $response[$baseIndex + 4] * 100
                        ) / $response[$baseIndex + 3],
                        2,
                        '.',
                        ''
                    );

                } else {

                    $response[$baseIndex + 5] = '';
                }

                // ===============================
                // TOTALS
                // ===============================

                $totalTarget +=
                    (float) ($response[$baseIndex] ?? 0);

                $totalAchievement +=
                    (float) ($response[$baseIndex + 1] ?? 0);

                $totalQtyTarget +=
                    (float) ($response[$baseIndex + 3] ?? 0);

                $totalQtyAchievement +=
                    (float) ($response[$baseIndex + 4] ?? 0);
            }
        }
    }

    // ===============================
    // FINAL TOTALS
    // ===============================

    $totalStart = $startColumn + (12 * 6);

    // VALUE TOTALS

    $response[$totalStart] = number_format(
        $totalTarget,
        2,
        '.',
        ''
    );

    $response[$totalStart + 1] = number_format(
        $totalAchievement,
        2,
        '.',
        ''
    );

    $response[$totalStart + 2] =
        $totalTarget > 0
            ? number_format(
                ($totalAchievement * 100) / $totalTarget,
                2
            ) . '%'
            : '0.00%';

    // QTY TOTALS

    $response[$totalStart + 3] = number_format(
        $totalQtyTarget,
        2,
        '.',
        ''
    );

    $response[$totalStart + 4] = number_format(
        $totalQtyAchievement,
        2,
        '.',
        ''
    );

    $response[$totalStart + 5] =
        $totalQtyTarget > 0
            ? number_format(
                ($totalQtyAchievement * 100) / $totalQtyTarget,
                2
            ) . '%'
            : '0.00%';

    // USER ACTIVE

    $response[$totalStart + 6] =
        $data['user']['active'] ?? '';

    $this->rowIndex++;

    return $response;
}

public function styles(Worksheet $sheet)
{
    // ===============================
    // STATIC MERGE CELLS
    // ===============================

    $sheet->mergeCells('A1:A2');
    $sheet->mergeCells('B1:B2');
    $sheet->mergeCells('C1:C2');
    $sheet->mergeCells('D1:D2');
    $sheet->mergeCells('E1:E2');
    $sheet->mergeCells('F1:F2');
    $sheet->mergeCells('G1:G2');
    $sheet->mergeCells('H1:H2');

    // ===============================
    // DYNAMIC MONTH MERGES
    // ===============================

    // Month section starts from column I
    $startColumn = 9;

    // 12 Months
    for ($i = 0; $i < 12; $i++) {

        $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumn);

        $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumn + 5);

        $sheet->mergeCells($start . '1:' . $end . '1');

        $startColumn += 6;
    }

    // ===============================
    // TOTAL COLUMN MERGE
    // ===============================

    $totalStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumn);

    $totalEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumn + 5);

    $sheet->mergeCells($totalStart . '1:' . $totalEnd . '1');

    // ===============================
    // LAST COLUMN
    // ===============================

    $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumn + 6);

    $sheet->mergeCells($lastColumn . '1:' . $lastColumn . '2');

    // ===============================
    // HEADER STYLE
    // ===============================

    $sheet->getStyle('A1:' . $lastColumn . '2')->applyFromArray([

        'font' => [
            'bold' => true,
            'color' => [
                'rgb' => 'FFFFFF'
            ],
        ],

        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => '87CEEB'
            ],
        ],

        'alignment' => [
            'horizontal' =>
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,

            'vertical' =>
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],

        'borders' => [
            'allBorders' => [
                'borderStyle' =>
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,

                'color' => [
                    'rgb' => '000000'
                ],
            ],
        ],
    ]);

    // ===============================
    // BODY STYLE
    // ===============================

    $sheet->getStyle('A3:' . $lastColumn . '500')->applyFromArray([

        'alignment' => [

            'horizontal' =>
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,

            'vertical' =>
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ]);

    // ===============================
    // AUTO ROW HEIGHT
    // ===============================

    $sheet->getDefaultRowDimension()->setRowHeight(-1);

    return [];
}
}

