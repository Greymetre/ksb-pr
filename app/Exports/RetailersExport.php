<?php

namespace App\Exports;

use App\Models\SecondaryCustomer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RetailersExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = SecondaryCustomer::with(['state', 'city', 'district', 'pincode', 'beat', 'country'])
            ->where('type', 'RETAILER'); // Fixed for Retailers

        // Apply same filters
        if (!empty($this->filters['owner_name'])) {
            $query->where('owner_name', 'like', '%' . $this->filters['owner_name'] . '%');
        }
        if (!empty($this->filters['shop_name'])) {
            $query->where('shop_name', 'like', '%' . $this->filters['shop_name'] . '%');
        }
        if (!empty($this->filters['mobile'])) {
            $query->where('mobile_number', 'like', '%' . $this->filters['mobile'] . '%');
        }
        if (!empty($this->filters['beat_id'])) {
            $query->where('beat_id', $this->filters['beat_id']);
        }
        if (!empty($this->filters['state_id'])) {
            $query->where('state_id', $this->filters['state_id']);
        }
        if (!empty($this->filters['city_id'])) {
            $query->where('city_id', $this->filters['city_id']);
        }
        if (!empty($this->filters['opportunity_status'])) {
            $query->where('opportunity_status', $this->filters['opportunity_status']);
        }
        if (!empty($this->filters['awareness_status'])) {
            $status = $this->filters['awareness_status'] === 'Done' ? 'Done' : 'Not Done';
            $query->where('nistha_awareness_status', $status);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Type',
            'Sub Type',
            'Owner Name',
            'Shop Name',
            'Mobile Number',
            'WhatsApp Number',
            'Vehicle Segment',
            'Address Line',
            'Belt/Area/Market Name',
            'Country',
            'State',
            'District',
            'City',
            'Pincode',
            'Beat',
            'Opportunity Status',
            'Nistha Awareness Status', // Fixed label
            'GPS Location',
            'Created At',
        ];
    }

    public function map($row): array
    {
        $nisthaStatus = $row->nistha_awareness_status ?? 'Not Done';

        return [
            $row->type ?? '-',
            $row->sub_type ?? '-',
            $row->owner_name ?? '-',
            $row->shop_name ?? '-',
            $row->mobile_number ?? '-',
            $row->whatsapp_number ?? '-',
            $row->vehicle_segment ?? '-',
            $row->address_line ?? '-',
            $row->belt_area_market_name ?? '-',
            $row->country?->country_name ?? '-',
            $row->state?->state_name ?? '-',
            $row->district?->district_name ?? '-',
            $row->city?->city_name ?? '-',
            $row->pincode?->pincode ?? '-',
            $row->beat?->beat_name ?? '-',
            $row->opportunity_status ?? '-',
            $nisthaStatus, // Direct Nistha
            $row->gps_location ?? '-',
            $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:S1')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE0E0E0'],
                    ],
                ]);
            },
        ];
    }
}