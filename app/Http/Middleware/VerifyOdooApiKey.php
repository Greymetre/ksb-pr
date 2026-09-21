<?php

namespace App\Http\Middleware;

use App\Models\OdooApiClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VerifyOdooApiKey
{
    /*
    |--------------------------------------------------------------------------
    | Protects the Odoo push endpoints (/api/v1/odoo/*).
    |--------------------------------------------------------------------------
    | Odoo sends its key in the "X-API-Key" header. The key's mode (test/live)
    | decides which table the data goes to - see odoo-integration-docs/README.md.
    */
    public function handle(Request $request, Closure $next)
    {
        $correlationId = (string) $request->header('X-Correlation-ID');
        if ($correlationId === '' || strlen($correlationId) > 100) {
            $correlationId = (string) Str::uuid();
        }
        $request->attributes->set('correlation_id', $correlationId);

        $providedKey = (string) $request->header('X-API-Key');

        $client = $providedKey === ''
            ? null
            : OdooApiClient::where('api_key_hash', OdooApiClient::hashKey($providedKey))->where('active', true)->first();

        if (!$client) {
            Log::warning('Odoo API key ' . ($providedKey === '' ? 'missing' : 'invalid'), [
                'endpoint' => $request->path(),
                'ip' => $request->ip(),
                'correlation_id' => $correlationId,
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Missing or invalid X-API-Key header',
                    'details' => [],
                ],
                'correlation_id' => $correlationId,
            ], 401);
        }

        $client->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('odoo_client', $client);

        $response = $next($request);
        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
