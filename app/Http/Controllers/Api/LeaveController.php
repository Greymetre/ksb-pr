<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CompOffLeave;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->success     = 200;
        $this->created     = 201;
        $this->badRequest  = 400;
        $this->unauthorized = 401;
        $this->notFound    = 404;
        $this->serverError = 500;
    }

    /**
     * Apply for leave (creates Leave record + marks attendance)
     */
    public function addLeaves(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
        'user_id'    => 'required|exists:users,id',
        'from_date'  => 'required|date|date_format:Y-m-d',
        'to_date'    => 'required|date|date_format:Y-m-d|after_or_equal:from_date',
        'type'       => ['required', Rule::in([
            'Leave',
            'Full Day Leave',
            'First Half Leave',
            'Second Half Leave'
        ])],
        'bal_type'   => ['required', Rule::in([
            'Casual Balance',
            'Sick Balance',
            'Earned Balance',
            'Comp-off Balance'
        ])],
        'reason'     => 'nullable|string|max:500',
    ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], $this->badRequest);
        }

        $user = User::findOrFail($request->user_id);

        // Calculate date range
        $from = Carbon::parse($request->from_date);
        $to   = Carbon::parse($request->to_date);
        $days = $from->diffInDays($to) + 1;

        $isHalfDay = in_array($request->type, ['First Half Leave', 'Second Half Leave']);
        $leaveDays = $isHalfDay ? 0.5 : $days;

        // ────────────────────────────────────────────────
        // 1. Check & deduct balance
        // ────────────────────────────────────────────────
        $balanceResult = $this->checkAndDeductBalance($user, $request->bal_type, $leaveDays, $request->type);

        if (!$balanceResult['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $balanceResult['message']
            ], $this->badRequest);
        }

        DB::beginTransaction();

        try {
            // Create Leave record
            $leave = Leave::create([
                'user_id'    => $user->id,
                'from_date'  => $from->format('Y-m-d'),
                'to_date'    => $to->format('Y-m-d'),
                'type'       => $request->type,
                'bal_type'   => $request->bal_type,
                'reason'     => $request->reason ?? '',
                'created_by' => auth()->id() ?? $user->id, // fallback if no auth
                'attendance_status'     => 0,                  // change to 'approved' if auto-approve
                'active'     => 'Y',
            ]);

            // Mark attendance records
            $this->markLeaveInAttendance($user->id, $from, $to, $request->type, $request->reason);

            // For comp-off: mark used
            if ($request->bal_type === 'Comp-off Balance') {
                $this->markCompOffAsUsed($user->id, $leave->id, $leaveDays, $isHalfDay);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Leave applied successfully',
                'data'    => $leave->load('users')
            ], $this->created);

        } catch (\Exception $e) {
            DB::rollBack();

            // Rollback balance deduction if needed
            // (you can implement rollback logic here if you want strict consistency)

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to apply leave: ' . $e->getMessage()
            ], $this->serverError);
        }
    }

    /**
     * Get all leaves of a user
     */
    public function getLeaves(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], $this->badRequest);
        }

        $leaves = Leave::with(['users', 'createdbyname'])
            ->where('user_id', $request->user_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Leaves retrieved successfully',
            'data'    => $leaves
        ], $this->success);
    }

    // ────────────────────────────────────────────────
    //          Helper Methods
    // ────────────────────────────────────────────────

    private function checkAndDeductBalance(User $user, string $balType, float $days, string $leaveType): array
    {
        $fieldMap = [
            'Casual Balance'   => 'casual_leave_balance',
            'Sick Balance'     => 'sick_leave_balance',
            'Earned Balance'   => 'earned_leave_balance', // or claimable_earned_leave_balance
            'Comp-off Balance' => 'compb_off',
        ];

        $field = $fieldMap[$balType] ?? null;

        if (!$field) {
            return ['success' => false, 'message' => 'Invalid balance type'];
        }

        $available = (float) $user->$field;

        if ($balType !== 'Casual Balance') {

            if ($available < $days) {
                return [
                    'success' => false,
                    'message' => "Insufficient {$balType} balance. Available: {$available}, Required: {$days}"
                ];
            }
        }

        // Deduct balance (you can move this to approval stage if you want)
        $user->$field = max(0, $available - $days);
        $user->save();

        return ['success' => true];
    }

    private function markLeaveInAttendance(int $userId, Carbon $from, Carbon $to, string $type, ?string $reason): void
    {
        $current = $from->copy();

        while ($current->lte($to)) {
            Attendance::updateOrCreate(
                [
                    'user_id'     => $userId,
                    'punchin_date' => $current->format('Y-m-d'),
                ],
                [
                    'active'          => 'Y',
                    'punchin_date'    => $current->format('Y-m-d'),
                    'punchin_time'    => '10:00:00', // or make configurable
                    'punchin_summary' => $reason ?? 'Leave applied',
                    'working_type'    => $type,
                    'punchin_from'    => 'App',
                    'attendance_status' => 0, // approved
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );

            $current->addDay();
        }
    }

    private function markCompOffAsUsed(int $userId, int $leaveId, float $daysNeeded, bool $isHalfDay): void
    {
        $query = CompOffLeave::where('user_id', $userId)
            ->where('is_used', false)
            ->where('expiry_date', '>=', now());

        if ($isHalfDay) {
            $compOff = $query->where('balance', '>=', 0.5)->first();

            if ($compOff) {
                $compOff->balance -= 0.5;
                $compOff->leave_id = $compOff->leave_id ? $compOff->leave_id . ',' . $leaveId : $leaveId;
                $compOff->is_used = $compOff->balance <= 0;
                $compOff->save();
            }
        } else {
            // Full day – consume whole records
            $compOffs = $query->where('balance', '>=', 1)->take((int)$daysNeeded)->get();

            foreach ($compOffs as $comp) {
                $comp->update([
                    'is_used'  => true,
                    'balance'  => 0,
                    'leave_id' => $comp->leave_id ? $comp->leave_id . ',' . $leaveId : $leaveId,
                ]);
            }
        }
    }

    /**
     * Get current leave & comp-off balances of the authenticated user
     */
    public function getMyBalances(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            // Sum active (non-expired, unused) comp-off balance
            $activeCompOff = CompOffLeave::where('user_id', $user->id)
                ->where('is_used', false)
                ->where('expiry_date', '>=', now())
                ->sum('balance');

            $data = [
                'casual'     => (float) ($user->casual_leave_balance   ?? 0),
                'sick'       => (float) ($user->sick_leave_balance     ?? 0),
                'earned'     => (float) ($user->earned_leave_balance   ?? 0),
                'claimable_earned' => (float) ($user->claimable_earned_leave_balance ?? 0),
                'comp_off'   => round((float) $activeCompOff, 2),
            ];

            return response()->json([
                'status'  => true,
                'message' => 'Balances fetched successfully',
                'data'    => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch balances',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}