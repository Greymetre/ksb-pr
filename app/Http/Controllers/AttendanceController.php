<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DataTables;
use Validator;
use Gate;
use App\Models\TourProgramme;
use App\DataTables\AttendancesDataTable;
use App\Exports\AttendanceExport;
use Carbon\Carbon;
use Excel;
use App\Models\BeatSchedule;
use Illuminate\Support\Facades\Storage;
use File;

use App\Models\Attachment;
use App\Models\User;
use App\Models\Holiday;

use App\Exports\ExcelExport;
use App\Models\Beat;
use App\Models\CompOffLeave;
use App\Models\Leave;
use App\Models\Media;
use App\Models\TourDetail;
use DateTime;
use DatePeriod;
use DateInterval;
use Stevebauman\Location\Drivers\IpInfo;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Log;
use App\Exports\attendanceSummaryDownload;


class AttendanceController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->attendance = new Attendance();
  }

  public function index(AttendancesDataTable $dataTable)
  {
    //abort_if(Gate::denies('attendance_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $pinchinimages = Attachment::where('file_path', '<>', '')->pluck('file_path');
    $path = public_path();
    //$files = File::allFiles($path);
    foreach ($pinchinimages as $key => $image) {
      if (File::exists($path . '/' . $image)) {
        File::move($path . '/' . $image, $path . '/finals/' . $image);
      }
    }

    $users = User::select('id', 'name')
        ->where('active', 'Y')
        ->orderBy('name')
        ->get();
    // foreach ($pinchinimages as $key => $value) {
    //     $newpath = str_ireplace('attendances', 'final', $value);
    //     Storage::move($value, $newpath);

    //     // if(File::exists($value)){
    //     //     File::move(public_path($value), public_path($newpath));
    //     // }           
    // }

    return $dataTable->render('attendances.index', compact('users'));
  }

  public function attendancesInfo(Request $request)
  {
    if ($request->ajax()) {
      $data = Attendance::where(function ($query) use ($request) {
        if (!empty($request['user_id'])) {
          $query->where('user_id', $request['user_id']);
        }
      })
        ->latest();
      dd(
            $data->take(10)->get(['id', 'user_id', 'punchin_date', 'punchin_time'])
                 ->map(function($row) {
                     return [
                         'id'           => $row->id,
                         'user_id'      => $row->user_id,
                         'date'         => $row->punchin_date,
                         'punchin_time' => $row->punchin_time ?? '— NULL —',
                     ];
                 })
        );
      return Datatables::of($data)
        ->addIndexColumn()
        ->editColumn('punchin_date', function ($data) {
          return isset($data->punchin_date) ? showdateformat($data->punchin_date) : '';
        })
        ->editColumn('worked_time', function ($data) {
          return isset($data->worked_time) ? $data->worked_time : '';
        })
        ->make(true);

    }
  }

  public function download(Request $request)
  {
    ////abort_if(Gate::denies('visitreport_download'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    if (ob_get_contents()) ob_end_clean();
    ob_start();
    return Excel::download(new AttendanceExport($request), 'attendancereports.xlsx');
  }



//   public function attendanceSummaryDownload(Request $request)
//   {


//     $filename = 'attendance-summary-report.xlsx';
//     $start_date = $request->start_date;
//     $end_date = $request->end_date;
//     $executive_id = $request->executive_id;
//     $end_date = date('Y-m-d', strtotime($end_date . "+1 days"));
    
    

//   if (empty($start_date) && empty($end_date)) {
//         $now = Carbon::now();
        
//         $start_date = $now->startOfMonth()->format('Y-m-d');   // 1st of current month
//         $end_date   = $now->endOfMonth()->format('Y-m-d');     // last day of current month
//     }

//     $period = new DatePeriod(
//       new DateTime($start_date),
//       new DateInterval('P1D'),
//       new DateTime($end_date)
//     );

    

//     $last60Days = Carbon::now()->subDays(60);



//     $attendancesummary = User::with(['attendance_details', 'createdbyname', 'getbranch', 'userinfo'])->where('active', 'Y')->whereDoesntHave('roles', function ($query) {
//       $query->whereIn('id', config('constants.customer_roles'));
//     })->where('show_attandance_report', '1');
//     if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
//       $attendancesummary = $attendancesummary->whereIn('id', getUsersReportingToAuth());
//     }

//     if ($executive_id) {
//       $attendancesummary = $attendancesummary->where('id', $executive_id);
//     }

//     $attendancesummary = $attendancesummary->limit(4500)->get();

//     $date1 = $start_date;
//     $date2 = $end_date;


//     $label2 = [];

//     //new

//     foreach ($period as $key => $value) {

//       $label2[] =  $value->format('j-M-Y');
//       $like_date =  $value->format('j-M-Y');
//     }


//     $data = $attendancesummary->map(function ($item, $key) use ($label2, $date1, $date2, $period, $last60Days) {


//       //neww
//       $label_data = [];
//       $total_wo = 0;
//       $total_a = 0;
//       $total_lop = 0;
//       $total_mis = 0;
//       $total_pw = 0;
//       $total_h = 0;
//       $total_hd = 0;
//       $total_p = 0;
//       $total_pn = 0;
//       $total_atte = 0;
//       $total_al = 0;
//       $total_hdal = 0;
//       $total_co = 0;
//       $total_con = 0;

//       foreach ($period as $key => $value) {
//         $like_date =  $value->format('j-M-Y');
//         $total_atte++;
//         $check = $value->format('Y-m-d');

