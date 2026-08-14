<?php

namespace App\Exports;

use App\Models\DealerAppointment;
use App\Models\NewDealerTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NewDealerTargetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    private ?string $search;
    private ?int $zoneId;
    private ?string $month;

    public function __construct(Request $request)
    {
        $this->search = $request->filled('search') ? trim($request->input('search')) : null;
        $this->zoneId = $request->filled('zone_id') ? (int) $request->input('zone_id') : null;
        $this->month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->input('month'))
            ? $request->input('month')
            : null;
    }

    public function collection()
    {
        return NewDealerTarget::query()
            ->with(['user.getdivision'])
            ->when($this->search, function ($query) {
                $search = $this->search;
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where(function ($nameQuery) use ($search) {
                        $nameQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_codes', 'like', "%{$search}%");
                    });
                });
            })
            ->when($this->zoneId, function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->where('division_id', $this->zoneId);
                });
            })
            ->when($this->month, function ($query) {
                $month = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
                $query->whereDate('target_month', $month->toDateString());
            })
            ->latest('target_month')
            ->latest('id')
            ->get()
            ->map(function (NewDealerTarget $target) {
                if ($target->achievement === null) {
                    $month = Carbon::parse($target->target_month);
                    $target->achievement = DealerAppointment::query()
                        ->where('created_by', $target->user_id)
                        ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                        ->count();
                }

                return $target;
            });
    }

    public function headings(): array
    {
        return [
            'Emp Code',
            'Emp Name',
            'Zone',
            'New Dealer Plan Nos',
            'Achievement Nos',
            'Month',
            'Note',
        ];
    }

    public function map($target): array
    {
        $user = $target->user;

        return [
            $user->employee_codes ?? '-',
            $user ? ($user->name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : '-',
            optional(optional($user)->getdivision)->division_name ?? '-',
            $target->target,
            $target->achievement,
            $target->target_month->format('M Y'),
            $target->note ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(1, $sheet->getHighestRow());

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:G{$lastRow}");
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['ARGB' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['ARGB' => 'FF123D60']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['ARGB' => 'FF315187']]],
                ]);
                if ($lastRow > 1) {
                    $sheet->getStyle("A2:G{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                    $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setWrapText(true);
                }
                $sheet->getColumnDimension('G')->setWidth(36);
            },
        ];
    }
}
