<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT: Skip file download responses
        |--------------------------------------------------------------------------
        */
        if (
            $response instanceof BinaryFileResponse ||
            $response instanceof StreamedResponse
        ) {
            return $response;
        }

        /*
        |--------------------------------------------------------------------------
        | Add security headers for normal responses
        |--------------------------------------------------------------------------
        */
        return $response->withHeaders([

            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'X-XSS-Protection' => '1; mode=block',

            'Strict-Transport-Security' =>
                'max-age=31536000; includeSubDomains; preload',

            'Permissions-Policy' =>
                'camera=(), microphone=(), geolocation=(), fullscreen=(self)',
        ]);
    }
}
