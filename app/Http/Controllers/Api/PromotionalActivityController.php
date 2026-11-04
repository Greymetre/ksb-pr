<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromotionalActivity;
use App\Models\PromotionalGift;
use App\Models\MasterDistributor;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\PromotionalActivityExecution;

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
            'request_no' => $activity->request_no,
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

    // Request form rules, shared by a new request and a resubmission after "revision required".
    private function requestValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'activity_type_id' => ['required', 'integer', Rule::exists('statuses', 'id')->where(fn ($q) => $q->where('module', 'Promotional Activity')->where('active', 'Y'))],
            'activity_date' => 'required|date',
            'location_name' => 'required|string|max:255',
            'distributor_id' => 'required|integer|exists:customers,id',
            'purpose' => 'required|string|max:1000',
            'estimated_budget' => 'required|numeric|min:0',
            'company_share' => 'required|numeric|min:0',
            'distributor_share' => 'required|numeric|min:0',
            'remark' => 'nullable|string|max:1000',
            'gifts' => 'nullable|array',
            'gifts.*.gift_id' => 'required|integer|exists:promotional_gifts,id',
            'gifts.*.quantity' => 'required|integer|min:1',
            'attachments' => 'nullable|array|max:'.PromotionalActivity::MAX_ATTACHMENTS,
            'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'keep_attachments' => 'nullable|array',
            'keep_attachments.*' => 'string',
        ]);
    }

    // Gift rows merged by gift, or null when a gift is inactive or short of stock.
    private function availableGiftRows(Request $request)
    {
        $giftRows = collect($request->input('gifts', []))->groupBy('gift_id')->map(fn ($rows, $giftId) => [
            'gift_id' => (int) $giftId,
            'quantity' => (int) $rows->sum('quantity'),
        ])->values();

        foreach ($giftRows as $row) {
            $gift = PromotionalGift::where('id', $row['gift_id'])->where('active', 'Y')->first();
            if (!$gift || $row['quantity'] > $gift->quantity) return null;
        }
        return $giftRows;
    }

    private function requestFields(Request $request): array
    {
        return [
            'activity_status_id' => $request->activity_type_id,
            'activity_date' => $request->activity_date,
            'location_name' => $request->location_name,
            'distributor_id' => $request->distributor_id,
            'purpose' => $request->purpose,
            'estimated_budget' => $request->estimated_budget,
            'company_share' => $request->company_share,
            'distributor_share' => $request->distributor_share,
            'remark' => $request->remark,
        ];
    }

    private function storeAttachments(Request $request): array
    {
        $paths = [];
        foreach ($request->file('attachments', []) as $attachment) {
            $paths[] = $attachment->store('promotional-activities/attachments', 'public');
        }
        return $paths;
    }

    public function store(Request $request)
    {
        $validator = $this->requestValidator($request);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $user = $request->user();
        $giftRows = $this->availableGiftRows($request);
        if ($giftRows === null) {
            return response()->json(['success' => false, 'message' => 'Selected gift quantity is not available.'], 422);
        }

        $attachmentPaths = $this->storeAttachments($request);

        try {
            $activity = DB::transaction(function () use ($request, $user, $giftRows, $attachmentPaths) {
                $reportingManagerId = collect(explode(',', (string) $user->reportingid))->map(fn ($id) => trim($id))->first(fn ($id) => ctype_digit($id));
                $activity = PromotionalActivity::create($this->requestFields($request) + [
                    'attachments' => $attachmentPaths,
                    'approval_status' => 'pending',
                    'created_by' => $user->id,
                    'reporting_manager_id' => $reportingManagerId ?: null,
                ]);
                foreach ($giftRows as $row) {
                    $activity->gifts()->attach($row['gift_id'], ['quantity' => $row['quantity']]);
                }
                return $activity;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($attachmentPaths);
            throw $e;
        }

        $activity->notifyManagers($user->name.' has submitted promotional activity '.$activity->request_no.' for approval.', 'Promotional activity approval');

        return response()->json(['success' => true, 'message' => 'Promotional activity '.$activity->request_no.' submitted for approval.', 'data' => ['id' => $activity->id, 'request_no' => $activity->request_no, 'approval_status' => 'pending']], 201);
    }

    // The creator corrects a request the manager sent back and submits it again for approval.
    public function resubmit(Request $request, PromotionalActivity $promotionalActivity)
    {
        $user = $request->user();
        abort_unless((int) $promotionalActivity->created_by === (int) $user->id, 403);
        abort_if($promotionalActivity->approval_status !== 'revision_required', 422, 'Only an activity sent back for correction can be resubmitted.');

        $validator = $this->requestValidator($request);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $existing = $promotionalActivity->attachments ?: [];
        $kept = array_values(array_intersect($existing, (array) $request->input('keep_attachments', [])));
        if (count($kept) + count($request->file('attachments', [])) > PromotionalActivity::MAX_ATTACHMENTS) {
            return response()->json(['success' => false, 'message' => 'Only '.PromotionalActivity::MAX_ATTACHMENTS.' attachments can be added.'], 422);
        }

        $giftRows = $this->availableGiftRows($request);
        if ($giftRows === null) {
            return response()->json(['success' => false, 'message' => 'Selected gift quantity is not available.'], 422);
        }

        $attachmentPaths = $this->storeAttachments($request);

        try {
            DB::transaction(function () use ($request, $promotionalActivity, $giftRows, $kept, $attachmentPaths) {
                $activity = PromotionalActivity::whereKey($promotionalActivity->id)->lockForUpdate()->firstOrFail();
                abort_if($activity->approval_status !== 'revision_required', 422, 'Only an activity sent back for correction can be resubmitted.');

                // The manager's correction remark stays visible until the next decision.
                $activity->update($this->requestFields($request) + [
                    'attachments' => array_merge($kept, $attachmentPaths),
                    'approval_status' => 'pending',
                    'approved_rejected_by' => null,
                    'approved_rejected_at' => null,
                ]);
                $activity->gifts()->sync($giftRows->mapWithKeys(fn ($row) => [$row['gift_id'] => ['quantity' => $row['quantity']]])->all());
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($attachmentPaths);
            throw $e;
        }
        Storage::disk('public')->delete(array_values(array_diff($existing, $kept)));

        $promotionalActivity->notifyManagers($user->name.' has corrected and resubmitted promotional activity '.$promotionalActivity->request_no.' for approval.', 'Promotional activity resubmitted');

        return response()->json(['success' => true, 'message' => 'Promotional activity '.$promotionalActivity->request_no.' resubmitted for approval.']);
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

        $canApprove = $promotionalActivity->approval_status === 'pending' && $this->canManagerAct($user, $promotionalActivity);
        $canReview = $promotionalActivity->approval_status === 'closure_pending' && $this->canManagerAct($user, $promotionalActivity);
        $canComplete = $promotionalActivity->approval_status === 'approved'
            && ((int) $promotionalActivity->created_by === (int) $user->id || $user->hasRole('superadmin'));
        $canResubmit = $promotionalActivity->approval_status === 'revision_required'
            && (int) $promotionalActivity->created_by === (int) $user->id;

        return response()->json(['success' => true, 'data' => [
            'id' => $promotionalActivity->id,
            'request_no' => $promotionalActivity->request_no,
            'activity_type_id' => $promotionalActivity->activity_status_id,
            'activity_type' => $promotionalActivity->activityType->display_name ?? $promotionalActivity->activityType->status_name ?? 'Promotional Activity',
            'activity_date' => $promotionalActivity->activity_date->format('Y-m-d'),
            'location_name' => $promotionalActivity->location_name,
            'purpose' => $promotionalActivity->purpose,
            'estimated_budget' => $promotionalActivity->estimated_budget !== null ? (float) $promotionalActivity->estimated_budget : null,
            'attachments' => collect($promotionalActivity->attachments ?: [])->values(),
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
            'can_resubmit' => $canResubmit,
            'can_review' => $canReview,
            'distributor' => $promotionalActivity->distributor,
            'activity_photos' => collect($promotionalActivity->activity_photos ?: [])->values(),
            'participants' => $promotionalActivity->participants ?: [],
            'execution_remark' => $promotionalActivity->execution_remark,
            'actual_expense' => $promotionalActivity->actual_expense !== null ? (float) $promotionalActivity->actual_expense : null,
            'over_budget' => $promotionalActivity->isOverBudget(),
            'bills' => collect($promotionalActivity->bills ?: [])->values(),
            'review_remark' => $promotionalActivity->review_remark,
        ]]);
    }

    // Superadmin, or one of the creator's reporting managers (never the creator).
    private function canManagerAct($user, PromotionalActivity $activity): bool
    {
        return $user->hasRole('superadmin') || (
            (int) $activity->created_by !== (int) $user->id
            && $this->isDirectManager($user, $activity)
        );
    }

    // Execution details and expenses go to the manager for review; the activity is not closed yet.
    public function complete(Request $request, PromotionalActivity $promotionalActivity)
    {
        $user = $request->user();
        abort_unless((int) $promotionalActivity->created_by === (int) $user->id || $user->hasRole('superadmin'), 403);
        abort_if($promotionalActivity->approval_status !== 'approved', 422, 'Only an approved activity can be submitted.');

        try {
            app(PromotionalActivityExecution::class)->submit($request, $promotionalActivity, $user);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->errors()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Activity details submitted for manager review.']);
    }

    public function review(Request $request, PromotionalActivity $promotionalActivity)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(PromotionalActivityExecution::REVIEW_DECISIONS)],
            'remark' => 'required_if:status,returned|nullable|string|max:1000',
        ], ['remark.required_if' => 'Remark is required to return an activity for correction.']);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $promotionalActivity->load('creator:id,name,reportingid');
        $user = $request->user();
        abort_unless($this->canManagerAct($user, $promotionalActivity), 403);
        abort_if($promotionalActivity->approval_status !== 'closure_pending', 422, 'Activity is not waiting for review.');

        app(PromotionalActivityExecution::class)->review($promotionalActivity, $request->status, $request->remark, $user);

        return response()->json(['success' => true, 'message' => $request->status === 'closed' ? 'Activity closed successfully.' : 'Activity returned for correction.']);
    }

    public function updateApproval(Request $request, PromotionalActivity $promotionalActivity)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(PromotionalActivity::DECISIONS)],
            'remark' => 'required_unless:status,approved|nullable|string|max:1000',
        ], ['remark.required_unless' => 'Remark is required to reject or send back an activity.']);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()], 422);

        $promotionalActivity->load('creator:id,name,reportingid');
        abort_unless($this->canManagerAct($request->user(), $promotionalActivity), 403);
        abort_if($promotionalActivity->approval_status !== 'pending', 422, 'Activity has already been actioned.');

        $promotionalActivity->update([
            'approval_status' => $request->status,
            'approved_rejected_by' => $request->user()->id,
            'approved_rejected_at' => now(),
            'approval_remark' => $request->remark,
        ]);
        $promotionalActivity->notifyCreatorOfDecision();

        return response()->json(['success' => true, 'message' => PromotionalActivity::decisionMessage($request->status)]);
    }
}
