<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class attendanceSummaryDownload implements 
FromArray, 
    WithHeadings, 
    ShouldAutoSize, 
    WithEvents
{
    protected $headings;
    protected $data;
    protected $startDate;
    protected $endDate;
    protected $holidays;

    public function __construct(array $headings, array $data, ?string $startDate = null, ?string $endDate = null, array $holidays = [])
    {
        $this->headings  = $headings;
        $this->data      = $data;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->holidays  = $holidays;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert 2 title rows
                $sheet->insertNewRowBefore(1, 2);

                // Title row
                $sheet->setCellValue('A1', 'Attendance Summary Sheet');
                $sheet->mergeCells("A1:{$sheet->getHighestColumn()}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE6F0FA'],
                    ],
                ]);

                // Period row
                $periodText = $this->getPeriodText();
                $sheet->setCellValue('A2', $periodText);
                $sheet->mergeCells("A2:{$sheet->getHighestColumn()}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'italic' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(36);
                $sheet->getRowDimension(2)->setRowHeight(26);

                // Get final dimensions
                $lastColumn = $sheet->getHighestColumn();
                $lastRow    = $sheet->getHighestRow();

                // ==============================================
                // Apply THIN BORDERS to ALL cells
                // ==============================================
                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF666666'], // light gray
                        ],
                    ],
                ]);

                // Optional: Make header row (original row 3, now row 3) stand out more
                $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD9EAD3'], // light green
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    // Header borders can be slightly thicker if you want
                    'borders' => [
                        'allBorders' => [
                            // 'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => 'FF666666'],
                        ],
                    ],
                ]);

                // Center align all content
                $sheet->getStyle("A3:{$lastColumn}{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Sunday columns: yellow bg + red text
                $dateColumns = $this->getDateColumnLetters();
                foreach ($dateColumns as $colLetter) {
                    $range = "{$colLetter}4:{$colLetter}{$lastRow}";

                    if ($this->isSundayColumn($colLetter)) {
                        $sheet->getStyle($range)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFFFFF99'],
                            ],
                            'font' => [
                                'color' => ['argb' => 'FFFF0000'],
                            ],
                        ]);
                    }

                    if ($this->isHolidayColumn($colLetter)) {
                        $sheet->getStyle($range)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFADD8E6'],
                            ],
                        ]);
                    }
                }

                // Freeze panes below title
                $sheet->freezePane('A4');
            },
        ];
    }

    private function getPeriodText(): string
    {
        if (empty($this->startDate) || empty($this->endDate)) {
            return 'Current Month';
        }

        try {
            $start = Carbon::parse($this->startDate);
            $end   = Carbon::parse($this->endDate);
            $startFmt = $start->format('F Y');
            $endFmt   = $end->format('F Y');
            return $startFmt === $endFmt ? $startFmt : "$startFmt to $endFmt";
        } catch (\Exception $e) {
            return 'Period Not Specified';
        }
    }

    private function getDateColumnLetters(): array
    {
        $fixed = 3; // User Id, Employee Code, User Name
        $dateCount = count($this->headings) - $fixed - 4; // 4 summary columns now

        $letters = [];
        $colIndex = $fixed + 1; // column D = first date

        for ($i = 0; $i < $dateCount; $i++) {
            $letters[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $colIndex++;
        }

        return $letters;
    }

    private function isSundayColumn(string $colLetter): bool
    {
        $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLetter);
        $headerValue = $this->headings[$colIndex - 1] ?? '';

        try {
            $date = Carbon::createFromFormat('j-M-Y', $headerValue);
            return $date && $date->isSunday();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isHolidayColumn(string $colLetter): bool
    {
        $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLetter);
        $headerValue = $this->headings[$colIndex - 1] ?? '';

        try {
            $dateYmd = Carbon::createFromFormat('j-M-Y', $headerValue)->format('Y-m-d');
            return in_array($dateYmd, $this->holidays);
        } catch (\Exception $e) {
            return false;
        }
    }
}