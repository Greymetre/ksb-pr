<?php

    // namespace App\Exports;

    // use App\Models\TourProgramme;
    // use App\Models\User;
    // use Maatwebsite\Excel\Concerns\FromCollection;
    // use Maatwebsite\Excel\Concerns\WithHeadings;
    // use Maatwebsite\Excel\Concerns\ShouldAutoSize;
    // use Maatwebsite\Excel\Concerns\WithEvents;
    // use Maatwebsite\Excel\Events\AfterSheet;
    // use Maatwebsite\Excel\Concerns\WithMapping;
    // use Illuminate\Support\Facades\Auth;
    // use Illuminate\Support\Facades\DB;



    // class TourExport implements FromCollection,WithHeadings,ShouldAutoSize,WithMapping
    // {
    //     public function __construct($request)
    //     {
            
    //         $this->userids = getUsersReportingToAuth();

    //         $this->user_id = $request->input('executive_id');
    //         $this->division_id = $request->input('division_id');
    //         $this->start_date = $request->input('start_date');
    //         $this->end_date = $request->input('end_date');

    //     }

        // public function collection()
        // {     
        //     // return TourProgramme::with('tourdetails','userinfo')->where(function ($query)  {
        //     //                         if(!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin'))

        //     //                         {
        //     //                             $query->whereIn('executive_id', $this->userids);
        //     //                         }
        //     //                     })->select('id','date', 'userid', 'town', 'objectives', 'type', 'status')->latest()->get();

        //     if(!empty($this->user_id) ||!empty($this->start_date) ||!empty($this->end_date)){
        //         return TourProgramme::with('tourdetails','userinfo')->where(function ($query)  {
        //                             if($this->user_id)
        //                             {
        //                                 $query->where('userid', $this->user_id);
        //                             }
        //                             if($this->division_id)
        //                             {
        //                                 $userIds = User::where('division_id', $this->division_id)->pluck('id');
        //                                 $query->whereIn('userid', $userIds);
        //                             }
        //                             if($this->start_date)
        //                             {
        //                                 $query->whereDate('date','>=',$this->start_date);
        //                             }
        //                             if($this->end_date)
        //                             {
        //                                 $query->whereDate('date','<=',$this->end_date);
        //                             }
        //                         })
        //                     ->select('id','date', 'userid', 'town','district', 'objectives', 'type', 'status')
        //                     //->latest()->get(); 
        //                     ->orderBy(DB::raw('YEAR(date)'), 'DESC')->orderBy(DB::raw('DATE(date)'), 'ASC')->get();    

        //     }else{

        //         return TourProgramme::with('tourdetails','userinfo')->where(function ($query)  {
        //                             if(!empty($this->division_id)){
        //                                 $userIds = User::where('division_id', $request['division_id'])->pluck('id');
        //                                 $query->whereIn('executive_id', $userIds);
        //                             }elseif(!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin'))
        //                             {
        //                                 $query->whereIn('executive_id', $this->userids);
        //                             }
        //                         })->select('id','date', 'userid', 'town','district', 'objectives', 'type', 'status')
        //                     //->latest()->get();
        //                     ->orderBy(DB::raw('YEAR(date)'), 'DESC')->orderBy(DB::raw('DATE(date)'), 'ASC')->get();   

        //     }



        // }
//         public function collection()
// {
//     $data = TourProgramme::with([
//         'tourdetails.visitedcities.districtname',
//         'userinfo.getbranch',
//         'userinfo.getdesignation',
//         'cityRelation',
//         'districtRelation'
//     ])
//     ->select('id','date','userid','town','district','objectives','type','status')
//     ->get();

//     return $data->flatMap(function ($tour) {

//         // agar koi tourdetails nahi hai
//         if ($tour->tourdetails->isEmpty()) {
//             return [[
//                 'tour' => $tour,
//                 'city' => '',
//                 'visited_cityid ' => null,
//                 'district'=> '',
//             ]];
//         }

//         // unique cities
//         return $tour->tourdetails
//             ->unique('visited_cityid')
//             ->map(function ($detail) use ($tour) {
//                 return [
//                     'tour' => $tour,
//                     'city' => $detail->visitedcities->city_name ?? '',
//                     'visited_cityid ' => $detail->visited_cityid  ?? null, 
//                     'district' => $detail->visitedcities->districtname->district_name ?? ''
//                 ];
//             });
//     });
// }

//         public function headings(): array
//         {
//             return ['Date',
//             'Branch',
//             // 'id',
//             'Employee Code',
//             // 'userid',
//             'Employee Name',
//             'Designation',
//             'District', 'Town','objectives','Actual District','Actual Town','Actual objectives','Deviation','Approval Status','Reporting Manager',
//             //  'type', 'city_id',  'last_visited','Division', 'Actual',
//             ];
//         }

