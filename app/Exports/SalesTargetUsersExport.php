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
        if (!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Sub_Admin') && !auth()->user()->hasRole('HR_Admin') && !auth()->user()->hasRole('HO_Account')  && !auth()->user()->hasRole('Sub_Support') && !auth()->user()->hasRole('Accounts Order') && !auth()->user()->hasRole('Service Admin') && !auth()->user()->hasRole('All Customers') && !auth()->user()->hasRole('Sub billing') && !auth()->user()->hasRole('Sales Admin')) {
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
            DB::raw('GROUP_CONCAT(target) as targets'),
            DB::raw('GROUP_CONCAT(achievement) as achievements'),
            DB::raw('GROUP_CONCAT(month) as months'),
            DB::raw('GROUP_CONCAT(year) as years'),
            DB::raw('GROUP_CONCAT(achievement_percent) as achievement_percents'),
            DB::raw('GROUP_CONCAT(qunatity_target) as quantity_targets'),
            DB::raw('GROUP_CONCAT(qunatity_achievement) as quantity_achievements'),
            DB::raw('GROUP_CONCAT(qunatity_achievement_percent) as quantity_achievement_percents'),
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
                        $query->where('year', '=', $f_year_array[1])
                            ->where('month', '<=', 'Mar');
                    });
                } else {
                    $query->where(function ($query) use ($f_year_array) {
                        $query->where('year', '=', $f_year_array[0])
                            ->where('month', '>=', $this->month);
                    })->orWhere(function ($query) use ($f_year_array) {
                        $query->where('year', '=', $f_year_array[1])
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
        // dd($data->toSql(), $f_year_array,  $this->type);
        $data = $data->groupBy('user_id', 'branch_id')->orderBy('month')->get();

        return $data;
    }


    public function headings(): array
    {
        $f_year_array = explode('-', $this->financial_year);

        $startYear = $f_year_array[0];

        $endYear = $f_year_array[1];

        $headings = ['Emp Code', 'User Name', 'Date Of Joining', 'Designation', 'Branch Id', 'Branch Name', 'Division', 'Sales Type'];

        $quarterNames = ['Q1', 'Q2', 'Q3', 'Q4'];

        $quarterIndex = 0;

        for ($year = $startYear; $year <= $endYear; $year++) {
            $startMonth = ($year == $startYear) ? 4 : 1;
            $endMonth = ($year == $endYear) ? 3 : 12;


            for ($month = $startMonth; $month <= $endMonth; $month++) {
                $formattedMonth = Carbon::createFromDate(null, $month, 1)->format('F');
                $headings[] = "$formattedMonth/$year";
                $headings[] = "";
                $headings[] = "";
                $headings[] = "";
                $headings[] = "";
                $headings[] = "";
                

                if ($month == '06' || $month == '09' || $month == '12' || $month == '03') {
                    $headings[] = $quarterNames[$quarterIndex];
                    $quarterIndex++;
                    $headings[] = "";
                    $headings[] = "";
                    $headings[] = "";
                    $headings[] = "";
                    $headings[] = "";
                }
            }
        }

        $headings[] = 'Total';
        $headings[] = '';
        $headings[] = '';
        $headings[] = 'User Active';

        $sub_headings = ['', '', '', '', '', '', '', '', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%','Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%','Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%','Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%','Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%','Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%', 'Tgt', 'Ach', 'Ach%', 'QTgt', 'QAch', 'QAch%'];

        $final_heading = [$headings, $sub_headings];

        return $final_heading;
    }


    public function map($data): array
    {
        $response = array();
        $response[0] = $data['user']['employee_codes'] ?? '';
        $response[1] = $data['user']['name'] ?? '';
        $response[2] = $data['user']['userinfo'] ? ($data['user']['userinfo']['date_of_joining'] ? date('d-M-Y', strtotime($data['user']['userinfo']['date_of_joining'])) : '') : '';
        $response[3] = $data['user']['getdesignation'] ? $data['user']['getdesignation']['designation_name'] : '';
        $response[4] = $data['branch_id'];
        $response[5] = $data['branch']['branch_name'] ?? '';
        $response[6] = $data['user']['getdivision']['division_name'] ?? '';
        $response[7] = $data['type'] ?? '';
        $f_year_array = explode('-', $this->financial_year);
        $data['months'] = explode(',', $data['months']);
        $data['targets'] = explode(',', $data['targets']);
        $data['achievements'] = explode(',', $data['achievements']);
        $data['achievement_percents'] = explode(',', $data['achievement_percents']);
        $data['quantity_targets'] = explode(',', $data['quantity_targets']);
        $data['quantity_achievements'] = explode(',', $data['quantity_achievements']);
        $data['quantity_achievement_percents'] = explode(',', $data['quantity_achievement_percents']);


        foreach ($data['months'] as $key => $month) {
            $year = explode(',', $data['years']);

            if ($month == 'Apr' && $f_year_array[0] == $year[$key]) {

                // 🔹 VALUE TARGET
                $response[8] = $data['targets'][$key];

                if ($data->user->sales_type == 'Primary') {
                    $monthNumber = Carbon::parse("1 $month")->month;
                    $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
                    $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

                    $response[9] = number_format(
                        ($data->user->primarySales
                            ->where('branch_id', $data['branch_id'])
                            ->where('invoice_date', '>=', $firstDate)
                            ->where('invoice_date', '<=', $lastDate)
                            ->sum('net_amount')
                        ) / 100000,
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[9] = $data['achievements'][$key] ?? '';
                }

                // 🔹 VALUE %
                if (!empty($response[8]) && !empty($response[9])) {
                    $response[10] = number_format(
                        ($response[8] == 0) ? 0 : ($response[9] * 100 / $response[8]),
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[10] = '';
                }

                // ===============================
                // 🔥 NEW: QUANTITY PART
                // ===============================

                $response[11] = $data['quantity_targets'][$key] ?? '';
                $response[12] = $data['quantity_achievements'][$key] ?? '';

                if (!empty($response[11]) && !empty($response[12])) {
                    $response[13] = number_format(
                        ($response[11] == 0) ? 0 : ($response[12] * 100 / $response[11]),
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[13] = '';
                }

            } else {
                // 🔹 VALUE fallback
                if (!isset($response[8]))  $response[8] = '';
                if (!isset($response[9]))  $response[9] = '';
                if (!isset($response[10])) $response[10] = '';

                // 🔹 QUANTITY fallback
                if (!isset($response[11])) $response[11] = '';
                if (!isset($response[12])) $response[12] = '';
                if (!isset($response[13])) $response[13] = '';
            }
        }

        foreach ($data['months'] as $key => $month) {
            $year = explode(',', $data['years']);

            if ($month == 'May' && $f_year_array[0] == $year[$key]) {

                // 🔹 VALUE TARGET
                $response[14] = $data['targets'][$key];

                if ($data->user->sales_type == 'Primary') {
                    $monthNumber = Carbon::parse("1 $month")->month;
                    $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
                    $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

                    $response[15] = number_format(
                        ($data->user->primarySales
                            ->where('branch_id', $data['branch_id'])
                            ->where('invoice_date', '>=', $firstDate)
                            ->where('invoice_date', '<=', $lastDate)
                            ->sum('net_amount')
                        ) / 100000,
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[15] = $data['achievements'][$key] ?? '';
                }

                // 🔹 VALUE %
                if (!empty($response[14]) && !empty($response[15])) {
                    $response[16] = number_format(
                        ($response[14] == 0) ? 0 : ($response[15] * 100 / $response[14]),
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[16] = '';
                }

                // ===============================
                // 🔥 QUANTITY PART
                // ===============================

                $response[17] = $data['quantity_targets'][$key] ?? '';
                $response[18] = $data['quantity_achievements'][$key] ?? '';

                if (!empty($response[17]) && !empty($response[18])) {
                    $response[19] = number_format(
                        ($response[17] == 0) ? 0 : ($response[18] * 100 / $response[17]),
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[19] = '';
                }

            } else {
                // 🔹 VALUE fallback
                if (!isset($response[14])) $response[14] = '';
                if (!isset($response[15])) $response[15] = '';
                if (!isset($response[16])) $response[16] = '';

                // 🔹 QUANTITY fallback
                if (!isset($response[17])) $response[17] = '';
                if (!isset($response[18])) $response[18] = '';
                if (!isset($response[19])) $response[19] = '';
            }
        }

        foreach ($data['months'] as $key => $month) {
            $year = explode(',', $data['years']);

            if ($month == 'Jun' && $f_year_array[0] == $year[$key]) {

                // 🔹 VALUE TARGET
                $response[20] = $data['targets'][$key];

                if ($data->user->sales_type == 'Primary') {
                    $monthNumber = Carbon::parse("1 $month")->month;
                    $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
                    $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

                    $response[21] = number_format(
                        ($data->user->primarySales
                            ->where('branch_id', $data['branch_id'])
                            ->where('invoice_date', '>=', $firstDate)
                            ->where('invoice_date', '<=', $lastDate)
                            ->sum('net_amount')
                        ) / 100000,
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[21] = $data['achievements'][$key] ?? '';
                }

                // 🔹 VALUE %
                if (!empty($response[20]) && !empty($response[21])) {
                    $response[22] = number_format(
                        ($response[20] == 0) ? 0 : ($response[21] * 100 / $response[20]),
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[22] = '';
                }

                // ===============================
                // 🔥 QUANTITY PART
                // ===============================

                $response[23] = $data['quantity_targets'][$key] ?? '';
                $response[24] = $data['quantity_achievements'][$key] ?? '';

                if (!empty($response[23]) && !empty($response[24])) {
                    $response[25] = number_format(
                        ($response[23] == 0) ? 0 : ($response[24] * 100 / $response[23]),
                        2,
                        '.',
                        ''
                    );
                } else {
                    $response[25] = '';
                }

            } else {
                // 🔹 VALUE fallback
                if (!isset($response[20])) $response[20] = '';
                if (!isset($response[21])) $response[21] = '';
                if (!isset($response[22])) $response[22] = '';

                // 🔹 QUANTITY fallback
                if (!isset($response[23])) $response[23] = '';
                if (!isset($response[24])) $response[24] = '';
                if (!isset($response[25])) $response[25] = '';
            }
        }

        $response[26] =
            (float) ($response[8] ?? 0) +   // Apr target
            (float) ($response[14] ?? 0) +  // May target
            (float) ($response[20] ?? 0);   // Jun target

        $response[27] =
            (float) ($response[9] ?? 0) +   // Apr ach
            (float) ($response[15] ?? 0) +  // May ach
            (float) ($response[21] ?? 0);   // Jun ach

        $response[28] = $response[26] > 0
            ? number_format(($response[27] / $response[26]) * 100, 2) . '%'
            : '0.00%';

        $response[29] =
            (float) ($response[11] ?? 0) +  // Apr qty target
            (float) ($response[17] ?? 0) +  // May qty target
            (float) ($response[23] ?? 0);   // Jun qty target

        $response[30] =
            (float) ($response[12] ?? 0) +  // Apr qty ach
            (float) ($response[18] ?? 0) +  // May qty ach
            (float) ($response[24] ?? 0);   // Jun qty ach

        $response[31] = $response[29] > 0
            ? number_format(($response[30] / $response[29]) * 100, 2) . '%'
            : '0.00%';


foreach ($data['months'] as $key => $month) {
    $year = explode(',', $data['years']);

    if ($month == 'Jul' && $f_year_array[0] == $year[$key]) {

        // 🔹 VALUE TARGET
        $response[32] = $data['targets'][$key];

        if ($data->user->sales_type == 'Primary') {
            $monthNumber = Carbon::parse("1 $month")->month;
            $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
            $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

            $response[33] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );
        } else {
            $response[33] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[32]) && !empty($response[33])) {
            $response[34] = number_format(
                ($response[32] == 0) ? 0 : ($response[33] * 100 / $response[32]),
                2,
                '.',
                ''
            );
        } else {
            $response[34] = '';
        }

        // 🔥 QUANTITY
        $response[35] = $data['quantity_targets'][$key] ?? '';
        $response[36] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[35]) && !empty($response[36])) {
            $response[37] = number_format(
                ($response[35] == 0) ? 0 : ($response[36] * 100 / $response[35]),
                2,
                '.',
                ''
            );
        } else {
            $response[37] = '';
        }

    } else {
        if (!isset($response[32])) $response[32] = '';
        if (!isset($response[33])) $response[33] = '';
        if (!isset($response[34])) $response[34] = '';

        if (!isset($response[35])) $response[35] = '';
        if (!isset($response[36])) $response[36] = '';
        if (!isset($response[37])) $response[37] = '';
    }
}  

foreach ($data['months'] as $key => $month) {
    $year = explode(',', $data['years']);

    if ($month == 'Aug' && $f_year_array[0] == $year[$key]) {

        // 🔹 VALUE TARGET
        $response[38] = $data['targets'][$key];

        if ($data->user->sales_type == 'Primary') {
            $monthNumber = Carbon::parse("1 $month")->month;
            $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
            $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

            $response[39] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );
        } else {
            $response[39] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[38]) && !empty($response[39])) {
            $response[40] = number_format(
                ($response[38] == 0) ? 0 : ($response[39] * 100 / $response[38]),
                2,
                '.',
                ''
            );
        } else {
            $response[40] = '';
        }

        // 🔥 QUANTITY
        $response[41] = $data['quantity_targets'][$key] ?? '';
        $response[42] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[41]) && !empty($response[42])) {
            $response[43] = number_format(
                ($response[41] == 0) ? 0 : ($response[42] * 100 / $response[41]),
                2,
                '.',
                ''
            );
        } else {
            $response[43] = '';
        }

    } else {
        if (!isset($response[38])) $response[38] = '';
        if (!isset($response[39])) $response[39] = '';
        if (!isset($response[40])) $response[40] = '';

        if (!isset($response[41])) $response[41] = '';
        if (!isset($response[42])) $response[42] = '';
        if (!isset($response[43])) $response[43] = '';
    }
}
       foreach ($data['months'] as $key => $month) {
    $year = explode(',', $data['years']);

    if ($month == 'Sep' && $f_year_array[0] == $year[$key]) {

        // 🔹 VALUE TARGET
        $response[44] = $data['targets'][$key];

        if ($data->user->sales_type == 'Primary') {
            $monthNumber = Carbon::parse("1 $month")->month;
            $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
            $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

            $response[45] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );
        } else {
            $response[45] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[44]) && !empty($response[45])) {
            $response[46] = number_format(
                ($response[44] == 0) ? 0 : ($response[45] * 100 / $response[44]),
                2,
                '.',
                ''
            );
        } else {
            $response[46] = '';
        }

        // 🔥 QUANTITY
        $response[47] = $data['quantity_targets'][$key] ?? '';
        $response[48] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[47]) && !empty($response[48])) {
            $response[49] = number_format(
                ($response[47] == 0) ? 0 : ($response[48] * 100 / $response[47]),
                2,
                '.',
                ''
            );
        } else {
            $response[49] = '';
        }

    } else {
        if (!isset($response[44])) $response[44] = '';
        if (!isset($response[45])) $response[45] = '';
        if (!isset($response[46])) $response[46] = '';

        if (!isset($response[47])) $response[47] = '';
        if (!isset($response[48])) $response[48] = '';
        if (!isset($response[49])) $response[49] = '';
    }
}

        // 🔹 VALUE TOTAL (Jul + Aug + Sep)
