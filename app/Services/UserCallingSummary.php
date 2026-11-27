<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * One day's work summary of an employee, shown in the User List calling
 * popup and in the Calling Report Excel. Keep both on this one source.
 */
class UserCallingSummary
{
    private array $branchCache = [];
    private $typeCache = null;

    public function forUser(User $user, string $date): array
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('punchin_date', $date)
            ->orderBy('id')
            ->first();

        $checkins = DB::table('check_in')->where('user_id', $user->id)->whereDate('checkin_date', $date);

        return [
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
            'first_checkin_time' => $this->formatTime((clone $checkins)->min('checkin_time')),
            'last_checkout_time' => $this->formatTime((clone $checkins)->max('checkout_time')),
            'total_visit' => (clone $checkins)->count(),
            'total_order' => DB::table('orders')->whereNull('deleted_at')
                ->where('created_by', $user->id)
                ->whereDate('order_date', $date)
                ->count(),
            'registrations' => $this->registrations($user->id, $date),
        ];
    }

    /**
     * Active customer types from the Customer Type master, in master order.
     */
    public function customerTypes()
    {
        if ($this->typeCache === null) {
            $this->typeCache = DB::table('customer_types')
                ->where('active', 'Y')
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get(['id', 'customertype_name']);
        }

        return $this->typeCache;
    }

    /**
     * Customers the user registered that day, counted per customer type.
     * Every active type is listed (0 when none) so the report columns stay fixed.
     */
    private function registrations(int $userId, string $date): array
    {
        $counts = DB::table('customers')
            ->whereNull('deleted_at')
            ->where('created_by', $userId)
            ->whereDate('created_at', $date)
            ->groupBy('customertype')
            ->pluck(DB::raw('COUNT(*) as total'), 'customertype');

        return $this->customerTypes()->map(fn ($type) => [
            'type_id' => $type->id,
            'label' => $type->customertype_name,
            'count' => (int) ($counts[$type->id] ?? 0),
        ])->values()->all();
    }

    private function branchNames($branchIds): ?string
    {
        $key = (string) $branchIds;
        if (!array_key_exists($key, $this->branchCache)) {
            $ids = array_filter(explode(',', $key));
            $this->branchCache[$key] = $ids
                ? DB::table('branches')->whereIn('id', $ids)->pluck('branch_name')->implode(', ')
                : null;
        }

        return $this->branchCache[$key];
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
