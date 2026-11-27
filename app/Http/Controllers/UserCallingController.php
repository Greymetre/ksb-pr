<?php

namespace App\Http\Controllers;

use App\Exports\CallingReportExport;
use App\Models\User;
use App\Models\UserCallingRemark;
use App\Services\UserCallingSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

/**
 * User List > call icon: one day's work summary of an employee plus the
 * calling remark / issue the admin records after calling them.
 */
class UserCallingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user() && Auth::user()->can('user_feedback_calling'), 403, '403 Forbidden');
            return $next($request);
        });
    }

    public function details(Request $request, UserCallingSummary $summary)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'date' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $user = $this->visibleUser((int) $request->user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        $date = Carbon::parse($request->date ?: Carbon::yesterday())->toDateString();
        $remark = UserCallingRemark::where('user_id', $user->id)->whereDate('call_date', $date)->first();

        return response()->json([
            'status' => 'success',
            'data' => $summary->forUser($user, $date) + [
                'calling_remark' => optional($remark)->calling_remark,
                'issue' => optional($remark)->issue,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'date' => 'required|date',
            'calling_remark' => 'nullable|string|max:5000',
            'issue' => 'nullable|string|max:5000',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }
        if (!trim((string) $request->calling_remark) && !trim((string) $request->issue)) {
            return response()->json(['status' => 'error', 'message' => 'Please enter a calling remark or an issue.'], 422);
        }

        $user = $this->visibleUser((int) $request->user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        $date = Carbon::parse($request->date)->toDateString();
        $remark = UserCallingRemark::firstOrNew(['user_id' => $user->id, 'call_date' => $date]);
        if (!$remark->exists) {
            $remark->created_by = Auth::id();
        }
        $remark->calling_remark = $request->calling_remark;
        $remark->issue = $request->issue;
        $remark->updated_by = Auth::id();
        $remark->save();

        return response()->json(['status' => 'success', 'message' => 'Calling details saved successfully.']);
    }

    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date)->toDateString();
        $end = Carbon::parse($request->end_date)->toDateString();

        return Excel::download(new CallingReportExport($start, $end), "Calling_Report_{$start}_{$end}.xlsx");
    }

    /**
     * Same visibility as the User List: admins see everyone, others only their team.
     */
    private function visibleUser(int $userId): ?User
    {
        $auth = Auth::user();
        if (!$auth->hasRole('superadmin') && !$auth->hasRole('Admin') && !$auth->hasRole('Sub_Admin')
            && !in_array($userId, array_map('intval', getUsersReportingToAuth()), true)) {
            return null;
        }

        return User::with('getdivision', 'getdesignation', 'userinfo')->find($userId);
    }
}
