<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class EnforceRoutePermission
{
    protected array $actionMap = [
        'index' => 'access',
        'show' => 'show',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'edit',
        'update' => 'edit',
        'destroy' => 'delete',
    ];

    public function handle(Request $request, Closure $next)
    {
        $routeName = optional($request->route())->getName();

        if (! $routeName || ! auth()->check()) {
            return $next($request);
        }

        $ability = $this->abilityFromRouteName($routeName);

        $gate = Gate::getFacadeRoot();

        if ($ability && method_exists($gate, 'has') && Gate::has($ability) && Gate::denies($ability)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        return $next($request);
    }

    private function abilityFromRouteName(string $routeName): ?string
    {
        $parts = explode('.', $routeName);
        $resource = $parts[0] ?? null;
        $action = $parts[1] ?? 'access';

        if (! $resource) {
            return null;
        }

        $resource = str_replace('-', '_', $resource);
        $resource = preg_replace('/ies$/', 'y', $resource);
        $resource = preg_replace('/s$/', '', $resource);
        $permissionAction = $this->actionMap[$action] ?? 'access';

        return $resource . '_' . $permissionAction;
    }
}
