<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeRequestInput
{
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        '_token',
    ];

    public function handle(Request $request, Closure $next)
    {
        $request->merge($this->clean($request->all()));

        return $next($request);
    }

    private function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->except, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->clean($value);
                continue;
            }

            if (is_string($value)) {
                $data[$key] = trim(strip_tags($value));
            }
        }

        return $data;
    }
}
