<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\UserCallingRemark;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    public function details(Request $request)
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

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('punchin_date', $date)
            ->orderBy('id')
            ->first();

        $checkins = DB::table('check_in')->where('user_id', $user->id)->whereDate('checkin_date', $date);
        $firstCheckin = (clone $checkins)->min('checkin_time');
        $lastCheckout = (clone $checkins)->max('checkout_time');

        $remark = UserCallingRemark::where('user_id', $user->id)->whereDate('call_date', $date)->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $user->id,
                'date' => $date,
                'zone' => optional($user->getdivision)->division_name,
                'branch' => $this->branchNames($user->branch_id),
                'employee_code' => $user->employee_codes,
                'employee_name' => $user->name,
                'designation' => optional($user->getdesignation)->designation_name,
                'head_quarter' => $user->location,
                'date_of_joining' => optional($user->userinfo)->date_of_joining,
                'punchin_time' => $this->formatTime(optional($attendance)->punchin_time),
                'punchout_time' => $this->formatTime(optional($attendance)->punchout_time),
                'worked_time' => $this->workedTime($attendance),
                'working_type' => optional($attendance)->working_type,
                'first_checkin_time' => $this->formatTime($firstCheckin),
                'last_checkout_time' => $this->formatTime($lastCheckout),
                'total_visit' => (clone $checkins)->count(),
                'total_order' => DB::table('orders')->whereNull('deleted_at')
                    ->where('created_by', $user->id)
                    ->whereDate('order_date', $date)
                    ->count(),
                'retailer_registered' => $this->registeredCount($user->id, $date, ['retailer'], ['RETAILER', 'WORKSHOP']),
                'influencer_registered' => $this->registeredCount($user->id, $date, ['influencer'], ['MECHANIC', 'GARAGE']),
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

    private function branchNames($branchIds): ?string
    {
        $ids = array_filter(explode(',', (string) $branchIds));
        if (!$ids) {
            return null;
        }

        return DB::table('branches')->whereIn('id', $ids)->pluck('branch_name')->implode(', ');
    }

    /**
     * Customers of the given type (customer master) plus secondary customers
     * (retailer app module) created by the user on that date.
     */
    private function registeredCount(int $userId, string $date, array $customerTypes, array $secondaryTypes): int
    {
        $placeholders = implode(',', array_fill(0, count($customerTypes), '?'));

        $customers = DB::table('customers')
            ->join('customer_types', 'customer_types.id', '=', 'customers.customertype')
            ->whereNull('customers.deleted_at')
            ->where('customers.created_by', $userId)
            ->whereDate('customers.created_at', $date)
            ->where(function ($query) use ($placeholders, $customerTypes) {
                $query->whereRaw("LOWER(TRIM(customer_types.type_name)) IN ($placeholders)", $customerTypes)
                    ->orWhereRaw("LOWER(TRIM(customer_types.customertype_name)) IN ($placeholders)", $customerTypes);
            })
            ->count();

        $secondary = DB::table('secondary_customers')
            ->where('created_by', $userId)
            ->whereIn('type', $secondaryTypes)
            ->whereDate('created_at', $date)
            ->count();

        return $customers + $secondary;
    }

    private function workedTime($attendance): ?string
    {
        if (!$attendance) {
            return null;
        }
        if (!empty($attendance->worked_time)) {
            return $attendance->worked_time;
        }
        if ($attendance->punchin_time && $attendance->punchout_time) {
            $in = Carbon::parse(($attendance->punchin_date ?: '') . ' ' . $attendance->punchin_time);
            $out = Carbon::parse(($attendance->punchout_date ?: $attendance->punchin_date) . ' ' . $attendance->punchout_time);
            if ($out->greaterThan($in)) {
                return gmdate('H:i:s', $out->diffInSeconds($in));
            }
        }

        return null;
    }

    private function formatTime($time): ?string
    {
        if (empty($time)) {
            return null;
        }

        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Throwable $e) {
            return (string) $time;
        }
    }
}
