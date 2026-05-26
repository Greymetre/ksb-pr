<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\City;
use App\Models\TourDetail;
use App\Models\TourProgramme;
use App\Models\User;
use App\Models\TourLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\District;
use Illuminate\Support\Facades\Auth;

class TourPlanController extends Controller
{
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $user_id    = $request->input('user_id');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $perPage    = $request->input('per_page', 30);   // ← make it paginated like global()

        // Build query
        $query = TourProgramme::query()
            ->where('userid', $user_id)
            ->orderBy('date', 'desc');

        // Optional date range filter
        if ($start_date && $end_date) {
            $start = date('Y-m-d', strtotime($start_date));
            $end   = date('Y-m-d', strtotime($end_date));
            $query->whereBetween('date', [$start, $end]);
        }

        // Paginate (recommended over ->take(30)->get())
        $tour_plans = $query->paginate($perPage);

        // ────────────────────────────────────────────────
        // Collect unique town & district IDs (only from current page)
        // ────────────────────────────────────────────────
        $townIds = $tour_plans->pluck('town')->unique()->filter()->values()->all();
        $districtIds = $tour_plans->pluck('district')->unique()->filter()->values()->all();

        // Fetch city names
        $cities = [];
        if (!empty($townIds)) {
            $cities = City::whereIn('id', $townIds)
                ->pluck('city_name', 'id')
                ->all();
        }

        // Fetch district names
        $districts = [];
        if (!empty($districtIds)) {
            $districts = District::whereIn('id', $districtIds)
                ->pluck('district_name', 'id')
                ->all();
        }

        // ────────────────────────────────────────────────
        // Format each record
        // ────────────────────────────────────────────────
        $formatted = $tour_plans->through(function ($plan) use ($cities, $districts, $user_id) {
            $plan->date = date('d-m-Y', strtotime($plan->date));

            $plan->status = match ($plan->status) {
                0    => 'Pending',
                1     => 'Approved',
                2 => 'Rejected',
            };

            $plan->self = ($plan->userid == auth()->id()) ? "true" : "false";

            // Add readable names (with fallback to ID string if not found)
            $plan->town_name     = $cities[$plan->town]     ?? (string) $plan->town;
            $plan->district_name = $districts[$plan->district] ?? (string) $plan->district;

            return $plan;
        });

