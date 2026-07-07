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
        $reportUserIds = $this->reportUserIds();

        if ($reportUserIds->isEmpty()) {
            return $this->headerRows();
        }

        $query = MasterDistributor::with(['supervisor.getbranch', 'supervisor.getdivision', 'billingCity', 'city']);

        $this->whereAssignedToUsers($query, $reportUserIds->all());

        $dealerId = $this->filters['dealer_id'] ?? $this->filters['distributor_id'] ?? null;

        if (!empty($dealerId)) {
            $query->where('id', $dealerId);
        }

        $distributors = $query->get();

        if ($distributors->isEmpty()) {
            return $this->headerRows();
        }

        $users = User::with(['getbranch', 'getdivision'])
            ->whereIn('id', $reportUserIds)
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
            ->whereIn('executive_id', $reportUserIds)
            ->when(!empty($this->filters['year']), function ($q) {
                $q->whereYear('order_date', $this->filters['year']);
            })
            ->when(!empty($this->filters['start_date']), function ($q) {
                $q->whereDate('order_date', '>=', $this->filters['start_date']);
            })
            ->when(!empty($this->filters['end_date']), function ($q) {
                $q->whereDate('order_date', '<=', $this->filters['end_date']);
            })
            ->select('seller_id', 'executive_id', 'order_date', 'grand_total')
            ->get()
            ->groupBy('seller_id');

        $preparedRows = $distributors->map(function ($distributor) use ($users, $orders, $reportingUsers, $reportUserIds) {
            $distributorUserIds = collect($this->assignedUserIds($distributor))
                ->push($distributor->supervisor_id)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->intersect($reportUserIds)
                ->values();

            $employeeCollection = $distributorUserIds->map(fn ($id) => $users->get($id))->filter();
            $primaryEmployee = $employeeCollection->first();
            $distributorOrders = $orders->get($distributor->id, collect());
            $monthlyValues = [];

            foreach (array_keys($this->months) as $month) {
                $monthlyValues[$month] = $distributorOrders
                    ->filter(function ($order) use ($month, $distributorUserIds) {
                        return Carbon::parse($order->order_date)->month === $month
                            && $distributorUserIds->contains((int) $order->executive_id);
                    })
                    ->sum('grand_total');
            }

            $zoneName = $primaryEmployee?->getdivision?->division_name
                ?: $distributor->sales_zone
                ?: 'No Branch';

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
                $employeeCodes,
                $employeeNames,
                $reportingNames ?: ($distributor->supervisor ? Str::title($distributor->supervisor->name) : ''),
            ];

            foreach ($monthlyValues as $value) {
                $row[] = ((float) $value == 0.0) ? '0' : round((float) $value, 0);
            }

            $yearTotal = array_sum($monthlyValues);
            $row[] = ((float) $yearTotal == 0.0) ? '0' : round((float) $yearTotal, 0);

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

                $sheet->getStyle("G3:{$highestColumn}{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0;-#,##0;0');

                for ($row = 3; $row <= $highestRow; $row++) {
                    $label = (string) $sheet->getCell('B' . $row)->getValue();

                    if (str_contains($label, 'Grand Total')) {
                        $this->styleTotalRow($sheet, $row, '43A047', true);
                    } elseif (str_contains($label, 'Total')) {
                        $this->styleTotalRow(
                            $sheet,
                            $row,
                            $this->isZoneTotal($label) ? '004a88' : 'FFF59D',
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
            array_merge(array_fill(0, 6, ''), array_values($this->months), ['Total']),
            array_merge([
                'Distributor Code',
                'Distributor Name',
                'Distributor Location',
                'Employees Code',
                'Employees Name',
                'Reporting Manager',
            ], array_fill(0, 13, 'Secondary Val')),
        ]);
    }

    private function totalRow(string $label, array $totals): array
    {
        $monthlyTotals = array_map(
            fn ($value) => ((float) $value == 0.0) ? '0' : round((float) $value, 0),
            array_values($totals)
        );
        $yearTotal = array_sum($totals);

        return array_merge(
            ['', Str::title($label), '', '', '', ''],
            $monthlyTotals,
            [((float) $yearTotal == 0.0) ? '0' : round((float) $yearTotal, 0)]
        );
    }

    private function emptyMonthlyTotals(): array
    {
        return array_fill_keys(array_keys($this->months), 0);
    }

    private function reportUserIds(): Collection
    {
        $query = User::query();

        $designationIds = $this->selectedDesignationIds();

        if (!empty($designationIds)) {
            $query->whereIn('designation_id', $designationIds);
        }

        if (!empty($this->filters['employee_id'])) {
            $query->where('id', $this->filters['employee_id']);
        }

        if (!empty($this->filters['branch_id'])) {
            $query->where('branch_id', $this->filters['branch_id']);
        }

        if (!empty($this->filters['division_id'])) {
            $query->where('division_id', $this->filters['division_id']);
        }

        if (!empty($this->filters['allowed_user_ids'])) {
            $query->whereIn('id', collect($this->filters['allowed_user_ids'])->filter()->all());
        }

        return $query->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
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

    private function selectedDesignationIds(): array
    {
        $designationId = $this->filters['designation_id'] ?? null;

        if (empty($designationId)) {
            return [];
        }

        return collect(is_array($designationId) ? $designationId : [$designationId])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
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

        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray($style);
    }
}
