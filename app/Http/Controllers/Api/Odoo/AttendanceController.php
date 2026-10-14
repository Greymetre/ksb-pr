<?php

namespace App\Http\Controllers\Api\Odoo;

use App\Http\Controllers\Controller;
use App\Services\Odoo\AttendanceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
| FieldKonnect -> Odoo monthly attendance (read only). API spec: odoo-integration-docs/attendance-api.md
*/
class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->attributes->get('odoo_client');
        $correlationId = $request->attributes->get('correlation_id');
        $table = AttendanceExport::TABLE;

        $month = $request->query('month');
        if ($month !== null && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $month)) {
            return $this->error($request, $client, 400, 'VALIDATION_ERROR', 'month must be YYYY-MM', ['field' => 'month']);
        }

        $month = $month ?? DB::table($table)->max('period');
        if ($month === null || !DB::table($table)->where('period', $month)->exists()) {
            return $this->error($request, $client, 404, 'NOT_FOUND', 'Attendance for this month is not published yet', ['month' => $month]);
        }

        $pageSize = min(max((int) $request->query('page_size', 100), 1), 500);
        $page = max((int) $request->query('page', 1), 1);

        $query = DB::table($table)->where('period', $month);
        if ($request->filled('employee_code')) {
            $query->where('employee_code', $request->query('employee_code'));
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('attendance_date')->orderBy('employee_code')->orderBy('user_id')
            ->forPage($page, $pageSize)
            ->get()
            ->map(fn ($row) => [
                'employee_code' => $row->employee_code,
                'employee_name' => $row->employee_name,
                'attendance_date' => $row->attendance_date,
                'status' => $row->status,
                'status_code' => $row->status_code,
                'attendance_id' => $row->attendance_id,
                'punchin_time' => $row->punchin_time,
                'punchout_date' => $row->punchout_date,
                'punchout_time' => $row->punchout_time,
                'total_working_time' => $row->total_working_time,
                'time_zone' => $row->time_zone,
                'working_type' => $row->working_type,
            ]);
        $totalPages = (int) ceil($total / $pageSize);

        $this->writeLog($request, $client, $rows->count(), 200, []);

        return response()->json([
            'success' => true,
            'message' => 'Records fetched successfully',
            'month' => $month,
            'generated_at' => DB::table($table)->where('period', $month)->max('generated_at'),
            'data' => $rows,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total_records' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
            ],
            'correlation_id' => $correlationId,
        ]);
    }

    private function error(Request $request, $client, int $status, string $code, string $message, array $details)
    {
        $this->writeLog($request, $client, 0, $status, [$details + ['message' => $message]]);

        return response()->json([
            'success' => false,
            'error' => ['code' => $code, 'message' => $message, 'details' => [$details]],
            'correlation_id' => $request->attributes->get('correlation_id'),
        ], $status);
    }

    private function writeLog(Request $request, $client, int $count, int $statusCode, array $errors): void
    {
        try {
            DB::table('odoo_sync_logs')->insert([
                'correlation_id' => $request->attributes->get('correlation_id'),
                'client_id' => $client->id,
                'mode' => $client->mode,
                'entity' => 'attendance',
                'method' => $request->method(),
                'ip' => $request->ip(),
                'received_count' => $count,
                'status_code' => $statusCode,
                'errors' => $errors ? json_encode($errors) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Could not write odoo_sync_logs row', ['error' => $e->getMessage()]);
        }
    }
}
