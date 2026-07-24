<?php

namespace App\Exports;

use App\Models\Customers;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;


class OrderExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $reportingUsers;

    public function __construct($request)
    {
        $this->pending_status = $request->input('pending_status');
        $this->startdate = $request->input('start_date');
        $this->enddate = $request->input('end_date');
        $this->order_id = $request->input('order_id');
        $this->user_id = $request->input('user_id');
        $this->dividion_id = $request->input('dividion_id');
        $this->customer_type_id = $request->input('customer_type_id');
        $this->designation_id = $request->input('designation_id');
        $this->retailers_id = $request->input('retailers_id');
        $this->distributor_id = $request->input('distributor_id');
        $this->userids = getUsersReportingToAuth();
        $this->reportingUsers = User::pluck('name', 'id');
    }

    public function collection()
    {
        $final = collect(); // Final result

        // return OrderDetails::with('orders','orders.createdbyname')->whereHas('orders',function ($query)  {
        //                         if(!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin'))

        //                         {
        //                             $query->whereIn('created_by', $this->userids);
        //                         }
        //                     })->select('id','order_id', 'product_id', 'product_detail_id', 'quantity', 'shipped_qty', 'price', 'discount', 'discount_amount', 'tax_amount', 'line_total', 'status_id', 'created_at')->latest()->get();  

        if ($this->pending_status != '' && $this->pending_status != NULL) {
            $query = OrderDetails::with(
                'orders',
                'orders.createdbyname',
                'orders.buyers.customertypes',
                'orders.sellers'
            )->whereHas('orders', function ($query) {
                if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
                    $query->whereIn('created_by', $this->userids);
                }
                if ($this->pending_status == '0') {
                    $query->where('status_id', NULL);
                } else {
                    $query->where('status_id', $this->pending_status);
                }
                if ($this->startdate) {
                    $query->where('order_date', '>=', $this->startdate);
                }
                if ($this->enddate) {
                    $query->where('order_date', '<=', $this->enddate);
                }
                if ($this->order_id) {
                    $query->where('id', $this->order_id);
                }
                if ($this->user_id) {
                    $query->where('created_by', $this->user_id);
                }
                if ($this->dividion_id) {
                    $query->whereHas('orders.executive', function ($q) {
                        $q->where('division_id', $this->dividion_id);
                    });
                }

                if ($this->customer_type_id && $this->customer_type_id != '') {
                    $order_ids = Order::with('buyers')
                        ->whereHas('buyers', function ($query) {
                            $query->where('customertype', $this->customer_type_id);
                        })->pluck('id');
                    $query->whereIn('order_id', $order_ids);
                }
                if (!empty($this->designation_id)) {

                    $userIds = \App\Models\User::whereIn('designation_id', $this->designation_id)
                        ->pluck('id');

                    $query->whereIn('created_by', $userIds);
                }
                if ($this->retailers_id) {
                    $query->where('buyer_id', $this->retailers_id);
                }

                if ($this->distributor_id) {
                    $query->where('seller_id', $this->distributor_id);
                }
            });

            $query->chunk(1000, function ($results) use (&$final) {
                foreach ($results as $row) {
                    $final->push($row);
                }
            });

            return $final;
        } else {
            $query =  OrderDetails::with(
                'orders',
                'orders.createdbyname',
                'orders.buyers.customertypes',
                'orders.executive',
                'orders.sellers'
            )->whereHas('orders', function ($query) {
                if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
                    $query->whereIn('created_by', $this->userids);
                }

                if ($this->order_id) {
                    $query->where('id', $this->order_id);
                }
                if ($this->dividion_id) {
                    $order_ids = Order::where(function ($query) {
                        $query->where('product_cat_id', $this->dividion_id)
                            ->orWhereNull('product_cat_id');
                    });
                    if ($this->startdate) {
                        $order_ids->where('order_date', '>=', $this->startdate);
                    }
                    if ($this->enddate) {
                        $order_ids->where('order_date', '<=', $this->enddate);
                    }
                    $order_ids = $order_ids->pluck('id');
                    $query->whereIn('id', $order_ids);
                    if ($this->order_id) {
                        $query->where('id', $this->order_id);
                    }
                    if ($this->user_id) {
                        $query->where('created_by', $this->user_id);
                    }
                    if (!empty($this->dividion_id)) {
                        $query->whereHas('executive', function ($subQ) {
                            $subQ->where('division_id', $this->dividion_id);
                        });
                    }

                    if ($this->customer_type_id && $this->customer_type_id != '') {
                        $Order_ids = Order::with('buyers')
                            ->whereHas('buyers', function ($query) {
                                $query->where('customertype', $this->customer_type_id);
                            })->pluck('id');
                        $query->whereIn('order_id', $order_ids);
                    }
                    if (!empty($this->designation_id)) {

                        $userIds = \App\Models\User::whereIn('designation_id', $this->designation_id)
                            ->pluck('id');

                        $query->whereIn('created_by', $userIds);
                    }
                    if ($this->retailers_id) {
                        $query->where('buyer_id', $this->retailers_id);
                    }

                    if ($this->distributor_id) {
                        $query->where('seller_id', $this->distributor_id);
                    }
                    // $query->where('orders.product_cat_id',$this->dividion_id);
                } else {
                    if ($this->startdate) {
                        $query->where('order_date', '>=', $this->startdate);
                    }
                    if ($this->enddate) {
                        $query->where('order_date', '<=', $this->enddate);
                    }
                    if ($this->order_id) {
                        $query->where('id', $this->order_id);
                    }
                    if ($this->user_id) {
                        $query->where('created_by', $this->user_id);
                    }
                    if (!empty($this->dividion_id)) {
                        $query->whereHas('executive', function ($subQ) {
                            $subQ->where('division_id', $this->dividion_id);
                        });
                    }

                    if ($this->customer_type_id && $this->customer_type_id != '') {
                        $Order_ids = Order::with('buyers')
                            ->whereHas('buyers', function ($query) {
                                $query->where('customertype', $this->customer_type_id);
                            })->pluck('id');
                        $query->whereIn('order_id', $order_ids);
                    }

                    if (!empty($this->designation_id)) {

                        $userIds = \App\Models\User::whereIn('designation_id', $this->designation_id)
                            ->pluck('id');

                        $query->whereIn('created_by', $userIds);
                    }
                    if ($this->retailers_id) {
                        $query->where('buyer_id', $this->retailers_id);
                    }

                    if ($this->distributor_id) {
                        $query->where('seller_id', $this->distributor_id);
                    }
                }
            })->select('id', 'order_id', 'product_id', 'product_detail_id', 'quantity', 'shipped_qty', 'price', 'discount', 'discount_amount', 'tax_amount', 'line_total', 'status_id', 'created_at', 'scheme_name', 'scheme_discount', 'scheme_amount', 'cluster_discount', 'cluster_amount', 'deal_discount', 'deal_amount', 'distributor_discount', 'distributor_amount');


            $query->chunk(1000, function ($results) use (&$final) {
                foreach ($results as $row) {
                    $final->push($row);
                }
            });

            return $final;
        }
    }

    public function headings(): array
    {
        return [
            'Order Date',
            'Order No',
            'Employee Name',
            'Reporting Manager',
            'Designation',
            'Customer Type',
            'Customer Name',
            'Distributor Name',
            'Distributor Code',
            'Product Code',
            'Product Name',
            'Quantity',
            'Rate',
            'Total Order Value',
            'Employee Code',
            'Customer ID',
            'Distributor ID',
            'Order Remark',
            'Category',
            'Subcategory',
            'id',
            'Zone',
        ];
    }

    public function map($data): array
    {

        $pending_qty = 0;
        $qty = $data['quantity'] ?? 0;
        $ship_qty = $data['shipped_qty'] ?? 0;
        $pending_qty = $qty - $ship_qty;

        $reportingNames = '';

        $employee = $data['orders']['getuserdetails'] ?? null;

        if ($employee && !empty($employee['reportingid'])) {

            $ids = explode(',', $employee['reportingid']);

            $reportingNames = collect($ids)
                ->map(function ($id) {
                    $id = (int) trim($id);
                    return $this->reportingUsers->get($id);
                })
                ->filter()
                ->implode(', ');
        }


        return [

            isset($data['orders']['order_date']) ? date('Y-m-d', strtotime($data['orders']['order_date'])) : '',
            isset($data['orders']['orderno']) ? $data['orders']['orderno'] : '',
            isset($data['orders']['createdbyname']['name']) ? $data['orders']['createdbyname']['name'] : '',
            $reportingNames,
            isset($data['orders']['getuserdetails']['getdesignation']['designation_name']) ? $data['orders']['getuserdetails']['getdesignation']['designation_name'] : '',
            $data->orders?->buyers?->customertypes?->customertype_name ?? '',
            isset($data['orders']['buyers']['name']) ? $data['orders']['buyers']['name'] : '',
            isset($data['orders']['sellers']['name']) ? $data['orders']['sellers']['name'] : '',
            isset($data['orders']['sellers']['customer_code']) ? $data['orders']['sellers']['customer_code'] : '',
            isset($data['products']['product_code']) ? $data['products']['product_code'] : '',
            isset($data['products']['product_name']) ? $data['products']['product_name'] : '',
            isset($data['quantity']) ? $data['quantity'] : '',
            isset($data['price']) ? $data['price'] : '',
            // isset($data['orders']['grand_total'])  ? $data['orders']['grand_total'] : '',
            isset($data['line_total']) ? $data['line_total'] : '',
            // isset($data['orders']['statusname']) ? $data['orders']['statusname']['status_name'] : 'Pending',
            isset($data['orders']['getuserdetails']['employee_codes']) ? $data['orders']['getuserdetails']['employee_codes'] : '',
            isset($data['orders']['buyer_id']) ? $data['orders']['buyer_id'] : '',
            isset($data['orders']['seller_id']) ? $data['orders']['seller_id'] : '',
            isset($data['orders']['order_remark']) ? $data['orders']['order_remark'] : '',
            isset($data['products']['categories']['category_name']) ? $data['products']['categories']['category_name'] : '',
            isset($data['products']['subcategories']['subcategory_name']) ? $data['products']['subcategories']['subcategory_name'] : '',

            $data['id'],
            isset($data['orders']['getuserdetails']['getdivision']['division_name']) ? $data['orders']['getuserdetails']['getdivision']['division_name'] : '',


        ];
    }
}
