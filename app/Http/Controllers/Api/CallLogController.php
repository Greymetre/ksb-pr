<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\LeadLog;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class CallLogController extends Controller
{
    public function mobileHistory(Request $request)
    {
        abort_unless($request->user()->call_management, 403, 'Call history is not enabled for this user.');

        $period = $request->input('period', 'weekly');
        $query = CallLog::with(['lead:id,company_name', 'lead.contacts:id,lead_id,name,phone_number'])
            ->where('user_id', $request->user()->id);

        if ($period === 'today') {
            $query->whereDate('started_at', today());
        } elseif ($period === 'monthly') {
            $query->where('started_at', '>=', now()->startOfMonth());
        } else {
            $query->where('started_at', '>=', now()->startOfWeek());
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('number', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($leadQuery) use ($search) {
                        $leadQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhereHas('contacts', function ($contactQuery) use ($search) {
                                $contactQuery->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $summaryQuery = clone $query;
        $attempts = (clone $summaryQuery)->count();
        $connected = (clone $summaryQuery)->whereNotNull('recording_url')->where('recording_url', '!=', '')->count();
        $duration = (clone $summaryQuery)->whereNotNull('recording_url')->where('recording_url', '!=', '')->sum('duration');
        $logs = $query->latest('started_at')->paginate((int) $request->input('page_size', 30));

        $items = $logs->getCollection()->map(function (CallLog $log) {
            $contact = optional($log->lead)->contacts->first();
            $connected = !empty($log->recording_url);

            return [
                'id' => $log->id,
                'lead_id' => $log->lead_id,
                'customer_name' => $contact?->name ?: $log->lead?->company_name ?: 'Unknown customer',
                'company_name' => $log->lead?->company_name,
                'number' => $log->number,
                'started_at' => optional($log->started_at)->toIso8601String(),
                'duration' => (int) $log->duration,
                'connected' => $connected,
                'remark' => $log->remark,
                'recording_play_url' => $connected ? URL::temporarySignedRoute(
                    'api.call-recordings.play',
                    now()->addHour(),
                    ['callLog' => $log->id]
                ) : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
            'summary' => [
                'attempts' => $attempts,
                'connected' => $connected,
                'not_connected' => max(0, $attempts - $connected),
                'duration' => (int) $duration,
            ],
        ]);
    }

    public function playRecording(CallLog $callLog)
    {
        abort_if(empty($callLog->recording_url), 404, 'Recording not available.');

        $recording = Http::withBasicAuth(
            config('services.plivo.auth_id'),
            config('services.plivo.auth_token')
        )->timeout(30)->get($callLog->recording_url);

        abort_unless($recording->successful(), 502, 'Unable to load recording.');

        return response($recording->body(), 200, [
            'Content-Type' => $recording->header('Content-Type') ?: 'audio/mpeg',
            'Content-Disposition' => 'inline; filename="call-'.$callLog->id.'.mp3"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Store a new call log.
     */
    public function store(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'lead_id'    => 'required|exists:leads,id',
                'started_at' => 'required|date',
                'duration'   => 'required|integer|min:0',
                'status'     => 'required|in:0,1', // 0 = No Response, 1 = Received
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422); // 422 Unprocessable Entity
            }

            $callLog = CallLog::create([
                'user_id'    => $request->user()->id,
                'lead_id'    => $request->lead_id,
                'number'     => $request->number,
                'started_at' => date('Y-m-d H:i:s', strtotime($request->started_at)),
                'duration'   => $request->duration ?? 0,
                'status'     => $request->status,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Call log created successfully',
                'data'    => $callLog,
            ], 200);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'success' => false,
                'message' => 'Call log created successfully',
                'data'    => [],
            ], 200);
        }
    }

    /**
     * Get call logs (optionally filter by user or lead).
     */
    public function index(Request $request)
    {
        $user_ids = getUsersReportingToAuth($request->user()->id);
        $users = User::select('id', 'name')->where('active', 'Y');
        $pageSize = $request->has('page_size') ? $request->page_size : 20;

        if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
            $users = $users->whereIn('id', $user_ids);
        }
        $users = $users->get();

        // Base query
        $query = CallLog::with(['user:id,name', 'lead:id,company_name', 'lead.contacts:id,lead_id,name']);

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('started_at', $request->date);
        }

        if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin')) {
            $query->whereIn('user_id', $user_ids); // ✅ should be user_id not id
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        // Clone query for totals (without pagination)
        $allLogs = (clone $query)->get();

        // Paginated logs
        $logs = $query->latest('started_at')->paginate($pageSize);

        // Totals for ALL logs
        $totalDuration = $allLogs->sum('duration');
        $callDialed    = $allLogs->count();
        $connected     = $allLogs->where('status', 1)->count();
        $noResponse    = $allLogs->where('status', 0)->count();

        foreach ($logs as $log) {
            $log->contact_name = $log->lead ? $log->lead->contacts->first()->name : '';
            $log->duration = gmdate('i:s', $log->duration);
        }

        // dd($totalDuration);

        return response()->json([
            'success' => true,
            'data'    => $logs->items(),
            'users'   => $users,
            'call_dialted'   => $callDialed,
            'connected'      => $connected,
            'no_response'    => $noResponse,
            'total_duration' => gmdate('H:i:s', $totalDuration),
        ]);
    }

    public function last_call(Request $request)
    {
        $user_id = $request->user()->id;
        $last_call = CallLog::where('user_id', $user_id)->latest()->first();
        $data['last_call_id'] = $last_call ? $last_call->id : '';
        $data['last_call_remark'] = $last_call ? ($last_call->remark ? true : false) : true;
        $data['lead_type'] = $last_call ? ($last_call->lead ? $last_call->lead->status_is->status_name : 'lead not found') : '';
        $data['lead_type_id'] = $last_call ? ($last_call->lead ? $last_call->lead->status : 'lead not found') : '';
        $data['all_types'] = Status::where('module', 'LeadStatus')->select('id', 'display_name')->get();
        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function update_remark(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'remark' => 'required',
            'lead_type_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => $validator->errors(),
            ]);
        }

        $last_call = CallLog::with('lead')->findOrFail($request->id);

        if (!$last_call) {
            return response()->json([
                'success' => false,
                'data'    => 'Call log not found',
            ]);
        }

        $last_call->remark = $request->remark;
        $last_call->save();

        // Update the related lead status
        if ($last_call->lead) {
            $old_status = Status::where('id', $last_call->lead->status)->first();
            $last_call->lead->status = $request->lead_type_id;
            $last_call->lead->save();
            $new_status = Status::where('id', $request->lead_type_id)->first();
            $msg = 'Lead move from ' . $old_status->display_name . ' to ' . $new_status->display_name .
                ' by ' . Auth::user()->name;
            LeadLog::create([
                'lead_id' => $last_call->lead->id,
                'message' => $msg,
                'created_by' => Auth::id(),
            ]);
        }
        return response()->json([
            'success' => true,
            'data'    => $last_call,
        ]);
    }
}
