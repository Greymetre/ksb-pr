<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifySapApiKey
{
    /*
    |--------------------------------------------------------------------------
    | Protects the SAP push endpoints (insert_sap_stock, insert_sap_sell).
    |--------------------------------------------------------------------------
    | SAP must send the key in the "X-API-Key" header.
    |
    | SAP_API_KEY_ENFORCE=false -> invalid/missing key is only logged, request
    |                              still goes through (rollout period).
    | SAP_API_KEY_ENFORCE=true  -> invalid/missing key is rejected with 401.
    */
    public function handle(Request $request, Closure $next)
    {
        $expectedKey = (string) config('services.sap.api_key');
        $providedKey = (string) $request->header('X-API-Key');
        $enforce = (bool) config('services.sap.enforce');

        $isValid = $expectedKey !== '' && $providedKey !== '' && hash_equals($expectedKey, $providedKey);

        if ($isValid) {
            return $next($request);
        }

        $reason = $providedKey === '' ? 'missing' : 'invalid';

        Log::channel('sapstock')->error("SAP API key {$reason}", [
            'endpoint' => $request->path(),
            'ip' => $request->ip(),
            'enforced' => $enforce,
        ]);

        if (!$enforce) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
        ], 401);
    }
}
