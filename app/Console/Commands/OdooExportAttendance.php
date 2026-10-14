<?php

namespace App\Console\Commands;

use App\Services\Odoo\AttendanceExport;
use Illuminate\Console\Command;

/*
| Freeze one month of attendance into odoo_attendance_exports for Odoo to read.
| Scheduled on the 1st at 01:00 IST for the previous month in app/Console/Kernel.php.
|
|   php artisan odoo:export-attendance                 (previous month)
|   php artisan odoo:export-attendance --month=2026-09 (rebuild a given month)
*/
class OdooExportAttendance extends Command
{
    protected $signature = 'odoo:export-attendance {--month= : Month as YYYY-MM, default previous month}';

    protected $description = 'Build the monthly attendance snapshot read by Odoo (GET /api/v1/odoo/attendance)';

    public function handle(AttendanceExport $export)
    {
        $period = $this->option('month') ?: AttendanceExport::previousPeriod();

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            $this->error('--month must be YYYY-MM');
            return self::FAILURE;
        }
        if ($period >= now()->format('Y-m')) {
            $this->error('Only a completed month can be exported.');
            return self::FAILURE;
        }

        $count = $export->build($period);
        $this->info("Attendance {$period}: {$count} rows exported.");

        return self::SUCCESS;
    }
}
