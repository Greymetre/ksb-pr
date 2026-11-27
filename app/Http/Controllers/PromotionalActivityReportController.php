<?php

namespace App\Http\Controllers;

use App\Exports\PromotionalActivityReportExport;
use App\Models\Branch;
use App\Models\Customers;
use App\Models\Division;
use App\Models\PromotionalActivity;
use App\Models\PromotionalGift;
use App\Models\Status;
use App\Models\User;
use App\Services\PromotionalActivityReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

/**
 * Reports > User > Promotional Activity Report: which distributor each activity
 * was for, which POP / sample materials were requested, budget vs actual and the
 * approval trail, with an Excel export.
 */
class PromotionalActivityReportController extends Controller
{
    public function __construct(private PromotionalActivityReport $report)
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user() && Auth::user()->can('promotional_activity_report'), Response::HTTP_FORBIDDEN, '403 Forbidden');
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if ($request->ajax()) {
            return $this->datatable($request);
        }

        $user = Auth::user();
        $visibleIds = $user->hasRole('superadmin') ? null : array_unique(array_merge(
            array_map('intval', getUsersReportingToAuth($user->id)), [(int) $user->id]
        ));

        $users = User::where('active', 'Y')
            ->whereIn('id', PromotionalActivity::query()
                ->when($visibleIds, fn ($q) => $q->whereIn('created_by', $visibleIds))
                ->select('created_by'))
            ->orderBy('name')->get(['id', 'name', 'employee_codes']);

        $distributors = Customers::whereIn('id', PromotionalActivity::query()
                ->when($visibleIds, fn ($q) => $q->whereIn('created_by', $visibleIds))
                ->whereNotNull('distributor_id')->select('distributor_id'))
            ->orderBy('name')->get(['id', 'name', 'customer_code']);

        return view('reports.promotional_activity', [
            'users' => $users,
            'distributors' => $distributors,
            'activityTypes' => Status::where('module', 'Promotional Activity')->where('active', 'Y')->orderBy('display_name')->get(['id', 'display_name', 'status_name']),
            'gifts' => PromotionalGift::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::where('active', 'Y')->orderBy('division_name')->get(['id', 'division_name']),
            'branches' => Branch::where('active', 'Y')->orderBy('branch_name')->get(['id', 'branch_name']),
            'statuses' => PromotionalActivityReport::STATUSES,
        ]);
    }

    public function download(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $name = 'Promotional_Activity_Report'
            . ($request->start_date ? '_' . $request->start_date : '')
            . ($request->end_date ? '_' . $request->end_date : '') . '.xlsx';

        return Excel::download(new PromotionalActivityReportExport($this->report->query($request), $this->report), $name);
    }

    private function datatable(Request $request)
    {
        // summary follows every filter except the status itself, like the status tabs elsewhere
        $base = $this->report->query($request, false);
        $counts = (clone $base)->reorder()->setEagerLoads([])
            ->selectRaw('approval_status, COUNT(*) as total')->groupBy('approval_status')
            ->pluck('total', 'approval_status');
        $filtered = $this->report->query($request)->reorder()->setEagerLoads([]);
        $summary = [
            'counts' => $counts,
            'total' => (int) $counts->sum(),
            'estimated_budget' => number_format((float) (clone $filtered)->sum('estimated_budget'), 2),
            'total_amount' => number_format((float) (clone $filtered)->selectRaw('COALESCE(SUM(company_share + distributor_share), 0) as t')->value('t'), 2),
            'actual_expense' => number_format((float) (clone $filtered)->sum('actual_expense'), 2),
        ];

        $query = $this->report->query($request)->orderByDesc('activity_date')->orderByDesc('id');

        return DataTables::eloquent($query)
            ->with('summary', $summary)
            ->addIndexColumn()
            ->addColumn('activity_date_fmt', fn ($a) => optional($a->activity_date)->format('d-M-Y'))
            ->addColumn('activity_type', fn ($a) => e($a->activityType->display_name ?? $a->activityType->status_name ?? '-'))
            ->addColumn('employee', function ($a) {
                $c = $a->creator;
                return '<div class="fk-pa-person"><b>' . e(optional($c)->name ?? '-') . '</b><small>'
                    . e(collect([optional($c)->employee_codes, optional(optional($c)->getdesignation)->designation_name])->filter()->implode(' · '))
                    . '</small></div>';
            })
            ->addColumn('zone', fn ($a) => e(optional(optional($a->creator)->getdivision)->division_name ?? '-'))
            ->addColumn('branch', fn ($a) => e(optional(optional($a->creator)->getbranch)->branch_name ?? '-'))
            ->addColumn('distributor', function ($a) {
                if (!$a->distributor) {
                    return '-';
                }
                return '<div class="fk-pa-person"><b>' . e($a->distributor->name) . '</b>'
                    . ($a->distributor->customer_code ? '<small>' . e($a->distributor->customer_code) . '</small>' : '') . '</div>';
            })
            ->addColumn('materials', function ($a) {
                $chips = collect($this->report->materialLines($a))->map(function ($line) {
                    [$type, $name, $qty] = $line;
                    $cls = $type === 'POP Material' ? 'is-pop' : 'is-sample';
                    return '<span class="fk-pa-chip ' . $cls . '" title="' . e($type) . '">' . e($name) . ' <b>× ' . (int) $qty . '</b></span>';
                });
                return $chips->isEmpty() ? '<span class="fk-pa-muted">No material</span>' : '<div class="fk-pa-chips">' . $chips->implode('') . '</div>';
            })
            ->addColumn('budget', function ($a) {
                $html = '<div class="fk-pa-money"><span>Est.</span> ' . ($a->estimated_budget !== null ? number_format((float) $a->estimated_budget, 2) : '-') . '</div>'
                    . '<div class="fk-pa-money"><span>Total</span> ' . number_format($this->report->totalAmount($a), 2) . '</div>';
                if ($a->actual_expense !== null) {
                    $html .= '<div class="fk-pa-money' . ($a->isOverBudget() ? ' is-over' : '') . '"><span>Actual</span> '
                        . number_format((float) $a->actual_expense, 2) . ($a->isOverBudget() ? ' ▲' : '') . '</div>';
                }
                return $html;
            })
            ->addColumn('status_badge', fn ($a) => '<span class="fk-pa-status is-' . e($a->approval_status) . '">'
                . e(PromotionalActivityReport::statusLabel($a->approval_status)) . '</span>')
            ->addColumn('approval', function ($a) {
                if (!$a->approver && !$a->approval_remark) {
                    return '<span class="fk-pa-muted">' . e(optional($a->reportingManager)->name ? 'Awaiting ' . $a->reportingManager->name : 'Awaiting') . '</span>';
                }
                return '<div class="fk-pa-person"><b>' . e(optional($a->approver)->name ?? '-') . '</b><small>'
                    . e(optional($a->approved_rejected_at)->format('d-M-Y h:i A')) . '</small>'
                    . ($a->approval_remark ? '<em>' . e($a->approval_remark) . '</em>' : '') . '</div>';
            })
            ->addColumn('location', fn ($a) => e($a->location_name ?? '-'))
            ->filter(function ($query) use ($request) {
                $search = trim((string) data_get($request->input('search'), 'value'));
                if ($search === '') {
                    return;
                }
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('request_no', 'like', $like)
                        ->orWhere('location_name', 'like', $like)
                        ->orWhere('purpose', 'like', $like)
                        ->orWhereHas('creator', fn ($u) => $u->where('name', 'like', $like)->orWhere('employee_codes', 'like', $like))
                        ->orWhereHas('distributor', fn ($c) => $c->where('name', 'like', $like)->orWhere('customer_code', 'like', $like))
                        ->orWhereHas('gifts', fn ($g) => $g->where('promotional_gifts.name', 'like', $like));
                });
            })
            ->rawColumns(['employee', 'distributor', 'materials', 'budget', 'status_badge', 'approval'])
            ->make(true);
    }
}