//         public function map($data): array
//         {
//             $cityname = '';
//             $cityid = '';
//             $visited_date = '';
//             $visited_cityid = '';
//             $visited_cityname = '';
//             $last_visited = '';
//             if($data->status == '0'){
//                 $status = "Pending";
//             }elseif($data->status == '1'){
//                 $status = "Approved";
//             }else{
//                 $status = "Rejected";
//             }
//             // if(!empty($data['tourdetails']))
//             // {
//             //     foreach ($data['tourdetails'] as $key => $detail) {


//             //         $rowcityname = isset($detail['cityname']['city_name']) ? $detail['cityname']['city_name'].' , ' : '';
//             //         $rowcityid = isset($detail['city_id']) ? $detail['city_id'].' , ' : '';
//             //         $rowvisited_date = isset($detail['visited_date']) ? $detail['visited_date'].' , ' : '';
//             //         $rowvisited_cityid = isset($detail['visited_cityid']) ? $detail['visited_cityid'].' , ' : '';
//             //         $rowvisited_cityname = isset($detail['visitedcities']['city_name']) ? $detail['visitedcities']['city_name'].' , ' : '';
//             //         $rowlast_visited = isset($detail['last_visited']) ? $detail['last_visited'].' , ' : '';
//             //         $cityname = $cityname.' '.$rowcityname;
//             //         $cityid = $cityid.' '.$rowcityid;
//             //         $visited_date = $visited_date.' '.$rowvisited_date;
//             //         $visited_cityname = $visited_cityname.' '.$rowvisited_cityname;
//             //         // $visited_cityid = $visited_cityid.' '.$rowvisited_cityid;
//             //         $visited_cityname = $visited_cityname.' '.$rowvisited_cityname;
//             //         $last_visited = $last_visited.' '.$rowlast_visited;
                    
//             //     }
//             // }

//             $visitedCitiesArray = [];

// foreach ($data['tourdetails'] as $detail) {
//     if (!empty($detail['visitedcities']['city_name'])) {
//         $visitedCitiesArray[] = $detail['visitedcities']['city_name'];
//     }
// }

// // remove duplicates
// $visitedCitiesArray = array_unique($visitedCitiesArray);

// // convert to string
// $visited_cityname = implode(', ', $visitedCitiesArray);

//             $reportingManagerName = '—';

//         if (!empty($data->userinfo?->reportingid)) {
//             $manager = User::select('name')
//                 ->where('id', $data->userinfo->reportingid)
//                 ->first();
                
//             $reportingManagerName = $manager?->name ?? '—';
//         }

// //         $baseCity = '';

// // if($data->userinfo && $data->userinfo->latitude && $data->userinfo->longitude){
// //     $baseCity = getLatLongToCity(
// //         $data->userinfo->latitude,
// //         $data->userinfo->longitude
// //     );
// // }

// // $baseCity = 'Not set';
// // if(!empty($data->userinfo?->latitude) && !empty($data->userinfo?->longitude)){
// //     $baseCity = getLatLongToCity($data->userinfo->latitude, $data->userinfo->longitude);
// // }


// $attendance = \App\Models\Attendance::where('user_id',$data->userid)
//         ->whereDate('punchin_date',$data->date)
//         ->first();

// $actualCity = '';

// if($attendance && $attendance->punchin_latitude && $attendance->punchin_longitude){
//     $actualCity = getLatLongToCity(
//         $attendance->punchin_latitude,
//         $attendance->punchin_longitude
//     );
// }


// // $distance = null; // numeric
// // if(!empty($attendance)){
// //     if(!empty($attendance->punchin_latitude) && !empty($attendance->punchin_longitude)){
// //         $actualCity = getLatLongToCity($attendance->punchin_latitude, $attendance->punchin_longitude);

// //         if(!empty($data->userinfo->latitude) && !empty($data->userinfo->longitude)){
// //             $distance = getRoadDistance(
// //                 $data->userinfo->latitude,
// //                 $data->userinfo->longitude,
// //                 $attendance->punchin_latitude,
// //                 $attendance->punchin_longitude
// //             ); // just number, no 'KM'
// //         }
// //     }
// // }

// // dd([
// //     'user_base_lat' => $data->userinfo->latitude,
// //     'user_base_long' => $data->userinfo->longitude,
// //     'punchin_lat' => $attendance->punchin_latitude ?? 'Not set',
// //     'punchin_long' => $attendance->punchin_longitude ?? 'Not set'
// // ]);
        
//             return [
//                 isset($data['date']) ? date("d-m-Y", strtotime($data['date'])) :'',
//                 // $data['id'],
//                 isset($data['userinfo']['getbranch']['branch_name']) ? $data['userinfo']['getbranch']['branch_name'] :'',

