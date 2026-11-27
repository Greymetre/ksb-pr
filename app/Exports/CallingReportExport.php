<?php

namespace App\Exports;

use App\Models\UserCallingRemark;
use App\Services\UserCallingSummary;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * One row per saved calling remark (employee + date) in the date range,
 * with the same day summary the calling popup shows. The registration
 * columns follow the active types in the Customer Type master.
 */
class CallingReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents, WithStrictNullComparison
{
    private UserCallingSummary $summary;

    public function __construct(private string $startDate, private string $endDate)
    {
        $this->summary = app(UserCallingSummary::class);
    }

    public function headings(): array
    {
        $typeColumns = $this->summary->customerTypes()
            ->map(fn ($type) => 'Total ' . $type->customertype_name . ' Registered')
            ->all();

        return array_merge([
            'Date', 'Zone', 'Branch', 'Employee Code', 'Employee Name', 'Designation', 'Head Quarters',
            'Date of Joining', 'Punchin Time', 'Punchout Time', 'Total Working Time', 'Working Type',
            'First Customer Checkin Time', 'Last Customer Checkout Time', 'Total Visit', 'Total Order',
        ], $typeColumns, [
            'Calling Remark', 'Called By', 'Called At',
        ]);
    }

    public function array(): array
    {
        $auth = Auth::user();
        $isAdmin = $auth->hasRole('superadmin') || $auth->hasRole('Admin') || $auth->hasRole('Sub_Admin');

        $remarks = UserCallingRemark::with(['user.getdivision', 'user.getdesignation', 'user.userinfo', 'createdBy'])
            ->whereDate('call_date', '>=', $this->startDate)
            ->whereDate('call_date', '<=', $this->endDate)
            ->when(!$isAdmin, fn ($q) => $q->whereIn('user_id', getUsersReportingToAuth()))
            ->orderByDesc('call_date')
            ->orderBy('user_id')
            ->get();

        $rows = [];
        foreach ($remarks as $remark) {
            if (!$remark->user) {
                continue;
            }
            $d = $this->summary->forUser($remark->user, date('Y-m-d', strtotime($remark->call_date)));
            $rows[] = array_merge([
                $d['date'], $d['zone'], $d['branch'], $d['employee_code'], $d['employee_name'], $d['designation'],
                $d['head_quarter'], $d['date_of_joining'], $d['punchin_time'], $d['punchout_time'], $d['worked_time'],
                $d['working_type'], $d['first_checkin_time'], $d['last_checkout_time'], $d['total_visit'],
                $d['total_order'],
            ], array_column($d['registrations'], 'count'), [
                $remark->calling_remark,
                optional($remark->createdBy)->name,
                optional($remark->updated_at)->format('Y-m-d h:i A'),
            ]);
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
                $sheet->freezePane('A2');
            },
        ];
    }
}
