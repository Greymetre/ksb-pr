<?php

namespace App\Services;

use App\Models\PromotionalActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Reports > User > Promotional Activity Report.
 * One place for the filters, visibility and row shape used by the screen and the Excel.
 */
class PromotionalActivityReport
{
    public const STATUSES = [
        'pending' => 'Pending Approval',
        'approved' => 'Approved',
        'revision_required' => 'Revision Required',
        'rejected' => 'Rejected',
        'closure_pending' => 'Closure Pending',
        'completed' => 'Completed',
    ];

    public static function statusLabel(?string $status): string
    {
        return self::STATUSES[$status] ?? ucwords(str_replace('_', ' ', (string) $status));
    }

    /**
     * Activities the logged-in user may see (superadmin all, others their team + self),
     * with every relation the report prints.
     */
    public function query(Request $request, bool $applyStatus = true)
    {
        $query = PromotionalActivity::query()
            ->with([
                'activityType:id,display_name,status_name',
                'creator' => fn ($q) => $q->select('id', 'name', 'employee_codes', 'division_id', 'branch_id', 'designation_id', 'reportingid')
                    ->with(['getdivision:id,division_name', 'getbranch:id,branch_name', 'getdesignation:id,designation_name']),
                'reportingManager:id,name',
                'approver:id,name',
                'reviewer:id,name',
                'distributor:id,name,customer_code',
                'gifts:id,name',
            ]);

        $user = Auth::user();
        if (!$user->hasRole('superadmin')) {
            $visibleIds = array_map('intval', getUsersReportingToAuth($user->id));
            $query->whereIn('created_by', array_unique(array_merge($visibleIds, [(int) $user->id])));
        }

        $query
            ->when($request->start_date, fn ($q, $d) => $q->whereDate('activity_date', '>=', $d))
            ->when($request->end_date, fn ($q, $d) => $q->whereDate('activity_date', '<=', $d))
            ->when($request->user_id, fn ($q, $id) => $q->where('created_by', $id))
            ->when($request->distributor_id, fn ($q, $id) => $q->where('distributor_id', $id))
            ->when($request->activity_type_id, fn ($q, $id) => $q->where('activity_status_id', $id))
            ->when($request->division_id, fn ($q, $id) => $q->whereHas('creator', fn ($u) => $u->where('division_id', $id)))
            ->when($request->branch_id, fn ($q, $id) => $q->whereHas('creator', fn ($u) => $u->whereRaw('FIND_IN_SET(?, branch_id)', [$id])))
            ->when($request->material, function ($q, $material) {
                // activities that requested this POP gift
                $q->whereHas('gifts', fn ($g) => $g->where('promotional_gifts.id', $material));
            });

        if ($applyStatus && $request->approval_status) {
            $query->where('approval_status', $request->approval_status);
        }

        return $query;
    }

    /** "Banner × 2, Cap × 10" */
    public function popMaterials(PromotionalActivity $activity): string
    {
        return $activity->gifts
            ->map(fn ($gift) => $gift->name . ' × ' . (int) $gift->pivot->quantity)
            ->implode(', ');
    }

    public function sampleMaterials(PromotionalActivity $activity): string
    {
        return collect($activity->sample_materials ?: [])
            ->filter(fn ($m) => !empty($m['name']))
            ->map(fn ($m) => $m['name'] . ' × ' . (int) ($m['quantity'] ?? 0))
            ->implode(', ');
    }

    /**
     * Flat list of every material line requested: [type, name, quantity].
     */
    public function materialLines(PromotionalActivity $activity): array
    {
        $lines = [];
        foreach ($activity->gifts as $gift) {
            $lines[] = ['POP Material', $gift->name, (int) $gift->pivot->quantity];
        }
        foreach ($activity->sample_materials ?: [] as $m) {
            if (!empty($m['name'])) {
                $lines[] = ['Sample Material', $m['name'], (int) ($m['quantity'] ?? 0)];
            }
        }

        return $lines;
    }

    public function totalAmount(PromotionalActivity $activity): float
    {
        return (float) $activity->company_share + (float) $activity->distributor_share;
    }
}
