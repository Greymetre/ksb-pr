<?php

namespace App\Exports;

use App\Services\PromotionalActivityReport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Promotional Activity Report workbook:
 *  - "Activities": one row per activity with distributor, materials, budget and approval trail
 *  - "Material Requests": one row per requested material line (POP + sample)
 */
class PromotionalActivityReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(private $query, private PromotionalActivityReport $report)
    {
    }

    public function sheets(): array
    {
        $activities = (clone $this->query)->orderByDesc('activity_date')->orderByDesc('id')->get();

        return [
            new PromotionalActivityReportSheet('Activities', $this->activityHeadings(), $this->activityRows($activities)),
            new PromotionalActivityReportSheet('Material Requests', $this->materialHeadings(), $this->materialRows($activities)),
        ];
    }

    private function activityHeadings(): array
    {
        return [
            'Request ID', 'Activity Date', 'Activity Type', 'Employee Code', 'Created By', 'Designation', 'Zone', 'Branch',
            'Reporting Manager', 'Distributor Code', 'Distributor', 'Location', 'Purpose',
            'POP Materials Requested', 'POP Delivery Address', 'Sample Materials Requested', 'Sample Delivery Address',
            'Estimated Budget', 'Company Share', 'Distributor Share', 'Total Amount', 'Actual Expense', 'Over Budget',
            'Approval Status', 'Approved / Rejected By', 'Approved / Rejected At', 'Approval Remark',
            'Participants', 'Execution Remark', 'Execution Submitted At', 'Reviewed By', 'Reviewed At', 'Review Remark',
            'Remark', 'Created At',
        ];
    }

    private function activityRows($activities): array
    {
        return $activities->map(function ($a) {
            $creator = $a->creator;

            return [
                $a->request_no,
                optional($a->activity_date)->format('d-M-Y'),
                $a->activityType->display_name ?? $a->activityType->status_name ?? '-',
                optional($creator)->employee_codes,
                optional($creator)->name,
                optional(optional($creator)->getdesignation)->designation_name,
                optional(optional($creator)->getdivision)->division_name,
                optional(optional($creator)->getbranch)->branch_name,
                optional($a->reportingManager)->name,
                optional($a->distributor)->customer_code,
                optional($a->distributor)->name,
                $a->location_name,
                $a->purpose,
                $this->report->popMaterials($a) ?: '-',
                $a->pop_delivery_address,
                $this->report->sampleMaterials($a) ?: '-',
                $a->sample_delivery_address,
                $a->estimated_budget !== null ? (float) $a->estimated_budget : null,
                (float) $a->company_share,
                (float) $a->distributor_share,
                $this->report->totalAmount($a),
                $a->actual_expense !== null ? (float) $a->actual_expense : null,
                $a->isOverBudget() ? 'Yes' : 'No',
                PromotionalActivityReport::statusLabel($a->approval_status),
                optional($a->approver)->name,
                optional($a->approved_rejected_at)->format('d-M-Y h:i A'),
                $a->approval_remark,
                count($a->participants ?: []),
                $a->execution_remark,
                optional($a->execution_submitted_at)->format('d-M-Y h:i A'),
                optional($a->reviewer)->name,
                optional($a->reviewed_at)->format('d-M-Y h:i A'),
                $a->review_remark,
                $a->remark,
                optional($a->created_at)->format('d-M-Y h:i A'),
            ];
        })->all();
    }

    private function materialHeadings(): array
    {
        return [
            'Request ID', 'Activity Date', 'Activity Type', 'Employee Code', 'Created By', 'Zone', 'Branch',
            'Distributor', 'Material Type', 'Material', 'Quantity', 'Delivery Address', 'Approval Status',
        ];
    }

    private function materialRows($activities): array
    {
        $rows = [];
        foreach ($activities as $a) {
            foreach ($this->report->materialLines($a) as [$type, $name, $qty]) {
                $rows[] = [
                    $a->request_no,
                    optional($a->activity_date)->format('d-M-Y'),
                    $a->activityType->display_name ?? $a->activityType->status_name ?? '-',
                    optional($a->creator)->employee_codes,
                    optional($a->creator)->name,
                    optional(optional($a->creator)->getdivision)->division_name,
                    optional(optional($a->creator)->getbranch)->branch_name,
                    optional($a->distributor)->name,
                    $type,
                    $name,
                    $qty,
                    $type === 'POP Material' ? $a->pop_delivery_address : $a->sample_delivery_address,
                    PromotionalActivityReport::statusLabel($a->approval_status),
                ];
            }
        }

        return $rows;
    }
}
