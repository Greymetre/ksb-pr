<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromotionalActivity;
use App\Models\PromotionalGift;
use App\Models\MasterDistributor;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PromotionalActivityController extends Controller
{
    private function isSuperAdmin($user): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('superadmin')) {
            return true;
        }

        if ($user->roles()->where('name', 'superadmin')->exists()) {
            return true;
        }

        $userTypes = is_string($user->user_type)
            ? (json_decode($user->user_type, true) ?: [$user->user_type])
            : (array) $user->user_type;

        return in_array('superadmin', $userTypes, true);
    }

    private function isDirectManager($user, PromotionalActivity $activity): bool
    {
        if ($this->isSuperAdmin($user)) return true;
        $creator = $activity->creator;
        if (!$creator) return false;
        $managerIds = array_map('intval', array_filter(array_map('trim', explode(',', (string) $creator->reportingid))));
        return in_array((int) $user->id, $managerIds, true);
    }

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
            if (!$user->hasRole('superadmin')) {
                $query->where('created_by', $user->id);
            }
            $query->where('approval_status', 'completed');
        } else {
            $visibleUserIds = array_map('intval', getUsersReportingToAuth($user->id));
            // Approval workflow includes the authenticated user's own submissions
            // as well as submissions from everyone visible in their reporting hierarchy.
            $query->whereIn('created_by', array_values(array_unique(array_merge(
                $visibleUserIds,
                [(int) $user->id]
            ))))->where('approval_status', '!=', 'completed');
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
            'can_complete' => $activity->approval_status === 'approved' && (int) $activity->created_by === (int) $user->id,
        ])->values();

        return response()->json(['success' => true, 'data' => $activities]);
    }

    public function distributors(Request $request)
    {
        $request->validate(['activity_id' => 'required|integer|exists:promotional_activities,id']);
        $activity = PromotionalActivity::findOrFail($request->activity_id);
        $user = $request->user();
        $visibleUserIds = array_map('intval', getUsersReportingToAuth($user->id));
        abort_unless(
            $this->isSuperAdmin($user)
            || (int) $activity->created_by === (int) $user->id
            || in_array((int) $activity->created_by, $visibleUserIds, true),
            403
        );

        $isSuperAdmin = $this->isSuperAdmin($user);
        $query = MasterDistributor::query()
            ->select('id', 'legal_name', 'trade_name', 'distributor_code');

        if (!$isSuperAdmin) {
            $query->where(function ($statusQuery) {
                $statusQuery->whereNull('business_status')
                    ->orWhere('business_status', '')
                    ->orWhereRaw('LOWER(TRIM(business_status)) != ?', ['inactive']);
            });

            $creatorId = (int) $activity->created_by;
            $query->where(function ($assigned) use ($creatorId) {
                $assigned->where('created_by', $creatorId)
                    ->orWhereJsonContains('sales_executive_id', $creatorId)
                    ->orWhereJsonContains('sales_executive_id', (string) $creatorId)
                    ->orWhereRaw("JSON_SEARCH(sales_executive_id, 'one', ?) IS NOT NULL", [(string) $creatorId])
                    ->orWhere('sales_executive_id', (string) $creatorId);
            });
        }

        return response()->json(['success' => true, 'data' => $query->orderBy('legal_name')->get()]);
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

    public function show(Request $request, PromotionalActivity $promotionalActivity)
    {
        $promotionalActivity->load([
            'activityType:id,display_name,status_name',
            'creator:id,name,reportingid',
            'reportingManager:id,name,designation_id',
            'reportingManager.getdesignation:id,designation_name',
            'gifts:id,name',
            'distributor:id,name,customer_code,sap_code',
        ]);

        $user = $request->user();
        $visibleUserIds = array_map('intval', getUsersReportingToAuth($user->id));
        $canView = $user->hasRole('superadmin')
            || (int) $promotionalActivity->created_by === (int) $user->id
            || in_array((int) $promotionalActivity->created_by, $visibleUserIds, true);
        abort_unless($canView, 403);

        $canApprove = $promotionalActivity->approval_status === 'pending'
            && ($user->hasRole('superadmin') || (
                (int) $promotionalActivity->created_by !== (int) $user->id
                && $this->isDirectManager($user, $promotionalActivity)
            ));
        $canComplete = $promotionalActivity->approval_status === 'approved'
            && ((int) $promotionalActivity->created_by === (int) $user->id || $user->hasRole('superadmin'));

        return response()->json(['success' => true, 'data' => [
            'id' => $promotionalActivity->id,
            'activity_type' => $promotionalActivity->activityType->display_name ?? $promotionalActivity->activityType->status_name ?? 'Promotional Activity',
            'activity_date' => $promotionalActivity->activity_date->format('Y-m-d'),
            'location_name' => $promotionalActivity->location_name,
            'remark' => $promotionalActivity->remark,
            'approval_status' => $promotionalActivity->approval_status,
            'approval_remark' => $promotionalActivity->approval_remark,
            'company_share' => (float) $promotionalActivity->company_share,
            'distributor_share' => (float) $promotionalActivity->distributor_share,
            'total_amount' => (float) $promotionalActivity->company_share + (float) $promotionalActivity->distributor_share,
            'creator' => $promotionalActivity->creator,
            'reporting_manager' => $promotionalActivity->reportingManager ? [
                'id' => $promotionalActivity->reportingManager->id,
                'name' => $promotionalActivity->reportingManager->name,
                'designation' => $promotionalActivity->reportingManager->getdesignation->designation_name ?? '',
            ] : null,
            'gifts' => $promotionalActivity->gifts->map(fn ($gift) => [
                'id' => $gift->id,
                'name' => $gift->name,
                'quantity' => (int) $gift->pivot->quantity,
            ])->values(),
            'can_approve' => $canApprove,
            'can_complete' => $canComplete,
            'distributor' => $promotionalActivity->distributor,
            'activity_photos' => collect($promotionalActivity->activity_photos ?: [])->map(fn ($path) => url('storage/'.$path))->values(),
            'participants' => $promotionalActivity->participants ?: [],
            'execution_remark' => $promotionalActivity->execution_remark,
        ]]);
    }

    public function complete(Request $request, PromotionalActivity $promotionalActivity)
    {
        $user = $request->user();
        abort_unless((int) $promotionalActivity->created_by === (int) $user->id || $user->hasRole('superadmin'), 403);
        abort_if($promotionalActivity->approval_status !== 'approved', 422, 'Only an approved activity can be completed.');

        $participants = json_decode((string) $request->input('participants', '[]'), true);
        $request->merge(['participants_data' => $participants]);
        $validator = Validator::make($request->all(), [
            'distributor_id' => 'required|integer|exists:customers,id',
            'photos' => 'required|array|min:1|max:3',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'participants_data' => 'required|array|min:1|max:50',
            'participants_data.*.name' => 'required|string|max:150',
            'participants_data.*.mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'participants_data.*.address' => 'required|string|max:255',
            'execution_remark' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $photoPaths = [];
        foreach ($request->file('photos', []) as $photo) {
            $photoPaths[] = $photo->store('promotional-activities', 'public');
        }

        $promotionalActivity->update([
            'distributor_id' => $request->distributor_id,
            'activity_photos' => $photoPaths,
            'participants' => array_values($participants),
            'execution_remark' => $request->input('execution_remark'),
            'approval_status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Promotional activity completed successfully.']);
    }

    public function updateApproval(Request $request, PromotionalActivity $promotionalActivity)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'remark' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $promotionalActivity->load('creator:id,name,reportingid');
        $user = $request->user();
        abort_unless(
            $user->hasRole('superadmin') || (
                (int) $promotionalActivity->created_by !== (int) $user->id
                && $this->isDirectManager($user, $promotionalActivity)
            ),
            403
        );
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
