<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAttendanceReminder extends Command
{
    protected $signature = 'attendance:send-reminder {type : punchin or punchout}';
    protected $description = 'Push a reminder to employees who have not punched in (9:30 AM) or punched out (8:30 PM) today';

    public function handle()
    {
        $type = strtolower($this->argument('type'));
        if (!in_array($type, ['punchin', 'punchout'], true)) {
            $this->error('Type must be punchin or punchout.');
            return Command::FAILURE;
        }

        $today = Carbon::now('Asia/Kolkata');
        $todayDate = $today->toDateString();

        if ($today->isSunday()) {
            $this->info('Sunday, no reminders sent.');
            return Command::SUCCESS;
        }

        if ($type === 'punchin') {
            [$userIds, $title, $message] = $this->punchinTargets($todayDate);
        } else {
            [$userIds, $title, $message] = $this->punchoutTargets($todayDate);
        }

        $sent = 0;
        foreach ($userIds as $userId) {
            if (SendPushNotification($userId, $message, 'attendance', null, $title)) {
                $sent++;
            }
        }

        $this->info(ucfirst($type) . " reminder: {$sent} sent out of " . count($userIds) . ' users.');
        return Command::SUCCESS;
    }

    /**
     * Active employees with the app installed, who have no attendance row for today,
     * are not on leave and do not have a branch holiday today.
     */
    private function punchinTargets(string $todayDate): array
    {
        $users = User::whereDoesntHave('roles', function ($query) {
                $query->whereIn('id', config('constants.customer_roles'));
            })
            ->where('active', 'Y')
            ->whereNotNull('notification_id')
            ->where('notification_id', '!=', '')
            ->get(['id', 'branch_id']);

        $alreadyPunchedIn = Attendance::whereDate('punchin_date', $todayDate)
            ->whereIn('user_id', $users->pluck('id'))
            ->pluck('user_id')
            ->flip();

        // Pending (0) or approved (1) leave covering today; rejected (2) leave does not count
        $onLeave = Leave::whereDate('from_date', '<=', $todayDate)
            ->whereDate('to_date', '>=', $todayDate)
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 2);
            })
            ->pluck('user_id')
            ->flip();

        $holidayCache = [];
        $userIds = [];
        foreach ($users as $user) {
            if (isset($alreadyPunchedIn[$user->id]) || isset($onLeave[$user->id])) {
                continue;
            }

            $branchKey = (string) $user->branch_id;
            if (!array_key_exists($branchKey, $holidayCache)) {
                $holidayCache[$branchKey] = in_array($todayDate, Holiday::datesForBranches(explode(',', $branchKey)), true);
            }
            if ($holidayCache[$branchKey]) {
                continue;
            }

            $userIds[] = $user->id;
        }

        return [
            $userIds,
            'Punch In Reminder',
            'You have not punched in yet today. Please mark your attendance in the app.',
        ];
    }

    /**
     * Employees who punched in today from the app but have not punched out.
     * Rows created by leave (no punch-in location) are skipped.
     */
    private function punchoutTargets(string $todayDate): array
    {
        $userIds = Attendance::whereDate('punchin_date', $todayDate)
            ->whereNotNull('punchin_time')
            ->whereNull('punchout_time')
            ->whereNotNull('punchin_latitude')
            ->where('punchin_latitude', '!=', '')
            ->whereHas('users', function ($query) {
                $query->where('active', 'Y');
            })
            ->distinct()
            ->pluck('user_id')
            ->all();

        return [
            $userIds,
            'Punch Out Reminder',
            'You have not punched out yet today. Please punch out in the app.',
        ];
    }
}
