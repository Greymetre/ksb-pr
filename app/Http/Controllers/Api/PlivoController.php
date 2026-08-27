<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class PlivoController extends Controller
{
    public function makeCall(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'integer', 'exists:leads,id'],
        ]);

        $user = $request->user();
        abort_unless($user->call_management, 403, 'Plivo calling is not enabled for this user.');
        $lead = Lead::with('contacts')->findOrFail($validated['lead_id']);
        $agentNumber = $this->e164($user->mobile);
        $customerNumber = $this->e164(optional($lead->contacts->first())->phone_number);

        if (!$agentNumber || !$customerNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Agent or lead contact has an invalid mobile number.',
            ], 422);
        }

        $this->ensureConfigured();

        $callLog = CallLog::create([
            'user_id' => $user->id,
            'lead_id' => $lead->id,
            'number' => $customerNumber,
            'started_at' => now(),
            'duration' => 0,
            'status' => 0,
            'plivo_status' => 'initiating',
            'webhook_token' => Str::random(64),
        ]);

        $query = http_build_query(['call_log_id' => $callLog->id, 'token' => $callLog->webhook_token]);

        try {
            $response = Http::withBasicAuth(config('services.plivo.auth_id'), config('services.plivo.auth_token'))
                ->asJson()
                ->post('https://api.plivo.com/v1/Account/'.config('services.plivo.auth_id').'/Call/', [
                    'from' => config('services.plivo.from_number'),
                    'to' => $agentNumber,
                    'answer_url' => $this->webhookUrl('answer_url', 'api/plivo/answer').'?'.$query,
                    'answer_method' => 'POST',
                    'ring_url' => $this->webhookUrl('status_url', 'api/plivo/status').'?'.$query,
                    'ring_method' => 'POST',
                    'hangup_url' => $this->webhookUrl('status_url', 'api/plivo/status').'?'.$query,
                    'hangup_method' => 'POST',
                ]);

            if (!$response->successful()) {
                $callLog->update(['plivo_status' => 'failed']);
                return response()->json([
                    'success' => false,
                    'message' => 'Plivo rejected the call request.',
                    'error' => $response->json(),
                ], 502);
            }

            $callUuid = $response->json('request_uuid.0') ?: $response->json('request_uuid');
            $callLog->update(['plivo_call_uuid' => $callUuid, 'plivo_status' => 'queued']);

            return response()->json([
                'success' => true,
                'message' => 'Call initiated. The agent phone will ring first.',
                'data' => ['call_log_id' => $callLog->id, 'call_uuid' => $callUuid],
            ], 201);
        } catch (Throwable $exception) {
            report($exception);
            $callLog->update(['plivo_status' => 'failed']);

            return response()->json(['success' => false, 'message' => 'Unable to connect to Plivo.'], 502);
        }
    }

    public function answer(Request $request)
    {
        $callLog = $this->authorizedCallLog($request);
        $customerNumber = $this->e164($callLog->number);
        $query = http_build_query(['call_log_id' => $callLog->id, 'token' => $callLog->webhook_token]);
        $statusUrl = $this->webhookUrl('status_url', 'api/plivo/status').'?'.$query;
        $recordingUrl = $this->webhookUrl('recording_url', 'api/plivo/recording').'?'.$query;

        $callLog->update([
            'plivo_call_uuid' => $request->input('CallUUID', $callLog->plivo_call_uuid),
            'plivo_status' => 'agent-answered',
            'answered_at' => $callLog->answered_at ?: now(),
        ]);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Response>'
            .'<Record startOnDialAnswer="true" redirect="false" callbackUrl="'.e($recordingUrl).'" callbackMethod="POST" />'
            .'<Dial callerId="'.e(config('services.plivo.from_number')).'" callbackUrl="'.e($statusUrl).'" callbackMethod="POST">'
            .'<Number>'.e($customerNumber).'</Number>'
            .'</Dial>'
            .'</Response>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function status(Request $request)
    {
        $callLog = $this->authorizedCallLog($request);
        $dialAction = $request->input('DialAction');
        $status = $request->input('DialBLegStatus')
            ?: $request->input('CallStatus')
            ?: $request->input('Event')
            ?: $dialAction
            ?: 'updated';

        $updates = [
            'plivo_status' => strtolower((string) $status),
            'plivo_b_leg_uuid' => $request->input('DialBLegUUID', $callLog->plivo_b_leg_uuid),
        ];

        $billDuration = $request->input('DialBLegBillDuration', $request->input('BillDuration'));
        $duration = is_numeric($billDuration)
            ? $billDuration
            : $request->input('DialBLegDuration', $request->input('Duration'));
        if (is_numeric($duration)) {
            $updates['duration'] = (int) $duration;
        }

        $cost = $request->input('TotalCost', $request->input('CallCost'));
        if (is_numeric($cost)) {
            $updates['cost'] = $cost;
        }

        if (in_array(strtolower((string) $status), ['completed', 'hangup', 'failed', 'busy', 'no-answer', 'timeout', 'cancel'], true)) {
            $updates['completed_at'] = now();
        }

        $callLog->update($updates);

        return response('OK', 200);
    }

    public function callStatus(Request $request, CallLog $callLog)
    {
        abort_unless(
            (int) $callLog->user_id === (int) $request->user()->id || $request->user()->hasRole('superadmin'),
            403,
            'You cannot view this call.'
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $callLog->id,
                'status' => $callLog->plivo_status,
                'answered' => (bool) $callLog->answered_at,
                'completed' => (bool) $callLog->completed_at,
            ],
        ]);
    }

    public function recording(Request $request)
    {
        $callLog = $this->authorizedCallLog($request);
        $duration = $request->input('RecordingDuration');
        $recordingUrl = $request->input('RecordUrl', $request->input('RecordingURL'));
        $callLog->update(array_filter([
            'recording_url' => $recordingUrl,
            'recording_id' => $request->input('RecordingID', $request->input('RecordingUUID')),
            'duration' => is_numeric($duration) && (int) $duration >= 0 ? (int) $duration : null,
            'status' => $recordingUrl ? 1 : null,
        ], static fn ($value) => $value !== null && $value !== ''));

        return response('OK', 200);
    }

    private function authorizedCallLog(Request $request): CallLog
    {
        return CallLog::whereKey($request->query('call_log_id'))
            ->where('webhook_token', $request->query('token'))
            ->firstOrFail();
    }

    private function webhookUrl(string $configKey, string $path): string
    {
        return rtrim(config('services.plivo.'.$configKey) ?: url($path), '/');
    }

    private function e164(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if (strlen($digits) === 10) {
            $digits = '91'.$digits;
        }

        return strlen($digits) >= 11 && strlen($digits) <= 15 ? '+'.$digits : null;
    }

    private function ensureConfigured(): void
    {
        abort_unless(
            config('services.plivo.auth_id')
                && config('services.plivo.auth_token')
                && config('services.plivo.from_number'),
            500,
            'Plivo credentials are not configured on the server.'
        );
    }
}
