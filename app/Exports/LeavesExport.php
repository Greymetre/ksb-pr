<?php

namespace App\Exports;

use App\Models\Leave;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeavesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Leave::with('users')->orderBy('from_date', 'desc');

        // Apply filters if they exist (recommended improvement)
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('from_date', [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        if (!empty($this->filters['executive_id'])) {
            $query->where('user_id', $this->filters['executive_id']);
        }

        return $query->get();
    }

    public function map($leave): array
    {
        // Convert numeric status to readable text
        $statusText = match ($leave->status) {
            1       => 'Approved',
            2       => 'Rejected',
            default => 'Pending',   // 0, null, or any other value
        };

        return [
            $leave->id,
            $leave->users->employee_code ?? $leave->users->id ?? 'N/A',
            $leave->users->name       ?? 'N/A',
            $leave->from_date,
            $leave->to_date,
            $leave->type,
            $leave->bal_type,
            $leave->reason            ?? '-',
            $statusText,                        // ← changed here
            $leave->created_at 
                ? $leave->created_at->format('d-m-Y H:i') 
                : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Code / ID',
            'Employee Name',
            'From Date',
            'To Date',
            'Type',           // ← was "Duration" but you're exporting "type"
            'Balance Type',
            'Reason',
            'Status',
            'Applied On',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'  => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
            ],
        ];
    }
}