        return response()->json([
            'status'  => 'success',
            'message' => $tour_plans->isNotEmpty() ? 'Data retrieved successfully.' : 'No records found.',
            'data'    => $formatted,
            'pagination' => [
                'current_page' => $tour_plans->currentPage(),
                'last_page'    => $tour_plans->lastPage(),
                'per_page'     => $tour_plans->perPage(),
                'total'        => $tour_plans->total(),
            ]
        ], 200);
    }

    //------------------------

    private function addTourLog($tourId, $action, $status, $remark = null)
    {
        TourLog::create([
            'tour_programme_id' => $tourId,
            'action'            => $action,
            'status'            => $status,
            'performed_by'      => Auth::id(),
            'remark'            => $remark,
        ]);
    }

    //------------------------

    public function user_list(Request $request)
    {
        $authUser = $request->user();
        $authUserId = $authUser->id;

        // Input parameters
        $pageSize     = $request->input('pageSize', 20);           // better default than 100
        $search_name  = trim($request->input('search_name') ?? '');
        $search_branches = $request->input('search_branches', []); // expect array of branch IDs

        // Get all users reporting to the authenticated user
        $reportingUserIds = getUsersReportingToAuth($authUserId);

        if (empty($reportingUserIds)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No reporting users found.',
                'branches' => [],
                'users'    => [],
                'data'     => [],
                'page_count' => 1,
            ], 200);
        }

        // ────────────────────────────────────────────────
        // Branch list (all unique branches of reporting users)
        // ────────────────────────────────────────────────
        // $branches = Branch::query()
        //     ->whereIn('id', function ($q) use ($reportingUserIds) {
        //         $q->selectRaw('DISTINCT TRIM(BOTH "," FROM REGEXP_REPLACE(branch_id, ",+", ","))')
        //         ->from('users')
        //         ->whereIn('id', $reportingUserIds);
        //     })
        //     ->orderBy('branch_name')
        //     ->get(['id', 'branch_name as name'])
        //     ->map(function ($branch) {
        //         return ['id' => $branch->id, 'name' => $branch->name];
        //     })
        //     ->all();

        // ────────────────────────────────────────────────
        // Base query for users
        // ────────────────────────────────────────────────
        $query = User::query()
            ->whereIn('id', $reportingUserIds)
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('id', config('constants.customer_roles'));
            })
            ->with([
                'getbranch' => fn($q) => $q->select('id', 'branch_name')
            ])
            ->select('id', 'name', 'branch_id');

        // Apply branch filter (multiple branches supported)
        if (!empty($search_branches) && is_array($search_branches)) {
            $search_branches = array_filter(array_map('trim', $search_branches), 'strlen');

            if (!empty($search_branches)) {
                $query->where(function ($q) use ($search_branches) {
                    foreach ($search_branches as $branchId) {
                        $q->orWhereRaw("FIND_IN_SET(?, branch_id)", [$branchId]);
                    }
                });
            }
        }

        // Apply name search
        if ($search_name !== '') {
            $query->where('name', 'like', "%{$search_name}%");
        }

        // Get paginated users
        $paginatedUsers = $query->orderBy('name')->paginate($pageSize);

        // Prepare simple user list for dropdown / autocomplete (all matching users)
        // $all_users = $query->get(['id', 'name'])->map(fn($u) => [
        //     'id'   => $u->id,
        //     'name' => $u->name,
        // ])->all();

        // Prepare response data (paginated list)
        $data = $paginatedUsers->through(fn($user) => [
            'user_id' => $user->id,
            'name'    => $user->name,
            // You can add more fields if frontend needs them
            // 'branch'  => optional($user->getbranch)->branch_name,
        ]);

        return response()->json([
            'status'     => 'success',
            'message'    => 'Data retrieved successfully.',
            // 'branches'   => $branches,
            // 'users'      => $all_users,           // all matching users (for dropdown)
            'data'       => $data,                // current page
            'page_count' => $paginatedUsers->lastPage(),
            'total'      => $paginatedUsers->total(),
            'current_page' => $paginatedUsers->currentPage(),
        ], 200);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|integer|exists:users,id',
            'date'        => 'required|array',
            'date.*'      => 'required|date',
            'town'        => 'required|array',
            'town.*'      => 'required',
            'district'    => 'required|array',
            'district.*'  => 'nullable', // or 'required' if you want to force it
            'objectives'  => 'required|array',
            'objectives.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        $created_by = auth()->id();
        $user_id    = $request->input('user_id');
        $dates      = $request->input('date');
        $towns      = $request->input('town');
        $districts  = $request->input('district');
        $objectives = $request->input('objectives');

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($dates as $k => $dateRaw) {
            $date = date('Y-m-d', strtotime($dateRaw));

            $town      = $towns[$k]      ?? null;
            $district  = $districts[$k]  ?? null;
            $objective = $objectives[$k] ?? null;

            // ────────────────────────────────────────────────
            // Unique key = user + date + town + district
            // ────────────────────────────────────────────────
            // $tour = TourProgramme::updateOrCreate(
            //     [
            //         'userid'   => $user_id,
            //         'date'     => $date,
            //         'town'     => $town,
            //         'district' => $district,
            //     ],
            //     [
            //         'objectives'  => $objective,
            //         'created_by'  => $created_by,
            //         'status'      => '0',           // reset to pending on update?
            //         'updated_at'  => now(),         // force fresh timestamp
            //     ]
            // );

            $tour = TourProgramme::create([
                'userid'     => $user_id,
                'date'       => $date,
                'town'       => $town,
                'district'   => $district,
                'objectives' => $objective,
                'created_by' => $created_by,
                'status'     => '0',
            ]);

            // if ($tour->wasRecentlyCreated) {
            $createdCount++;
            // } else {
            //     $updatedCount++;
            // }

            // ────────────────────────────────────────────────
            // Your existing TourDetail logic
            // (moved inside loop so it runs for every saved tour)
            // ────────────────────────────────────────────────
            $city = City::where('city_name', $town)->first();

            if ($city) {
                $lastVisited = TourDetail::whereHas('tourinfo', function ($q) use ($user_id) {
                    $q->where('userid', $user_id);
                })
                    ->where('visited_cityid', $city->id)
                    ->whereNotNull('visited_date')
                    ->latest('visited_date')
                    ->value('visited_date');

                TourDetail::create([
                    'tourid'       => $tour->id,
                    'city_id'      => $city->id,
                    'last_visited' => $lastVisited,
                ]);
            }
        }

        $message = "Tour plan(s) processed successfully. ";
        $message .= $createdCount ? "Created: $createdCount. " : "";
        $message .= $updatedCount ? "Updated: $updatedCount." : "";

        return response()->json([
            'status'  => 'success',
            'message' => trim($message) ?: 'No changes made.',
        ], 200);
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => 'required|array',
            'user_id' => 'required',
            'date' => 'required|array',
            'town' => 'required|array',
            'objectives' => 'required|array',
            'status' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>  $validator->errors()], 400);
        }
        $tour_id = $request->input('tour_id');
        $user_id = $request->input('user_id');
        $date = $request->input('date');
        $town = $request->input('town');
        $objectives = $request->input('objectives');
        $status = $request->input('status');


        foreach ($tour_id as $k => $val) {
            $tour_plan = TourProgramme::find($val);

            if ($tour_plan) {
                $tour_plan->date = date('Y-m-d', strtotime($date[$k]));
                $tour_plan->userid = $user_id;
                $tour_plan->town = $town[$k];
                $tour_plan->objectives = $objectives[$k];
                $tour_plan->status = $status[$k];
                $tour_plan->save();
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Data updated successfully.'], 200);
    }

    public function global(Request $request)
    {
        // Optional filters
        $start_date     = $request->input('start_date');
        $end_date       = $request->input('end_date');
        $search_user_id = $request->input('user_id');

        $authUserId = auth()->id();   // Current logged-in user (from token)
        // If search_user_id is provided, we calculate hierarchy for that user
        $targetUserId = $search_user_id ? $search_user_id : $authUserId;
        $query = TourProgramme::query()
            ->with(['user' => function ($q) {
                $q->select('id', 'name');
            }])
            ->orderBy('date', 'desc');

        if ($start_date && $end_date) {
            $start = date('Y-m-d', strtotime($start_date));
            $end   = date('Y-m-d', strtotime($end_date));
            $query->whereBetween('date', [$start, $end]);
        }

        if ($search_user_id) {
            $query->where('userid', $search_user_id);
        }

        $perPage    = $request->input('per_page', 30);
        $tour_plans = $query->paginate($perPage);

        // Pre-calculate hierarchy levels for all users in current page (Best for performance)
        $hierarchy_level = getHierarchyLevel($targetUserId, $authUserId);

        $hierarchy_label = match ($hierarchy_level) {
            0   => 'Self',
            -1  => 'Not in Hierarchy',
            default => 'Level ' . $hierarchy_level
        };
        // ────────────────────────────────────────────────
        // Collect unique town (city) and district IDs from current page only
        // ────────────────────────────────────────────────
        $townIds = $tour_plans->pluck('town')->unique()->filter()->values()->all();
        $districtIds = $tour_plans->pluck('district')->unique()->filter()->values()->all();

        // Fetch city names (town → city_name)
        $cities = [];
        if (!empty($townIds)) {
            $cities = City::whereIn('id', $townIds)
                ->pluck('city_name', 'id')
                ->all();
        }

        // Fetch district names
        $districts = [];
        if (!empty($districtIds)) {
            $districts = District::whereIn('id', $districtIds)
                ->pluck('district_name', 'id')
                ->all();
        }

        // ────────────────────────────────────────────────
        // Format the response (add town_name & district_name)
        // ────────────────────────────────────────────────
        $formatted = $tour_plans->through(function ($plan) use ($cities, $districts) {

            $plan->date = date('d-m-Y', strtotime($plan->date));

            $plan->status = match ($plan->status) {
                0     => 'Pending',
                1     => 'Approved',
                default => 'Rejected',
            };

            $plan->self = ($plan->userid == auth()->id()) ? "true" : "false";

            // Add names (fallback to ID if not found)
            $plan->town_name     = $cities[$plan->town]     ?? (string) $plan->town;
            $plan->district_name = $districts[$plan->district] ?? (string) $plan->district;


            return $plan;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Global tour plans retrieved successfully.',
            'hierarchy_level' => $hierarchy_level,     // ← Outside the data (as requested)
            'hierarchy_label' => $hierarchy_label,     // ← Outside the data
            'data'    => $formatted,
            'pagination' => [
                'current_page' => $tour_plans->currentPage(),
                'last_page'    => $tour_plans->lastPage(),
                'per_page'     => $tour_plans->perPage(),
                'total'        => $tour_plans->total(),
            ]
        ], 200);
    }

    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => 'required|integer|exists:tour_programmes,id',
            'status'  => 'required|in:0,1,2',
            'remark'  => 'nullable|string'
        ]);

        // Extra validation: remark required if rejected
        $validator->after(function ($validator) use ($request) {
            if ($request->status == 2 && empty($request->remark)) {
                $validator->errors()->add('remark', 'Remark is required when rejecting.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $tour = TourProgramme::find($request->tour_id);

        $tour->status = $request->status;

        // Save remark only if rejected
        if ($request->status == 2) {
            $tour->remark = $request->remark;
        } else {
            $tour->remark = null; // optional: clear remark on approve
        }

        $tour->save();

        //------------------------

        $statusLabels = [
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected',
        ];

        $action = match ((int)$request->status) {
            1 => 'approved',
            2 => 'rejected',
            0 => 'pending',
            default => 'status_changed',
        };

        $this->addTourLog(
            $tour->id,
            $action,
            $request->status,
            $request->remark
                ? $request->remark
                : 'Status changed to ' . ($statusLabels[$request->status] ?? 'Unknown')
        );

        //------------------------

        return response()->json([
            'status'  => 'success',
            'message' => 'Status updated successfully.',
            'data'    => [
                'tour_id' => $tour->id,
                'status'  => $tour->status,
                'remark'  => $tour->remark
            ]
        ], 200);
    }
}
