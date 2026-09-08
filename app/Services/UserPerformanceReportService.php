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
        $start = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end = Carbon::parse($filters['end_date'] ?? now())->endOfDay();
        $userIds = collect(getUsersReportingToAuth())->map(fn ($id) => (int) $id);

        $users = User::with(['getdivision', 'getbranch', 'getdesignation', 'reportinginfo'])
            ->where('active', 'Y')
            ->whereIn('id', $userIds)
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
                ->where('orders.executive_id', $user->id)
                ->whereBetween('orders.order_date', [$start, $end])
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))->from('customers')
                        ->whereColumn('customers.id', 'orders.buyer_id')
                        ->where('customers.customertype', 2);
                });
            $secondaryValue = (clone $retailerOrders)->sum('orders.grand_total');

            // Primary Sales stores the employee code supplied in the primary-sales import.
            $primaryOrders = DB::table('primary_sales')
                ->whereBetween('invoice_date', [$start, $end])
                ->where(function ($query) use ($user) {
                    $query->where('emp_code', $user->employee_codes)
                        ->orWhere(function ($fallback) use ($user) {
                            $fallback->where(function ($blank) {
                                $blank->whereNull('emp_code')->orWhere('emp_code', '');
                            })->where('sales_person', $user->name);
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
                'new_counters' => DB::table('secondary_customers')->where('created_by', $user->id)->whereBetween('created_at', [$start, $end])->count(),
                'cumulative_counters' => DB::table('secondary_customers')
                    ->whereRaw("FIND_IN_SET(?, REPLACE(employee_id, ' ', ''))", [$user->id])
                    ->where('created_at', '<=', $end)->count(),
                'secondary_orders_value' => (float) $secondaryValue,
                'primary_target' => (float) (clone $targetQuery)->sum('target'),
                'primary_achievement' => (float) (clone $targetQuery)->sum('achievement'),
                'overdue' => (float) $overdue,
                'payment_collection' => (float) $collection,
                'total_payment_dues' => (float) $dues,
                'new_dealers' => DB::table('dealer_appointments')->where('created_by', $user->id)->whereBetween('appointment_date', [$start, $end])->count(),
                'primary_orders_collected' => (clone $primaryOrders)->distinct()->count('invoiceno'),
                'zone' => optional($user->getdivision)->division_name,
                'branch' => optional($user->getbranch)->branch_name,
                'designation' => optional($user->getdesignation)->designation_name,
                'reporting_manager' => optional($user->reportinginfo)->name,
            ];
        })->values();
    }
}
