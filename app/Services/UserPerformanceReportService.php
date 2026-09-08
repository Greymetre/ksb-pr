<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserPerformanceReportService
{
    public const DAILY_VISIT_TARGET = 6;

    public function rows(array $filters): Collection
    {
        // Rolling 8-day period, inclusive of today (today minus 7 days through today).
        $start = Carbon::parse($filters['start_date'] ?? now()->subDays(7))->startOfDay();
        $end = Carbon::parse($filters['end_date'] ?? now())->endOfDay();
        $userIds = collect(getUsersReportingToAuth())->map(fn ($id) => (int) $id);

        $users = User::with(['getdivision', 'getbranch', 'getdesignation', 'reportinginfo'])
            ->where('active', 'Y')
            ->whereIn('id', $userIds)
            ->whereDoesntHave('getdivision', function ($query) {
                $query->whereRaw('LOWER(TRIM(division_name)) = ?', ['ho']);
            })
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->when($filters['designation_id'] ?? null, fn ($q, $id) => $q->where('designation_id', $id))
            ->when($filters['division_id'] ?? null, fn ($q, $id) => $q->where('division_id', $id))
            ->when($filters['branch_id'] ?? null, fn ($q, $id) => $q->where('branch_id', $id))
            ->orderBy('name')
            ->get();

        return $users->map(function (User $user) use ($start, $end) {
            $workingDays = DB::table('attendances')->where('user_id', $user->id)
                ->whereBetween('punchin_date', [$start, $end])
                ->where('working_type', '!=', 'Full Day Leave')
                ->distinct()->count(DB::raw('DATE(punchin_date)'));
            $visitTarget = self::DAILY_VISIT_TARGET * $workingDays;
            $visited = DB::table('check_in')->where('user_id', $user->id)
                ->whereBetween('checkin_date', [$start, $end])
                ->distinct()->count(DB::raw('COALESCE(entity_id, customer_id)'));

            // Secondary value is specifically the value of retailer orders.
            $retailerOrders = DB::table('orders')->whereNull('orders.deleted_at')
                ->where('orders.created_by', $user->id)
                ->whereBetween('orders.order_date', [$start, $end])
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))->from('customers')
                        ->join('customer_types', 'customer_types.id', '=', 'customers.customertype')
                        ->whereColumn('customers.id', 'orders.buyer_id')
                        ->where(function ($retailerType) {
                            $retailerType->whereRaw('LOWER(TRIM(customer_types.type_name)) = ?', ['retailer'])
                                ->orWhereRaw('LOWER(TRIM(customer_types.customertype_name)) = ?', ['retailer']);
                        });
                });
            $secondaryValue = (clone $retailerOrders)->sum('orders.sub_total');

            // Primary orders collected are orders taken from Dealers or Distributors.
            $primaryOrders = DB::table('orders')->whereNull('orders.deleted_at')
                ->where('orders.created_by', $user->id)
                ->whereBetween('orders.order_date', [$start, $end])
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))->from('customers')
                        ->join('customer_types', 'customer_types.id', '=', 'customers.customertype')
                        ->whereColumn('customers.id', 'orders.buyer_id')
                        ->where(function ($primaryType) {
                            $primaryType->whereRaw(
                                'LOWER(TRIM(customer_types.type_name)) IN (?, ?)',
                                ['dealer', 'distributor']
                            )->orWhereRaw(
                                'LOWER(TRIM(customer_types.customertype_name)) IN (?, ?)',
                                ['dealer', 'distributor']
                            );
                        });
                });

            $targetQuery = DB::table('salestargetusers')->where('user_id', $user->id)->where('type', 'primary');
            $months = collect();
            for ($date = $start->copy()->startOfMonth(); $date->lte($end); $date->addMonth()) {
                $months->push([$date->format('M'), $date->year]);
            }
            $targetQuery->where(function ($query) use ($months) {
                foreach ($months as [$month, $year]) {
                    $query->orWhere(fn ($q) => $q->where('month', $month)->whereYear('year', $year));
                }
            });

            $collection = DB::table('payments')->whereNull('deleted_at')->where('user_id', $user->id)
                ->whereBetween('payment_date', [$start, $end])->sum('amount');
            $dues = DB::table('customer_outstantings')->where('user_id', $user->id)->sum('amount');
            $overdue = DB::table('customer_outstantings')->where('user_id', $user->id)
                ->whereNotIn('days', ['0-30', '31-60'])->sum('amount');

            return [
                'employee_code' => $user->employee_codes,
                'employee_name' => $user->name,
                'daily_visit_target' => self::DAILY_VISIT_TARGET,
                'working_days' => $workingDays,
                'visit_target' => $visitTarget,
                'customers_visited' => $visited,
                'adherence' => $visitTarget ? round($visited * 100 / $visitTarget, 1) : 0,
                'new_counters' => DB::table('customers')->where('created_by', $user->id)
                    ->whereBetween('created_at', [$start, $end])->count(),
                // Match Customers List > Employee filter exactly: assigned executive,
                // creator, or an assignment in employee_details.
                'cumulative_counters' => DB::table('customers')->where(function ($query) use ($user) {
                    $query->where('executive_id', $user->id)
                        ->orWhere('created_by', $user->id)
                        ->orWhereExists(function ($assignment) use ($user) {
                            $assignment->select(DB::raw(1))->from('employee_details')
                                ->whereColumn('employee_details.customer_id', 'customers.id')
                                ->where('employee_details.user_id', $user->id);
                        });
                })->count(),
                'secondary_orders_value' => (float) $secondaryValue,
                'primary_target' => (float) (clone $targetQuery)->sum('target'),
                'primary_achievement' => (float) (clone $targetQuery)->sum('achievement'),
                'overdue' => (float) $overdue,
                'payment_collection' => (float) $collection,
                'total_payment_dues' => (float) $dues,
                'new_dealers' => DB::table('dealer_appointments')->where('created_by', $user->id)->whereBetween('appointment_date', [$start, $end])->count(),
                'primary_orders_collected' => (float) (clone $primaryOrders)->sum('orders.sub_total'),
                'zone' => optional($user->getdivision)->division_name,
                'branch' => optional($user->getbranch)->branch_name,
                'designation' => optional($user->getdesignation)->designation_name,
                'reporting_manager' => optional($user->reportinginfo)->name,
            ];
        })->values();
    }
}
