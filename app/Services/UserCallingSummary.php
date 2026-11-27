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
            'retailer_registered' => $this->registeredCount($user->id, $date, ['retailer'], ['RETAILER', 'WORKSHOP']),
            'influencer_registered' => $this->registeredCount($user->id, $date, ['influencer'], ['MECHANIC', 'GARAGE']),
        ];
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
