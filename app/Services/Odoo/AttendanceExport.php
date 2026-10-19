<?php

namespace App\Services\Odoo;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/*
| Builds the monthly attendance snapshot that Odoo reads from
| GET /api/v1/odoo/attendance: one row per employee per day of the month.
|
| The day status follows the CRM "Attendance Summary" download exactly
| (AttendanceController::attendanceSummaryDownload): same employees, same
| holiday / leave / working-hours rules, same codes. If those rules change
| there, change them here too.
|
| Re-running a month replaces its rows, so it is safe to run again.
| See odoo-integration-docs/attendance-api.md
*/
class AttendanceExport
{
    public const TABLE = 'odoo_attendance_exports';

    // Report code => status sent to Odoo
    public const STATUSES = [
        'P' => 'Present',
        'PW' => 'Sunday Working',
        'HF' => 'Half Day',
        'A' => 'Absent',
        'MIS' => 'Miss Punch',
        'CL' => 'Leave',
        '1/2P+1/2CL' => 'Half Day Leave',
        'Comp-Off' => 'Comp Off',
        'H' => 'Holiday',
        'WOFF' => 'Week Off',
        // Only reached for attendance rows without a punch-in time
        'AL' => 'Leave',
        '1/2P+1/2AL' => 'Half Day Leave',
        'Comp Off' => 'Comp Off',
        '1/2P+1/2Comp Off' => 'Half Day Comp Off',
        'LOPN' => 'Loss Of Pay',
        '1/2P+1/2LOPN' => 'Half Day Loss Of Pay',
        'Comp Off N' => 'Comp Off Rejected',
        '1/2P+1/2Comp Off N' => 'Half Day Comp Off Rejected',
        'PN' => 'Pending',
    ];

    private const LEAVE_TYPES = ['Full Day Leave', 'First Half Leave', 'Second Half Leave', 'Leave'];

    /**
     * @return int number of rows written for the month
     */
    public function build(string $period): int
    {
        $from = Carbon::createFromFormat('Y-m-d', $period . '-01')->startOfDay();
        $to = $from->copy()->endOfMonth()->startOfDay();
        $timeZone = config('app.timezone');
        $generatedAt = now();
        $count = 0;

        DB::transaction(function () use ($period, $from, $to, $timeZone, $generatedAt, &$count) {
            DB::table(self::TABLE)->where('period', $period)->delete();

            User::with('userinfo')
                ->where('active', 'Y')
                ->whereDoesntHave('roles', fn ($q) => $q->whereIn('id', config('constants.customer_roles')))
                ->where('show_attandance_report', '1')
                ->chunkById(200, function ($users) use ($period, $from, $to, $timeZone, $generatedAt, &$count) {
                    $rows = $this->rowsFor($users, $period, $from, $to, $timeZone, $generatedAt);
                    foreach (array_chunk($rows, 1000) as $chunk) {
                        DB::table(self::TABLE)->insert($chunk);
                    }
                    $count += count($rows);
                });
        });

        return $count;
    }

    /**
     * Previous calendar month in the app timezone, e.g. "2026-09" when run in October.
     */
    public static function previousPeriod(): string
    {
        return now()->startOfMonth()->subMonth()->format('Y-m');
    }

    private function rowsFor($users, string $period, Carbon $from, Carbon $to, string $timeZone, $generatedAt): array
    {
        $userIds = $users->pluck('id')->all();
        $fromYmd = $from->toDateString();
        $toYmd = $to->toDateString();

        // First attendance of each user per day, as the report's ->first() picks it
        $attendances = [];
        Attendance::whereIn('user_id', $userIds)
            ->whereBetween('punchin_date', [$fromYmd, $toYmd])
            ->orderBy('id')
            ->get()
            ->each(function ($a) use (&$attendances) {
                $attendances[$a->user_id][substr($a->punchin_date, 0, 10)] ??= $a;
            });

        // Report uses the first leave covering the day
        $leaves = Leave::whereIn('user_id', $userIds)
            ->whereDate('from_date', '<=', $toYmd)
            ->whereDate('to_date', '>=', $fromYmd)
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');

        $holidayData = getHolidayData(
            $users->pluck('branch_id')->filter()->flatMap(fn ($ids) => explode(',', $ids))
                ->map(fn ($id) => trim($id))->filter()->unique()->all()
        );

        $rows = [];
        foreach ($users as $user) {
            $holidays = collect(explode(',', (string) $user->branch_id))
                ->flatMap(fn ($branchId) => $holidayData->get((int) trim($branchId), []))
                ->all();
            $doj = $user->userinfo && $user->userinfo->date_of_joining
                ? Carbon::parse($user->userinfo->date_of_joining)->startOfDay()
                : null;

            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $ymd = $date->toDateString();
                $attendance = $attendances[$user->id][$ymd] ?? null;
                $leave = ($leaves->get($user->id) ?? collect())
                    ->first(fn ($l) => substr($l->from_date, 0, 10) <= $ymd && substr($l->to_date, 0, 10) >= $ymd);

                $code = $this->dayCode($date, $attendance, $leave, in_array($ymd, $holidays), $doj);
                if ($code === null) {
                    continue; // report shows "-": before joining date / no joining date
                }

                $rows[] = [
                    'period' => $period,
                    'user_id' => $user->id,
                    'employee_code' => $user->employee_codes !== null ? trim($user->employee_codes) : null,
                    'employee_name' => $user->name,
                    'attendance_date' => $ymd,
                    'status' => self::STATUSES[$code],
                    'status_code' => $code,
                    'attendance_id' => $attendance?->id,
                    'punchin_time' => $attendance?->punchin_time ?: null,
                    'punchout_date' => $attendance && $attendance->punchout_time ? ($attendance->punchout_date ?: $attendance->punchin_date) : null,
                    'punchout_time' => $attendance?->punchout_time ?: null,
                    'total_working_time' => $attendance ? $this->workingTime($attendance) : null,
                    'time_zone' => $timeZone,
                    'working_type' => $attendance?->working_type ?: null,
                    'generated_at' => $generatedAt,
                ];
            }
        }

