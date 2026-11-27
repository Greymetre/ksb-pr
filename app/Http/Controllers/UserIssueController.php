<?php

namespace App\Http\Controllers;

use App\Exports\UserIssuesExport;
use App\Models\User;
use App\Models\UserIssue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

/**
 * User Management > User Issues: issues raised from the calling popup or
 * created here, moved pending -> in progress -> resolved.
 */
class UserIssueController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user() && Auth::user()->can('user_feedback_calling'), 403, '403 Forbidden');
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->datatable($request);
        }

        $employees = $this->employeeQuery()->orderBy('name')->get(['id', 'name', 'employee_codes']);

        return view('user_issues.index', [
            'employees' => $employees,
            'statuses' => UserIssue::STATUSES,
        ]);
    }

    /**
     * Select2 source for the User / Solve By pickers in the popups.
     */
    public function users(Request $request)
    {
        $term = trim((string) $request->q);

        $users = $this->employeeQuery()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%' . $term . '%')
                        ->orWhere('employee_codes', 'like', '%' . $term . '%')
                        ->orWhere('mobile', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'employee_codes']);

        return response()->json([
            'results' => $users->map(fn ($u) => [
                'id' => $u->id,
                'text' => $u->name . ($u->employee_codes ? ' (' . $u->employee_codes . ')' : ''),
                'name' => $u->name,
                'code' => $u->employee_codes,
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'description' => 'required|string|max:5000',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }
        if (!$this->employeeQuery()->where('id', $request->user_id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Please select a valid user.'], 422);
        }

        UserIssue::create([
            'user_id' => $request->user_id,
            'issue_date' => Carbon::today()->toDateString(),
            'description' => trim($request->description),
            'status' => 'pending',
            'source' => 'manual',
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Issue created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $issue = $this->visibleIssues()->find($id);
        if (!$issue) {
            return response()->json(['status' => 'error', 'message' => 'Issue not found.'], 404);
        }
        if ($issue->status === 'resolved') {
            return response()->json(['status' => 'error', 'message' => 'This issue is already resolved.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:in_progress,resolved',
            'solved_by' => 'required_if:status,resolved|nullable|integer|exists:users,id',
            'solve_remark' => 'required_if:status,resolved|nullable|string|max:5000',
        ], [
            'solved_by.required_if' => 'Please select who solved the issue.',
            'solve_remark.required_if' => 'Please enter the solve remark.',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $issue->status = $request->status;
        $issue->updated_by = Auth::id();
        if ($request->status === 'resolved') {
            $issue->solved_by = $request->solved_by;
            $issue->solve_remark = trim($request->solve_remark);
            $issue->solved_at = now();
        }
        $issue->save();

        return response()->json([
            'status' => 'success',
            'message' => $request->status === 'resolved' ? 'Issue resolved successfully.' : 'Issue moved to In Progress.',
        ]);
    }

    public function download(Request $request)
    {
        $query = $this->filteredQuery($request)->orderByDesc('issue_date')->orderByDesc('id');

        return Excel::download(new UserIssuesExport($query), 'User_Issues_' . date('Y-m-d') . '.xlsx');
    }

    private function datatable(Request $request)
    {
        $query = $this->filteredQuery($request);

        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('zone', fn ($row) => optional(optional($row->user)->getdivision)->division_name ?? '-')
            ->addColumn('branch', fn ($row) => $this->branchNames(optional($row->user)->branch_id) ?: '-')
            ->addColumn('employee_code', fn ($row) => optional($row->user)->employee_codes ?? '-')
            ->addColumn('employee_name', fn ($row) => optional($row->user)->name ?? '-')
            ->addColumn('designation', fn ($row) => optional(optional($row->user)->getdesignation)->designation_name ?? '-')
            ->editColumn('description', fn ($row) => nl2br(e($row->description)))
            ->addColumn('status_badge', function ($row) {
                $label = UserIssue::STATUSES[$row->status] ?? ucfirst($row->status);
                return '<span class="fk-issue-status is-' . e($row->status) . '">' . e($label) . '</span>';
            })
            ->addColumn('created_by_name', fn ($row) => optional($row->createdBy)->name ?? '-')
            ->addColumn('solved_by_name', fn ($row) => optional($row->solvedBy)->name ?? '-')
            ->editColumn('solve_remark', fn ($row) => $row->solve_remark ? nl2br(e($row->solve_remark)) : '-')
            ->addColumn('action', function ($row) {
                if ($row->status === 'resolved') {
                    return '<span class="fk-issue-locked" title="Resolved"><i class="material-icons">lock</i></span>';
                }
                $data = e(json_encode([
                    'id' => $row->id,
                    'name' => optional($row->user)->name,
                    'date' => $row->issue_date,
                    'zone' => optional(optional($row->user)->getdivision)->division_name,
                    'description' => $row->description,
                    'status' => $row->status,
                ]));
                return '<button type="button" class="btn fk-issue-resolve" data-issue="' . $data . '">
                            <i class="material-icons">task_alt</i> Resolve
                        </button>';
            })
            ->filter(function ($query) use ($request) {
                $search = trim((string) data_get($request->input('search'), 'value'));
                if ($search === '') {
                    return;
                }
                $query->where(function ($q) use ($search) {
                    $like = '%' . $search . '%';
                    $q->where('description', 'like', $like)
                        ->orWhere('solve_remark', 'like', $like)
                        ->orWhereHas('user', function ($u) use ($like) {
                            $u->where('name', 'like', $like)
                                ->orWhere('employee_codes', 'like', $like)
                                ->orWhereHas('getdivision', fn ($d) => $d->where('division_name', 'like', $like))
                                ->orWhereHas('getdesignation', fn ($d) => $d->where('designation_name', 'like', $like));
                        });
                });
            })
            ->rawColumns(['description', 'status_badge', 'solve_remark', 'action'])
            ->make(true);
    }

    private function filteredQuery(Request $request)
    {
        return $this->visibleIssues()
            ->with(['user.getdivision', 'user.getdesignation', 'createdBy', 'solvedBy'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->user_id, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($request->start_date, fn ($q, $date) => $q->whereDate('issue_date', '>=', $date))
            ->when($request->end_date, fn ($q, $date) => $q->whereDate('issue_date', '<=', $date))
            ->when(!$request->input('order'), fn ($q) => $q->orderByDesc('id'));
    }

    private function visibleIssues()
    {
        $query = UserIssue::query();
        if (!$this->isAdmin()) {
            $query->whereIn('user_id', getUsersReportingToAuth());
        }

        return $query;
    }

    /**
     * Active employees (no customer logins) the logged-in user can see.
     */
    private function employeeQuery()
    {
        return User::where('active', 'Y')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('id', config('constants.customer_roles')))
            ->when(!$this->isAdmin(), fn ($q) => $q->whereIn('id', getUsersReportingToAuth()));
    }

    private function isAdmin(): bool
    {
        $auth = Auth::user();

        return $auth->hasRole('superadmin') || $auth->hasRole('Admin') || $auth->hasRole('Sub_Admin');
    }

    private function branchNames($branchIds): ?string
    {
        static $cache = [];
        $key = (string) $branchIds;
        if (!array_key_exists($key, $cache)) {
            $ids = array_filter(explode(',', $key));
            $cache[$key] = $ids ? DB::table('branches')->whereIn('id', $ids)->pluck('branch_name')->implode(', ') : null;
        }

        return $cache[$key];
    }
}
