<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TourProgramme;
use App\Models\TourDetail;
use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TourProgrammeApiController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:users'); // ← very important – same guard as other field user APIs
    }

    /**
     * GET /api/tour-plans
     * List upcoming / recent tour plans of the logged-in user
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $status  = $request->input('status'); // optional: 0=pending,1=approved,2=rejected

        $query = TourProgramme::where('userid', Auth::id())
            ->with(['city', 'districtRelation', 'tourdetails'])
            ->orderBy('date', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        $tours = $query->paginate($perPage);

        return response()->json([
            'status'   => 'success',
            'data'     => $tours->items(),
            'meta'     => [
                'current_page' => $tours->currentPage(),
                'last_page'    => $tours->lastPage(),
                'per_page'     => $tours->perPage(),
                'total'        => $tours->total(),
            ]
        ]);
    }

    /**
     * POST /api/tour-plans
     * Create one or multiple tour plans
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tours'                => 'required|array|min:1',
            'tours.*.date'         => 'required|date',
            'tours.*.town'         => 'required|string|max:150',     // city name
            'tours.*.district'     => 'nullable|string|max:150',
            'tours.*.objectives'   => 'nullable|string|max:500',
            'tours.*.type'         => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $created = [];

        foreach ($request->tours as $item) {
            // Try to resolve city & district IDs (optional – depends on your needs)
            $city = City::where('city_name', trim($item['town']))->first();
            $district = $item['district']
                ? District::where('district_name', trim($item['district']))->first()
                : null;

            $tour = TourProgramme::create([
                'date'       => $item['date'],
                'userid'     => Auth::id(),
                'town'       => $city ? $city->id : $item['town'],          // ← you decide: ID or name
                'district'   => $district ? $district->id : ($item['district'] ?? null),
                'objectives' => $item['objectives'] ?? null,
                'type'       => $item['type'] ?? 'field_visit',
                'status'     => 0, // pending
            ]);

            // Optional: create TourDetail if city was resolved
            if ($city) {
                TourDetail::create([
                    'tourid'   => $tour->id,
                    'city_id'  => $city->id,
                    // 'last_visited' etc...
                ]);
            }

            $created[] = $tour->load(['city', 'districtRelation']);
        }

        return response()->json([
            'status'  => 'success',
            'message' => count($created) . ' tour plan(s) created',
            'data'    => $created
        ], 201);
    }

    /**
     * GET /api/tour-plans/{id}
     */
    public function show($id)
    {
        $tour = TourProgramme::where('userid', Auth::id())
            ->with(['city', 'districtRelation', 'tourdetails', 'userinfo'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $tour
        ]);
    }

    /**
     * PUT / PATCH /api/tour-plans/{id}
     */
    public function update(Request $request, $id)
    {
        $tour = TourProgramme::where('userid', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'date'       => 'sometimes|required|date',
            'town'       => 'sometimes|required|string|max:150',
            'district'   => 'nullable|string|max:150',
            'objectives' => 'nullable|string|max:500',
            'type'       => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['date', 'objectives', 'type']);

        if ($request->filled('town')) {
            $city = City::where('city_name', trim($request->town))->first();
            $updateData['town'] = $city ? $city->id : $request->town;
        }

        if ($request->filled('district')) {
            $district = District::where('district_name', trim($request->district))->first();
            $updateData['district'] = $district ? $district->id : $request->district;
        }

        $tour->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Tour plan updated',
            'data'    => $tour->fresh(['city', 'districtRelation'])
        ]);
    }

    /**
     * DELETE /api/tour-plans/{id}
     */
    public function destroy($id)
    {
        $tour = TourProgramme::where('userid', Auth::id())->findOrFail($id);

        TourDetail::where('tourid', $tour->id)->delete();
        $tour->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Tour plan deleted'
        ]);
    }

    public function globalList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'per_page'   => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        // ──────────────────────────────────────────────
        // REMOVE or COMMENT these lines:
        // if (!auth()->user()->hasRole('admin')) {
        //     return response()->json(['status' => 'error', 'message' => 'Unauthorized access'], 403);
        // }
        // ──────────────────────────────────────────────

        $query = TourProgramme::with('user')
            ->orderBy('date', 'desc');

        if ($request->start_date && $request->end_date) {
            $start = date('Y-m-d', strtotime($request->start_date));
            $end   = date('Y-m-d', strtotime($request->end_date));
            $query->whereBetween('date', [$start, $end]);
        }

        $perPage    = $request->input('per_page', 30);
        $tour_plans = $query->paginate($perPage);

        $tour_plans->getCollection()->transform(function ($plan) {
            $plan->date         = date('d-m-Y', strtotime($plan->date));
            $plan->status_label = match ((int) $plan->status) {
                0 => 'Pending',
                1 => 'Approved',
                default => 'Rejected',
            };
            $plan->user_name = $plan->user?->name ?? 'Unknown';
            $plan->self      = ($plan->userid === auth()->id()) ? "true" : "false";

            return $plan;
        });

        return response()->json([
            'status'     => 'success',
            'message'    => $tour_plans->isNotEmpty() ? 'Global tour plans retrieved.' : 'No records found.',
            'data'       => $tour_plans->items(),
            'pagination' => [
                'current_page' => $tour_plans->currentPage(),
                'last_page'    => $tour_plans->lastPage(),
                'per_page'     => $tour_plans->perPage(),
                'total'        => $tour_plans->total(),
            ]
        ], 200);
    }
}