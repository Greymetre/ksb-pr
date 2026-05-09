<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterDistributor;
use Illuminate\Support\Str;

class MasterDistributorSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            MasterDistributor::create([
                // BASIC INFO
                'legal_name'              => 'Distributor Legal ' . $i,
                'trade_name'              => 'Trade Name ' . $i,
                'distributor_code'        => 'MD' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'category'                => ['Diamond','Platinum','Gold','Silver'][array_rand(['Diamond','Platinum','Gold','Silver'])],
                'business_status'         => 'Active',
                'business_start_date'     => now()->subYears(rand(1, 10))->format('Y-m-d'),

                // CONTACT
                'contact_person'          => 'Contact Person ' . $i,
                'designation'             => 'Owner',
                'mobile'                  => '98' . rand(10000000, 99999999),
                'alternate_mobile'        => '97' . rand(10000000, 99999999),
                'email'                   => 'distributor'.$i.'@example.com',
                'secondary_email'         => 'alt'.$i.'@example.com',

                // ADDRESS
                'billing_address'         => 'Billing Address Line ' . $i,
                'billing_city'            => 'City ' . $i,
                'billing_district'        => 'District ' . $i,
                'billing_state'           => 'State ' . $i,
                'billing_country'         => 'India',
                'billing_pincode'         => rand(400000, 499999),

                'shipping_address'        => 'Shipping Address Line ' . $i,
                'shipping_city'           => 'City ' . $i,
                'shipping_district'       => 'District ' . $i,
                'shipping_state'          => 'State ' . $i,
                'shipping_country'        => 'India',
                'shipping_pincode'        => rand(400000, 499999),

                // BUSINESS INFO
                'sales_zone'              => 'Zone ' . rand(1,5),
                'area_territory'          => 'Territory ' . $i,
                'beat_route'              => 'Beat ' . $i,
                'market_classification'   => ['Urban','Rural','Semi-Urban'][array_rand(['Urban','Rural','Semi-Urban'])],
                'competitor_brands'       => 'Brand A, Brand B',

                // KYC
                'gst_number'              => '27ABCDE' . rand(1000,9999) . 'Z1',
                'pan_number'              => 'ABCDE' . rand(1000,9999) . 'F',
                'registration_type'       => 'Proprietorship',

                // BANK
                'bank_name'               => 'HDFC Bank',
                'account_holder'          => 'Account Holder ' . $i,
                'account_number'          => rand(1000000000, 9999999999),
                'ifsc'                    => 'HDFC0000' . rand(100,999),
                'branch_name'             => 'Main Branch',
                'credit_limit'            => rand(500000, 2000000),
                'credit_days'             => rand(15, 60),
                'avg_monthly_purchase'    => rand(100000, 500000),
                'outstanding_balance'     => rand(0, 100000),
                'preferred_payment_method'=> 'NEFT',

                // SALES
                'monthly_sales'           => rand(200000, 1000000),
                'product_categories'      => '2W, 3W',
                'secondary_sales_required'=> 'Yes',
                'last_12_months_sales'    => rand(2000000, 10000000),
                'customer_segment'        => '2W',

                // ADDITIONAL
                'weekly_tai_alert'        => 'Weekly Alert OK',
                'target_vs_achievement'   => '85%',
                'schemes_updates'         => 'Scheme A',
                'new_launch_update'       => 'New Product Launch',
                'payment_alert'           => 'Payment On Due Date',
                'pending_orders'          => 'None',
                'inventory_status'        => 'In Stock',

                // CAPACITY
                'turnover'                => rand(5000000, 20000000),
                'staff_strength'          => rand(5, 30),
                'vehicles_capacity'       => rand(2, 10) . ' Vehicles',
                'area_coverage'           => '50+ Retailers',
                'other_brands_handled'    => 'Brand X, Brand Y',
                'warehouse_size'          => rand(1000, 5000) . ' Sq Ft',

                // SYSTEM
                // 'active'                  => 'Y',
                // 'created_by'              => 1,
            ]);
        }
    }
}
