<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PromotionalActivityExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private $activityQuery) {}
    public function query() { return $this->activityQuery->latest('activity_date'); }
    public function headings(): array
    {
        return ['Request ID', 'Activity Date', 'Activity Type', 'Created By', 'Location', 'Purpose', 'Status', 'Distributor', 'Estimated Budget', 'Company Share', 'Distributor Share', 'Total', 'Actual Expense', 'Over Budget', 'Participants', 'Created At'];
    }
    public function map($row): array
    {
        return [
            $row->request_no ?? '-',
            optional($row->activity_date)->format('d-M-Y'),
            $row->activityType->display_name ?? $row->activityType->status_name ?? '-',
            $row->creator->name ?? '-', $row->location_name, $row->purpose ?? '-', ucwords(str_replace('_', ' ', $row->approval_status)),
            $row->distributor->name ?? '-', $row->estimated_budget !== null ? (float) $row->estimated_budget : '-',
            (float) $row->company_share, (float) $row->distributor_share,
            (float) $row->company_share + (float) $row->distributor_share,
            $row->actual_expense !== null ? (float) $row->actual_expense : '-', $row->isOverBudget() ? 'Yes' : 'No',
            count($row->participants ?: []),
            optional($row->created_at)->format('d-M-Y h:i A'),
        ];
    }
}
