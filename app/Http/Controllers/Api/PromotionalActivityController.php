<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromotionalActivity;
use App\Models\PromotionalGift;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PromotionalActivityController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tab' => ['nullable', Rule::in(['details', 'approval'])],
            'period' => ['nullable', Rule::in(['mtd', 'ytd'])],
            'activity_type_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $user = $request->user();
        $tab = $request->input('tab', 'details');
        $query = PromotionalActivity::with(['activityType:id,display_name,status_name', 'creator:id,name,reportingid', 'gifts:id,name']);

        if ($tab === 'details') {
            $query->where('created_by', $user->id);
        } else {
            $visibleUserIds = array_map('intval', getUsersReportingToAuth($user->id));
            $query->whereIn('created_by', array_values(array_diff($visibleUserIds, [(int) $user->id])));
        }

        $period = $request->input('period', 'mtd');
        $query->whereDate('activity_date', '>=', $period === 'ytd' ? now()->startOfYear() : now()->startOfMonth());
        if ($request->filled('activity_type_id')) $query->where('activity_status_id', $request->activity_type_id);

        $activities = $query->latest('activity_date')->get()->map(fn ($activity) => [
            'id' => $activity->id,
            'activity_type_id' => $activity->activity_status_id,
            'activity_type' => $activity->activityType->display_name ?? $activity->activityType->status_name ?? 'Promotional Activity',
            'activity_date' => $activity->activity_date->format('Y-m-d'),
            'location_name' => $activity->location_name,
            'company_share' => (float) $activity->company_share,
            'distributor_share' => (float) $activity->distributor_share,
            'total_amount' => (float) $activity->company_share + (float) $activity->distributor_share,
            'remark' => $activity->remark,
            'approval_status' => $activity->approval_status,
            'created_by' => $activity->creator,
            'gifts' => $activity->gifts->map(fn ($gift) => [
                'id' => $gift->id, 'name' => $gift->name, 'quantity' => (int) $gift->pivot->quantity,
            ])->values(),
        ])->values();

        return response()->json(['success' => true, 'data' => $activities]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activity_type_id' => ['required', 'integer', Rule::exists('statuses', 'id')->where(fn ($q) => $q->where('module', 'Promotional Activity')->where('active', 'Y'))],
            'activity_date' => 'required|date',
            'location_name' => 'required|string|max:255',
            'company_share' => 'required|numeric|min:0',
            'distributor_share' => 'required|numeric|min:0',
            'remark' => 'nullable|string|max:1000',
            'gifts' => 'nullable|array',
            'gifts.*.gift_id' => 'required|integer|exists:promotional_gifts,id',
            'gifts.*.quantity' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $user = $request->user();
        $giftRows = collect($request->input('gifts', []))->groupBy('gift_id')->map(fn ($rows, $giftId) => [
            'gift_id' => (int) $giftId,
            'quantity' => (int) $rows->sum('quantity'),
        ])->values();

        foreach ($giftRows as $row) {
            $gift = PromotionalGift::where('id', $row['gift_id'])->where('active', 'Y')->first();
            if (!$gift || $row['quantity'] > $gift->quantity) {
                return response()->json(['success' => false, 'message' => 'Selected gift quantity is not available.'], 422);
            }
        }

        $activity = DB::transaction(function () use ($request, $user, $giftRows) {
            $reportingManagerId = collect(explode(',', (string) $user->reportingid))->map(fn ($id) => trim($id))->first(fn ($id) => ctype_digit($id));
            $activity = PromotionalActivity::create([
                'activity_status_id' => $request->activity_type_id,
                'activity_date' => $request->activity_date,
                'location_name' => $request->location_name,
                'company_share' => $request->company_share,
                'distributor_share' => $request->distributor_share,
                'remark' => $request->remark,
                'approval_status' => 'pending',
                'created_by' => $user->id,
                'reporting_manager_id' => $reportingManagerId ?: null,
            ]);
            foreach ($giftRows as $row) {
                $activity->gifts()->attach($row['gift_id'], ['quantity' => $row['quantity']]);
            }
            return $activity;
        });

        return response()->json(['success' => true, 'message' => 'Promotional activity submitted for approval.', 'data' => ['id' => $activity->id, 'approval_status' => 'pending']], 201);
    }

    public function updateApproval(Request $request, PromotionalActivity $promotionalActivity)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'remark' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $visibleUserIds = array_map('intval', getUsersReportingToAuth($request->user()->id));
        abort_unless(in_array((int) $promotionalActivity->created_by, $visibleUserIds, true) && (int) $promotionalActivity->created_by !== (int) $request->user()->id, 403);
        abort_if($promotionalActivity->approval_status !== 'pending', 422, 'Activity has already been actioned.');

        $promotionalActivity->update([
            'approval_status' => $request->status,
            'approved_rejected_by' => $request->user()->id,
            'approved_rejected_at' => now(),
            'approval_remark' => $request->remark,
        ]);

        return response()->json(['success' => true, 'message' => 'Activity '.$request->status.' successfully.']);
    }
}
