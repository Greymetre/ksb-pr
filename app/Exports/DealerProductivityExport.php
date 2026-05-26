<?php

namespace App\Exports;

use App\Models\MasterDistributor;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DealerProductivityExport implements FromCollection, ShouldAutoSize, WithEvents
{
    protected array $filters;
    protected array $months = [];

    public function __construct(array $filters)
    {
        $this->filters = $filters;

        for ($month = 1; $month <= 12; $month++) {
            $this->months[$month] = Carbon::create()->month($month)->format('M');
        }
    }

    public function collection()
    {
        $query = MasterDistributor::with(['supervisor.getbranch', 'supervisor.getdivision', 'billingCity', 'city']);

        if (!empty($this->filters['allowed_user_ids'])) {
            $this->whereAssignedToUsers($query, $this->filters['allowed_user_ids']);
        }

        if (!empty($this->filters['employee_id'])) {
            $this->whereAssignedToUsers($query, [$this->filters['employee_id']]);
        }

        $dealerId = $this->filters['dealer_id'] ?? $this->filters['distributor_id'] ?? null;

        if (!empty($dealerId)) {
            $query->where('id', $dealerId);
        }

        if (!empty($this->filters['designation_id'])) {
            $designationIds = is_array($this->filters['designation_id'])
                ? $this->filters['designation_id']
                : [$this->filters['designation_id']];

            $userIds = User::whereIn('designation_id', array_filter($designationIds))->pluck('id')->all();

            if (!empty($userIds)) {
                $this->whereAssignedToUsers($query, $userIds);
            }
        }

        if (!empty($this->filters['branch_id'])) {
            $userIds = User::where('branch_id', $this->filters['branch_id'])->pluck('id')->all();

            if (!empty($userIds)) {
                $this->whereAssignedToUsers($query, $userIds);
            }
        }

        if (!empty($this->filters['division_id'])) {
            $userIds = User::where('division_id', $this->filters['division_id'])->pluck('id')->all();

            if (!empty($userIds)) {
                $this->whereAssignedToUsers($query, $userIds);
            }
        }

        $distributors = $query->get();

        if ($distributors->isEmpty()) {
            return $this->headerRows();
        }

        $assignedUserIds = $distributors
            ->flatMap(fn ($distributor) => $this->assignedUserIds($distributor))
            ->merge($distributors->pluck('supervisor_id')->filter())
            ->unique()
            ->values();

        $users = User::with(['getbranch', 'getdivision'])
            ->whereIn('id', $assignedUserIds)
            ->get()
            ->keyBy('id');

        $reportingIds = $users
            ->pluck('reportingid')
            ->filter()
            ->flatMap(fn ($ids) => explode(',', $ids))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        $reportingUsers = User::whereIn('id', $reportingIds)->pluck('name', 'id');

        $orders = Order::whereIn('seller_id', $distributors->pluck('id'))
            ->when(!empty($this->filters['year']), function ($q) {
                $q->whereYear('order_date', $this->filters['year']);
            })
            ->when(!empty($this->filters['start_date']), function ($q) {
                $q->whereDate('order_date', '>=', $this->filters['start_date']);
            })
            ->when(!empty($this->filters['end_date']), function ($q) {
                $q->whereDate('order_date', '<=', $this->filters['end_date']);
            })
            ->select('seller_id', 'order_date', 'grand_total')
            ->get()
            ->groupBy('seller_id');

        $preparedRows = $distributors->map(function ($distributor) use ($users, $orders, $reportingUsers) {
            $employeeIds = $this->assignedUserIds($distributor);
            $employeeCollection = collect($employeeIds)->map(fn ($id) => $users->get($id))->filter();
            $primaryEmployee = $employeeCollection->first() ?: $users->get($distributor->supervisor_id);
            $distributorOrders = $orders->get($distributor->id, collect());
            $monthlyValues = [];

            foreach (array_keys($this->months) as $month) {
                $monthlyValues[$month] = $distributorOrders
                    ->filter(fn ($order) => Carbon::parse($order->order_date)->month === $month)
                    ->sum('grand_total');
            }

            $zoneName = $primaryEmployee?->getdivision?->division_name
                ?: $distributor->sales_zone
                ?: 'No Zone';

            $branchName = $primaryEmployee?->getbranch?->branch_name ?: 'No Branch';

            $employeeCodes = $employeeCollection
                ->pluck('employee_codes')
                ->filter()
                ->implode(', ');

            $employeeNames = $employeeCollection
                ->pluck('name')
                ->filter()
                ->map(fn ($name) => Str::title($name))
                ->implode(', ');

            $reportingNames = $employeeCollection
                ->pluck('reportingid')
                ->filter()
                ->flatMap(fn ($ids) => explode(',', $ids))
                ->map(fn ($id) => $reportingUsers->get((int) trim($id)))
                ->filter()
                ->unique()
                ->map(fn ($name) => Str::title($name))
                ->implode(', ');

            $row = [
                $distributor->distributor_code ?? '',
                $distributor->trade_name ?: $distributor->legal_name,
                $this->locationName($distributor),
                $employeeCodes ?: ($primaryEmployee->employee_codes ?? ''),
                $employeeNames ?: ($primaryEmployee ? Str::title($primaryEmployee->name) : ''),
                $reportingNames ?: ($distributor->supervisor ? Str::title($distributor->supervisor->name) : ''),
            ];

            foreach ($monthlyValues as $value) {
                $row[] = ((float) $value == 0.0) ? '0' : round((float) $value, 2);
            }

            return [
                'zone' => $zoneName,
                'branch' => $branchName,
                'sort' => sprintf(
                    '%02d_%s_%s_%s',
                    $this->zoneSortOrder($zoneName),
                    strtolower($zoneName),
                    strtolower($branchName),
                    strtolower((string) ($distributor->trade_name ?: $distributor->legal_name))
                ),
                'row' => $row,
                'monthly' => $monthlyValues,
            ];
        })->sortBy('sort')->values();

        return $this->headerRows()->merge($this->withTotals($preparedRows));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getFont()
                    ->setName('Calibri')
                    ->setSize(9);

                $sheet->getStyle("A1:{$highestColumn}2")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E88E5'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("G3:R{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00;-#,##0.00;0');

                for ($row = 3; $row <= $highestRow; $row++) {
                    $label = (string) $sheet->getCell('B' . $row)->getValue();

                    if (str_contains($label, 'Grand Total')) {
                        $this->styleTotalRow($sheet, $row, '43A047', true);
                    } elseif (str_contains($label, 'Total')) {
                        $this->styleTotalRow(
                            $sheet,
                            $row,
                            $this->isZoneTotal($label) ? 'E53935' : 'FFF59D',
                            $this->isZoneTotal($label)
                        );
                    }
                }

                $sheet->freezePane('A3');
                $sheet->getRowDimension(1)->setRowHeight(18);
                $sheet->getRowDimension(2)->setRowHeight(20);
            },
        ];
    }

    private function withTotals(Collection $preparedRows): Collection
    {
        $rows = collect();
        $currentBranch = null;
        $currentZone = null;
        $branchTotals = $this->emptyMonthlyTotals();
        $zoneTotals = $this->emptyMonthlyTotals();
        $grandTotals = $this->emptyMonthlyTotals();

        foreach ($preparedRows as $prepared) {
            if ($currentZone !== null && $currentZone !== $prepared['zone']) {
                $rows->push($this->totalRow($currentBranch . ' Total', $branchTotals));
                $rows->push($this->totalRow($currentZone . ' Total', $zoneTotals));
                $branchTotals = $this->emptyMonthlyTotals();
                $zoneTotals = $this->emptyMonthlyTotals();
            } elseif ($currentBranch !== null && $currentBranch !== $prepared['branch']) {
                $rows->push($this->totalRow($currentBranch . ' Total', $branchTotals));
                $branchTotals = $this->emptyMonthlyTotals();
            }

            $currentZone = $prepared['zone'];
            $currentBranch = $prepared['branch'];
            $rows->push($prepared['row']);

            foreach ($prepared['monthly'] as $month => $value) {
                $branchTotals[$month] += $value;
                $zoneTotals[$month] += $value;
                $grandTotals[$month] += $value;
            }
        }

        if ($currentBranch !== null) {
            $rows->push($this->totalRow($currentBranch . ' Total', $branchTotals));
            $rows->push($this->totalRow($currentZone . ' Total', $zoneTotals));
            $rows->push($this->totalRow('Grand Total', $grandTotals));
        }

        return $rows;
    }

    private function headerRows(): Collection
    {
        return collect([
            array_merge(array_fill(0, 6, ''), array_values($this->months)),
            array_merge([
                'Distributor Code',
                'Distributor Name',
                'Distributor Location',
                'Employees Code',
                'Employees Name',
                'Reporting Manager',
            ], array_fill(0, 12, 'Secondary Val')),
        ]);
    }

    private function totalRow(string $label, array $totals): array
    {
        return array_merge(['', Str::title($label), '', '', '', ''], array_map(
            fn ($value) => ((float) $value == 0.0) ? '0' : round((float) $value, 2),
            array_values($totals)
        ));
    }

    private function emptyMonthlyTotals(): array
    {
        return array_fill_keys(array_keys($this->months), 0);
    }

    private function assignedUserIds(MasterDistributor $distributor): array
    {
        $ids = $distributor->sales_executive_id;

        if (is_string($ids)) {
            $decodedIds = json_decode($ids, true);
            $ids = json_last_error() === JSON_ERROR_NONE ? $decodedIds : explode(',', $ids);
        }

        if (!is_array($ids)) {
            $ids = $ids ? [$ids] : [];
        }

        return collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
    }

    private function whereAssignedToUsers(Builder $query, $userIds): void
    {
        $userIds = collect($userIds)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        $query->where(function ($q) use ($userIds) {
            $q->whereIn('supervisor_id', $userIds);

            foreach ($userIds as $userId) {
                $q->orWhereJsonContains('sales_executive_id', $userId)
                    ->orWhereJsonContains('sales_executive_id', (string) $userId);
            }
        });
    }

    private function locationName(MasterDistributor $distributor): string
    {
        return $distributor->billingCity?->city_name
            ?: $distributor->city?->city_name
            ?: (string) ($distributor->billing_city ?: $distributor->city_id ?: '');
    }

    private function zoneSortOrder(?string $divisionName): int
    {
        $divisionName = strtolower(trim((string) $divisionName));

        $zones = [
            1 => ['north', 'norrth'],
            2 => ['east'],
            3 => ['west'],
            4 => ['south'],
        ];

        foreach ($zones as $order => $aliases) {
            foreach ($aliases as $alias) {
                if ($divisionName === $alias || preg_match('/\b' . preg_quote($alias, '/') . '\b/', $divisionName)) {
                    return $order;
                }
            }
        }

        return 99;
    }

    private function isZoneTotal(string $label): bool
    {
        return $this->zoneSortOrder(str_replace(' Total', '', $label)) !== 99;
    }

    private function styleTotalRow($sheet, int $row, string $color, bool $whiteFont = false): void
    {
        $style = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $color],
            ],
        ];

        if ($whiteFont) {
            $style['font']['color'] = ['rgb' => 'FFFFFF'];
        }

        $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray($style);
    }
}