$response[50] =
    (float) ($response[32] ?? 0) +   // Jul target
    (float) ($response[38] ?? 0) +   // Aug target
    (float) ($response[44] ?? 0);    // Sep target

$response[51] =
    (float) ($response[33] ?? 0) +   // Jul ach
    (float) ($response[39] ?? 0) +   // Aug ach
    (float) ($response[45] ?? 0);    // Sep ach

$response[52] = $response[50] > 0
    ? number_format(($response[51] / $response[50]) * 100, 2) . '%'
    : '0.00%';

// 🔹 QUANTITY TOTAL
$response[53] =
    (float) ($response[35] ?? 0) +   // Jul qty target
    (float) ($response[41] ?? 0) +   // Aug qty target
    (float) ($response[47] ?? 0);    // Sep qty target

$response[54] =
    (float) ($response[36] ?? 0) +   // Jul qty ach
    (float) ($response[42] ?? 0) +   // Aug qty ach
    (float) ($response[48] ?? 0);    // Sep qty ach

$response[55] = $response[53] > 0
    ? number_format(($response[54] / $response[53]) * 100, 2) . '%'
    : '0.00%';

       foreach ($data['months'] as $key => $month) {
    $year = explode(',', $data['years']);

    if ($month == 'Oct' && $f_year_array[0] == $year[$key]) {

        // 🔹 VALUE TARGET
        $response[56] = $data['targets'][$key];

        if ($data->user->sales_type == 'Primary') {
            $monthNumber = Carbon::parse("1 $month")->month;
            $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
            $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

            $response[57] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );
        } else {
            $response[57] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[56]) && !empty($response[57])) {
            $response[58] = number_format(
                ($response[56] == 0) ? 0 : ($response[57] * 100 / $response[56]),
                2,
                '.',
                ''
            );
        } else {
            $response[58] = '';
        }

        // 🔥 QUANTITY
        $response[59] = $data['quantity_targets'][$key] ?? '';
        $response[60] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[59]) && !empty($response[60])) {
            $response[61] = number_format(
                ($response[59] == 0) ? 0 : ($response[60] * 100 / $response[59]),
                2,
                '.',
                ''
            );
        } else {
            $response[61] = '';
        }

    } else {
        if (!isset($response[56])) $response[56] = '';
        if (!isset($response[57])) $response[57] = '';
        if (!isset($response[58])) $response[58] = '';

        if (!isset($response[59])) $response[59] = '';
        if (!isset($response[60])) $response[60] = '';
        if (!isset($response[61])) $response[61] = '';
    }
}

       foreach ($data['months'] as $key => $month) {
    $year = explode(',', $data['years']);

    if ($month == 'Nov' && $f_year_array[0] == $year[$key]) {

        // 🔹 VALUE TARGET
        $response[62] = $data['targets'][$key];

        if ($data->user->sales_type == 'Primary') {
            $monthNumber = Carbon::parse("1 $month")->month;
            $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
            $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

            $response[63] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );
        } else {
            $response[63] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[62]) && !empty($response[63])) {
            $response[64] = number_format(
                ($response[62] == 0) ? 0 : ($response[63] * 100 / $response[62]),
                2,
                '.',
                ''
            );
        } else {
            $response[64] = '';
        }

        // 🔥 QUANTITY
        $response[65] = $data['quantity_targets'][$key] ?? '';
        $response[66] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[65]) && !empty($response[66])) {
            $response[67] = number_format(
                ($response[65] == 0) ? 0 : ($response[66] * 100 / $response[65]),
                2,
                '.',
                ''
            );
        } else {
            $response[67] = '';
        }

    } else {
        if (!isset($response[62])) $response[62] = '';
        if (!isset($response[63])) $response[63] = '';
        if (!isset($response[64])) $response[64] = '';

        if (!isset($response[65])) $response[65] = '';
        if (!isset($response[66])) $response[66] = '';
        if (!isset($response[67])) $response[67] = '';
    }
}

        foreach ($data['months'] as $key => $month) {
    $year = explode(',', $data['years']);

    if ($month == 'Dec' && $f_year_array[0] == $year[$key]) {

        // 🔹 VALUE TARGET
        $response[68] = $data['targets'][$key];

        if ($data->user->sales_type == 'Primary') {
            $monthNumber = Carbon::parse("1 $month")->month;
            $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
            $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

            $response[69] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );
        } else {
            $response[69] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[68]) && !empty($response[69])) {
            $response[70] = number_format(
                ($response[68] == 0) ? 0 : ($response[69] * 100 / $response[68]),
                2,
                '.',
                ''
            );
        } else {
            $response[70] = '';
        }

        // 🔥 QUANTITY
        $response[71] = $data['quantity_targets'][$key] ?? '';
        $response[72] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[71]) && !empty($response[72])) {
            $response[73] = number_format(
                ($response[71] == 0) ? 0 : ($response[72] * 100 / $response[71]),
                2,
                '.',
                ''
            );
        } else {
            $response[73] = '';
        }

    } else {
        if (!isset($response[68])) $response[68] = '';
        if (!isset($response[69])) $response[69] = '';
        if (!isset($response[70])) $response[70] = '';

        if (!isset($response[71])) $response[71] = '';
        if (!isset($response[72])) $response[72] = '';
        if (!isset($response[73])) $response[73] = '';
    }
}

        // 🔹 VALUE TOTAL (Oct + Nov + Dec)
