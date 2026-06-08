<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\CheckIn;
use App\Models\Customers;
use App\Models\Order;
use App\Models\User;
use App\Models\Designation;
use App\Models\SecondaryCustomer;
use App\Models\MasterDistributor;
use Validator;

class ReportingActivityController extends Controller
{
    public function allReportingUsers(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;

        $pageSize = $request->input('pageSize', 20);
        $page = $request->input('page', 1);
        $search_name = $request->input('search_name');           // single user id
        $search_branches = $request->input('search_branches');   // array or comma?
        $designation = $request->input('designation');           // comma separated ids e.g. "3,4"
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $validator = Validator::make($request->all(), [
            'end_date' => 'required_with:start_date|date',
            'start_date' => 'date',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
            'designation' => 'nullable|string',   // new
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        // Get base reporting users
        $all_reporting_user_ids = $search_name
            ? [$search_name]
            : getUsersReportingToAuth($user_id);
        $dropdown_user_ids = getUsersReportingToAuth($user_id);
        // ==================== Apply Designation Filter ====================
        if ($designation) {
            $designationIds = array_filter(array_map('trim', explode(',', $designation)));

            if (!empty($designationIds)) {
                $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                    ->whereDoesntHave('roles', function ($q) {
                        $q->whereIn('id', config('constants.customer_roles'));
                    })
                    ->whereIn('designation_id', $designationIds)   // Filter by designation_id
                    ->pluck('id')
                    ->toArray();
            }
        }

        // Filter by branches (if provided)
        if ($search_branches && count($search_branches) > 0 && $search_branches[0] != null) {
            $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                ->whereIn('branch_id', $search_branches)
                ->pluck('id')
                ->toArray();
        }

        // Main Query for Attendance / Activity
        $date_checkIn = Attendance::select('punchin_date', 'user_id')
            ->with('users.reportinginfo')
            ->whereIn('user_id', $all_reporting_user_ids);

        if ($start_date && $end_date) {
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));
            $date_checkIn->whereBetween('punchin_date', [$start_date, $end_date]);
        }

        $date_checkIn->orderBy('punchin_date', 'desc');

        // Paginate
        $paginatedData = $date_checkIn->paginate($pageSize);

        // Get all users list for dropdown (respecting designation filter)
        $all_user_details = User::with('getbranch', 'getdesignation')
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('id', config('constants.customer_roles'));
            })
            ->whereIn('id', $dropdown_user_ids)
            ->orderBy('name', 'asc')
            ->get();

        $all_users = $all_user_details->map(fn($val) => [
            'id' => $val->id,
            'name' => $val->name,
        ])->toArray();

        // Get branches
        $branches = $this->getUniqueBranches($all_user_details);

        // Prepare response data
        $data = [];
        foreach ($paginatedData->items() as $checkIn) {
            $data[] = [
                'user_id' => $checkIn->users->id ?? null,
                'name'    => $checkIn->users->name ?? null,
                'date'    => $checkIn->punchin_date ? date('d/m/Y', strtotime($checkIn->punchin_date)) : null,
                'reportingManagerName' => optional(optional($checkIn->users)->reportinginfo)->name,
                'reportingManagerMobile' => optional(optional($checkIn->users)->reportinginfo)->mobile,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully.',
            'users' => $all_users,
            'branches' => $branches,
            'data' => $data,
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
                'has_more' => $paginatedData->hasMorePages(),
            ]
        ], 200);
    }

    // Helper function to get branches
    private function getUniqueBranches($users)
    {
        $branches = [];
        $seen = [];

        foreach ($users as $user) {
            if ($user->getbranch && !in_array($user->getbranch->id, $seen)) {
                $seen[] = $user->getbranch->id;
                $branches[] = [
                    'id' => $user->getbranch->id,
                    'name' => $user->getbranch->branch_name,
                ];
            }
        }

        usort($branches, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $branches;
    }


    public function userActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>  $validator->errors()], 400);
        }
        $date = date('Y-m-d', strtotime($request->input('date')));
        $user_id = $request->input('user_id');

        // $punchInOut = Attendance::where('user_id', $user_id)->where('punchin_date', $date)->get();
        // Fetch Data
        $cityLookup = \App\Models\City::pluck('city_name', 'id')->toArray();
        $stateLookup = \App\Models\State::pluck('state_name', 'id')->toArray();
        $punchInOut = Attendance::where('user_id', $user_id)
            ->where('punchin_date', $date)
            ->get();
        // CheckIn with entity support
        $checkInOut = CheckIn::with(['user', 'visitreport'])
            ->where('user_id', $user_id)
            ->where('checkin_date', $date)
            ->get();
        // $checkInOut = CheckIn::with('visitreports')->with('customers')->where('user_id', $user_id)->where('checkin_date', $date)->get();
        // $orders = Order::with('buyers')->where('created_by', $user_id)->whereRaw('DATE(created_at)="'.$date.'"')->get();
        $orders = Order::with([
            'buyer',                    // SecondaryCustomer
            'orderdetails',             // OrderDetails
            'orderdetails.products'     // Product info
        ])
            ->where('created_by', $user_id)
            ->whereDate('created_at', $date)   // or use order_date if you prefer
            ->get();
        // New Customer Registration (using SecondaryCustomer and MasterDistributor)
        // $customer_add = SecondaryCustomer::with(['city', 'state'])
        //     ->where('created_by', $user_id)
        //     ->whereDate('created_at', $date)
        //     ->get();

        // $customer_update = SecondaryCustomer::with(['city', 'state'])
        //     ->where('created_by', $user_id)
        //     ->whereColumn('updated_at', '>', 'created_at')
        //     ->whereDate('updated_at', $date)
        //     ->get();

        // // Also fetch MasterDistributor if they can be created/updated by user
        // $master_add = MasterDistributor::with(['getCity', 'getState'])   // adjust relation names if different
        //     ->where('created_by', $user_id)
        //     ->whereDate('created_at', $date)
        //     ->get();

        // $master_update = MasterDistributor::with(['getCity', 'getState'])
        //     ->where('created_by', $user_id)
        //     ->whereColumn('updated_at', '>', 'created_at')
        //     ->whereDate('updated_at', $date)
        //     ->get();


        // Secondary Customer - Add & Update (with city_name and state_name)
        $customer_add = SecondaryCustomer::with(['state', 'city'])
            ->where('created_by', $user_id)
            ->whereDate('created_at', $date)
            ->get();

        // $customer_update = SecondaryCustomer::with(['city.statename', 'state'])
        //     ->where('created_by', $user_id)
        //     ->whereColumn('updated_at', '>', 'created_at')
        //     ->whereDate('updated_at', $date)
        //     ->get();
        $customer_update = SecondaryCustomer::with(['city.statename', 'state'])
            ->where('created_by', $user_id)

            // exclude approve/reject updates
            ->where(function ($q) {
                $q->whereNull('status_updated_at')
                    ->orWhereColumn('updated_at', '!=', 'status_updated_at');
            })

            ->whereColumn('updated_at', '>', 'created_at')
            ->whereDate('updated_at', $date)
            ->get();

        // Master Distributor - Add & Update
        $master_add = MasterDistributor::with(['billingCity', 'billingDistrict'])
            ->where('created_by', $user_id)
            ->whereDate('created_at', $date)
            ->get();

        $master_update = MasterDistributor::with(['billingCity', 'billingDistrict'])
            ->where('created_by', $user_id)
            ->whereColumn('updated_at', '>', 'created_at')
            ->whereDate('updated_at', $date)
            ->get();
        $approvedCustomers = SecondaryCustomer::with(['city', 'state'])
            ->where('status', 'APPROVED')
            ->where('approve_reject_by', $user_id)
            ->whereNotNull('status_updated_at')
            ->whereDate('status_updated_at', $date)
            ->get();
        $rejectedCustomers = SecondaryCustomer::with(['city', 'state'])
            ->where('status', 'REJECTED')
            ->where('approve_reject_by', $user_id)
            ->whereNotNull('status_updated_at')
            ->whereDate('status_updated_at', $date)
            ->get();


        $punchInData = array();
        $punchOutData = array();
        $checkInData = array();
        $checkOutData = array();
        $orderData = array();
        $customerAddData = array();
        $customerUpdateData = array();
        $customerApprovedData = array();
        $customerRejectedData = array();

        // foreach($punchInOut as $k=>$val){
        //     if($val->punchin_time != null){
        //         $punch_in_city = getLatLongToCity($val->punchin_latitude, $val->punchin_longitude);
        //         $punchInData[$k]['title'] = 'Punchin';
        //         $punchInData[$k]['time'] = $val->punchin_time;
        //         $punchInData[$k]['latitude'] = $val->punchin_latitude!=null?$val->punchin_latitude:'';
        //         $punchInData[$k]['longitude'] = $val->punchin_longitude!=null?$val->punchin_longitude:'';
        //         $punchInData[$k]['msg'] = $val->punchin_summary.' - '.$punch_in_city;
        //     }
        //     if($val->punchout_time != null){
        //         $punchOutData[$k]['title'] = 'Punchout';
        //         $punchOutData[$k]['time'] = $val->punchout_time;
        //         $punchOutData[$k]['latitude'] = $val->punchout_latitude!=null?$val->punchout_latitude:'';
        //         $punchOutData[$k]['longitude'] = $val->punchout_longitude!=null?$val->punchout_longitude:'';
        //         $punchOutData[$k]['msg'] = $val->punchout_address;
        //     }
        // }
        // ====================== PUNCH IN & PUNCH OUT ======================
        foreach ($punchInOut as $val) {

            $location = $val->punchin_address ?? 'No Location';
            $city     = getLatLongToCity($val->punchin_latitude ?? 0, $val->punchin_longitude ?? 0);
            $workingType = $val->working_type ?? 'Regular';

            // ------------------- Punch In -------------------
            if ($val->punchin_time) {
                $punchInData[] = [
                    'title'         => 'Punchin',
                    'time'          => $val->punchin_time,
                    'date'          => $date,
                    'latitude'      => $val->punchin_latitude ?? '',
                    'longitude'     => $val->punchin_longitude ?? '',
                    'time_display'  => date('h:i A', strtotime($val->punchin_time)),
                    'location'      => $location,
                    'city'          => $city,
                    'working_type'  => $workingType,
                    'customer'      => ''   // not applicable for punch
                ];
            }

            // ------------------- Punch Out -------------------
            if ($val->punchout_time) {
                $punchOutData[] = [
                    'title'         => 'Punchout',
                    'time'          => $val->punchout_time,
                    'date'          => $date,
                    'latitude'      => $val->punchout_longitude ?? '',
                    'longitude'     => $val->punchout_latitude ?? '',
                    'time_display'  => date('h:i A', strtotime($val->punchout_time)),
                    'location'      => $val->punchout_address ?? 'No Location',
                    'city'          => $city,                    // using same city as punchin
                    'working_type'  => '',                       // not needed for punchout
                    'customer'      => ''
                ];
            }
        }

        // ====================== Check In / Checkout (New Format) ======================
        foreach ($checkInOut as $val) {
            $entity = $val->entity;                    // This uses your getEntityAttribute()
            $entityName = $val->entity_name ?? 'Unknown';   // Uses your getEntityNameAttribute()
            $cityName = $entity?->city?->city_name
                ?? $entity?->billing_city
                ?? 'City not available';

            $location = $val->checkin_address ?? 'No Location';

            // ==================== Checkin ====================
            if ($val->checkin_time) {
                $checkInData[] = [
                    'title'         => 'Checkin',
                    'time'          => $val->checkin_time,
                    'date'          => $date,
                    'latitude'      => $val->checkin_latitude ?? '',
                    'longitude'     => $val->checkin_longitude ?? '',
                    'time_display'  => date('h:i A', strtotime($val->checkin_time)),
                    'location'      => $location,
                    'city'          => $cityName,
                    'customer'      => $entityName,
                    'customer_type' => $val->entity_type_display ?? 'Customer'
                ];
            }

            // ==================== Checkout ====================
            if ($val->checkout_time) {
                $remark = $val->visitreport?->description ?? 'No Remark';
                $checkOutData[] = [
                    'title'         => 'Checkout',
                    'time'          => $val->checkout_time,
                    'date'          => $date,
                    'latitude'      => $val->checkout_latitude ?? '',
                    'longitude'     => $val->checkout_longitude ?? '',
                    'time_display'  => date('h:i A', strtotime($val->checkout_time)),
                    'location'      => $location,
                    'city'          => $cityName,
                    'customer'      => $entityName,
                    'remark'        => $remark,
                    'customer_type' => $val->entity_type_display ?? 'Customer'
                ];
            }
        }

        // foreach($checkInOut as $k=>$val){
        //     if($val->checkin_time != null){
        //         $check_in_city = getLatLongToCity($val->checkin_latitude, $val->checkin_longitude);
        //         $checkInData[$k]['title'] = 'Checkin';
        //         $checkInData[$k]['time'] = $val->checkin_time;
        //         $checkInData[$k]['latitude'] = $val->checkin_latitude!=null?$val->checkin_latitude:'';
        //         $checkInData[$k]['longitude'] = $val->checkin_longitude!=null?$val->checkin_longitude:'';
        //         $checkInData[$k]['msg'] = $val->customers->name.' - '.$check_in_city;
        //     }
        //     if($val->checkout_time != null){
        //         $check_out_city = getLatLongToCity($val->checkout_latitude, $val->checkout_longitude);
        //         $checkOutData[$k]['title'] = 'Checkout';
        //         $checkOutData[$k]['time'] = $val->checkout_time;
        //         $checkOutData[$k]['latitude'] = $val->checkout_latitude!=null?$val->checkout_latitude:'';
        //         $checkOutData[$k]['longitude'] = $val->checkout_longitude!=null?$val->checkout_longitude:'';
        //         $checkOutData[$k]['msg'] = $val->customers->name.' - '.$check_out_city.'<br>Remark - '.$val->visitreports->description;
        //     }
        // }

        // ====================== Orders ======================
        foreach ($orders as $order) {

            $sellerName = $order->seller?->trade_name
                ?? $order->seller?->legal_name
                ?? 'Unknown Seller';

            $buyerName = $order->buyer?->shop_name
                ?? $order->buyer?->owner_name
                ?? 'Unknown Buyer';

            $totalQty   = $order->orderdetails ? $order->orderdetails->sum('quantity') : 0;
            $grandTotal = $order->grand_total ?? 0;

            $orderData[] = [
                'title'        => 'Order',
                'time'         => date('H:i:s', strtotime($order->created_at)),   // raw time (keep for sorting)
                'date'         => date('Y-m-d', strtotime($order->created_at)),   // ← Added as requested
                'latitude'     => '',
                'longitude'    => '',

                // Key-Value fields as you want
                'seller'       => $sellerName,
                'customer'     => $buyerName,
                'qty'          => (int)$totalQty,
                'value'        => (float)$grandTotal,

                // Helpful display fields
                'time_display' => date('h:i A', strtotime($order->created_at)),
                'order_no'     => $order->orderno ?? ''
            ];
        }

        // foreach ($customer_add as $k => $val) {
        //     $customerAddData[$k]['title'] = 'New Customer Registration';
        //     $customerAddData[$k]['time'] = date('H:i:s', strtotime($val->created_at));
        //     $customerAddData[$k]['latitude'] = $val->latitude;
        //     $customerAddData[$k]['longitude'] = $val->longitude;
        //     if($val->customeraddress->cityname != null){
        //         $customerAddData[$k]['msg'] = $val->name.' - '. $val->customeraddress?->cityname?->city_name;
        //     }else{
        //         $customerAddData[$k]['msg'] = $val->name.' - City not enter';
        //     }
        // }

        foreach ($customer_add as $val) {
            $customerName = $val->shop_name ?? $val->owner_name ?? 'Unknown Customer';
            $location     = $val->address_line ?? $val->belt_area_market_name ?? 'No Location';

            // Get city name using city_id (fast lookup)
            $cityName = $cityLookup[$val->city_id] ?? 'City not available';
            $stateName = $stateLookup[$val->state_id] ?? 'State not available';

            // GPS Handling (unchanged)
            $latitude = $longitude = '';
            if (!empty($val->gps_location)) {
                $coords = explode(',', trim($val->gps_location));
                if (count($coords) >= 2) {
                    $latitude  = trim($coords[0]);
                    $longitude = trim($coords[1]);
                } else {
                    $latitude = trim($val->gps_location);
                }
            }

            $customerAddData[] = [
                'title'         => 'New Customer Registration',
                'time'          => date('H:i:s', strtotime($val->created_at)),
                'date'          => $date,
                'latitude'      => $longitude,
                'longitude'     => $latitude,
                'time_display'  => date('h:i A', strtotime($val->created_at)),
                'location'      => $location,
                'city'          => $cityName,
                'state'         => $stateName,
                'customer'      => $customerName,
                'customer_type' => $val->type
            ];
        }

        // Add Master Distributor new registrations if needed
        foreach ($master_add as $val) {
            $customerName = $val->trade_name ?? $val->legal_name ?? 'Unknown Distributor';
            $cityName = $cityLookup[$val->billing_city] ?? 'City not available';
            $stateName = $stateLookup[$val->billing_state] ?? 'State not available';
            $location     = $val->billing_address ?? 'No Location';

            $customerAddData[] = [
                'title'         => 'New Customer Registration',
                'time'          => date('H:i:s', strtotime($val->created_at)),
                'date'          => $date,
                'latitude'      => '',
                'longitude'     => '',
                'time_display'  => date('h:i A', strtotime($val->created_at)),
                'location'      => $location,
                'city'          => $cityName,
                'state'         => $stateName,
                'customer'      => $customerName,
                'customer_type' => "Distributor"                    // or 'Master Distributor'
            ];
        }

        // Secondary Customer Update
        foreach ($customer_update as $val) {
            $customerName = $val->shop_name ?? $val->owner_name ?? 'Unknown Customer';
            $cityName = $cityLookup[$val->city_id] ?? 'City not available';
            $stateName = $stateLookup[$val->state_id] ?? 'State not available';

            $location     = $val->address_line ?? $val->belt_area_market_name ?? 'No Location';
            // Handle gps_location (split if it's "lat,long" format)
            $latitude  = '';
            $longitude = '';

            if (!empty($val->gps_location)) {
                $coords = explode(',', trim($val->gps_location));
                if (count($coords) >= 2) {
                    $latitude  = trim($coords[0]);
                    $longitude = trim($coords[1]);
                } else {
                    $latitude = trim($val->gps_location); // fallback
                }
            }

            $customerUpdateData[] = [
                'title'         => 'Customer Edit',
                'time'          => date('H:i:s', strtotime($val->updated_at)),
                'date'          => $date,
                'latitude'      => $longitude,
                'longitude'     => $latitude,
                'time_display'  => date('h:i A', strtotime($val->updated_at)),
                'location'      => $location,
                'city'          => $cityName,
                'state'         => $stateName,
                'customer'      => $customerName,
                'customer_type' => $val->type
            ];
        }

        foreach ($approvedCustomers as $val) {

            $customerName = $val->shop_name ?? $val->owner_name ?? 'Unknown Customer';

            $cityName = $cityLookup[$val->city_id] ?? 'City not available';
            $stateName = $stateLookup[$val->state_id] ?? 'State not available';

            $location = $val->address_line ?? $val->belt_area_market_name ?? 'No Location';

            $customerApprovedData[] = [
                'title'         => 'Customer Approved',
                'time'          => date('H:i:s', strtotime($val->status_updated_at)),
                'date'          => $date,
                'latitude'      => '',
                'longitude'     => '',
                'time_display'  => date('h:i A', strtotime($val->status_updated_at)),
                'location'      => $location,
                'city'          => $cityName,
                'state'         => $stateName,
                'customer'      => $customerName,
                'customer_type' => $val->type,
                'remark'        => $val->remark ?? ''
            ];
        }

        foreach ($rejectedCustomers as $val) {

            $customerName = $val->shop_name ?? $val->owner_name ?? 'Unknown Customer';

            $cityName = $cityLookup[$val->city_id] ?? 'City not available';
            $stateName = $stateLookup[$val->state_id] ?? 'State not available';

            $location = $val->address_line ?? $val->belt_area_market_name ?? 'No Location';

            $customerRejectedData[] = [
                'title'         => 'Customer Rejected',
                'time'          => date('H:i:s', strtotime($val->status_updated_at)),
                'date'          => $date,
                'latitude'      => '',
                'longitude'     => '',
                'time_display'  => date('h:i A', strtotime($val->status_updated_at)),
                'location'      => $location,
                'city'          => $cityName,
                'state'         => $stateName,
                'customer'      => $customerName,
                'customer_type' => $val->type,
                'remark'        => $val->remark ?? ''
            ];
        }

        // Master Distributor Update
        foreach ($master_update as $val) {
            $customerName = $val->trade_name ?? $val->legal_name ?? 'Unknown Distributor';
            $cityName = $cityLookup[$val->billing_city] ?? 'City not available';
            $stateName = $stateLookup[$val->billing_state] ?? 'State not available';
            $location     = $val->billing_address ?? 'No Location';

            $customerUpdateData[] = [
                'title'         => 'Customer Edit',
                'time'          => date('H:i:s', strtotime($val->updated_at)),
                'date'          => $date,
                'latitude'      => '',
                'longitude'     => '',
                'time_display'  => date('h:i A', strtotime($val->updated_at)),
                'location'      => $location,
                'city'          => $cityName,
                'state'         => $stateName,
                'customer'      => $customerName,
                'customer_type' => "Distributor"
            ];
        }

        $data = array_merge($punchInData, $punchOutData, $orderData, $customerAddData, $customerUpdateData, $checkInData, $checkOutData, $customerApprovedData, $customerRejectedData);
        // $data = array_merge($punchInData, $punchOutData, $checkInData, $checkOutData, $orderData, $customerAddData, $customerUpdateData);
        // return response(['status' => 'error', 'message' => 'No Record data Found.', 'data' => $data ],200);

        usort($data, function ($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });
        foreach ($data as $k => $val) {
            $data[$k]['time'] = date('h:i A', strtotime($val['time']));
            $data[$k]['date'] = $request->input('date');
        }

        if (count($data) > 0) {
            return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], 200);
        } else {
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        }
    }

    public function customerActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>  $validator->errors()], 400);
        }
        $date = date('Y-m-d', strtotime($request->input('date')));
        $user_id = $request->input('customer_id');

        $punchInOut = Attendance::where('user_id', $user_id)->get();
        $checkInOut = CheckIn::with('visitreports')->with(['customers', 'users'])->where('customer_id', $user_id)->get();
        $orders = Order::with('buyers')->where('created_by', $user_id)->get();
        $customer_add = Customers::with('customeraddress')->where('created_by', $user_id)->get();
        $customer_update = Customers::with('customeraddress')->where('created_by', $user_id)->whereColumn('updated_at', '>', 'created_at')->get();

        $punchInData = array();
        $punchOutData = array();
        $checkInData = array();
        $checkOutData = array();
        $orderData = array();
        $customerAddData = array();
        $customerUpdateData = array();

        // foreach($punchInOut as $k=>$val){
        //     if($val->punchin_time != null){
        //         $punch_in_city = getLatLongToCity($val->punchin_latitude, $val->punchin_longitude);
        //         $punchInData[$k]['title'] = 'Punchin';
        //         $punchInData[$k]['time'] = $val->punchin_time;
        //         $punchInData[$k]['latitude'] = $val->punchin_latitude!=null?$val->punchin_latitude:'';
        //         $punchInData[$k]['longitude'] = $val->punchin_longitude!=null?$val->punchin_longitude:'';
        //         $punchInData[$k]['msg'] = $val->punchin_summary.' - '.$punch_in_city;
        //     }
        //     if($val->punchout_time != null){
        //         $punchOutData[$k]['title'] = 'Punchout';
        //         $punchOutData[$k]['time'] = $val->punchout_time;
        //         $punchOutData[$k]['latitude'] = $val->punchout_latitude!=null?$val->punchout_latitude:'';
        //         $punchOutData[$k]['longitude'] = $val->punchout_longitude!=null?$val->punchout_longitude:'';
        //         $punchOutData[$k]['msg'] = $val->punchout_address;
        //     }
        // }

        foreach ($checkInOut as $k => $val) {
            if ($val->checkin_time != null) {
                $check_in_city = getLatLongToCity($val->checkin_latitude, $val->checkin_longitude);
                $checkInData[$k]['title'] = 'Checkin';
                $checkInData[$k]['time'] = $val->checkin_time;
                // $checkInData[$k]['latitude'] = $val->checkin_latitude!=null?$val->checkin_latitude:'';
                // $checkInData[$k]['longitude'] = $val->checkin_longitude!=null?$val->checkin_longitude:'';
                $checkInData[$k]['msg'] = $val->customers->name . ' - ' . $check_in_city;
                $checkInData[$k]['user'] = ($val->users->name ?? '');
            }
            if ($val->checkout_time != null) {
                $check_out_city = getLatLongToCity($val->checkout_latitude, $val->checkout_longitude);
                $checkOutData[$k]['title'] = 'Checkout';
                $checkOutData[$k]['time'] = $val->checkout_time;
                // $checkOutData[$k]['latitude'] = $val->checkout_latitude!=null?$val->checkout_latitude:'';
                // $checkOutData[$k]['longitude'] = $val->checkout_longitude!=null?$val->checkout_longitude:'';
                $checkOutData[$k]['msg'] = $val->customers->name . ' - ' . $check_out_city . '<br>Remark - ' . $val->visitreports->description;
                $checkOutData[$k]['user'] = ($val->users->name ?? '');
            }
        }

        // foreach ($orders as $k => $val) {
        //     $orderData[$k]['title'] = 'Order';
        //     $orderData[$k]['time'] = date('H:i:s', strtotime($val->created_at));
        //     $orderData[$k]['latitude'] = '';
        //     $orderData[$k]['longitude'] = '';
        //     $orderData[$k]['msg'] = 
        //     ($val->buyers->name ?? '') . ' - ' . 
        //     ($val->buyers->customeraddress->cityname->city_name ?? '') . 
        //     ',<br>Qty : ' . $val->orderdetails->sum('quantity') . 
        //     ',<br>Total : ' . ($val->grand_total ?? '');        
        // }

        // foreach ($customer_add as $k => $val) {
        //     $customerAddData[$k]['title'] = 'New Customer Registration';
        //     $customerAddData[$k]['time'] = date('H:i:s', strtotime($val->created_at));
        //     $customerAddData[$k]['latitude'] = $val->latitude;
        //     $customerAddData[$k]['longitude'] = $val->longitude;
        //     if($val->customeraddress->cityname != null){
        //         $customerAddData[$k]['msg'] = $val->name.' - '. $val->customeraddress->cityname->city_name;
        //     }else{
        //         $customerAddData[$k]['msg'] = $val->name.' - City not enter';
        //     }
        // }

        // foreach ($customer_update as $k => $val) {
        //     $customerUpdateData[$k]['title'] = 'Customer Edit';
        //     $customerUpdateData[$k]['time'] = date('H:i:s', strtotime($val->created_at));
        //     $customerUpdateData[$k]['latitude'] = $val->latitude;
        //     $customerUpdateData[$k]['longitude'] = $val->longitude;
        //     $customerUpdateData[$k]['msg'] = $val->name.' - '. $val->customeraddress->cityname->city_name;
        // }

        $data = array_merge($punchInData, $punchOutData, $checkInData, $checkOutData, $orderData, $customerAddData, $customerUpdateData);


        usort($data, function ($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });
        foreach ($data as $k => $val) {
            $data[$k]['time'] = date('h:i A', strtotime($val['time']));
            $data[$k]['date'] = $request->input('date');
        }

        if (count($data) > 0) {
            return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], 200);
        } else {
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        }
    }

    public function getDesignations(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;

        // Get all reporting user ids
        $reportingUserIds = getUsersReportingToAuth($user_id);

        // Fetch unique designation ids from users table
        $designationIds = User::whereIn('id', $reportingUserIds)
            ->whereNotNull('designation_id')
            ->pluck('designation_id')
            ->unique()
            ->toArray();

        // Get designation list
        $designations = Designation::select('id', 'designation_name')
            ->whereIn('id', $designationIds)
            ->orderBy('designation_name', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Designations retrieved successfully',
            'data' => $designations
        ], 200);
    }
}
