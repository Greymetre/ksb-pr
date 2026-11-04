<?php

namespace App\Http\Controllers;

use App\Exports\PromotionalActivityExport;
use App\Models\CustomerType;
use App\Models\Customers;
use App\Models\PromotionalActivity;
use App\Models\PromotionalGift;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\PromotionalActivityExecution;
use App\Services\PromotionalGiftStock;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PromotionalActivityWebController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_if(!$user || (!$user->hasRole('superadmin') && !$user->can('promotional_activity_access')), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function query(Request $request, bool $applyStatus = true)
    {
        $query = PromotionalActivity::with(['activityType:id,display_name,status_name', 'creator:id,name', 'distributor:id,name,customer_code']);
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $visibleIds = array_map('intval', getUsersReportingToAuth($user->id));
            $query->whereIn('created_by', array_unique(array_merge($visibleIds, [(int) $user->id])));
        }
        if ($request->filled('activity_type_id')) $query->where('activity_status_id', $request->activity_type_id);
        if ($request->filled('date_from')) $query->whereDate('activity_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('activity_date', '<=', $request->date_to);
        if ($applyStatus && $request->filled('approval_status')) $query->where('approval_status', $request->approval_status);
        return $query;
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();
        if ($request->ajax()) {
            // Status tab counts follow the other filters but ignore the selected status tab.
            $statusCounts = $this->query($request, false)->reorder()->selectRaw('approval_status, COUNT(*) as total')
                ->groupBy('approval_status')->pluck('total', 'approval_status');
            return DataTables::of($this->query($request)->latest('activity_date'))
                ->with('status_counts', $statusCounts)
                ->addIndexColumn()
                ->addColumn('activity_type_name', fn ($row) => $row->activityType->display_name ?? $row->activityType->status_name ?? '-')
                ->addColumn('creator_name', fn ($row) => $row->creator->name ?? '-')
                ->addColumn('distributor_name', fn ($row) => $row->distributor->name ?? '-')
                ->addColumn('total_amount', fn ($row) => number_format((float) $row->company_share + (float) $row->distributor_share, 2))
                ->addColumn('participants_count', fn ($row) => count($row->participants ?: []))
                ->editColumn('activity_date', fn ($row) => $row->activity_date ? $row->activity_date->format('d-M-Y') : '-')
                ->editColumn('approval_status', fn ($row) => '<span class="badge badge-'.($row->approval_status === 'completed' ? 'success' : ($row->approval_status === 'rejected' ? 'danger' : ($row->approval_status === 'approved' ? 'info' : ($row->approval_status === 'revision_required' ? 'secondary' : ($row->approval_status === 'closure_pending' ? 'primary' : 'warning'))))).'">'.ucwords(str_replace('_', ' ', $row->approval_status)).'</span>')
                ->editColumn('created_at', fn ($row) => showdatetimeformat($row->created_at))
                ->rawColumns(['approval_status'])->make(true);
        }
        $activityTypes = Status::where('module', 'Promotional Activity')->where('active', 'Y')->orderBy('display_name')->get(['id', 'display_name', 'status_name']);
        return view('promotional_activities.index', compact('activityTypes'));
    }

    private function canView(PromotionalActivity $activity): bool
    {
        $user = auth()->user();
        if ($user->hasRole('superadmin') || (int) $activity->created_by === (int) $user->id) return true;
        return in_array((int) $activity->created_by, array_map('intval', getUsersReportingToAuth($user->id)), true);
    }

    // Same rule as the mobile app: superadmin, or the creator's direct reporting manager (not the creator).
    private function canApprove(PromotionalActivity $activity): bool
    {
        return $activity->approval_status === 'pending' && $this->isManagerOf($activity);
    }

    // The same managers close an activity after checking its execution details and expenses.
    private function canReview(PromotionalActivity $activity): bool
    {
        return $activity->approval_status === 'closure_pending' && $this->isManagerOf($activity);
    }

    private function isManagerOf(PromotionalActivity $activity): bool
    {
        $user = auth()->user();
        if ($user->hasRole('superadmin')) return true;
        if ((int) $activity->created_by === (int) $user->id || !$activity->creator) return false;
        $managerIds = array_map('intval', array_filter(array_map('trim', explode(',', (string) $activity->creator->reportingid))));
        return in_array((int) $user->id, $managerIds, true);
    }

    // Same rule as the mobile app: only an approved activity, by its creator or a superadmin.
    private function canComplete(PromotionalActivity $activity): bool
    {
        $user = auth()->user();
        return $activity->approval_status === 'approved'
            && ((int) $activity->created_by === (int) $user->id || $user->hasRole('superadmin'));
    }

    public function show(PromotionalActivity $promotionalActivity)
    {
        $this->authorizeAccess();
        $promotionalActivity->load([
            'activityType:id,display_name,status_name', 'creator:id,name,reportingid',
            'reportingManager:id,name', 'approver:id,name', 'reviewer:id,name', 'gifts:id,name', 'distributor:id,name,customer_code',
        ]);
        abort_unless($this->canView($promotionalActivity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $activity = $promotionalActivity;
        return response()->json(['success' => true, 'data' => [
            'id' => $activity->id,
            'request_no' => $activity->request_no,
            'activity_type' => $activity->activityType->display_name ?? $activity->activityType->status_name ?? 'Promotional Activity',
            'activity_date' => $activity->activity_date ? $activity->activity_date->format('d-M-Y') : '-',
            'location_name' => $activity->location_name,
            'purpose' => $activity->purpose,
            'estimated_budget' => $activity->estimated_budget !== null ? number_format((float) $activity->estimated_budget, 2) : null,
            'attachments' => collect($activity->attachments ?: [])->map(fn ($path) => ['url' => Storage::disk('public')->url($path), 'name' => basename($path)])->values(),
            'creator' => $activity->creator->name ?? '-',
            'reporting_manager' => $activity->reportingManager->name ?? '-',
            'company_share' => number_format((float) $activity->company_share, 2),
            'distributor_share' => number_format((float) $activity->distributor_share, 2),
            'total_amount' => number_format((float) $activity->company_share + (float) $activity->distributor_share, 2),
            'remark' => $activity->remark,
            'approval_status' => $activity->approval_status,
            'approval_remark' => $activity->approval_remark,
            'approved_rejected_by' => $activity->approver->name ?? null,
            'approved_rejected_at' => $activity->approved_rejected_at ? showdatetimeformat($activity->approved_rejected_at) : null,
            'gifts' => $activity->gifts->map(fn ($gift) => ['id' => $gift->id, 'name' => $gift->name, 'quantity' => (int) $gift->pivot->quantity])->values(),
            'distributor' => $activity->distributor->name ?? null,
            'distributor_id' => $activity->distributor_id,
            'participants' => $activity->participants ?: [],
            'photos' => collect($activity->activity_photos ?: [])->map(fn ($path) => Storage::disk('public')->url($path))->values(),
            // Paths let a returned submission keep its files when it is corrected.
            'photo_files' => collect($activity->activity_photos ?: [])->map(fn ($path) => ['path' => $path, 'url' => Storage::disk('public')->url($path)])->values(),
            'execution_remark' => $activity->execution_remark,
            'actual_expense' => $activity->actual_expense !== null ? number_format((float) $activity->actual_expense, 2) : null,
            'over_budget' => $activity->isOverBudget(),
            'bills' => collect($activity->bills ?: [])->map(fn ($path) => ['path' => $path, 'url' => Storage::disk('public')->url($path), 'name' => basename($path)])->values(),
            'execution_submitted_at' => $activity->execution_submitted_at ? showdatetimeformat($activity->execution_submitted_at) : null,
            'reviewed_by' => $activity->reviewer->name ?? null,
            'reviewed_at' => $activity->reviewed_at ? showdatetimeformat($activity->reviewed_at) : null,
            'review_remark' => $activity->review_remark,
            'created_at' => showdatetimeformat($activity->created_at),
            'can_approve' => $canApprove = $this->canApprove($activity),
            'can_review' => $this->canReview($activity),
            'can_complete' => $this->canComplete($activity),
            'can_delete' => auth()->user()->hasRole('superadmin'),
            // Gift options for editing before approval.
            'available_gifts' => $canApprove ? PromotionalGift::where('active', 'Y')->orderBy('name')->get(['id', 'name', 'quantity']) : [],
        ]]);
    }

    public function updateApproval(Request $request, PromotionalActivity $promotionalActivity)
    {
        $this->authorizeAccess();
        $request->validate([
            'status' => ['required', Rule::in(PromotionalActivity::DECISIONS)],
            'remark' => 'required_unless:status,approved|nullable|string|max:1000',
            'gifts' => 'nullable|array',
            'gifts.*.gift_id' => 'required|integer|exists:promotional_gifts,id',
            'gifts.*.quantity' => 'required|integer|min:1',
        ], ['remark.required_unless' => 'Remark is required to reject or send back an activity.']);
        $promotionalActivity->load('creator:id,name,reportingid');
        abort_if($promotionalActivity->approval_status !== 'pending', 422, 'Activity has already been actioned.');
        abort_unless($this->canApprove($promotionalActivity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Gifts edited in the detail popup are saved only when the activity is approved.
        $giftRows = null;
        if ($request->status === 'approved' && $request->has('gifts')) {
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
        }

        DB::transaction(function () use ($request, $promotionalActivity, $giftRows) {
            if ($giftRows !== null) {
                $promotionalActivity->gifts()->sync($giftRows->mapWithKeys(fn ($row) => [$row['gift_id'] => ['quantity' => $row['quantity']]])->all());
            }
            $promotionalActivity->update([
                'approval_status' => $request->status,
                'approved_rejected_by' => auth()->id(),
                'approved_rejected_at' => now(),
                'approval_remark' => $request->remark,
            ]);
        });
        $promotionalActivity->notifyCreatorOfDecision();

        return response()->json(['success' => true, 'message' => PromotionalActivity::decisionMessage($request->status)]);
    }

    // Distributor/dealer customers assigned to the activity creator or their team, as the app's execution form lists them.
    public function distributors(Request $request, PromotionalActivity $promotionalActivity)
    {
        $this->authorizeAccess();
        abort_unless($this->canComplete($promotionalActivity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // "distrib" also covers spelling variants such as "Distributer".
        $typeIds = CustomerType::where(function ($q) {
            $q->where('customertype_name', 'like', '%distrib%')->orWhere('customertype_name', 'like', '%dealer%');
        })->where('customertype_name', 'not like', '%master%')->where('customertype_name', 'not like', '%secondary%')->pluck('id')
            // 3 and 4 are the dealer/distributor types used elsewhere (dashboard, reports).
            ->merge([3, 4])->unique()->values();

        $query = Customers::where('active', 'Y')->whereIn('customertype', $typeIds);

        // Like the app's customer list: a superadmin sees every distributor, others only their team's.
        if (!auth()->user()->hasRole('superadmin')) {
            $userIds = array_values(array_unique(array_merge(
                array_map('intval', getUsersReportingToAuth($promotionalActivity->created_by)),
                [(int) $promotionalActivity->created_by]
            )));
            $query->where(function ($q) use ($userIds) {
                $q->whereIn('created_by', $userIds)->orWhereIn('executive_id', $userIds)
                    ->orWhereHas('getemployeedetail', fn ($employee) => $employee->whereIn('user_id', $userIds));
            });
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"));
        }

        return response()->json(['results' => $query->orderBy('name')->limit(500)->get(['id', 'name', 'mobile'])
            ->map(fn ($customer) => ['id' => $customer->id, 'text' => $customer->name.($customer->mobile ? ' ('.$customer->mobile.')' : '')])]);
    }

    // Execution details and expenses go to the manager for review; the activity is not closed yet.
    public function complete(Request $request, PromotionalActivity $promotionalActivity)
    {
        $this->authorizeAccess();
        abort_unless($this->canComplete($promotionalActivity), 422, 'Only an approved activity can be submitted.');

        try {
            app(PromotionalActivityExecution::class)->submit($request, $promotionalActivity, auth()->user());
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Activity details submitted for manager review.']);
    }

    public function review(Request $request, PromotionalActivity $promotionalActivity)
    {
        $this->authorizeAccess();
        $request->validate([
            'status' => ['required', Rule::in(PromotionalActivityExecution::REVIEW_DECISIONS)],
            'remark' => 'required_if:status,returned|nullable|string|max:1000',
        ], ['remark.required_if' => 'Remark is required to return an activity for correction.']);
        $promotionalActivity->load('creator:id,name,reportingid');
        abort_if($promotionalActivity->approval_status !== 'closure_pending', 422, 'Activity is not waiting for review.');
        abort_unless($this->canReview($promotionalActivity), Response::HTTP_FORBIDDEN, '403 Forbidden');

        app(PromotionalActivityExecution::class)->review($promotionalActivity, $request->status, $request->remark, auth()->user());

        return response()->json(['success' => true, 'message' => $request->status === 'closed' ? 'Activity closed successfully.' : 'Activity returned for correction.']);
    }

    // Superadmin only. A completed activity's gifts go back into gift stock.
    public function destroy(PromotionalActivity $promotionalActivity)
    {
        $this->authorizeAccess();
        abort_unless(auth()->user()->hasRole('superadmin'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $filePaths = array_merge($promotionalActivity->activity_photos ?: [], $promotionalActivity->attachments ?: [], $promotionalActivity->bills ?: []);
        DB::transaction(function () use ($promotionalActivity) {
            app(PromotionalGiftStock::class)->returnForActivity($promotionalActivity, auth()->id());
            $promotionalActivity->gifts()->detach();
            $promotionalActivity->delete();
        });
        Storage::disk('public')->delete($filePaths);

        return response()->json(['success' => true, 'message' => 'Promotional activity deleted successfully.']);
    }

    public function export(Request $request)
    {
        $this->authorizeAccess();
        abort_if(!auth()->user()->hasRole('superadmin') && !auth()->user()->can('promotional_activity_export'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return Excel::download(new PromotionalActivityExport($this->query($request)), 'Promotional Activities.xlsx');
    }
}