$response[74] =
    (float) ($response[56] ?? 0) +   // Oct target
    (float) ($response[62] ?? 0) +   // Nov target
    (float) ($response[68] ?? 0);    // Dec target

$response[75] =
    (float) ($response[57] ?? 0) +   // Oct ach
    (float) ($response[63] ?? 0) +   // Nov ach
    (float) ($response[69] ?? 0);    // Dec ach

$response[76] = $response[74] > 0
    ? number_format(($response[75] / $response[74]) * 100, 2) . '%'
    : '0.00%';

// 🔹 QUANTITY TOTAL
$response[77] =
    (float) ($response[59] ?? 0) +   // Oct qty target
    (float) ($response[65] ?? 0) +   // Nov qty target
    (float) ($response[71] ?? 0);    // Dec qty target

$response[78] =
    (float) ($response[60] ?? 0) +   // Oct qty ach
    (float) ($response[66] ?? 0) +   // Nov qty ach
    (float) ($response[72] ?? 0);    // Dec qty ach

$response[79] = $response[77] > 0
    ? number_format(($response[78] / $response[77]) * 100, 2) . '%'
    : '0.00%';

foreach ($data['months'] as $key => $month) {
    $year = explode(',', $data['years']);

    if ($month == 'Jan' && $f_year_array[1] == $year[$key]) {

        // 🔹 VALUE TARGET
        $response[80] = $data['targets'][$key];

        if ($data->user->sales_type == 'Primary') {
            $monthNumber = Carbon::parse("1 $month")->month;
            $firstDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->startOfMonth()->toDateString();
            $lastDate = Carbon::createFromDate($year[$key], $monthNumber, 1)->endOfMonth()->toDateString();

            $response[81] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );
        } else {
            $response[81] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[80]) && !empty($response[81])) {
            $response[82] = number_format(
                ($response[80] == 0) ? 0 : ($response[81] * 100 / $response[80]),
                2,
                '.',
                ''
            );
        } else {
            $response[82] = '';
        }

        // ===============================
        // 🔥 QUANTITY PART
        // ===============================

        $response[83] = $data['quantity_targets'][$key] ?? '';
        $response[84] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[83]) && !empty($response[84])) {
            $response[85] = number_format(
                ($response[83] == 0) ? 0 : ($response[84] * 100 / $response[83]),
                2,
                '.',
                ''
            );
        } else {
            $response[85] = '';
        }

    } else {
        // 🔹 VALUE fallback
        if (!isset($response[80])) $response[80] = '';
        if (!isset($response[81])) $response[81] = '';
        if (!isset($response[82])) $response[82] = '';

        // 🔹 QUANTITY fallback
        if (!isset($response[83])) $response[83] = '';
        if (!isset($response[84])) $response[84] = '';
        if (!isset($response[85])) $response[85] = '';
    }
}

