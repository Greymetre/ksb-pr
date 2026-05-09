<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MasterDistributorsTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Empty collection → no data rows
        return collect([]);
    }

    public function headings(): array
    {
        return [
            // 'id,

            'Distributor Code',	'Legal Name',	'Trade Name',	'Business Status'	,'Business Start Date'	,'Contact Person',	'Mobile	Alternate Mobile'	,'Email	Billing Address'	,'Billing City',	'Billing City Id',	'Billing District',	'Billing District Id',	'Billing State',	'Billing State Id'	,'Billing Country'	,'Billing Country Id',	'Billing Pincode',	'Billing Pincode Id',	'Shipping Address'	,'Beat Route',	'Beat Id'	,'GST Number',	'PAN Number',	'Registration Type',	'Product Categories',	'Sales Executive ID (JSON)'
            // 'distributor_code',
            // 'legal_name',
            // 'trade_name',
            // 'category',


            // 'business_status',
            // 'business_start_date',
            // 'shop_image',
            // 'profile_image',
            // 'contact_person',


            // 'designation',
            // 'mobile',
            // 'alternate_mobile',
            // 'email',
            // 'secondary_email',


            // 'billing_address',
            // 'billing_city',
            // 'billing_district',
            // 'billing_state',
            // 'billing_country',


            // 'billing_pincode',
            // 'shipping_address',
            // 'shipping_city',
            // 'shipping_district',
            // 'shipping_state',


            // 'shipping_country',
            // 'shipping_pincode',
            // 'sales_zone',
            // 'area_territory',
            // 'beat_route',


            // 'market_classification',
            // 'competitor_brands',
            // 'gst_number',
            // 'pan_number',
            // 'registration_type',


            // 'documents',
            // 'bank_name',
            // 'account_holder',
            // 'account_number',
            // 'ifsc',


            // 'branch_name',
            // 'credit_limit',
            // 'credit_days',
            // 'avg_monthly_purchase',
            // 'outstanding_balance',


            // 'preferred_payment_method',
            // 'cancelled_cheque',
            // 'monthly_sales',
            // 'product_categories',
            // 'secondary_sales_required',


            // 'last_12_months_sales',
            // 'sales_executive_id',
            // 'supervisor_id',
            // 'customer_segment',
            // 'weekly_tai_alert',


            // 'target_vs_achievement',
            // 'schemes_updates',
            // 'new_launch_update',
            // 'payment_alert',
            // 'pending_orders',


            // 'inventory_status',
            // 'turnover',
            // 'staff_strength',
            // 'vehicles_capacity',
            // 'area_coverage',


            // 'other_brands_handled',
            // 'warehouse_size',
          

            // 'Distributor Code',
            // 'Legal Name',
            // 'Trade Name',
            // 'Category',
            // 'Business Status', // Active / Inactive


            // 'Business Start Date', // YYYY-MM-DD format
            // 'Contact Person',
            // 'Designation',
            // 'Mobile',
            // 'Alternate Mobile',


            // 'Email',
            // 'Secondary Email',
            // 'Billing Address',
            // 'Billing City',
            // 'Billing District',


            // 'Billing State',
            // 'Billing Country',
            // 'Billing Pincode',
            // 'Shipping Address',
            // 'Shipping City',


            // 'Shipping District',
            // 'Shipping State',
            // 'Shipping Country',
            // 'Shipping Pincode',
            // 'Sales Zone',


            // 'Area Territory',
            // 'Beat Route',
            // 'Market Classification',
            // 'Competitor Brands',
            // 'GST Number',


            // 'PAN Number',
            // 'Registration Type',
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
            // 'Monthly Sales',
            // 'Product Categories',
            // 'Secondary Sales Required', // Y/N


            // 'Last 12 Months Sales',
            // 'Sales Executive IDs', // comma separated like: 5,8,12
            // 'Supervisor ID',
            // 'Customer Segment',
            // 'Weekly TAI Alert', // Y/N


            // 'Target vs Achievement', // Y/N
            // 'Schemes Updates', // Y/N
            // 'New Launch Update', // Y/N
            // 'Payment Alert', // Y/N
            // 'Pending Orders', // Y/N


            // 'Inventory Status', // Y/N
            // 'Turnover',
            // 'Staff Strength',
            // 'Vehicles Capacity',
            // 'Area Coverage',


            // 'Other Brands Handled',
            // 'Warehouse Size',


            // 'id,
            // 'distributor_code,
            // 'legal_name,
            // 'trade_name,
            // 'category,


            // 'business_status,
            // 'business_start_date,
            // 'shop_image ? 'Yes' : 'No',
            // 'profile_image ? 'Yes' : 'No',
            // 'contact_person,


            // 'designation,
            // 'mobile,
            // 'alternate_mobile,
            // 'email,
            // 'secondary_email,


            // 'billing_address,
            // 'billing_city,
            // 'billing_district,
            // 'billing_state,
            // 'billing_country,


            // 'billing_pincode,
            // 'shipping_address,
            // 'shipping_city,
            // 'shipping_district,
            // 'shipping_state,


            // 'shipping_country,
            // 'shipping_pincode,
            // 'sales_zone,
            // 'area_territory,
            // 'beat_route,


            // 'market_classification,
            // 'competitor_brands,
            // 'gst_number,
            // 'pan_number,
            // 'registration_type,


            // 'documents ? 'Yes (Multiple)' : 'No',
            // 'bank_name,
            // 'account_holder,
            // 'account_number,
            // 'ifsc,


            // 'branch_name,
            // 'credit_limit,
            // 'credit_days,
            // 'avg_monthly_purchase,
            // 'outstanding_balance,


            // 'preferred_payment_method,
            // 'cancelled_cheque ? 'Yes' : 'No',
            // 'monthly_sales,
            // 'product_categories,
            // 'secondary_sales_required,


            // 'last_12_months_sales,
            // 'sales_executive_id, // JSON stored hai
            // 'supervisor_id,
            // 'customer_segment,
            // 'weekly_tai_alert,


            // 'target_vs_achievement,
            // 'schemes_updates,
            // 'new_launch_update,
            // 'payment_alert,
            // 'pending_orders,


            // 'inventory_status,
            // 'turnover,
            // 'staff_strength,
            // 'vehicles_capacity,
            // 'area_coverage,


            // 'other_brands_handled,
            // 'warehouse_size,
            // 'created_at?->format('d-m-Y H:i'),
            // 'updated_at?->format('d-m-Y H:i'),
        ];
    }
}