        return $rows;
    }

    /**
     * Same decision order as AttendanceController::attendanceSummaryDownload.
     * Returns null where the report shows "-".
     */
    private function dayCode(Carbon $date, $attendance, $leave, bool $isHoliday, ?Carbon $doj): ?string
    {
        if ($isHoliday) {
            return 'H';
        }

        if ($attendance) {
            $status = (int) $attendance->attendance_status;
            if ($status === 2) {
                return 'A';
            }

            $workingTypes = array_map('trim', explode(',', $attendance->working_type ?? ''));
            if (array_intersect($workingTypes, self::LEAVE_TYPES)) {
                if (($leave?->bal_type ?? '') === 'Comp-off Balance') {
                    return 'Comp-Off';
                }
                $isHalf = (bool) array_intersect($workingTypes, ['First Half Leave', 'Second Half Leave']);
                if (($leave?->bal_type ?? '') === 'Leave Without Pay') {
                    return $isHalf ? '1/2P+1/2LOPN' : 'LOPN';
                }
                return $isHalf ? '1/2P+1/2CL' : 'CL';
            }

            if (!empty($attendance->punchin_time) && !empty($attendance->punchout_time)) {
                // 8:30+ = full day, 4:30 to <8:30 = half day, below 4:30 = absent
                $minutes = $this->workedMinutes($attendance);
                if ($minutes >= 510) {
                    return $date->isSunday() ? 'PW' : 'P';
                }
                return $minutes >= 270 ? 'HF' : 'A';
            }

            if (!empty($attendance->punchin_time)) {
                return 'MIS';
            }

            return $this->noPunchinCode($status, $attendance->working_type ?? '', $leave);
        }

        if ($leave) {
            if (($leave->bal_type ?? '') === 'Comp-off Balance') {
                return 'Comp-Off';
            }
            $isHalf = in_array($leave->type, ['First Half Leave', 'Second Half Leave'], true);
            if (($leave->bal_type ?? '') === 'Leave Without Pay') {
                return $isHalf ? '1/2P+1/2LOPN' : 'LOPN';
            }
            return $isHalf ? '1/2P+1/2CL' : 'CL';
        }

        if (!$doj || $doj->gt($date)) {
            return null;
        }

        return $date->isSunday() ? 'WOFF' : 'A';
    }

    /**
     * Attendance row without a punch-in time (rare; e.g. a leave row created by a command).
     */
    private function noPunchinCode(int $status, string $workingType, $leave): string
    {
        $compOff = ($leave?->bal_type ?? '') === 'Comp-off Balance';
        $lwp = ($leave?->bal_type ?? '') === 'Leave Without Pay';
        $fullLeave = in_array($workingType, ['Leave', 'Full Day Leave']);
        $halfLeave = in_array($workingType, ['Second Half Leave', 'First Half Leave']);

        if ($status === 0 || $status === 1) {
            if ($lwp && ($fullLeave || $halfLeave)) {
                return $fullLeave ? 'LOPN' : '1/2P+1/2LOPN';
            }
            if ($fullLeave) {
                return $compOff ? 'Comp Off' : 'AL';
            }
            if ($halfLeave) {
                return $compOff ? '1/2P+1/2Comp Off' : '1/2P+1/2AL';
            }
            return $workingType === 'Holiday' ? 'H' : 'P';
        }

        if ($status === 2) {
            if ($fullLeave) {
                return $compOff ? 'Comp Off N' : 'LOPN';
            }
            if ($halfLeave) {
                return $compOff ? '1/2P+1/2Comp Off N' : '1/2P+1/2LOPN';
            }
            return 'A';
        }

        return 'PN';
    }

    private function workedMinutes($attendance): int
    {
        if (!empty($attendance->worked_time)) {
            $parts = array_map('intval', explode(':', $attendance->worked_time));
            return (($parts[0] ?? 0) * 60) + ($parts[1] ?? 0);
        }

        try {
            $in = Carbon::parse($attendance->punchin_date . ' ' . $attendance->punchin_time);
            $out = Carbon::parse(($attendance->punchout_date ?: $attendance->punchin_date) . ' ' . $attendance->punchout_time);
            return $in->diffInMinutes($out, false);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Uses the stored worked_time; falls back to punch-out minus punch-in. Null when there is no punch-out.
     */
    private function workingTime($attendance): ?string
    {
        if (empty($attendance->punchout_time)) {
            return null;
        }
        if (!empty($attendance->worked_time)) {
            return $attendance->worked_time;
        }

        $minutes = $this->workedMinutes($attendance);
        if ($minutes < 0) {
            return null;
        }

        return sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60);
    }
}