//                 isset($data['userinfo']['employee_codes']) ? $data['userinfo']['employee_codes'] :'',

//                 // isset($data['userid']) ? $data['userid'] :'',
//                 isset($data['userinfo']['name']) ? $data['userinfo']['name'] : '',
//                 isset($data['userinfo']['getdesignation']['designation_name']) ? $data['userinfo']['getdesignation']['designation_name'] :'',
                
//                 $data->districtRelation?->district_name ?? $data->districtRelation ?? '',           // District name
//                 $data->cityRelation?->city_name ?? $data->town ?? '',
//                 // isset($visited_cityname) ? $visited_cityname :'',
//                 isset($data['objectives']) ? $data['objectives'] :'',
//                 '-',
//                 $visited_cityname,
//                 // $visited_cityid,    // Punchin City
//                 '-',
//                 $data->cityRelation?->city_name ==$actualCity ? "No" :"Yes",
//                 $status,
//                 $reportingManagerName,
//                 // ($data->userinfo && $data->userinfo->latitude && $data->userinfo->longitude)
//                 //         ? number_format($data->userinfo->latitude, 4) . ', ' . number_format($data->userinfo->longitude, 4)
//                 //         : 'Not set',
//                 // $baseCity,     // Base City
                
//                 // isset($data['type']) ? $data['type'] :'',

//                 // $cityid,
                
//                 // $distance,
//                 // $visited_date,
//                 // $visited_cityid,
//                 // $last_visited,
                
//                 // isset($data['userinfo']['getdepartment']['division_name']) ? $data['userinfo']['getdepartment']['division_name'] :'',
                
//             ];
//         }


// public function map($row): array
// {
//     $data = $row['tour'];
//     $city = $row['city'];
//     $actualCityId  = $row['visited_cityid '];
//     $actualDistrict = $row['district'];

//     if($data->status == '0'){
//         $status = "Pending";
//     }elseif($data->status == '1'){
//         $status = "Approved";
//     }else{
//         $status = "Rejected";
//     }

//     $attendance = \App\Models\Attendance::where('user_id',$data->userid)
//         ->whereDate('punchin_date',$data->date)
//         ->first();

//     $actualCity = '';

//     if($attendance && $attendance->punchin_latitude && $attendance->punchin_longitude){
//         $actualCity = getLatLongToCity(
//             $attendance->punchin_latitude,
//             $attendance->punchin_longitude
//         );
//     }
// $plannedTownId = $data->town; // tour table ka town (planned)

// $deviation = '-';

// if (!empty($plannedTownId) && !empty($actualCityId)) {
//     $deviation = ($plannedTownId == $actualCityId) ? 'No' : 'Yes';
// }

//     $reportingManagerName = '—';
//     if (!empty($data->userinfo?->reportingid)) {
//         $manager = User::select('name')
//             ->where('id', $data->userinfo->reportingid)
//             ->first();

//         $reportingManagerName = $manager?->name ?? '—';
//     }

//     return [
//         date("d-m-Y", strtotime($data->date)),
//         $data->userinfo->getbranch->branch_name ?? '-',
//         $data->userinfo->employee_codes ?? '-',
//         $data->userinfo->name ?? '-',
//         $data->userinfo->getdesignation->designation_name ?? '-',
//         $data->districtRelation->district_name ?? '-',
//         $data->cityRelation->city_name ?? '-',
//         $data->objectives ?? '-',
//         $actualDistrict ?? '-',
//         $city, // ✅ single city per row
//         $data->type ?? '-',
//         $deviation, 

//         $status ?? '-',
//         $reportingManagerName ?? '-',
//     ];
// }
//     }




namespace App\Exports;

