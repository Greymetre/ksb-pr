<?php

namespace App\Exports;

use App\Services\UserPerformanceReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class UserPerformanceReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStrictNullComparison
{
    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        return app(UserPerformanceReportService::class)->rows($this->filters)->map(function (array $row) {
            return [
                $row['employee_code'], $row['employee_name'], $row['daily_visit_target'], $row['working_days'],
                $row['visit_target'], $row['customers_visited'], $row['adherence'] . ' %', $row['new_counters'],
                $row['cumulative_counters'], $row['secondary_orders_value'], $row['primary_target'],
                $row['primary_achievement'], $row['overdue'], $row['payment_collection'],
                $row['total_payment_dues'], $row['new_dealers'], $row['primary_orders_collected'], $row['zone'],
                $row['branch'], $row['designation'], $row['reporting_manager'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employees Code', 'Employees Name', 'Daily Visit Target', 'Total Working Days', 'Total Customer Visit',
            'Total Customers Visited', 'Adherence %', 'New Counters Added', 'Total Cumulative Counter',
            'Secondary Orders (value)', 'Primary Target', 'Primary Achievement', 'Overdue', 'Payment Collection',
            'Total Payment Dues', 'New Dealer Appointed', 'Primary Orders Collected (Value)', 'ZONE', 'Branch', 'Designation',
            'Reporting Manager',
        ];
    }
}
