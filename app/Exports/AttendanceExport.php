<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Leave;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;


class AttendanceExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithEvents
{
    public function __construct($request)
    {
        $this->start_date = $request->start_date;
        $this->end_date = $request->end_date;
        $this->executive_id = $request->executive_id;
        $this->active = $request->active;
        $this->status = $request->status;
        $this->division_id = $request->division_id;
        $this->type = $request->type;
           $this->branch_id = is_array($request->branch_id) 
        ? $request->branch_id 
        : ($request->branch_id ? [$request->branch_id] : []);
        $this->designation_id = is_array($request->designation_id)
        ? $request->designation_id
        : ($request->designation_id ? [$request->designation_id] : []);
        $this->reportingUsers = User::pluck('name', 'id'); 
        
    }
    

    public function collection()
    {
        return Attendance::with('users', 'approveReject')->where(function ($query) {

            if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
                $query->whereIn('user_id', getUsersReportingToAuth());
            }
            if ($this->start_date) {
                $query->whereDate('punchin_date', '>=', $this->start_date);
            }
            if ($this->end_date) {
                $query->whereDate('punchin_date', '<=', $this->end_date);
            }
            if ($this->executive_id) {
                $query->where('user_id', $this->executive_id);
            }
            if ($this->status != null && $this->status != "") {
                $query->where('attendance_status', $this->status);
            }
            if (!empty($this->active) && $this->active != null && $this->active != "") {
                $active = $this->active;
                $query->whereHas('users', function ($query) use ($active) {
                    $query->where('active', $active);
                });
            }
            if (!empty($this->division_id) && $this->division_id != null && $this->division_id != "") {
                $division_id = $this->division_id;
                $query->whereHas('users', function ($query) use ($division_id) {
                    $query->where('division_id', $division_id);
                });
            }
            if (!empty($this->branch_id)) {
                $query->whereHas('users', function ($q) {
                    $q->whereIn('branch_id', $this->branch_id);
                });
            }
            if (!empty($this->type)) {

                if ($this->type == 'leave') {
                    $query->whereIn('working_type', [
                        'Full Day Leave',
                        'First Half Leave',
                        'Second Half Leave'
                    ]);
                }

                if ($this->type == 'attendance') {
                    $query->where(function ($q) {
                        $q->whereNull('working_type')
                        ->orWhereNotIn('working_type', [
                            'Full Day Leave',
                            'First Half Leave',
                            'Second Half Leave'
                        ]);
                    });
                }
            }

            if (!empty($this->designation_id)) {
                $query->whereHas('users', function ($q) {
                    $q->whereIn('designation_id', $this->designation_id);
                });
            }

            

        })
            ->select('id', 'user_id', 'punchin_date', 'punchin_time', 'punchin_address', 'punchout_date', 'punchout_time', 'punchout_address', 'worked_time', 'punchin_summary', 'punchout_summary', 'punchin_longitude', 'punchin_latitude', 'punchout_latitude', 'punchout_longitude', 'working_type', 'attendance_status', 'remark_status', 'attendance_status','punchin_from', 'approve_reject_by')
            ->limit(5000)->latest()->get();
    }

    public function headings(): array
    {
        if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('Admin')) {
            return ['id', 'Employee Code', 'Employee Name', 'Designation', 'Branch',
             'Division', 'Reporting Manager',
             'Punchin Date', 'Punchin Time', 'Punchout Time', 'Worked Time','Status', 'Objective', 'Attendance Status', 'Remark Status', 'Punchin Address', 'Punchout Address',
            //   'Punchin Summary',
            //  'punchin_longitude', 'punchin_latitude', 'punchout_longitude', 'punchout_latitude', 
             'From', 'Approve/Reject By'];
        } else {
            return ['id', 'Employee Code', 'Employee Name', 'Designation', 'Branch',
             'Division', 'Reporting Manager',
             'Punchin Date', 'Punchin Time', 'Punchout Time', 'Worked Time','Status', 'Objective', 'Attendance Status', 'Remark Status', 'Punchin Address', 'Punchout Address',
            //   'Punchin Summary',
            //  'punchin_longitude', 'punchin_latitude', 'punchout_longitude', 'punchout_latitude'
             ];
        }
    }

    private function getAttendanceLabel($worked_time, $punchout_time, $working_type)
{
    $workingTypes = array_map('trim', explode(',', $working_type));

    if (array_intersect($workingTypes, ['Full Day Leave', 'First Half Leave', 'Second Half Leave'])) {
        return 'Leave';
    }
    if (!$punchout_time) {
        return 'Misspunch';
    }


    if (!$worked_time) {
        return 'Absent';
    }

    // HH:MM:SS → minutes
    $time = explode(':', $worked_time);
    $minutes = ($time[0] * 60) + ($time[1] ?? 0);

    if ($minutes >= 510) { // 8:30 hrs = 510 min
        return 'Full Day';
    } elseif ($minutes >= 270) { // 4:30 hrs = 270 min
        return 'Half Day';
    } else {
        return 'Absent';
    }
}

    public function map($data): array
    {

        $status = '';
        if ($data['attendance_status'] == '0') {
            $status = 'Pending';
        } elseif ($data['attendance_status'] == '1') {
            $status = 'Approved';
        } else {
            $status = 'Rejected';
        }
        if(in_array($data['working_type'], ['Full Day Leave', 'Second Half Leave','First Half Leave'])) {
            $leave_details = Leave::where('user_id', $data['user_id'])->where('from_date', '<=', $data['punchin_date'])->where('to_date', '>=', $data['punchin_date'])->first();
        }

        $reportingManagers = '—';

        if (!empty($data['users']['reportingid'])) {

            $ids = explode(',', $data['users']['reportingid']);

            $names = collect($ids)
                ->map(function ($id) {
                    $id = (int) trim($id);
                    return $this->reportingUsers[$id] ?? null;
                })
                ->filter()
                ->unique()
                ->values();

            $reportingManagers = $names->implode(', ') ?: '—';
        }

        if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('Admin')) {
            return [
                $data['id'],
                isset($data['users']['employee_codes']) ? $data['users']['employee_codes'] : '',
                Str::title(isset($data['users']['name']) ? $data['users']['name'] : ''),
                isset($data['users']['getdesignation']['designation_name']) ? $data['users']['getdesignation']['designation_name'] : '',
                isset($data['users']['getbranch']['branch_name']) ? $data['users']['getbranch']['branch_name'] : '',
                isset($data['users']['getdivision']['division_name']) ? $data['users']['getdivision']['division_name'] : '',
                $reportingManagers,
                $data['punchin_date'],
                $data['punchin_time'],
                isset($data['punchout_time']) ? $data['punchout_time'] : 'misspunch',
                $data['worked_time'],
                $attendanceLabel = $this->getAttendanceLabel(
                    $data['worked_time'], 
                    $data['punchout_time'], 
                    $data['working_type']
                ),
                Str::title(isset($data['working_type']) ? $data['working_type'] . (isset($leave_details) && $leave_details ? ' - ' . $leave_details['bal_type'] : '') : ''),
                $status,
                Str::title($data['remark_status']),

                $data['punchin_address'],

                $data['punchout_address'],
                // $data['punchin_summary'],
                // isset($data['punchin_longitude']) ? $data['punchin_longitude'] : '',
                // isset($data['punchin_latitude']) ? $data['punchin_latitude'] : '',
                // isset($data['punchout_longitude']) ? $data['punchout_longitude'] : '',
                // isset($data['punchout_latitude']) ? $data['punchout_latitude'] : '',
                $data['punchin_from'],
                isset($data['approveReject']) ? $data['approveReject']['name'] : '',
            ];
        } else {
            return [
                $data['id'],
                isset($data['users']['employee_codes']) ? $data['users']['employee_codes'] : '',
                isset($data['users']['name']) ? $data['users']['name'] : '',
                isset($data['users']['getdesignation']['designation_name']) ? $data['users']['getdesignation']['designation_name'] : '',
                isset($data['users']['getbranch']['branch_name']) ? $data['users']['getbranch']['branch_name'] : '',
                isset($data['users']['getdivision']['division_name']) ? $data['users']['getdivision']['division_name'] : '',
                $reportingManagers,
                $data['punchin_date'],
                $data['punchin_time'],
                isset($data['punchout_time']) ? $data['punchout_time'] : 'misspunch',
                $attendanceLabel = $this->getAttendanceLabel($data['worked_time'], $data['punchout_time']),
                $data['worked_time'],
                isset($data['working_type']) ? $data['working_type'] . (isset($leave_details) && $leave_details ? ' - ' . $leave_details['bal_type'] : '') : '',
                $status,
                $data['remark_status'],

                $data['punchin_address'],

                $data['punchout_address'],
                $data['punchin_summary'],
                // isset($data['punchin_longitude']) ? $data['punchin_longitude'] : '',
                // isset($data['punchin_latitude']) ? $data['punchin_latitude'] : '',
                // isset($data['punchout_longitude']) ? $data['punchout_longitude'] : '',
                // isset($data['punchout_latitude']) ? $data['punchout_latitude'] : '',

            ];
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestDataRow();
                $lastColumn = $sheet->getHighestDataColumn();
                $statusColumn = 'J';
                $firstRowRange = 'A1:' . $lastColumn . '1';
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getStyle($firstRowRange)->getAlignment()->setWrapText(true);
                $sheet->getStyle($firstRowRange)->getFont()->setSize(14);

                $event->sheet->getStyle($firstRowRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '00aadb'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                $event->sheet->getStyle('A1:' . $lastColumn . '' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                ]);

                for ($row = 2; $row <= $lastRow; $row++) {
        $cell = $statusColumn . $row;
        $value = $sheet->getCell($cell)->getValue();

        if ($value == 'Full Day') {
            $sheet->getStyle($cell)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '00FF00'], // Green
                ],
            ]);
        } elseif ($value == 'Half Day') {
            $sheet->getStyle($cell)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00'], // Yellow
                ],
            ]);
        } elseif ($value == 'Absent') {
            $sheet->getStyle($cell)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF0000'], // Red
                ],
            ]);
        } elseif ($value == 'Misspunch') {
            $sheet->getStyle($cell)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFA500'], // Orange (optional)
                ],
            ]);
        }
    }
            },
        ];
    }
}
