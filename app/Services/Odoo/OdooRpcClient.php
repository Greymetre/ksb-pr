<?php

namespace App\Services\Odoo;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/*
|--------------------------------------------------------------------------
| Calls Odoo's JSON-RPC endpoint (/json-call) to PULL data from Odoo.
|--------------------------------------------------------------------------
| Credentials come from config/services.php 'odoo' (ODOO_* in .env).
| Every call sends the authenticate block + model + method + args, and
| returns Odoo's "result" (success, data, pagination, correlation_id...).
*/
class OdooRpcClient
{
    /**
     * @throws RuntimeException when Odoo is not configured, unreachable or returns an error
     */
    public function call(string $model, string $method, array $args = []): array
    {
        $config = config('services.odoo');

        foreach (['url', 'db', 'login', 'dev_key'] as $key) {
            if (empty($config[$key])) {
                throw new RuntimeException('Odoo is not configured: set ODOO_' . strtoupper($key) . ' in .env');
            }
        }

        $response = Http::timeout((int) $config['timeout'])
            ->acceptJson()
            ->post($config['url'], [
                'jsonrpc' => '2.0',
                'params' => [
                    'authenticate' => [[
                        'db' => $config['db'],
                        'login' => $config['login'],
                        'dev_key' => $config['dev_key'],
                    ]],
                    'model' => $model,
                    'method' => $method,
                    'args' => $args,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Odoo {$model}.{$method} returned HTTP {$response->status()}");
        }

        $body = $response->json();

        if (isset($body['error'])) {
            $message = $body['error']['data']['message'] ?? $body['error']['message'] ?? 'Unknown error';
            throw new RuntimeException("Odoo {$model}.{$method} error: {$message}");
        }

        $result = $body['result'] ?? null;

        if (!is_array($result) || empty($result['success'])) {
            throw new RuntimeException("Odoo {$model}.{$method} failed: " . ($result['message'] ?? 'empty response'));
        }

        return $result;
    }
}
