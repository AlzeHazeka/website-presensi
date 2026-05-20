<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnyPermission
{
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $list = array_values(array_filter(array_map('trim', explode('|', (string) $permissions))));
        if ($list === []) {
            abort(403);
        }

        foreach ($list as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        abort(403);
    }
}