foreach ($data['months'] as $key => $month) {

    $years = explode(',', $data['years']);

    if ($month == 'Mar' && $f_year_array[1] == $years[$key]) {

        // 🔹 VALUE TARGET
        $response[86] = $data['targets'][$key] ?? '';

        if ($data->user->sales_type == 'Primary') {

            $monthNumber = Carbon::parse("1 $month")->month;

            $firstDate = Carbon::createFromDate($years[$key], $monthNumber, 1)
                ->startOfMonth()
                ->toDateString();

            $lastDate = Carbon::createFromDate($years[$key], $monthNumber, 1)
                ->endOfMonth()
                ->toDateString();

            $response[87] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );

        } else {
            $response[87] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[86]) && !empty($response[87])) {
            $response[88] = number_format(
                ($response[86] == 0) ? 0 : ($response[87] * 100 / $response[86]),
                2,
                '.',
                ''
            );
        } else {
            $response[88] = '';
        }

        // ===============================
        // 🔥 QUANTITY PART
        // ===============================

        $response[89] = $data['quantity_targets'][$key] ?? '';
        $response[90] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[89]) && !empty($response[90])) {
            $response[91] = number_format(
                ($response[89] == 0) ? 0 : ($response[90] * 100 / $response[89]),
                2,
                '.',
                ''
            );
        } else {
            $response[91] = '';
        }

    } else {

        // 🔹 VALUE fallback
        $response[86] = $response[86] ?? '';
        $response[87] = $response[87] ?? '';
        $response[88] = $response[88] ?? '';

        // 🔹 QUANTITY fallback
        $response[89] = $response[89] ?? '';
        $response[90] = $response[90] ?? '';
        $response[91] = $response[91] ?? '';
    }
}

