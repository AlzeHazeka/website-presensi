<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Support\Permissions;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) abort(403);

        // Backward compatible: "admin area" means the user can access at least
        // user listing OR any report view.
        $canAccess = $user->can(Permissions::USERS_VIEW)
            || $user->can(Permissions::REPORT_DAILY_VIEW)
            || $user->can(Permissions::REPORT_BY_USER_VIEW)
            || $user->can(Permissions::REPORT_MONTHLY_VIEW);

        if (! $canAccess) abort(403);

        return $next($request);
    }
}
