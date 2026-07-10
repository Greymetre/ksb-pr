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
use App\Models\Division;
use App\Models\SecondaryCustomer;
use App\Models\MasterDistributor;
use Validator;

class ReportingActivityController extends Controller
{
    private function getZoneSortOrder($zoneName)
    {
        $zoneOrder = ['north', 'east', 'west', 'south'];
        $zoneName = strtolower((string) $zoneName);

        foreach ($zoneOrder as $index => $zone) {
            if (strpos($zoneName, $zone) !== false) {
                return $index;
            }
        }

        return count($zoneOrder);
    }

    private function sortZoneList(array $zones)
    {
        usort($zones, function ($firstZone, $secondZone) {
            $firstName = $firstZone['name'] ?? $firstZone['zone'] ?? '';
            $secondName = $secondZone['name'] ?? $secondZone['zone'] ?? '';
            $orderComparison = $this->getZoneSortOrder($firstName) <=> $this->getZoneSortOrder($secondName);

            return $orderComparison ?: strcasecmp($firstName, $secondName);
        });

        return $zones;
    }

    private function customerDisplayName($customer)
    {
        if (!$customer) {
            return '';
        }

        $personName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));

        return $customer->name ?: ($personName ?: ($customer->customer_code ?: ($customer->mobile ?: 'Unknown Customer')));
    }

    private function customerActivitySummary($customer)
    {
        if (!$customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => $this->customerDisplayName($customer),
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile' => $customer->mobile,
            'contact_number' => $customer->contact_number,
            'customer_code' => $customer->customer_code,
            'customer_type_id' => $customer->customertype,
            'customer_type' => optional($customer->customertypes)->customertype_name,
            'address' => optional($customer->customeraddress)->full_address,
            'city' => optional(optional($customer->customeraddress)->cityname)->city_name,
            'state' => optional(optional($customer->customeraddress)->statename)->state_name,
            'latitude' => $customer->latitude,
            'longitude' => $customer->longitude,
        ];
    }

    public function allReportingUsers(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;

        $pageSize = $request->input('pageSize', 20);
        $page = $request->input('page', 1);
        $search_name = $request->input('search_name');           // single user id
        $search_branches = $request->input('search_branches');   // array or comma?
        $designation = $request->input('designation');           // comma separated ids e.g. "3,4"
        $zone = $request->input('zone');
        $zone_id = $request->input('zone_id');
        $branch = $request->input('branch');
        $branch_id = $request->input('branch_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $search_branches = is_array($search_branches)
            ? $search_branches
            : explode(',', (string) $search_branches);
        $search_branches = array_values(array_filter(array_map('trim', $search_branches), fn($value) => $value !== ''));

        $validator = Validator::make($request->all(), [
            'end_date' => 'required_with:start_date|date',
            'start_date' => 'date',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
            'designation' => 'nullable|string',   // new
            'zone' => 'nullable|string',
            'zone_id' => 'nullable',
            'branch' => 'nullable|string',
            'branch_id' => 'nullable',
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

        if (!empty($zone_id)) {
            $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('id', config('constants.customer_roles'));
                })
                ->where('division_id', $zone_id)
                ->pluck('id')
                ->toArray();
        } elseif (!empty($zone)) {
            $zoneIds = Division::where('division_name', 'LIKE', "%{$zone}%")->pluck('id')->toArray();
            $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('id', config('constants.customer_roles'));
                })
                ->whereIn('division_id', $zoneIds)
                ->pluck('id')
                ->toArray();
        }

        if (!empty($branch_id)) {
            $branchIds = is_array($branch_id) ? $branch_id : explode(',', $branch_id);
            $branchIds = array_values(array_filter(array_map('trim', $branchIds), fn($value) => $value !== ''));
            $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('id', config('constants.customer_roles'));
                })
                ->where(function ($q) use ($branchIds) {
                    $q->whereIn('branch_id', $branchIds);
                    foreach ($branchIds as $branchId) {
                        $q->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branchId]);
                    }
                })
                ->pluck('id')
                ->toArray();
        } elseif (!empty($branch)) {
            $branchIds = Branch::where('branch_name', 'LIKE', "%{$branch}%")->pluck('id')->toArray();
            $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('id', config('constants.customer_roles'));
                })
                ->where(function ($q) use ($branchIds) {
                    $q->whereIn('branch_id', $branchIds);
                    foreach ($branchIds as $branchId) {
                        $q->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branchId]);
                    }
                })
                ->pluck('id')
                ->toArray();
        }

        // Filter by branches (if provided)
        if (!empty($search_branches)) {
            $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                ->where(function ($q) use ($search_branches) {
                    $q->whereIn('branch_id', $search_branches);
                    foreach ($search_branches as $branchId) {
                        $q->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branchId]);
                    }
                })
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
            ->when(!empty($zone_id), function ($q) use ($zone_id) {
                $q->where('division_id', $zone_id);
            })
            ->when(empty($zone_id) && !empty($zone), function ($q) use ($zone) {
                $zoneIds = Division::where('division_name', 'LIKE', "%{$zone}%")->pluck('id')->toArray();
                $q->whereIn('division_id', $zoneIds);
            })
            ->when(!empty($branch_id), function ($q) use ($branch_id) {
                $branchIds = is_array($branch_id) ? $branch_id : explode(',', $branch_id);
                $branchIds = array_values(array_filter(array_map('trim', $branchIds), fn($value) => $value !== ''));
                $q->where(function ($branchQuery) use ($branchIds) {
                    $branchQuery->whereIn('branch_id', $branchIds);
                    foreach ($branchIds as $branchId) {
                        $branchQuery->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branchId]);
                    }
                });
            })
            ->when(empty($branch_id) && !empty($branch), function ($q) use ($branch) {
                $branchIds = Branch::where('branch_name', 'LIKE', "%{$branch}%")->pluck('id')->toArray();
                $q->where(function ($branchQuery) use ($branchIds) {
                    $branchQuery->whereIn('branch_id', $branchIds);
                    foreach ($branchIds as $branchId) {
                        $branchQuery->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branchId]);
                    }
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        $all_users = $all_user_details->map(fn($val) => [
            'id' => $val->id,
            'name' => $val->name,
        ])->toArray();

        // Get branches
        $branches = $this->getUniqueBranches($all_user_details);
        $zones = $this->getUniqueZones($all_user_details);

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
            'zones' => $zones,
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
        $branchZoneIds = [];
        foreach ($users as $user) {
            foreach (explode(',', (string) $user->branch_id) as $branchId) {
                $branchId = trim($branchId);
                if ($branchId !== '' && !isset($branchZoneIds[$branchId])) {
                    $branchZoneIds[$branchId] = $user->division_id;
                }
            }
        }

        $branchIds = $users->pluck('branch_id')
            ->flatMap(fn($branchId) => explode(',', (string) $branchId))
            ->map(fn($branchId) => trim($branchId))
            ->filter()
            ->unique()
            ->values();

        return Branch::whereIn('id', $branchIds)
            ->orderBy('branch_name')
            ->get()
            ->map(fn($branch) => [
                'id' => $branch->id,
                'name' => $branch->branch_name,
                'zone_id' => $branchZoneIds[$branch->id] ?? null,
            ])
            ->values()
            ->toArray();
    }

    private function getUniqueZones($users)
    {
        $zones = Division::whereIn('id', $users->pluck('division_id')->filter()->unique()->values())
            ->get()
            ->map(fn($zone) => [
                'id' => $zone->id,
                'name' => $zone->division_name,
            ])
            ->values()
            ->toArray();

        return $this->sortZoneList($zones);
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
            'buyerCustomer.customertypes',
            'sellerCustomer.customertypes',
            'buyerCustomer.customeraddress.cityname',
            'sellerCustomer.customeraddress.cityname',
            'orderdetails',
            'orderdetails.products'
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


        // Customer - Add & Update (single customer module)
        $customer_add = Customers::with(['customeraddress.cityname', 'customeraddress.statename', 'customertypes'])
            ->where('created_by', $user_id)
            ->whereDate('created_at', $date)
            ->get();

        // $customer_update = SecondaryCustomer::with(['city.statename', 'state'])
        //     ->where('created_by', $user_id)
        //     ->whereColumn('updated_at', '>', 'created_at')
        //     ->whereDate('updated_at', $date)
        //     ->get();
        $customer_update = Customers::with(['customeraddress.cityname', 'customeraddress.statename', 'customertypes'])
            ->where('created_by', $user_id)
            ->whereColumn('updated_at', '>', 'created_at')
            ->whereDate('updated_at', $date)
            ->get();

        $master_add = collect();
        $master_update = collect();
        $approvedCustomers = collect();
        $rejectedCustomers = collect();


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
                ?? $entity?->customeraddress?->cityname?->city_name
                ?? $entity?->billing_city
                ?? 'City not available';
            $customer = $entity instanceof Customers ? $this->customerActivitySummary($entity) : null;

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
                    'customer_mobile' => $customer['mobile'] ?? ($entity->mobile ?? $entity->mobile_number ?? null),
                    'customer_detail' => $customer,
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
                    'customer_mobile' => $customer['mobile'] ?? ($entity->mobile ?? $entity->mobile_number ?? null),
                    'customer_detail' => $customer,
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
            $seller = $this->customerActivitySummary($order->sellerCustomer);
            $buyer = $this->customerActivitySummary($order->buyerCustomer);

            $totalQty   = $order->orderdetails ? $order->orderdetails->sum('quantity') : 0;
            $grandTotal = $order->grand_total ?? 0;

            $orderData[] = [
                'title'        => 'Order',
                'time'         => date('H:i:s', strtotime($order->created_at)),   // raw time (keep for sorting)
                'date'         => date('Y-m-d', strtotime($order->created_at)),   // ← Added as requested
                'latitude'     => '',
                'longitude'    => '',

                // Key-Value fields as you want
                'seller'       => $seller['name'] ?? 'Unknown Seller',
                'seller_mobile' => $seller['mobile'] ?? null,
                'seller_customer' => $seller,
                'customer'     => $buyer['name'] ?? 'Unknown Buyer',
                'customer_mobile' => $buyer['mobile'] ?? null,
                'customer_detail' => $buyer,
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
            $customer = $this->customerActivitySummary($val);

            $customerAddData[] = [
                'title'         => 'New Customer Registration',
                'time'          => date('H:i:s', strtotime($val->created_at)),
                'date'          => $date,
                'latitude'      => $customer['latitude'] ?? '',
                'longitude'     => $customer['longitude'] ?? '',
                'time_display'  => date('h:i A', strtotime($val->created_at)),
                'location'      => $customer['address'] ?? 'No Location',
                'city'          => $customer['city'] ?? 'City not available',
                'state'         => $customer['state'] ?? 'State not available',
                'customer'      => $customer['name'] ?? 'Unknown Customer',
                'customer_mobile' => $customer['mobile'] ?? null,
                'customer_detail' => $customer,
                'customer_type' => $customer['customer_type'] ?? 'Customer'
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
            $customer = $this->customerActivitySummary($val);

            $customerUpdateData[] = [
                'title'         => 'Customer Edit',
                'time'          => date('H:i:s', strtotime($val->updated_at)),
                'date'          => $date,
                'latitude'      => $customer['latitude'] ?? '',
                'longitude'     => $customer['longitude'] ?? '',
                'time_display'  => date('h:i A', strtotime($val->updated_at)),
                'location'      => $customer['address'] ?? 'No Location',
                'city'          => $customer['city'] ?? 'City not available',
                'state'         => $customer['state'] ?? 'State not available',
                'customer'      => $customer['name'] ?? 'Unknown Customer',
                'customer_mobile' => $customer['mobile'] ?? null,
                'customer_detail' => $customer,
                'customer_type' => $customer['customer_type'] ?? 'Customer'
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