//         //last new

//         $attendance_details = Attendance::where(['user_id' => $item->id])->where('punchin_date', 'like', $check . '%')->first();


//         ///nnn

//         $userId = $item->id;
//         $branchId = $item->branch_id;
//         $holiday_detail = Holiday::where('branch', $branchId)->get();
//         $holiday_dates = $holiday_detail->pluck('holiday_date')->toArray(); // Extract holiday_date values
//         $check_date_attendance = explode(',', implode(',', $holiday_dates));

//         if (in_array($check, $check_date_attendance)) {
//           $label_data[] = 'H';
//           $total_h++;
//         } else {


//           ///nnn  

//           $dayname = date('l', strtotime($check));

//           if (!empty($attendance_details)) {

//             if ($attendance_details->working_type == 'Second Half Leave' || $attendance_details->working_type == 'First Half Leave' || $attendance_details->working_type == 'Full Day Leave' || $attendance_details->working_type == 'Leave') {
//               $leaveExists = Leave::where('user_id', $attendance_details->user_id)
//                 ->whereDate('from_date', '<=', $attendance_details->punchin_date)
//                 ->whereDate('to_date', '>=', $attendance_details->punchin_date)
//                 ->first();
//             }

//             if ($attendance_details->attendance_status == '1') {
//               if ($attendance_details->working_type == 'Leave' || $attendance_details->working_type == 'Full Day Leave') {
//                 if (($leaveExists->bal_type ?? null) == 'Comp-off Balance') {
//                   $label_data[] =  'Comp Off';
//                   $total_co++;
//                 } else {
//                   $label_data[] =  'AL';
//                   $total_al++;
//                 }
//               } elseif ($dayname == 'Sunday') {
//                 $label_data[] =  'PW';
//                 $total_pw++;
//               } elseif ($attendance_details->working_type == 'Second Half Leave' || $attendance_details->working_type == 'First Half Leave') {
//                 if (($leaveExists->bal_type ?? null) == 'Comp-off Balance') {
//                   $label_data[] =  '1/2P+1/2Comp Off';
//                   $total_co++;
//                 } else {
//                   $label_data[] =  '1/2P+1/2AL';
//                   $total_hdal++;
//                 }
//               } elseif ($attendance_details->working_type == 'Local Market Visit') {
//                 $label_data[] =  'P';
//                 $total_p++;
//               } elseif ($attendance_details->working_type == 'Office Work') {
//                 $label_data[] =  'P';
//                 $total_p++;
//               } elseif ($attendance_details->working_type == 'Office Meeting') {
//                 $label_data[] =  'P';
//                 $total_p++;
//               } elseif ($attendance_details->working_type == 'Scouting for market') {
//                 $label_data[] =  'P';
//                 $total_p++;
//               } elseif ($attendance_details->working_type == 'Plumber Meet') {
//                 $label_data[] =  'P';
//                 $total_p++;
//               } elseif ($attendance_details->working_type == 'Retailer Meet') {
//                 $label_data[] =  'P';
//                 $total_p++;
//               } elseif ($attendance_details->working_type == 'Service Center Visit') {
//                 $label_data[] =  'P';
//                 $total_p++;
//               } elseif ($attendance_details->working_type == 'Tour') {
//                 $label_data[] =  'P';
//                 $total_p++;
//               } elseif ($attendance_details->working_type == 'Holiday') {
//                 $label_data[] = 'H';
//                 $total_h++;
//               }
//             } else if ($attendance_details->attendance_status == '2') {
//               if ($attendance_details->working_type == 'Full Day Leave' || $attendance_details->working_type == 'Leave') {
//                 if (($leaveExists->bal_type ?? null) == 'Comp-off Balance') {
//                   $label_data[] =  'Comp Off N';
//                   $total_con++;
//                 } else {
//                   $label_data[] =  'LOPN';
//                   $total_lop++;
//                 }
//               } elseif ($attendance_details->working_type == 'Second Half Leave' || $attendance_details->working_type == 'First Half Leave') {
//                 if (($leaveExists->bal_type ?? null) == 'Comp-off Balance') {
//                   $label_data[] =  '1/2P+1/2Comp Off N';
//                   $total_con++;
//                 } else {
//                   $label_data[] =  '1/2P+1/2LOPN';
//                   $total_hd++;
//                 }
//               } else {
//                 $label_data[] = 'A';
//                 $total_a++;
//               }
//             } else {
//               if ($attendance_details->working_type == 'Full Day Leave' || $attendance_details->working_type == 'Leave') {

//                 if (($leaveExists->bal_type ?? null) == 'Comp-off Balance') {
//                   $label_data[] =  'Comp Off N';
//                   $total_con++;
//                 } else {
//                   $label_data[] =  'LOPN';
//                   $total_lop++;
//                 }
//               } elseif ($attendance_details->working_type == 'Second Half Leave' || $attendance_details->working_type == 'First Half Leave') {
//                 if ($leaveExists->bal_type == 'Comp-off Balance') {
//                   $label_data[] =  '1/2P+1/2Comp Off N';
//                   $total_con++;
//                 } else {
//                   $label_data[] =  '1/2P+1/2LOPN';
//                   $total_hd++;
//                 }
//               } else {
//                 $label_data[] = 'PN';
//                 $total_pn++;
//               }
//             }
//           } else {
//             $date_of_joining_object = new DateTime($item->userinfo->date_of_joining ?? '');

