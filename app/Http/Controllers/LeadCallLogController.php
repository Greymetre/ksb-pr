<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class LeadCallLogController extends Controller
{
    /**
     * Get call logs (optionally filter by user or lead).
     */
    public function index(Request $request)
    {
        $user_ids = getUsersReportingToAuth();
        $users = User::select('id', 'name')->where('active', 'Y');

        if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
            $users = $users->whereIn('id', $user_ids);
        }
        $users = $users->get();

        if ($request->ajax()) {
            // Base query
            $query = CallLog::with(['user:id,name', 'lead:id,company_name,status', 'lead.contacts:id,lead_id,name,phone_number']);

            if ($request->has('user_id') && !empty($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('start_date') && !empty($request->start_date)) {
                $query->whereDate('started_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && !empty($request->end_date)) {
                $query->whereDate('started_at', '<=', $request->end_date);
            }

            if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
                $query->whereIn('user_id', $user_ids); // ✅ should be user_id not id
            }

            if ($request->has('lead_id')) {
                $query->where('lead_id', $request->lead_id);
            }
            if (!empty($request->columns[5]['search']['value'])) {
                $statusSearch = $request->columns[5]['search']['value'];
            
                // Adjust this according to how your status is stored
                if (strtolower($statusSearch) === 'connected') {
                    $query->where('status', 1);
                } elseif (strtolower($statusSearch) === 'no response') {
                    $query->where('status', 0);
                } else {
                    // Optional: fuzzy match for text
                    $query->where('status', 'like', "%{$statusSearch}%");
                }
            }
            // Clone query for counts before pagination/filtering of datatables
            $countsQuery = clone $query;

            $totalCalls = $countsQuery->count();
            $connectedCalls = (clone $countsQuery)->where('status', 1)->count();
            $noResponseCalls = (clone $countsQuery)->where('status', 0)->count();
            $totalDurationSeconds = (clone $countsQuery)->sum('duration');

            // Convert seconds to HH:MM:SS
            $hours = floor($totalDurationSeconds / 3600);
            $minutes = floor(($totalDurationSeconds % 3600) / 60);
            $seconds = $totalDurationSeconds % 60;
            $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            // DataTable response
            $call_logs = $query->orderBy('started_at', 'desc');
            
            // return data to datatable
            return datatables()->of($call_logs)
                ->editColumn('started_at', function ($row) {
                    return date('d/m/Y h:i A', strtotime($row->started_at));
                })
                ->editColumn('duration', function ($row) {
                    $seconds = (int) $row->duration;

                    $hours = floor($seconds / 3600);
                    $minutes = floor(($seconds % 3600) / 60);
                    $seconds = $seconds % 60;

                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                })
                ->addColumn('status', function ($row) {
                    return ($row->status == 0) ? '<span class="badge badge-danger">No Response</span>' : '<span class="badge badge-success">Connected</span>';
                })
                ->addColumn('lead_status', function ($row) {
                    if (!$row->lead) return 'Not Found';
                    return $row->lead->status_is->status_name;
                })
                ->rawColumns(['started_at', 'duration', 'status'])
                ->with([
                    'summary' => [
                        'total' => $totalCalls,
                        'connected' => $connectedCalls,
                        'no_response' => $noResponseCalls,
                        'total_duration' => $formattedDuration,
                    ]
                ])
                ->make(true);
        }

        return view('call_logs.index', compact('users'));
    }

    public function download(Request $request)
    {
        $filename = 'Call Logs.xlsx';
        $user_ids = getUsersReportingToAuth();

        $query = CallLog::with(['user:id,name', 'lead:id,company_name,status', 'lead.contacts:id,lead_id,name,phone_number']);

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('started_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('started_at', '<=', $request->end_date);
        }

        if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
            $query->whereIn('user_id', $user_ids); // ✅ should be user_id not id
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        $call_logs = $query->orderBy('started_at', 'desc')->get();

        $rows = [];

        $headers = ['User Name', 'Customer Name', 'Contact No', 'Date & Time', 'Call Duration', 'Call Status', 'Lead Status', 'Remark'];

        foreach ($call_logs as $call_log) {
            $seconds = (int) $call_log->duration;

            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;

            $call_duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            $rows[] = [
                $call_log->user->name,
                $call_log->lead ? $call_log->lead->company_name : 'Not Found',
                $call_log->number,
                date('d/m/Y h:i A', strtotime($call_log->started_at)),
                $call_duration,
                $call_log->status == 0 ? 'No Response' : 'Connected',
                $call_log->lead ? $call_log->lead->status_is?->status_name : 'Not Found',
                '',
            ];
        }

        // ✅ Export
        $export = new ExcelExport($headers, $rows);
        return Excel::download($export, $filename);


        return view('call_logs.download', compact('users'));
    }
}
