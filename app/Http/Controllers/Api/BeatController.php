<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;
use Gate;
use App\Models\Beat;
use App\Models\BeatCustomer;
use App\Models\BeatSchedule;
use App\Models\CheckIn;
use App\Models\Customers;
use Carbon\Carbon;
use App\Models\MasterDistributor;
use App\Models\SecondaryCustomer;

class BeatController extends Controller
{
    public function __construct()
    {
        $this->beats = new Beat();

        $this->successStatus = 200;
        $this->created = 201;
        $this->accepted = 202;
        $this->noContent = 204;
        $this->badrequest = 400;
        $this->unauthorized = 401;
        $this->notFound = 404;
        $this->notactive = 406;
        $this->internalError = 500;
    }

    // public function getBeatList(Request $request)

    
    // {
    //     try {
    //         $user_id = $request->user()->id;
    //         $pageSize = $request->input('pageSize');
    //         $beatDate = !empty($request->input('beatDate')) ? getcurentDate() : '';
    //         $query = BeatSchedule::with('beats', 'beatcheckininfo')->withCount(['beatcustomers as total_customers', 'beatcheckininfo as visited_customers', 'beatscheduleorders as order_count', 'beatschedulecustomer as new_customers'])
    //             ->where(function ($query) use ($user_id, $request) {
    //                 if (!empty($request['city_id'])) {
    //                     $cityids = explode(',', preg_replace('/\s*,\s*/', ',', $request['city_id']));
    //                     $query->whereHas('beats', function ($query) use ($cityids) {
    //                         $query->whereIn('city_id', $cityids);
    //                     });
    //                 }
    //                 $query->where('user_id', $user_id);
    //                 $query->whereDate('beat_date', '>=', date('Y-m-d'));
    //             });
    //         $db_data = (!empty($pageSize)) ? $query->paginate($pageSize) : $query->get();
    //         $data = collect([]);
    //         if ($db_data->isNotEmpty()) {
    //             $beats = $db_data->map(function ($item, $key) {
    //                 $item['beat_name'] = isset($item['beats']['beat_name']) ? $item['beats']['beat_name'] : '';
    //                 $item['beatscheduleid'] = isset($item['id']) ? $item['id'] : null;
    //                 $item['description'] = isset($item['beats']['description']) ? $item['beats']['description'] : '';
    //                 $item['visited_customers'] = $item['beatcheckininfo']->unique('customer_id', 'checkin_date')->count();
    //                 $item['remaining_customers'] = $item['total_customers'] - $item['visited_customers'];
    //                 $item['is_today'] = $item['beat_date'] == date('Y-m-d') ? true : false;
    //                 unset($item["id"], $item["active"], $item['user_id'], $item['created_at'], $item['updated_at'], $item['beats']);
    //                 return $item;
    //             });
    //             return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $beats], $this->successStatus);
    //         }
    //         return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
    //     }
    // }

