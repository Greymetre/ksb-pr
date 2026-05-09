<?php

namespace App\Exports;

use App\Models\MasterDistributor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MasterDistributorsExport implements FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles,
    WithColumnFormatting,
    ShouldAutoSize
{
    protected $distributors;
    protected $employees;
   

    public function __construct($distributors, $isTemplate = false)
    {
        $this->distributors = $distributors;
        $allIds = collect($distributors)
        ->pluck('sales_executive_id')
        ->filter()
        ->flatMap(function ($item) {
            if (is_array($item)) {
                return $item;
            }
            return json_decode($item, true) ?? [];
        })
        ->unique()
        ->values();

    // 👉 Fetch all employees once
        $this->employees = \App\Models\User::whereIn('id', $allIds)
        ->get()
        ->keyBy('id');

    $reportingIds = $this->employees
        ->pluck('reportingid')
        ->filter()
        ->flatMap(function ($ids) {
            return explode(',', $ids);
        })
        ->map(fn($id) => (int) trim($id))
        ->unique()
        ->values();

    $this->reportingUsers = \App\Models\User::whereIn('id', $reportingIds)
        ->pluck('name', 'id'); // [id => name]
       
    }

    public function collection()
    {
       

        return $this->distributors;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Distributor Code',
            'Legal Name',
            'Trade Name',
            // 'Category',
            'Business Status',
            'Business Start Date',
            // 'Shop Image',
            // 'Profile Image',
            'Contact Person',
            // 'Designation',
            'Mobile',
            'Alternate Mobile',
            'Email',
            // 'Secondary Email', 
            'Billing Address',
            'Billing City',
            'Billing City ID',
            'Billing District',
            'Billing District ID',
            'Billing State',
            'Billing State ID',
            'Billing Country',
            'Billing Country ID',
            'Billing Pincode',
            'Billing Pincode ID',
            'Shipping Address',
            // 'Shipping City',
            // 'Shipping District',
            // 'Shipping State',
            // 'Shipping Country',
            // 'Shipping Pincode',
            // 'Sales Zone',
            // 'Area Territory',
            'Beat Route',
            'Beat ID',
            // 'Market Classification',
            // 'Competitor Brands',
            'GST Number',
            'PAN Number',
            'Registration Type',
            // 'Documents',
            // 'Bank Name',
            // 'Account Holder',
            // 'Account Number',
            // 'IFSC',
            // 'Branch Name',
            // 'Credit Limit',
            // 'Credit Days',
            // 'Avg Monthly Purchase',
            // 'Outstanding Balance',
            // 'Preferred Payment Method',
            // 'Cancelled Cheque',
            // 'Monthly Sales',
            // 'Product Categories',
            // 'Secondary Sales Required',
            // 'Last 12 Months Sales',
            'Sales Executive ID (JSON)',
            'Supervisor ID',
            'Customer Segment',
            // 'Weekly TAI Alert',
            // 'Target vs Achievement',
            // 'Schemes Updates',
            // 'New Launch Update',
            // 'Payment Alert',
            // 'Pending Orders',
            // 'Inventory Status',
            // 'Turnover',
            // 'Staff Strength',
            // 'Vehicles Capacity',
            // 'Area Coverage',
            // 'Other Brands Handled',
            // 'Warehouse Size',
            'Employee Names',
            'Employee Codes',
            'Reporting Managers',
            'Created At',
            'Updated At',
        ];
    }

    public function map($distributor): array
    {
        $employeeNames = [];
$employeeCodes = [];
$reportingManagers = [];

$ids = $distributor->sales_executive_id;

// Handle both array & string
if (is_string($ids)) {
    $ids = json_decode($ids, true);
}

$ids = $ids ?? [];

foreach ($ids as $id) {
    $emp = $this->employees[$id] ?? null;

     if ($emp && !empty($emp->reportingid)) {

        $managerIds = explode(',', $emp->reportingid);

        foreach ($managerIds as $mid) {
            $mid = (int) trim($mid);

            if (isset($this->reportingUsers[$mid])) {
                $reportingManagers[] = $this->reportingUsers[$mid];
            }
        }
    }

    if ($emp) {
        $employeeNames[] = $emp->name ?? '-';
        $employeeCodes[] = $emp->employee_codes ?? '-';
    }
}

$reportingManagers = array_unique($reportingManagers);
        return [
            $distributor->id,
            $distributor->distributor_code,
            $distributor->legal_name,
            $distributor->trade_name,
            // $distributor->category,
            $distributor->business_status,
            $distributor->business_start_date,
            // $distributor->shop_image ? 'Yes' : 'No',
            // $distributor->profile_image ? 'Yes' : 'No',
            $distributor->contact_person,
            // $distributor->designation,
            $distributor->mobile,
            $distributor->alternate_mobile,
            $distributor->email,
            // $distributor->secondary_email,
            $distributor->billing_address,
\App\Models\City::find($distributor->billing_city)?->city_name,
            $distributor->billing_city,
\App\Models\District::find($distributor->billing_district)?->district_name,
            $distributor->billing_district,
\App\Models\State::find($distributor->billing_state)?->state_name,
            $distributor->billing_state,
\App\Models\Country::find($distributor->billing_country)?->country_name,
            $distributor->billing_country,
\App\Models\Pincode::find($distributor->billing_pincode)?->pincode,
            $distributor->billing_pincode,
            
            $distributor->shipping_address,
            // $distributor->shipping_city,
            // $distributor->shipping_district,
            // $distributor->shipping_state,
            // $distributor->shipping_country,
            // $distributor->shipping_pincode,
            // $distributor->sales_zone,
            // $distributor->area_territory,
            $distributor->beat_route,
            $distributor->beat_id ?? null,
            // $distributor->market_classification,
            // $distributor->competitor_brands,
            $distributor->gst_number,
            $distributor->pan_number,
            $distributor->registration_type,
            // $distributor->documents ? 'Yes (Multiple)' : 'No',
            // $distributor->bank_name,
            // $distributor->account_holder,
            // $distributor->account_number,
            // $distributor->ifsc,
            // $distributor->branch_name,
            // $distributor->credit_limit,
            // $distributor->credit_days,
            // $distributor->avg_monthly_purchase,
            // $distributor->outstanding_balance,
            // $distributor->preferred_payment_method,
            // $distributor->cancelled_cheque ? 'Yes' : 'No',
            // $distributor->monthly_sales,
            // $distributor->product_categories,
            // $distributor->secondary_sales_required,
            // $distributor->last_12_months_sales,
            $distributor->sales_executive_id, // JSON stored hai
            $distributor->supervisor_id,
            $distributor->customer_segment,
            // $distributor->weekly_tai_alert,
            // $distributor->target_vs_achievement,
            // $distributor->schemes_updates,
            // $distributor->new_launch_update,
            // $distributor->payment_alert,
            // $distributor->pending_orders,
            // $distributor->inventory_status,
            // $distributor->turnover,
            // $distributor->staff_strength,
            // $distributor->vehicles_capacity,
            // $distributor->area_coverage,
            // $distributor->other_brands_handled,
            // $distributor->warehouse_size,
            implode(', ', $employeeNames) ?: '-',   // ✅ Employee Names
            implode(', ', $employeeCodes) ?: '-', 
            implode(', ', $reportingManagers) ?: '-',
            $distributor->created_at?->format('d-m-Y H:i'),
            $distributor->updated_at?->format('d-m-Y H:i'),
        ];
    }
    public function columnFormats(): array
    {
        return [
            'F'  => NumberFormat::FORMAT_DATE_DDMMYYYY,     // Business Start Date
            // 'AB' => NumberFormat::FORMAT_DATE_DATETIME,     // Created At
            // 'AC' => NumberFormat::FORMAT_DATE_DATETIME,     // Updated At

            
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [  // Header Row
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}