//             if ($dayname == 'Sunday') {
//               if ($date_of_joining_object <= $value) {
//                 $label_data[] = 'W/o';
//                 $total_wo++;
//               } else {
//                 $label_data[] = '-';
//               }
//             } else {
//               if ($date_of_joining_object <= $value) {
//                 $label_data[] = 'MIS';
//                 $total_mis++;
//               } else {
//                 $label_data[] = '-';
//               }
//             }
//           }
//         }
//       }


//       if ($attendance_details && $attendance_details->attendance_status == '1' && in_array($attendance_details->working_type, ['Office Work', 'Local Market Visit', 'Tour', /* baaki sab */])) {
//     Log::info("Present found for user: {$item->id}, date: {$check}, working_type: {$attendance_details->working_type}");
//     $label_data[] = 'P';
//     $total_p++;
// }
//       //neww

//       $sundayPunchinCount = CompOffLeave::where('comp_off_date', '>=', $last60Days)->where('is_used', false)
//         ->where('user_id', $item->id)
//         ->sum('balance');

//       $label_data[] = $item->leave_balance ?? '0';
//       $label_data[] = $sundayPunchinCount > 0 ? $sundayPunchinCount : '0';
//       $label_data[] = (string)$total_wo;
//       $label_data[] = (string)$total_a;
//       $label_data[] = (string)$total_lop;
//       $label_data[] = (string)$total_al;
//       $label_data[] = (string)$total_con;
//       $label_data[] = (string)$total_co;
//       $label_data[] = (string)$total_mis;
//       $label_data[] = (string)$total_pw;
//       $label_data[] = (string)$total_h;
//       $label_data[] = (string)$total_hd;
//       $label_data[] = (string)$total_hdal;
//       $label_data[] = (string)$total_p;
//       $label_data[] = (string)$total_pn;
//       $label_data[] = $total_wo + $total_al + $total_pw + $total_h + $total_hdal + $total_p;
//       $label_data[] = (string)$total_atte;

//       $return =  [
//         $item->id ?? '',
//         $item->employee_codes ?? '',
//         $item->name ?? '',
//         $item->getbranch->branch_name ?? '',
//         $item->getdivision->division_name ?? '',
//         $item->getdesignation->designation_name ?? '',
//         $item->userinfo ? date('d M Y', strtotime($item->userinfo->date_of_joining)) : '-',
//       ];

//       return  $option_array = array_merge($return, $label_data);
//     })->toArray();


//     $start_date = $request->start_date;   // e.g. "2025-04-01"
//     $end_date   = $request->end_date;     // e.g. "2026-03-31"


//     $label1 = [
//       'User Id',
//       'Employee Code',
//       'User Name',
//       'Branch',
//       'Division',
//       'Designation',
//       'DOJ',
//     ];

//     $label3 = [
//       'Leave Balance',
//       'Comp Leave Balance',
//       'Week Of (W/o)',
//       'Absent (A)',
//       'LOP',
//       'AL',
//       'Comp Off NP',
//       'Comp Off A',
//       'MIS Punch (MIS)',
//       'Present Week of (PW)',
//       'Holiday (H)',
//       'Half Day (1/2P+1/2LOP)',
//       'Half Day (1/2P+1/AL)',
//       'Present (P)',
//       'Present Not Approve (PN)',
//       'Paid Days',
//       'TOTAL Days',
//     ];

//     $headings = array_merge($label1, $label2, $label3);
    

//     // $export = new ExcelExport($label, $data);
//   return Excel::download(
//         new attendanceSummaryDownload ($headings, $data, $start_date, $end_date),
//         'attendance-summary-' . date('Y-m-d') . '.xlsx'
//     );
//   }