foreach ($data['months'] as $key => $month) {

    $years = explode(',', $data['years']);

    if ($month == 'Apr' && $f_year_array[1] == $years[$key]) {

        // 🔹 VALUE TARGET
        $response[92] = $data['targets'][$key] ?? '';

        if ($data->user->sales_type == 'Primary') {

            $monthNumber = Carbon::parse("1 $month")->month;

            $firstDate = Carbon::createFromDate($years[$key], $monthNumber, 1)
                ->startOfMonth()
                ->toDateString();

            $lastDate = Carbon::createFromDate($years[$key], $monthNumber, 1)
                ->endOfMonth()
                ->toDateString();

            $response[93] = number_format(
                ($data->user->primarySales
                    ->where('branch_id', $data['branch_id'])
                    ->where('invoice_date', '>=', $firstDate)
                    ->where('invoice_date', '<=', $lastDate)
                    ->sum('net_amount')
                ) / 100000,
                2,
                '.',
                ''
            );

        } else {
            $response[93] = $data['achievements'][$key] ?? '';
        }

        // 🔹 VALUE %
        if (!empty($response[92]) && !empty($response[93])) {
            $response[94] = number_format(
                ($response[92] == 0) ? 0 : ($response[93] * 100 / $response[92]),
                2,
                '.',
                ''
            );
        } else {
            $response[94] = '';
        }

        // ===============================
        // 🔥 QUANTITY PART
        // ===============================

        $response[95] = $data['quantity_targets'][$key] ?? '';
        $response[96] = $data['quantity_achievements'][$key] ?? '';

        if (!empty($response[95]) && !empty($response[96])) {
            $response[97] = number_format(
                ($response[95] == 0) ? 0 : ($response[96] * 100 / $response[95]),
                2,
                '.',
                ''
            );
        } else {
            $response[97] = '';
        }

    } else {

        // 🔹 VALUE fallback
        $response[92] = $response[92] ?? '';
        $response[93] = $response[93] ?? '';
        $response[94] = $response[94] ?? '';

        // 🔹 QUANTITY fallback
        $response[95] = $response[95] ?? '';
        $response[96] = $response[96] ?? '';
        $response[97] = $response[97] ?? '';
    }
}

       $response[98] =
            (float) ($response[80] ?? 0) +
            (float) ($response[86] ?? 0); // if extending pattern, adjust source months accordingly

        $response[99] =
            (float) ($response[81] ?? 0) +
            (float) ($response[87] ?? 0);

        $response[100] = $response[98] > 0
            ? number_format(($response[99] / $response[98]) * 100, 2) . '%'
            : '0.00%';

        // QUANTITY
        $response[101] =
            (float) ($response[83] ?? 0) +
            (float) ($response[89] ?? 0);

        $response[102] =
            (float) ($response[84] ?? 0) +
            (float) ($response[90] ?? 0);

        $response[103] = $response[101] > 0
            ? number_format(($response[102] / $response[101]) * 100, 2) . '%'
            : '0.00%';

       // VALUE
       // 🔹 YEAR VALUE TOTAL
        $response[104] =
            (float) ($response[26] ?? 0) + 
            (float) ($response[50] ?? 0) + 
            (float) ($response[74] ?? 0) +  // Q4 target (Jul–Sep already pattern)
            (float) ($response[98] ?? 0);   // adjust if Q4 includes Jan cycle etc

        $response[105] =
            (float) ($response[27] ?? 0) +
            (float) ($response[51] ?? 0) +
            (float) ($response[75] ?? 0) +
            (float) ($response[99] ?? 0);

        $response[106] = $response[104] > 0
            ? number_format(($response[105] / $response[104]) * 100, 2) . '%'
            : '0.00%';

        // QUANTITY
        $response[107] =
            (float) ($response[29] ?? 0) +
            (float) ($response[53] ?? 0) +
            (float) ($response[77] ?? 0) +
            (float) ($response[101] ?? 0);

        $response[108] =
            (float) ($response[30] ?? 0) +
            (float) ($response[54] ?? 0) +
            (float) ($response[78] ?? 0) +
            (float) ($response[102] ?? 0);

        $response[109] = $response[107] > 0
            ? number_format(($response[108] / $response[107]) * 100, 2) . '%'
            : '0.00%';
        $response[110] = $data['user']['active'] ?? '';

        $this->rowIndex++;
        return $response;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:D2');
        $sheet->mergeCells('E1:E2');
        $sheet->mergeCells('F1:F2');
        $sheet->mergeCells('G1:G2');
        $sheet->mergeCells('H1:H2');
        $sheet->mergeCells('I1:N1');
        $sheet->mergeCells('O1:T1');
        $sheet->mergeCells('U1:Z1');
        $sheet->mergeCells('AA1:AF1');
        $sheet->mergeCells('AG1:AL1');
        $sheet->mergeCells('AM1:AR1');
        $sheet->mergeCells('AS1:AX1');
        $sheet->mergeCells('AY1:BD1');
        $sheet->mergeCells('BE1:BJ1');
        $sheet->mergeCells('BK1:BQ1');
        $sheet->mergeCells('BR1:BW1');
        $sheet->mergeCells('BX1:CC1');
        $sheet->mergeCells('CD1:CI1');
        $sheet->mergeCells('CJ1:CO1');
        $sheet->mergeCells('CP1:CU1');
        $sheet->mergeCells('CV1:DA1');
        $sheet->mergeCells('DB1:DG1');

        $sheet->getStyle('A1:DG2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // White font color
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '87CEEB'], // Sky blue background color
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, // Thin border
                    'color' => ['rgb' => '000000'], // Black border color
                ],
            ],
        ]);


        $sheet->getStyle('A3:BH300')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
}

