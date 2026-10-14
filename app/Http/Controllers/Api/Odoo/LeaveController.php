<?php

namespace App\Http\Controllers\Api\Odoo;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
| FieldKonnect -> Odoo employee leaves (read only, live data). API spec: odoo-integration-docs/leave-api.md
*/
class LeaveController extends Controller
{
    private const STATUS = [0 => 'Pending', 1 => 'Approved', 2 => 'Rejected'];

    // Leave hours per type, as agreed with the Odoo team
    private const TIMES = [
        'First Half Leave' => ['10:00:00', '13:00:00'],
        'Second Half Leave' => ['13:00:00', '18:00:00'],
    ];
    private const FULL_DAY = ['10:00:00', '18:00:00'];

    public function index(Request $request)
    {
        $client = $request->attributes->get('odoo_client');
        $correlationId = $request->attributes->get('correlation_id');

        $month = $request->query('month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $month)) {
            return $this->error($request, $client, 400, 'month must be YYYY-MM', ['field' => 'month']);
        }

        $status = $request->query('status');
        $statusCode = $status === null ? null : array_search(ucfirst(strtolower((string) $status)), self::STATUS, true);
        if ($status !== null && $statusCode === false) {
            return $this->error($request, $client, 400, 'status must be Pending, Approved or Rejected', ['field' => 'status']);
        }

        $from = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth()->toDateString();
        $to = Carbon::createFromFormat('Y-m-d', $month . '-01')->endOfMonth()->toDateString();
        $pageSize = min(max((int) $request->query('page_size', 100), 1), 500);
        $page = max((int) $request->query('page', 1), 1);

        // Every leave that touches the month, including ones that start before or end after it
        $query = DB::table('leaves')
            ->leftJoin('users', 'users.id', '=', 'leaves.user_id')
            ->whereDate('leaves.from_date', '<=', $to)
            ->whereDate('leaves.to_date', '>=', $from);
        if ($request->filled('employee_code')) {
            $query->where('users.employee_codes', $request->query('employee_code'));
        }
        if ($statusCode !== null) {
            $statusCode === 0
                ? $query->where(fn ($q) => $q->where('leaves.status', 0)->orWhereNull('leaves.status'))
                : $query->where('leaves.status', $statusCode);
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('leaves.from_date')->orderBy('leaves.id')
            ->forPage($page, $pageSize)
            ->get([
                'leaves.id', 'leaves.from_date', 'leaves.to_date', 'leaves.type', 'leaves.bal_type', 'leaves.reason',
                'leaves.status', 'leaves.created_at', 'leaves.updated_at', 'users.employee_codes', 'users.name',
            ])
            ->map(function ($row) {
                $isHalfDay = isset(self::TIMES[$row->type]);
                [$start, $end] = self::TIMES[$row->type] ?? self::FULL_DAY;

                return [
                    'leave_id' => $row->id,
                    'employee_code' => $row->employee_codes !== null ? trim($row->employee_codes) : null,
                    'employee_name' => $row->name,
                    'from_date' => substr($row->from_date, 0, 10),
                    'to_date' => substr($row->to_date, 0, 10),
                    'leave_time_start' => $start,
                    'leave_time_end' => $end,
                    'type' => $row->type,
                    'balance_type' => $row->bal_type,
                    'leave_days' => $isHalfDay ? 0.5 : Carbon::parse($row->from_date)->diffInDays(Carbon::parse($row->to_date)) + 1,
                    'reason' => $row->reason !== '' ? $row->reason : null,
                    'status' => self::STATUS[(int) $row->status] ?? 'Pending',
                    'applied_on' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'time_zone' => config('app.timezone'),
                ];
            });
        $totalPages = (int) ceil($total / $pageSize);

        $this->writeLog($request, $client, $rows->count(), 200, []);

        return response()->json([
            'success' => true,
            'message' => 'Records fetched successfully',
            'month' => $month,
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

    private function error(Request $request, $client, int $status, string $message, array $details)
    {
        $this->writeLog($request, $client, 0, $status, [$details + ['message' => $message]]);

        return response()->json([
            'success' => false,
            'error' => ['code' => 'VALIDATION_ERROR', 'message' => $message, 'details' => [$details]],
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
                'entity' => 'leaves',
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