use App\Models\TourProgramme;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TourExport implements 
    FromCollection, 
    WithHeadings, 
    ShouldAutoSize, 
    WithMapping, 
    WithEvents
{
    protected $reportingUsers;
    public function __construct($request)
    {
        $this->userids     = getUsersReportingToAuth();
        $this->user_id     = $request->input('executive_id');
        $this->division_id = $request->input('division_id');
        $this->designation_id = $request->input('designation_id'); 
        $this->start_date  = $request->input('start_date');
        $this->end_date    = $request->input('end_date');
        $this->reportingUsers = User::pluck('name', 'id');
    }

    public function collection()
    {
        $tours = TourProgramme::with([
            'tourdetails.visitedcities.districtname',
            'userinfo.getbranch',
            'userinfo.getdesignation',
            'cityRelation',
            'districtRelation'
        ])
        ->select('id', 'date', 'userid', 'town', 'district', 'objectives', 'type', 'status')
        ->when($this->user_id, fn($q) => $q->where('userid', $this->user_id))
        ->when($this->division_id, function ($q) {
            $userIds = User::where('division_id', $this->division_id)->pluck('id');
            $q->whereIn('userid', $userIds);
        })
        ->when($this->designation_id, function ($q) {
            $q->whereHas('userinfo', function ($query) {
                $query->whereIn('designation_id', $this->designation_id);
            });
        })
        ->when($this->start_date, fn($q) => $q->whereDate('date', '>=', $this->start_date))
        ->when($this->end_date,   fn($q) => $q->whereDate('date', '<=', $this->end_date))
        ->orderByRaw('YEAR(date) DESC, DATE(date) ASC')
        ->get();

        return $tours->flatMap(function ($tour) {
            if ($tour->tourdetails->isEmpty()) {
                return [[
                    'tour'            => $tour,
                    'actual_city'     => '',
                    'actual_district' => '',
                    'visited_cityid'  => null,
                ]];
            }

            return $tour->tourdetails
                ->unique('visited_cityid')
                ->map(function ($detail) use ($tour) {
                    return [
                        'tour'            => $tour,
                        'actual_city'     => $detail->visitedcities->city_name ?? '',
                        'actual_district' => $detail->visitedcities->districtname->district_name ?? '',
                        'visited_cityid'  => $detail->visited_cityid ?? null,
                    ];
                });
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Branch',
            'Employee Code',
            'Employee Name',
            'Designation',
            'Planned District',
            'Planned Town',
            'Objectives',
            'Actual District',
            'Actual Town',
            'Type',
            'Deviation',
            'Approval Status',
            'Reporting Manager',
        ];
    }

    public function map($row): array
    {
        $tour = $row['tour'];
        $actualCity = $row['actual_city'] ?? '';
        $actualDistrict = $row['actual_district'] ?? '';
        $actualCityId = $row['visited_cityid'];

        // Status
        $status = match ((string) $tour->status) {
            '0' => 'Pending',
            '1' => 'Approved',
            default => 'Rejected',
        };

        // Actual Punch-in City from Attendance
        $attendance = \App\Models\Attendance::where('user_id', $tour->userid)
            ->whereDate('punchin_date', $tour->date)
            ->first();

        // if ($attendance && $attendance->punchin_latitude && $attendance->punchin_longitude) {
        //     $actualPunchinCity = getLatLongToCity($attendance->punchin_latitude, $attendance->punchin_longitude);
        // } else {
        //     $actualPunchinCity = '';
        // }

        // Deviation
        $plannedTownId = $tour->town;
        $deviation = '-';
        if (!empty($plannedTownId) && $actualCityId !== null) {
            $deviation = ($plannedTownId == $actualCityId) ? 'No' : 'Yes';
        }

        // Reporting Manager


        // $reportingManager = '—';
        // if (!empty($tour->userinfo?->reportingid)) {
        //     $manager = User::select('name')->where('id', $tour->userinfo->reportingid)->first();
        //     $reportingManager = $manager?->name ?? '—';
        // }

        $reportingManager = '—';

        $employee = $tour->userinfo;

        if ($employee && !empty($employee->reportingid)) {

            $ids = explode(',', $employee->reportingid);

            $reportingManager = collect($ids)
                ->map(function ($id) {
                    $id = (int) trim($id);
                    return $this->reportingUsers->get($id);
                })
                ->filter()
                ->implode(', ');
        }

        return [
            date('d-m-Y', strtotime($tour->date ?? now())),
            $tour->userinfo->getbranch->branch_name ?? '-',
            $tour->userinfo->employee_codes ?? '-',
            $tour->userinfo->name ?? '-',
            $tour->userinfo->getdesignation->designation_name ?? '-',
            $tour->districtRelation?->district_name ?? '-',
            $tour->cityRelation?->city_name ?? $tour->town ?? '-',
            !empty($tour->objectives) ? $tour->objectives : '-',
            $actualDistrict ?: '-',           // Show '-' if empty
            $actualCity ?: '-',               // Show '-' if empty
            !empty($tour->type) ? $tour->type : '-',
            $deviation,
            $status,
            $reportingManager,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'N';
                $headerRange = "A1:{$lastColumn}1";
                $dataRange = "A1:{$lastColumn}" . $sheet->getHighestRow();

                // Header Style
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E3A8A'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(32);

                // Freeze Header
                $sheet->freezePane('A2');

                // Borders
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'A0A0A0'],
                        ],
                    ],
                ]);

                // Center some columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('L:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Deviation
                $sheet->getStyle('M:M')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status

                // Wrap text
                $sheet->getStyle('H:H')->getAlignment()->setWrapText(true); // Objectives
                $sheet->getStyle('J:J')->getAlignment()->setWrapText(true); // Actual Town
            },
        ];
    }
}