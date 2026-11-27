<?php

namespace App\Exports;

use App\Models\UserIssue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class UserIssuesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    private array $branchCache = [];

    public function __construct(private $query)
    {
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Zone', 'Branch', 'E Code', 'Name', 'Designation', 'Issue Date', 'Issue Description', 'Status',
            'Source', 'Issue Create By', 'Solve By', 'Solve Remark', 'Solved At',
        ];
    }

    public function map($issue): array
    {
        $user = $issue->user;

        return [
            optional(optional($user)->getdivision)->division_name,
            $this->branchNames(optional($user)->branch_id),
            optional($user)->employee_codes,
            optional($user)->name,
            optional(optional($user)->getdesignation)->designation_name,
            $issue->issue_date,
            $issue->description,
            UserIssue::STATUSES[$issue->status] ?? $issue->status,
            $issue->source === 'calling' ? 'Calling' : 'Manual',
            optional($issue->createdBy)->name,
            optional($issue->solvedBy)->name,
            $issue->solve_remark,
            optional($issue->solved_at)->format('Y-m-d h:i A'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:M1')->getFont()->setBold(true);
                $sheet->freezePane('A2');
            },
        ];
    }

    private function branchNames($branchIds): ?string
    {
        $key = (string) $branchIds;
        if (!array_key_exists($key, $this->branchCache)) {
            $ids = array_filter(explode(',', $key));
            $this->branchCache[$key] = $ids ? DB::table('branches')->whereIn('id', $ids)->pluck('branch_name')->implode(', ') : null;
        }

        return $this->branchCache[$key];
    }
}
