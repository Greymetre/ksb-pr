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
        return ['Activity Date', 'Activity Type', 'Created By', 'Location', 'Status', 'Distributor', 'Company Share', 'Distributor Share', 'Total', 'Participants', 'Created At'];
    }
    public function map($row): array
    {
        return [
            optional($row->activity_date)->format('d-M-Y'),
            $row->activityType->display_name ?? $row->activityType->status_name ?? '-',
            $row->creator->name ?? '-', $row->location_name, ucfirst($row->approval_status),
            $row->distributor->name ?? '-', (float) $row->company_share, (float) $row->distributor_share,
            (float) $row->company_share + (float) $row->distributor_share, count($row->participants ?: []),
            optional($row->created_at)->format('d-M-Y h:i A'),
        ];
    }
}
