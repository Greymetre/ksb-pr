<?php

namespace App\Exports;

use App\Models\SecondaryCustomer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SecondaryCustomersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents
{
    protected $filters;
    protected $type;
    protected $userIds;

    public function __construct(array $filters, string $type, $userIds = [])
    {
        $this->filters = $filters;
        $this->type = $type;
        $this->userIds = $userIds;
    }

    public function collection()
    {
        $query = SecondaryCustomer::select([
            'id', 'type', 'owner_name', 'shop_name', 'mobile_number',
            'address_line', 'belt_area_market_name',
            'country_id', 'state_id', 'district_id', 'city_id', 'pincode_id', 'beat_id',
            'gps_location', 'gmap', 'distributor_name', 'gst_number', 'pan_number',
            'bank_account_type', 'bank_account_number', 'bank_name', 'ifsc_code',
            'account_holder_name', 'status', 'active', 'agri_distributor',
            'created_by', 'employee_id', 'created_at', 'remark', 'approve_reject_by',

            // Photo & Attachment fields
            'owner_photo',
            'shop_photo',
            'gst_attachment',
            'pan_attachment',
        ])
        ->with([
            'country:id,country_name',
            'state:id,state_name',
            'district:id,district_name',
            'city:id,city_name',
            'pincode:id,pincode',
            'beat:id,beat_name',
            'distributor:id,legal_name,distributor_code',
            'agriDistributor:id,legal_name,distributor_code',
            'approvedBy:id,name',
            'creator:id,name',
        ])
        ->where('type', $this->type);

        if (!empty($this->userIds)) {
            $query->whereIn('created_by', $this->userIds);
        }

        // === Filters (unchanged) ===
        if (!empty($this->filters['owner_name'])) {
            $query->where('owner_name', 'like', '%' . $this->filters['owner_name'] . '%');
        }
        if (!empty($this->filters['shop_name'])) {
            $query->where('shop_name', 'like', '%' . $this->filters['shop_name'] . '%');
        }
        if (!empty($this->filters['mobile'])) {
            $query->where('mobile_number', 'like', '%' . $this->filters['mobile'] . '%');
        }
        if (!empty($this->filters['beat_id'])) $query->where('beat_id', $this->filters['beat_id']);
        if (!empty($this->filters['state_id'])) $query->where('state_id', $this->filters['state_id']);
        if (!empty($this->filters['city_id'])) $query->where('city_id', $this->filters['city_id']);
        if (!empty($this->filters['status'])) $query->where('status', $this->filters['status']);
        if (!empty($this->filters['active'])) $query->where('active', $this->filters['active']);

        // Date Filters
        if (!empty($this->filters['start_date'])) {
            try {
                $start = Carbon::parse($this->filters['start_date'])->startOfDay();
                $query->where('created_at', '>=', $start);
            } catch (\Exception $e) {
                try {
                    $start = Carbon::createFromFormat('d-m-Y', $this->filters['start_date'])->startOfDay();
                    $query->where('created_at', '>=', $start);
                } catch (\Exception $e2) {}
            }
        }
        if (!empty($this->filters['end_date'])) {
            try {
                $end = Carbon::parse($this->filters['end_date'])->endOfDay();
                $query->where('created_at', '<=', $end);
            } catch (\Exception $e) {
                try {
                    $end = Carbon::createFromFormat('d-m-Y', $this->filters['end_date'])->endOfDay();
                    $query->where('created_at', '<=', $end);
                } catch (\Exception $e2) {}
            }
        }

        if (!empty($this->filters['designation_id'])) {
            $designationIds = (array) $this->filters['designation_id'];
            $query->whereRaw("
                EXISTS (
                    SELECT 1 FROM users 
                    WHERE FIND_IN_SET(users.id, secondary_customers.employee_id)
                    AND users.designation_id IN (" . implode(',', $designationIds) . ")
                )
            ");
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Type', 'Approval Status', 'Employee Names', 'Branch Name', 'Shop Name', 
            'Owner Name', 'Mobile Number-1', 'Mobile Number-2', 'Mobile Number-3', 
            'Mobile Number-4', 'Mobile Number-5', 'Domestic Distributor Name', 
            'Distributor Code', 'Agri Distributor', 'Distributor Code', 'Address', 
            'Belt/Area/Market Name', 'Country', 'Country ID', 'State', 'State ID', 
            'District', 'District ID', 'City', 'City ID', 'Pincode', 'Pincode ID', 
            'Beat', 'Beat ID', 'Gst Number', 'Pan Number', 'Bank Account Type', 
            'Bank Account Number', 'Bank Name', 'IFSC Code', 'Account Holder Name', 
            'Active Status', 'GPS Location', 'Google Map', 'Created Date', 
            'Employee Designations', 'Created By', 'Approved/Rejected By', 
            'Rejected Reason', 'Retailer ID', 'Domestic Distributor ID', 
            'Agri Distributor ID', 'Employee Codes', 'Reporting Managers',

            // New Columns
            'Owner Photo',
            'Shop Photo',
            'GST Attachment',
            'PAN Attachment',
            'Zone',
        ];
    }

    public function map($row): array
    {
        $mobiles = $row->getMobileNumbersAttribute() ?? [];

        $employeeNames = [];
        $employeeCodes = [];
        $employeeDesignations = [];
        $reportingManagerNames = [];
        $employeeZones = [];

        if (!empty($row->employee_id)) {
            $employeeIds = array_filter(array_map('trim', explode(',', $row->employee_id)));
            $employees = \App\Models\User::whereIn('id', $employeeIds)
                ->with(['getbranch', 'getdesignation'])
                ->get();

            foreach ($employees as $emp) {
                $employeeNames[] = Str::title($emp->name ?? '-');
                $employeeCodes[] = $emp->employee_codes ?? '-';
                $employeeDesignations[] = Str::title($emp->getdesignation?->designation_name ?? '-');
                $employeeZones[] = Str::title($emp->getdivision?->division_name ?? '-');

                if (!empty($emp->reportingid)) {
                    $managerIds = array_filter(array_map('trim', explode(',', $emp->reportingid)));
                    $managers = \App\Models\User::whereIn('id', $managerIds)->pluck('name');
                    foreach ($managers as $name) {
                        $reportingManagerNames[] = Str::title($name);
                    }
                }
            }
        }

        return [
            $row->type ?? '-',
            $row->status ?? '-',
            implode(', ', $employeeNames) ?: '-',
            Str::title($row->employee?->getbranch?->branch_name ?? '-'),
            Str::title($row->shop_name ?? '-'),
            Str::title($row->owner_name ?? '-'),
            $mobiles[0] ?? '-', $mobiles[1] ?? '-', $mobiles[2] ?? '-', 
            $mobiles[3] ?? '-', $mobiles[4] ?? '-',
            Str::title($row->distributor?->legal_name ?? '-'),
            $row->distributor?->distributor_code ?? '-',
            Str::title($row->agriDistributor?->legal_name ?? '-'),
            $row->agriDistributor?->distributor_code ?? '-',
            Str::title($row->address_line ?? '-'),
            Str::title($row->belt_area_market_name ?? '-'),
            Str::title($row->country?->country_name ?? '-'),
            $row->country_id ?? '-',
            Str::title($row->state?->state_name ?? '-'),
            $row->state_id ?? '-',
            Str::title($row->district?->district_name ?? '-'),
            $row->district_id ?? '-',
            Str::title($row->city?->city_name ?? '-'),
            $row->city_id ?? '-',
            $row->pincode?->pincode ?? '-',
            $row->pincode_id ?? '-',
            Str::title($row->beat?->beat_name ?? '-'),
            $row->beat_id ?? '-',
            $row->gst_number ?? '-',
            $row->pan_number ?? '-',
            Str::title($row->bank_account_type ?? '-'),
            $row->bank_account_number ?? '-',
            Str::title($row->bank_name ?? '-'),
            $row->ifsc_code ?? '-',
            Str::title($row->account_holder_name ?? '-'),
            $row->active === 'Y' ? 'Active' : 'Inactive',
            $row->gps_location ?? '-',
            $row->gmap ?? '-',
            $row->created_at ? $row->created_at->format('d-m-Y') : '-',
            implode(', ', $employeeDesignations) ?: '-',
            Str::title($row->creator?->name ?? '-'),
            Str::title($row->approvedBy?->name ?? '-'),
            $row->remark ?? '-',
            $row->id ?? '-',
            $row->distributor_name ?? '-',
            $row->agri_distributor ?? '-',
            implode(', ', $employeeCodes) ?: '-',
            implode(', ', $reportingManagerNames) ?: '-',

            // Hyperlinks
            $this->getHyperlink($row->owner_photo, 'View Owner Photo'),
            $this->getHyperlink($row->shop_photo, 'View Shop Photo'),
            $this->getHyperlink($row->gst_attachment, 'View GST Attachment'),
            $this->getHyperlink($row->pan_attachment, 'View PAN Attachment'),
            implode(', ', $employeeZones) ?: '-',
        ];
    }

    /**
     * Generate Hyperlink based on your actual URL structure
     */
    private function getHyperlink(?string $path, string $displayText): string
    {
        if (empty($path)) {
            return '-';
        }

        // Remove leading slash if present
        $path = ltrim($path, '/');

        // Full URL matching your domain
        $fullUrl = 'https://ksb-pr.fieldkonnect.in/public/storage/' . $path;

        return '=HYPERLINK("' . $fullUrl . '", "' . $displayText . '")';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:AY1')->applyFromArray([
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