<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class SecondaryCustomersTemplateExport implements 
    FromCollection, 
    WithHeadings, 
    ShouldAutoSize,
    WithEvents
{
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Blank rows (sirf 1 empty row daal rahe hain example ke liye)
     */
    public function collection()
    {
        return new Collection([
            // Ek blank row example ke liye (optional)
            array_fill(0, 19, ''), 
        ]);
    }

    /**
     * Headers – bilkul wohi jo final export mein hain
     */
    public function headings(): array
    {
        return [
            'Type',
            'Sub Type',
            'Owner Name*',
            'Shop Name*',
            'Mobile Number*',
            'WhatsApp Number',
            'Vehicle Segment',
            'Address Line*',
            'Belt/Area/Market Name',
            'Country',
            'State',
            'District',
            'City',
            'Pincode',
            'Beat',
            'Opportunity Status*', // HOT, WARM, COLD, LOST
            'Awareness Status*',   // Done / Not Done
            'GPS Location',
            'Created At', // User ise blank chhod de
        ];
    }

    /**
     * Styling: Headers bold + background color
     */
    public function registerEvents(): array
    {
        return [
        AfterSheet::class => function(AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();

            // Headers style
            $sheet->getStyle('A1:S1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
            ]);

            $awarenessLabel = in_array($this->type, ['RETAILER', 'WORKSHOP']) ? 'Nistha' : 'Saathi';

            // Instructions below headers (row 3 se start)
            $sheet->setCellValue('A3', 'Instructions:');
            $sheet->setCellValue('A4', '1. Required fields marked with *');
            $sheet->setCellValue('A5', "2. Opportunity Status: HOT, WARM, COLD, LOST");
            $sheet->setCellValue('A6', "3. {$awarenessLabel} Awareness Status: Done / Not Done");
            $sheet->setCellValue('A7', '4. Type column must be: ' . strtoupper($this->type));
            if ($this->type === 'MECHANIC') {
                $sheet->setCellValue('A8', '5. Sub Type is required for Mechanic');
            }

            // Bold instructions
            $sheet->getStyle('A3:A8')->getFont()->setBold(true);
            $sheet->getStyle('A3:A8')->getFont()->setSize(11);
            $sheet->getStyle('A3:A8')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF0F0F0');
        },
    ];
    
    }
}