    public function getBeatList(Request $request)
    {
        try {
            // ────────────────────────────────────────────────
            // 1. Determine which user to query for
            // ────────────────────────────────────────────────
            $targetUserId = $request->input('user_id')
                ? (int) $request->input('user_id')
                : $request->user()->id;   // fallback to authenticated user

            // ────────────────────────────────────────────────
            // 2. Determine date filter
            // ────────────────────────────────────────────────
            $beatDate = $request->input('beat_date'); // format: YYYY-MM-DD
            $isSpecificDate = !empty($beatDate) && Carbon::hasFormat($beatDate, 'Y-m-d');
            
            // ────────────────────────────────────────────────
            // Build base query
            // ────────────────────────────────────────────────
            $query = BeatSchedule::with('beats', 'beatcheckininfo')
                ->withCount([
                    'beatcustomers as total_customers',
                    'beatcheckininfo as visited_customers',
                    'beatscheduleorders as order_count',
                    'beatschedulecustomer as new_customers'
                ])
                ->where('user_id', $targetUserId);

            // Date filtering logic
            if ($isSpecificDate) {
                $query->whereDate('beat_date', $beatDate);
            }

            // Optional beat_id filter
            $query->when($request->input('beat_id'), function ($q) use ($request) {
                $q->where('beat_id', $request->input('beat_id'));
            });

            // Optional city_id filter (multiple cities)
            $query->when($request->input('city_id'), function ($q) use ($request) {
                $cityIds = array_filter(explode(',', preg_replace('/\s*,\s*/', ',', $request->city_id)));
                if (!empty($cityIds)) {
                    $q->whereHas('beats', function ($sub) use ($cityIds) {
                        $sub->whereIn('city_id', $cityIds);
                    });
                }
            });

            // Pagination or get all
            $pageSize = $request->input('pageSize', 15); // default to 15 if not sent
            $db_data = $query->paginate($pageSize);

            // ────────────────────────────────────────────────
            // Format response
            // ────────────────────────────────────────────────
            $beats = $db_data->through(function ($item) use ($beatDate) {
                $today = Carbon::today()->toDateString();

                // get all beat customer ids for this beat
                $beatCustomerIds = BeatCustomer::where('beat_id', $item->beat_id)
                    ->pluck('distributor_id')
                    ->filter()
                    ->unique()
                    ->toArray();
            
                // count today's visited customers using same logic as getBeatCustomers
                $visitedCount = CheckIn::where('user_id', $item->user_id)
                    ->where('entity_type', 'secondary_customer')
                    ->whereDate('checkin_date', $today)
                    ->whereIn('entity_id', $beatCustomerIds)
                    ->distinct('entity_id')
                    ->count('entity_id');

                return [
                    'beatscheduleid'    => $item->id,
                    'beat_id'           => $item->beat_id,
                    'beat_name'         => $item->beats->beat_name ?? '',
                    'description'       => $item->beats->description ?? '',
                    'beat_date'         => $item->beat_date,
                    'total_customers'   => $item->total_customers,
                    'visited_customers' => $visitedCount,
                    'remaining_customers' => $item->total_customers - $visitedCount,
                    'order_count'       => $item->order_count,
                    'new_customers'     => $item->new_customers,
                    'is_today'          => $item->beat_date === Carbon::today()->toDateString(),
                ];
            });

            return response()->json([
                'status'  => 'success',
                'message' => $db_data->isNotEmpty() ? 'Data retrieved successfully' : 'No Record Found',
                'data'    => $beats,
                'pagination' => [
                    'current_page' => $db_data->currentPage(),
                    'last_page'    => $db_data->lastPage(),
                    'per_page'     => $db_data->perPage(),
                    'total'        => $db_data->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                // 'trace'   => $e->getTraceAsString()   // ← only in development!
            ], 500);
        }
    }

    public function getBeatDropdownList(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $beats = Beat::whereHas('beatusers', function ($query) use ($user_id) {
                $query->where('user_id', '=', $user_id);
            })
                ->where(function ($query) use ($request) {
                    if (!empty($request['city_id'])) {
                        $cityids = explode(',', preg_replace('/\s*,\s*/', ',', $request['city_id']));
                        foreach ($cityids as $city_id) {
                            $query->orWhereRaw("FIND_IN_SET(?, city_id)", [$city_id]);
                        }
                    }
                })
                ->select('id as beat_id', 'beat_name', 'city_id')
                ->orderBy('city_id', 'asc')
                ->get();
            if ($beats->isNotEmpty()) {
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $beats], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $beats], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function getBeatCustomers(Request $request)
    {
        try {
            $beat_id = $request->input('beat_id');
            $user = $request->user();
    
            if (!$beat_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'beat_id is required',
                ], 400);
            }
    
            $pageSize = (int) $request->input('pageSize', 10);
            $search = $request->input('search');
            $city_name = $request->input('city_name');
            $status = $request->input('status');
            $today = now()->startOfDay()->toDateString();
    
            // Today's checked-in customers (for isvisited flag)
            $checkedin = CheckIn::where('user_id', $user->id)
                ->whereDate('checkin_date', $today)
                ->pluck('customer_id')
                ->toArray();
    
            // Base query
            $query = BeatCustomer::with([
                'distributor',
                'retailer',
                'distributorFull',
                'retailerFull',
                'beats'
            ])
            ->where('beat_id', $beat_id);
    
            // Global Search on customer fields
            if ($search) {
                $query->where(function ($q) use ($search) {
                    // Search in Secondary Customer (retailer)
                    $q->whereHas('retailerFull', function ($sub) use ($search) {
                        $sub->where(function ($s) use ($search) {
                            $s->where('owner_name', 'like', "%{$search}%")
                              ->orWhere('shop_name', 'like', "%{$search}%")
                              ->orWhere('mobile_number', 'like', "%{$search}%");
                        });
                    })
                    // Search in Master Distributor
                    ->orWhereHas('distributorFull', function ($sub) use ($search) {
                        $sub->where(function ($s) use ($search) {
                            $s->where('trade_name', 'like', "%{$search}%")
                              ->orWhere('mobile', 'like', "%{$search}%");
                        });
                    });
                });
            }
            // Filter by City Name (only applicable for Secondary Customers)
            if ($city_name) {
                $query->whereHas('retailerFull.city', function ($q) use ($city_name) {
                    $q->where('city_name', 'like', "%{$city_name}%");
                });
            }
    
            // Filter by Status (only applicable for Secondary Customers)
            if ($status) {
                $query->whereHas('retailerFull', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }
            // ── Add Last Check-in / Check-out Data using Subqueries ──
            $query->addSelect([
                // Check-in fields
                'last_checkin_date' => \App\Models\CheckIn::select('checkin_date')
                    ->whereColumn('entity_id', 'beat_customers.distributor_id')   // Adjust if your foreign key is different
                    ->where('entity_type', 'secondary_customer')                         // Change if your entity_type is different
                    ->where('user_id', $user->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
    
                'last_checkin_time' => \App\Models\CheckIn::select('checkin_time')
                    ->whereColumn('entity_id', 'beat_customers.distributor_id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $user->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
    
                'has_checked_in_today' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'beat_customers.distributor_id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $user->id)
                    ->whereDate('checkin_date', $today),
    
                // Check-out fields
                'last_checkout_date' => \App\Models\CheckIn::select('checkout_date')
                    ->whereColumn('entity_id', 'beat_customers.distributor_id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $user->id)
                    ->whereNotNull('checkout_date')
                    ->orderByDesc('checkout_date')
                    ->orderByDesc('checkout_time')
                    ->limit(1),
    
                'last_checkout_time' => \App\Models\CheckIn::select('checkout_time')
                    ->whereColumn('entity_id', 'beat_customers.distributor_id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $user->id)
                    ->whereNotNull('checkout_date')
                    ->orderByDesc('checkout_date')
                    ->orderByDesc('checkout_time')
                    ->limit(1),
    
                'has_checked_out_today' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'beat_customers.distributor_id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $user->id)
                    ->whereDate('checkout_date', $today),
    
                'current_visit_is_open' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'beat_customers.distributor_id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $user->id)
                    ->whereNull('checkout_date')
                    ->whereDate('checkin_date', $today),
    
                'last_checkin_id' => \App\Models\CheckIn::select('id')
                    ->whereColumn('entity_id', 'beat_customers.distributor_id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $user->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
            ]);
            
            // Order by newest first
            $query->orderBy('id', 'desc');   // or 'created_at' if you have the column
                
            // Pagination
            $beatCustomers = $query->paginate($pageSize);
            
            // Transform data
            $data = collect();
    
            foreach ($beatCustomers->items() as $item) {
                $customer = $item->customer_full;
    
                if (!$customer) {
                    continue;
                }
    
                $data->push([
                    'beat_customer_id'     => $item->id,
                    'beat_id'              => $item->beat_id,
                    'beat_name'            => optional($item->beats)->beat_name,
                    'customer_type'        => $item->customer_type,
                    'isvisited'            => in_array($customer->id, $checkedin),
                    // 'customer_id'      => $item,
                    // Last Visit / Check-in Data
                    'customer' => [
                    ...$customer->toArray(),
        
                    'isvisited' => $item->isvisited,
                    'last_checkin_date' => $item->last_checkin_date,
                    'last_checkin_time' => $item->last_checkin_time,
                    'has_checked_in_today' => $item->has_checked_in_today,
                    'last_checkout_date' => $item->last_checkout_date,
                    'last_checkout_time' => $item->last_checkout_time,
                    'has_checked_out_today' => $item->has_checked_out_today,
                    'current_visit_is_open' => $item->current_visit_is_open,
                    'last_checkin_id' => $item->last_checkin_id,
                ],
                ]);
            }
    
            // Clean pagination response (same format as index())
            $cleanData = [
                'current_page' => $beatCustomers->currentPage(),
                'data'         => $data,
                'from'         => $beatCustomers->firstItem(),
                'to'           => $beatCustomers->lastItem(),
                'per_page'     => $beatCustomers->perPage(),
                'total'        => $beatCustomers->total(),
                'last_page'    => $beatCustomers->lastPage(),
            ];
    
            return response()->json([
                'status'  => 'success',
                'message' => 'Data retrieved successfully',
                'data'    => $cleanData
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch beat customers',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function userScheduleBeat(Request $request)
    {
        try {
            $userid = $request->user()->id;
            $validator = Validator::make($request->all(), [
                'beats.*'  => "required",
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }
            $collection = array();
            if (is_array($request['beats'])) {
                foreach ($request['beats'] as $key => $beat) {
                    array_push($collection, array(
                        "user_id" => $userid,
                        'beat_id' => $beat,
                        'beat_date' => date('Y-m-d'),
                        'created_at' => date('Y-m-d H:i:s')
                    ));
                }
            }
            if (BeatSchedule::insert($collection)) {
                return response()->json(['status' => 'success', 'message' => 'Data inserted successfully.'], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'Error in No Record Found.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function getTodaySchedul(Request $request)
    {
        try {
            $userid = $request->user()->id;
            $todayDate = Carbon::today()->toDateString();
            $data = BeatSchedule::with('beats')->where('user_id', $userid)->where('beat_date', $todayDate)->get();

            foreach ($data as $key => $value) {
                $data[$key]['beats']['city_id'] = (string)$value->beats->city_id;
                $data[$key]['beats']['state_id'] = (string)$value->beats->state_id;
                $data[$key]['beats']['district_id'] = (string)$value->beats->district_id;
            }

            return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }
}
