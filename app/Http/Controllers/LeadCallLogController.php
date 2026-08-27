<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class LeadCallLogController extends Controller
{
    /**
     * Get call logs (optionally filter by user or lead).
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('call_management_access'), 403, '403 Forbidden');
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
            if (!empty($request->columns[6]['search']['value'])) {
                $statusSearch = $request->columns[6]['search']['value'];
            
                // Adjust this according to how your status is stored
                if (strtolower($statusSearch) === 'connected') {
                    $query->where('status', 1)
                        ->whereNotNull('recording_url')
                        ->where('recording_url', '!=', '');
                } elseif (in_array(strtolower($statusSearch), ['no response', 'not connected'], true)) {
                    $query->where(function ($statusQuery) {
                        $statusQuery->where('status', 0)
                            ->orWhereNull('recording_url')
                            ->orWhere('recording_url', '');
                    });
                } else {
                    // Optional: fuzzy match for text
                    $query->where('status', 'like', "%{$statusSearch}%");
                }
            }
            // Clone query for counts before pagination/filtering of datatables
            $countsQuery = clone $query;

            $totalCalls = $countsQuery->count();
            $connectedCalls = (clone $countsQuery)->where('status', 1)
                ->whereNotNull('recording_url')
                ->where('recording_url', '!=', '')
                ->count();
            $noResponseCalls = (clone $countsQuery)->where(function ($statusQuery) {
                $statusQuery->where('status', 0)
                    ->orWhereNull('recording_url')
                    ->orWhere('recording_url', '');
            })->count();
            $totalDurationSeconds = (clone $countsQuery)
                ->whereNotNull('recording_url')
                ->where('recording_url', '!=', '')
                ->sum('duration');

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
                    $connected = (int) $row->status === 1 && !empty($row->recording_url);
                    $badge = $connected ? 'badge-success' : 'badge-danger';
                    $label = $connected ? 'Connected' : 'Not Connected';
                    return '<span class="badge '.$badge.'">'.$label.'</span>';
                })
                ->addColumn('customer_name', function ($row) {
                    return optional(optional($row->lead)->contacts->first())->name ?: '-';
                })
                ->addColumn('recording', function ($row) {
                    if (!$row->recording_url) {
                        return '<span class="text-muted">Processing / unavailable</span>';
                    }
                    return '<audio controls preload="none" style="width:220px;height:36px">'
                        .'<source src="'.route('call-management.recording', $row).'" type="audio/mpeg">'
                        .'Your browser does not support audio playback.</audio>';
                })
                ->addColumn('lead_status', function ($row) {
                    if (!$row->lead) return 'Not Found';
                    return $row->lead->status_is->status_name;
                })
                ->rawColumns(['started_at', 'duration', 'status', 'recording'])
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
        abort_if(Gate::denies('call_management_access'), 403, '403 Forbidden');
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

        $headers = ['Agent', 'Customer', 'Lead', 'Contact No', 'Date & Time', 'Call Duration', 'Call Status', 'Plivo Status', 'Call UUID', 'Recording URL', 'Cost', 'Remark'];

        foreach ($call_logs as $call_log) {
            $seconds = (int) $call_log->duration;

            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;

            $call_duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            $rows[] = [
                $call_log->user?->name ?? 'Not Found',
                optional(optional($call_log->lead)->contacts->first())->name ?: 'Not Found',
                $call_log->lead ? $call_log->lead->company_name : 'Not Found',
                $call_log->number,
                date('d/m/Y h:i A', strtotime($call_log->started_at)),
                $call_duration,
                $call_log->status == 0 ? 'No Response' : 'Connected',
                $call_log->plivo_status,
                $call_log->plivo_call_uuid,
                $call_log->recording_url,
                $call_log->cost,
                '',
            ];
        }

        // ✅ Export
        $export = new ExcelExport($headers, $rows);
        return Excel::download($export, $filename);


    }

    public function recording(CallLog $callLog)
    {
        abort_if(Gate::denies('call_management_access'), 403, '403 Forbidden');
        abort_if(empty($callLog->recording_url), 404, 'Recording not available.');

        $recording = Http::withBasicAuth(
            config('services.plivo.auth_id'),
            config('services.plivo.auth_token')
        )->timeout(30)->get($callLog->recording_url);

        abort_unless($recording->successful(), 502, 'Unable to load recording from Plivo.');

        return response($recording->body(), 200, [
            'Content-Type' => $recording->header('Content-Type') ?: 'audio/mpeg',
            'Content-Disposition' => 'inline; filename="call-'.$callLog->id.'.mp3"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