public function attendanceSummaryDownload(Request $request)
{
    $start_date   = $request->start_date;
    $end_date     = $request->end_date;
    $executive_id = $request->executive_id;
    $designation_ids = $request->designation_id;

    // If no date range is provided → use current month
    if (empty($start_date) && empty($end_date)) {
        $now = Carbon::now();
        $start_date = $now->startOfMonth()->format('Y-m-d');
        $end_date   = $now->endOfMonth()->format('Y-m-d');
    }
    $holidayRecords = Holiday::where('active', 'Y')->get();
    

    // Prepare period (end date is exclusive → +1 day)
    $end_date_for_period = Carbon::parse($end_date)->addDay()->format('Y-m-d');

    $period = new DatePeriod(
        new DateTime($start_date),
        new DateInterval('P1D'),
        new DateTime($end_date_for_period)
    );

    $last60Days = Carbon::now()->subDays(60);

    // Build users query
    $attendancesummary = User::with(['attendance_details', 'createdbyname', 'getbranch', 'userinfo'])
        ->where('active', 'Y')
        ->whereDoesntHave('roles', function ($query) {
            $query->whereIn('id', config('constants.customer_roles'));
        })
        ->where('show_attandance_report', '1');

    if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('subAdmin')) {
        $attendancesummary->whereIn('id', getUsersReportingToAuth());
    }

    if ($executive_id) {
        $attendancesummary->where('id', $executive_id);
    }

    if (!empty($designation_ids)) {
        $attendancesummary->whereIn('designation_id', $designation_ids);
    }

    $users = $attendancesummary->limit(4500)->get();
    $allManagerIds = $users->pluck('reportingids')
    ->filter()
    ->flatMap(function ($ids) {
        return array_map('trim', explode(',', $ids));
    })
    ->unique()
    ->values();
    $managers = \App\Models\User::whereIn('id', $allManagerIds)
    ->get()
    ->keyBy('id');
    

    // Build date labels
    $label2 = [];
    foreach ($period as $date) {
        $label2[] = $date->format('j-M-Y');
    }

    // Prepare column headings
    $label1 = [
        'User Id',
        'Employee Code',
        'User Name',
        'Reporting Managers',
    ];

    $label3 = [
        // 'Leave Balance',
        // 'Comp Leave Balance',
        
        // 'Week Off (W/o)',
        // 'Half Day',
        // 'Absent (A)',
        // 'LOP',
        // 'AL',
        // 'Comp Off NP',
        // 'Comp Off A',
        // 'MIS Punch (MIS)',
        // 'Present Week of (PW)',
        // 'Holiday (H)',
        // 'Half Day (1/2P+1/2LOP)',
        // 'Half Day (1/2P+1/AL)',
        // 'Present (P)',
        // 'Present Not Approve (PN)',
        // 'Paid Days',
        // 'TOTAL Days',
            'Week Off (W/o)',
    'Absent (A)',
    'Half Day',
    'Holiday (H)',
    'Present (P)',
    'TOTAL Days',

    ];

    $headings = array_merge($label1, $label2, $label3);

    // Generate data rows
    $data = $users->map(function ($user) use ($period, $last60Days, $holidayRecords, $start_date, $end_date,$managers ) {
        $holidays = $holidayRecords
    ->where('branch', $user->branch_id)
    ->flatMap(function ($holiday) use ($start_date, $end_date) {
        return collect(explode(',', $holiday->holiday_date))
            ->map(fn($d) => trim($d))
            ->filter()
            ->map(function ($date) use ($start_date, $end_date) {
                try {
                    $formatted = Carbon::parse($date)->format('Y-m-d');

                    if ($formatted >= $start_date && $formatted <= $end_date) {
                        return $formatted;
                    }

                    return null;
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter();
    })
    ->unique()
    ->values()
    ->toArray();
        $row = [];
        $totals = [
            'wo' => 0, 'a' => 0, 'lop' => 0, 'mis' => 0, 'pw' => 0,
            'h' => 0, 'hd' => 0, 'p' => 0, 'pn' => 0, 'atte' => 0,
            'al' => 0, 'hdal' => 0, 'co' => 0, 'con' => 0,
        ];

        // Pre-fetch holidays once per user
        // $holidays = Holiday::where('branch', $user->branch_id)
        //     ->pluck('holiday_date')
        //     ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
        //     ->toArray();

        foreach ($period as $date) {
            $totals['atte']++;
            $ymd = $date->format('Y-m-d');
            $dayName = $date->format('l');

            $attendance = Attendance::where('user_id', $user->id)
                ->where('punchin_date', 'like', $ymd . '%')
                ->first();

            if (in_array($ymd, $holidays)) {
                $row[] = 'H';
                $totals['h']++;
                continue;
            }

            if ($attendance) {
                $workingType = $attendance->working_type ?? '';
                $workingTypes = array_map('trim', explode(',', $workingType));

                // ✅ 1. Leave FIRST
                if (array_intersect($workingTypes, ['Full Day Leave', 'First Half Leave', 'Second Half Leave', 'Leave'])) {
                    $row[] = 'Leave';
                    continue;
                }
                if (!empty($attendance->punchin_time) && !empty($attendance->punchout_time)) {
                    // Full attendance → Present
                    $row[] = 'P';
                    $totals['p']++;
                    continue;
                }

                if (!empty($attendance->punchin_time) && empty($attendance->punchout_time)) {
                    // Punchout missing → Miss Punch
                    $row[] = 'MIS';
                    $totals['mis']++;
                    continue;
                }

    $status = (int) $attendance->attendance_status;
    $workingType = $attendance->working_type ?? '';
    $leaveExists = null;

    // Check for leave only when needed
    if (in_array($workingType, ['Second Half Leave', 'First Half Leave', 'Full Day Leave', 'Leave'])) {
        $leaveExists = Leave::where('user_id', $user->id)
            ->whereDate('from_date', '<=', $attendance->punchin_date)
            ->whereDate('to_date', '>=', $attendance->punchin_date)
            ->first();
    }

    // ────────────────────────────────────────────────────────────────
    // Status 0 or 1 → both treated as Present (P) unless special leave
    // ────────────────────────────────────────────────────────────────
    if ($status === 0 || $status === 1) {
        // Special leave types still override to AL / Comp Off / etc.
        if (in_array($workingType, ['Leave', 'Full Day Leave'])) {
            if (($leaveExists?->bal_type ?? '') === 'Comp-off Balance') {
                $row[] = 'Comp Off';
                $totals['co']++;
            } else {
                $row[] = 'AL';
                $totals['al']++;
            }
        } elseif (in_array($workingType, ['Second Half Leave', 'First Half Leave'])) {
            if (($leaveExists?->bal_type ?? '') === 'Comp-off Balance') {
                $row[] = '1/2P+1/2Comp Off';
                $totals['co']++;
            } else {
                $row[] = '1/2P+1/2AL';
                $totals['hdal']++;
            }
        } elseif ($workingType === 'Holiday') {
            $row[] = 'H';
            $totals['h']++;
        } else {
            // All other approved / pending cases → P
            $row[] = 'P';
            $totals['p']++;
        }
    }
    // ────────────────────────────────────────────────────────────────
    // Rejected (status = 2)
    // ────────────────────────────────────────────────────────────────
    elseif ($status === 2) {
        if (in_array($workingType, ['Full Day Leave', 'Leave'])) {
            if (($leaveExists?->bal_type ?? '') === 'Comp-off Balance') {
                $row[] = 'Comp Off N';
                $totals['con']++;
            } else {
                $row[] = 'LOPN';
                $totals['lop']++;
            }
        } elseif (in_array($workingType, ['Second Half Leave', 'First Half Leave'])) {
            if (($leaveExists?->bal_type ?? '') === 'Comp-off Balance') {
                $row[] = '1/2P+1/2Comp Off N';
                $totals['con']++;
            } else {
                $row[] = '1/2P+1/2LOPN';
                $totals['hd']++;
            }
        } else {
            $row[] = 'A';
            $totals['a']++;
        }
    }
    // Unknown / other status
    else {
        $row[] = 'PN';
        $totals['pn']++;
    }
} else {
    $doj = $user->userinfo ? new DateTime($user->userinfo->date_of_joining) : null;
    $today = new DateTime(); // current date

    if ($date > $today) {
        // ✅ Future date → no absent
        $row[] = '-';
        continue;
    }

    if ($dayName === 'Sunday') {
        if ($doj && $doj <= $date) {
            $row[] = 'W/O';
            $totals['wo']++;
        } else {
            $row[] = '-';
        }
    } else {
        if ($doj && $doj <= $date) {
            $row[] = 'A'; // ✅ Absent only for past dates
            $totals['a']++;
        } else {
            $row[] = '-';
        }
    }
            }
        }

        // Comp-off logic (outside the date loop)
        $sundayPunchinCount = CompOffLeave::where('comp_off_date', '>=', $last60Days)
            ->where('is_used', false)
            ->where('user_id', $user->id)
            ->sum('balance');
        // removed: lop, al, con, co, mis, pw, hdal, hd, pn, paid days
        // Add summary columns
        // $row[] = $user->leave_balance ?? '0';
        
        // $row[] = $sundayPunchinCount > 0 ? $sundayPunchinCount : '0';
        // $row[] = (string) $totals['wo'];
        
        // $row[] = (string) $totals['a'];
        // $row[] = (string) ($totals['hd'] + $totals['hdal']);
        // $row[] = (string) $totals['lop'];
        // $row[] = (string) $totals['al'];
        // $row[] = (string) $totals['con'];
        // $row[] = (string) $totals['co'];
        // $row[] = (string) $totals['mis'];
        // $row[] = (string) $totals['pw'];
        // $row[] = (string) $totals['h'];
        // $row[] = (string) $totals['hd'];
        // $row[] = (string) $totals['hdal'];
        // $row[] = (string) $totals['p'];
        // $row[] = (string) $totals['pn'];
        // $row[] = $totals['wo'] + $totals['al'] + $totals['pw'] + $totals['h'] + $totals['hdal'] + $totals['p'];
        // $row[] = (string) $totals['atte'];
        // Summary Columns (ORDER MUST MATCH label3)

$row[] = (string) $totals['wo']; // Week Off
$row[] = (string) $totals['a'];  // Absent
$row[] = (string) ($totals['hd'] + $totals['hdal']); // Half Day
$row[] = (string) $totals['h'];  // Holiday
$row[] = (string) $totals['p'];  // Present

// TOTAL Days (attendance count)
$row[] = (string) $totals['atte'];

$managerNames = [];

$ids = $user->reportingids;

// string → array
if (is_string($ids)) {
    $ids = explode(',', $ids);
}

$ids = $ids ?? [];

foreach ($ids as $id) {
    $id = trim($id);

    $manager = $managers[$id] ?? null;

    if ($manager) {
        $managerNames[] = $manager->name;
    }
}

$reportingManagers = !empty($managerNames)
    ? implode(', ', $managerNames)
    : '-';

        // Basic user info columns
        $basic = [
            $user->id ?? '',
            $user->employee_codes ?? '',
            $user->name ?? '',
            $reportingManagers,
        ];

        return array_merge($basic, $row);
    })->toArray();

//    $holidays = Holiday::whereBetween('holiday_date', [$start_date, $end_date])
//     ->pluck('holiday_date')
//     ->flatMap(function ($dateString) {
//         // Split comma-separated dates, trim whitespace, parse each one
//         return collect(explode(',', $dateString))
//             ->map(fn($d) => trim($d))
//             ->filter() // remove empty entries
//             ->map(function ($singleDate) {
//                 try {
//                     return Carbon::parse($singleDate)->format('Y-m-d');
//                 } catch (\Exception $e) {
//                     // Log bad date if you want
//                     // Log::warning("Invalid holiday date: $singleDate");
//                     return null;
//                 }
//             })
//             ->filter(); // remove failed parses
//     })
//     ->unique()
//     ->values()
//     ->toArray();


    // Export
    return Excel::download(
        new attendanceSummaryDownload($headings, $data, $start_date, $end_date),
        'attendance-summary-' . date('Y-m-d') . '.xlsx'
    );
}







  public function submitAttendances(Request $request)
  {
    // dd($request);
    // try {
      $ipAddress = $request->server('HTTP_X_FORWARDED_FOR') 
      ? explode(',', $request->server('HTTP_X_FORWARDED_FOR'))[0] 
      : $request->ip();
    $accessToken = '4060dae74e438c';
    $location = Location::get($ipAddress);
    // $addressP = getLatLongToAddress($location->latitude, $location->longitude);
        $addressP = '';

    $user = User::find($request['user_id']);
    $branchIds = explode(',', $user->branch_id);

    $punchinDate = Carbon::parse($request['punchin_date'])->format('Y-m-d');
    // dd([
    //     'user_id'          => $request['user_id'],
    //     'punchin_date'     => $request['punchin_date'],
    //     'punchin_time_raw' => $request['punchin_date'],           // full datetime from request
    //     'calculated_time'  => date('G:i', strtotime($request['punchin_date'])),  // what will be saved
    //     'current_server_time' => date('H:i:s'),
    //     'request_all'      => $request->all(),
    // ]);
    $isSunday = Carbon::parse($request['punchin_date'])->isSunday();
    $holidayDates = Holiday::whereIn('branch', $branchIds)
      ->pluck('holiday_date')
      ->map(function ($dateString) {
        return explode(',', $dateString);
      })
      ->collapse()
      ->map('trim')
      ->toArray();
   
    $isHoliday = in_array($punchinDate, $holidayDates);

    if ($isSunday || $isHoliday) {
      $expiryDate = Carbon::parse($request['punchin_date'])->addDays(60);

      CompOffLeave::create([
        'user_id' => $request['user_id'],
        'comp_off_date' => $punchinDate,
        'expiry_date' => $expiryDate,
        'is_used' => false,
      ]);
    }

    if (Attendance::updateOrCreate(['user_id' => $request['user_id'], 'punchin_date' => date('Y-m-d', strtotime($request['punchin_date']))], [
      'user_id' => $request['user_id'],
      'active' => 'Y',
      'punchin_date' => date('Y-m-d', strtotime($request['punchin_date'])),
      // 'punchin_time' => date('G:i', strtotime($request['punchin_date'])),
      'punchin_time' => !empty($request['punchin_time'])
    ? date('H:i', strtotime($request['punchin_time']))
    : date('H:i'),
      'punchin_summary' => !empty($request['punchin_summary']) ? $request['punchin_summary'] : '',
      'punchin_address' => !empty($addressP) ? $addressP : '',
      'working_type' => !empty($request['working_type']) ? $request['working_type'] : '',
      'punchin_from' => 'Web',
      'flag' => 'true',
      'created_at' => getcurentDateTime(),
      'updated_at' => getcurentDateTime(),
    ])) {
      if (!empty($request['tour_id'])) {
        TourProgramme::where('id', '=', $request['tour_id'])->update([
          'type' => $request->working_type ?? ''
        ]);

        dd('updated');

        $cityids = Beat::whereHas('beatschedules', function ($query) use ($request) {
          $query->where('user_id', '=', $request['user_id']);
          $query->whereDate('beat_date', '=', date('Y-m-d', strtotime($request['punchin_date'])));
        })
          ->orderBy('city_id', 'asc')
          ->pluck('city_id');
        $cityids = $cityids->unique();

        //start new

        if (!empty($request['city'])) {

          $city_datas = explode(",", $request['city']);
foreach ($city_datas as $city) {

    TourDetail::updateOrCreate(
        [
            'tour_id' => $request['tour_id'],
            'visited_cityid' => $city,
        ],
        [
            'visited_date' => date('Y-m-d'),
            'last_visited' => date('Y-m-d'),
        ]
    );
}
        }
      }



$user = User::findOrFail($request['user_id']);

$today = Carbon::today();
$joiningDate = $user->date_of_joining
    ? Carbon::parse($user->date_of_joining)
    : null;
    $todayDate = $today->format('Y-m-d');

    $isSunday = $today->isSunday();

    $branchIds = explode(',', $user->branch_id);
$holidayDates = Holiday::whereIn('branch', $branchIds)
    ->pluck('holiday_date')
    ->map(fn ($d) => explode(',', $d))
    ->collapse()
    ->map(fn ($d) => Carbon::parse(trim($d))->format('Y-m-d'))
    ->toArray();

$isHoliday = in_array($todayDate, $holidayDates);


if (!$joiningDate) {
    dd('Joining date missing');
}

if ($isSunday || $isHoliday) {

    $alreadyEarned = CompOffLeave::where('user_id', $user->id)
        ->whereDate('comp_off_date', $today)
        ->exists();

    if (!$alreadyEarned) {

        CompOffLeave::create([
            'user_id'       => $user->id,
            'leave_id'      => null,        // future use
            'comp_off_date' => $today,
            'expiry_date'   => $today->copy()->addDays(60),
            'is_used'       => false,
            'balance'       => 1,
        ]);
    }
}
$expiredCompOffs = CompOffLeave::where('user_id', $user->id)
    ->where('is_used', false)
    ->whereDate('expiry_date', '<', Carbon::today())
    ->get();

if ($expiredCompOffs->count() > 0) {

    CompOffLeave::whereIn('id', $expiredCompOffs->pluck('id'))->delete();
}

$activeCompOffBalance = CompOffLeave::where('user_id', $user->id)
    ->where('is_used', false)
    ->whereDate('expiry_date', '>=', Carbon::today())
    ->sum('balance'); // usually count
$user->update([
    'compb_off' => $activeCompOffBalance
]);


/*
|--------------------------------------------------------------------------
| ACCRUAL START DATE
|--------------------------------------------------------------------------
| If last_leave_accrual_date exists → next day
| Else → joining date
*/

$accrualStartDate = $user->last_leave_accrual_date
    ? Carbon::parse($user->last_leave_accrual_date)->addDay()
    : $joiningDate->copy();

/*
|--------------------------------------------------------------------------
| HOLIDAYS
|--------------------------------------------------------------------------
*/

$branchIds = explode(',', $user->branch_id);

$holidayDates = Holiday::whereIn('branch', $branchIds)
    ->pluck('holiday_date')
    ->map(fn ($d) => explode(',', $d))
    ->collapse()
    ->map(fn ($d) => Carbon::parse(trim($d))->format('Y-m-d'))
    ->toArray();

/*
|--------------------------------------------------------------------------
| COLLECT WORKING DAYS (FROM ACCRUAL START → TODAY)
|--------------------------------------------------------------------------
*/

$workingDays = [];
$current = $accrualStartDate->copy();

while ($current <= $today) {

    $date = $current->format('Y-m-d');

    if (
        !$current->isSunday() &&
        !in_array($date, $holidayDates)
    ) {
        $workingDays[] = $date;
    }

    $current->addDay();
}

/*
|--------------------------------------------------------------------------
| CALCULATE 20-DAY CYCLES
|--------------------------------------------------------------------------
*/

$totalWorkingDays = count($workingDays);
$newCycles = intdiv($totalWorkingDays, 20);

/*
|--------------------------------------------------------------------------
| APPLY LEAVE ACCRUAL (ONLY IF NEW CYCLES FOUND)
|--------------------------------------------------------------------------
*/

$cycleEndDates = [];

if ($newCycles > 0) {

    // Get exact 20th working day dates
    for ($i = 1; $i <= $newCycles; $i++) {
        $cycleEndDates[] = $workingDays[($i * 20) - 1];
    }

    $user->increment('casual_leave_balance',  $newCycles * 0.5);
    $user->increment('sick_leave_balance',    $newCycles * 0.5);
    $user->increment('earned_leave_balance',  $newCycles * 1);

    $user->update([
        'last_leave_accrual_date' => end($cycleEndDates),
    ]);
}

/*
|--------------------------------------------------------------------------
| EARNED LEAVE CLAIMABLE LOGIC (AFTER 1 YEAR)
|--------------------------------------------------------------------------
*/

// 1 year completion date
$earnedLeaveUnlockDate = $joiningDate->copy()->addYear();

// Check if 1 year completed
if (
    $today->greaterThanOrEqualTo($earnedLeaveUnlockDate) &&
    $user->earned_leave_claim_activated_at === null
) {

    // Move all earned leaves to claimable
    $user->update([
        'claimable_earned_leave_balance' => $user->earned_leave_balance,
        'earned_leave_claim_activated_at' => $today,
    ]);
}

/*
|--------------------------------------------------------------------------
| DEBUG OUTPUT
|--------------------------------------------------------------------------
*/

// dd('✅ LEAVE ACCRUAL SUMMARY', [
//     'user_id' => $user->id,
//     'joining_date' => $joiningDate->format('d M Y'),
//     'accrual_start_date' => $accrualStartDate->format('Y-m-d'),

//     'working_days_count' => $totalWorkingDays,
//     'working_day_dates'  => $workingDays,

//     'new_cycles' => $newCycles,
//     'cycle_end_dates' => $cycleEndDates,

//     'updated_balances' => [
//         'casual' => $user->casual_leave_balance,
//         'sick'   => $user->sick_leave_balance,
//         'earned' => $user->earned_leave_balance,
//     ],

//     'last_leave_accrual_date' => $user->last_leave_accrual_date,
//         'earned_leave_unlock_date' => $earnedLeaveUnlockDate->format('d M Y'),
//     'earned_leave_balance' => $user->earned_leave_balance,
//     'claimable_earned_leave_balance' => $user->claimable_earned_leave_balance,
//     'earned_leave_claim_activated_at' => $user->earned_leave_claim_activated_at,
//      'today' => $todayDate,
//     'is_sunday' => $isSunday,
//     'is_holiday' => $isHoliday,
//     'active_comp_off' => $activeCompOffBalance,

// ]);



      return Redirect::to('reports/attendancereport')->with('message_success', 'PunchIn Successfully');
    }
dd(
    'Punch-in time for ' . $user->name,
    $attendance ? $attendance->punchin_time : 'No punch-in record',
    $attendance ? $attendance->toArray() : 'No record found'
);
    return redirect()->back()->with('message_danger', 'Error in Lead Stages')->withInput();
    // } catch (\Exception $e) {
    //   return redirect()->back()->withErrors($e->getMessage())->withInput();
    // }
  }

  public function removePunchout(Request $request)
  {
    try {
      if (Attendance::where('id', '=', $request['id'])->whereDate('punchin_date', '=', date('Y-m-d'))->update([
        'punchout_date' => null,
        'punchout_time' => null,
        'punchout_latitude' => null,
        'punchout_longitude' => null,
        'punchout_address' => '',
        'punchout_image' => '',
        'punchout_summary' => '',
        'worked_time' => '',
        'updated_at' => getcurentDateTime(),
      ])) {
        return response()->json(['status' => 'success', 'message' => 'Punchout Remeoved Successfully'], 200);
      }
      return response()->json(['status' => 'error', 'message' => 'Error in Punchout Remeoved'], 404);
    } catch (\Exception $e) {
      return redirect()->back()->withErrors($e->getMessage())->withInput();
    }
  }
  public function destroy($id)
  {
    ////abort_if(Gate::denies('customer_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    try {
      $attendance_details = Attendance::where('id', '=', $id)->first();
      $attendance = Attendance::whereDate('punchin_date', '=', $attendance_details->punchin_date)->where('id', '=', $id)->first();
      TourProgramme::whereDate('date', '=', $attendance_details->punchin_date)->where('userid', '=', $attendance['user_id'])->update(['type' => '']);
      BeatSchedule::whereDate('beat_date', '=', $attendance_details->punchin_date)->where('user_id', '=', $attendance['user_id'])->delete();
      if ($attendance->delete()) {
        return response()->json(['status' => 'success', 'message' => 'Attendance deleted successfully!']);
      }
      return response()->json(['status' => 'error', 'message' => 'Error in Attendance Delete!']);
    } catch (\Exception $e) {
      return redirect()->back()->withErrors($e->getMessage())->withInput();
    }
  }

  public function approveAttendance(Request $request)
  {
    try {
      $ids = explode(',', $request['id']);
      foreach ($ids as $key => $value) {
        Attendance::where('id', '=', $value)->update([
          'attendance_status' => 1,
          'approve_reject_by' => Auth::user()->id,
          'remark_status' => null
        ]);
      }
      return  response()->json(['status' => 'success', 'message' => 'Attendance Approved Successfully']);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Attendance Not Approved Successfully']);
    }
  }


  public function rejectAttendance(Request $request)
  {
    $remark_status  = $request['remark_status'] ?? null;
    try {
      $id_array = explode(',', $request['attendance_id']);
      foreach ($id_array as $key => $value) {
        Attendance::where('id', '=', $value)->update([
          'attendance_status' => 2,
          'approve_reject_by' => Auth::user()->id,
          'remark_status' => $remark_status ?? null,
        ]);
      }
      return Redirect::to('reports/attendancereport')->with('message_success', 'Attendance Rejected Successfully');
    } catch (\Exception $e) {
      return redirect()->back()->withErrors($e->getMessage())->withInput();
    }
  }

  public function punchoutnow(Request $request)
  {
    try {
      $user = $request->user();
      $validator = Validator::make($request->all(), [
        'id' => 'required|exists:attendances,id',
      ]);
      if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
      }

      $punchout = Attendance::where('id', $request->id)->first();
      $punchout->punchout_date = getcurentDate();
      $punchout->punchout_time = getcurentTime();
      $punchout->punchout_summary = !empty($request['punchout_summary']) ? $request['punchout_summary'] : '';
      $punchout->worked_time = gmdate("H:i:s", strtotime(getcurentDateTime()) - strtotime($punchout->punchin_date . ' ' . $punchout->punchin_time));
      if ($punchout->save()) {
        // $useractivity = array(
        //         'userid' => $user->id, 
        //         'latitude' => $request['punchout_latitude'], 
        //         'longitude' => $request['punchout_longitude'], 
        //         'type' => 'Punchout',
        //         'description' => 'User Logout',
        //     );
        // submitUserActivity($useractivity);
        // $zsmnotify = collect([
        //     'title' => 'Successfully punched out',
        //     'body' =>  $user->name.' has Punched out'
        // ]);
        // sendNotification($user->reportingid,$zsmnotify);
        // $asmnotify = collect([
        //     'title' => 'Successfully punched out',
        //     'body' =>  'You have successfully Punched out'
        // ]);
        // sendNotification($user->id,$asmnotify);
        return response()->json(['status' => 'success', 'message' => 'Punch Out successfully', 'punchout' => $punchout], 200);
      }
      return response()->json(['status' => 'error', 'message' => 'Error in Punch Out'], 404);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }
  

public function getTourAndBeatByUserAndDate(Request $request)
{
    $request->validate([
        'date'    => 'required|date_format:Y-m-d',
        'user_id' => 'required|exists:users,id',
    ]);

    $date    = $request->date;
    $user_id = $request->user_id;

    // ── Tour ────────────────────────────────────────────────────────
    $tour = TourProgramme::where('userid', $user_id)
        ->whereDate('date', $date)
        ->with('cityRelation:id,city_name')     // optimize
        ->select('id', 'objectives', 'town')
        ->first();

    // ── Beats ───────────────────────────────────────────────────────
    $beatSchedules = BeatSchedule::where('user_id', $user_id)
        ->whereDate('beat_date', $date)
        ->with(['beats' => function($q) {
    $q->select('id','beat_name','description','city_id')
      ->with('city:id,city_name');
}])
        // ->with(['beats' => function($q) {
        //     $q->select('id', 'beat_name', 'description', 'city_id');
        // }])
        ->get();

    $beatNames = $beatSchedules
        ->pluck('beats.beat_name')
        ->filter()
        ->unique()
        ->implode(', ');

    $mainBeat = $beatSchedules->first();

    return response()->json([
        'tour' => [
            'exists' => $tour !== null,
            'data'   => $tour ? [
                'id'         => $tour->id,
                'name'       => $tour->objectives ?: 'Tour Plan',
                'objectives' => $tour->objectives ?: '-',
                'city_name'  => $tour->cityRelation?->city_name ?? '-',
            ] : null
        ],
        'beat' => [
            'exists' => $beatSchedules->isNotEmpty(),
            'data'   => $beatSchedules->isNotEmpty() ? [
                'beat_name'   => $beatNames ?: ($mainBeat->beats->beat_name ?? '-'),
                'area_town' => $mainBeat->beats?->city?->city_name ?? '-',
'city_id'   => $mainBeat->beats?->city_id ?? null,
                'description' => $mainBeat->beats->description ?: '-',
            ] : null
        ]
    ]);
}
}