<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripSensitiveJsonResponseData
{
    protected array $sensitiveKeys = [
        'password',
        'password_string',
        'remember_token',
        'mfa_token',
        'otp',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }

        $data = $response->getData(true);
        $response->setData($this->strip($data));

        return $response;
    }

    private function strip($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $this->sensitiveKeys, true)) {
                unset($data[$key]);
                continue;
            }

            $data[$key] = $this->strip($value);
        }

        return $data;
    }
}
