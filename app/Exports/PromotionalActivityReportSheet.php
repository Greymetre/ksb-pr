<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/** One sheet of the Promotional Activity Report workbook. */
class PromotionalActivityReportSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison
{
    public function __construct(private string $title, private array $headings, private array $rows)
    {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last = Coordinate::stringFromColumnIndex(count($this->headings));
                $sheet->getStyle('A1:' . $last . '1')->getFont()->setBold(true);
                $sheet->getStyle('A1:' . $last . '1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE8EEF9');
                $sheet->freezePane('A2');
            },
        ];
    }
}
