<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictOdooRole
{
    /*
    |--------------------------------------------------------------------------
    | The "odoo" role is an external developer login. It may only open the
    | Odoo Sync page and log out; every other CRM URL is blocked.
    |--------------------------------------------------------------------------
    */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('odoo') || $user->hasRole('superadmin')) {
            return $next($request);
        }

        if ($request->is('odoo-sync', 'odoo-sync/*', 'logout')) {
            return $next($request);
        }

        if ($request->isMethod('GET') && !$request->ajax() && !$request->expectsJson()) {
            return redirect()->route('odoo_sync.index');
        }

        abort(403, '403 Forbidden');
    }
}
