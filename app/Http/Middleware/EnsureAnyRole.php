<?php

namespace App\Http\Middleware;

use App\Support\RoleAccess;
use App\Support\Roles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnyRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();
        $allowedRoles = array_values(array_filter(array_map('trim', explode('|', $roles))));

        if (! $user || $allowedRoles === [] || ! RoleAccess::userHasAnyRole($user, $allowedRoles)) {
            return $this->deny($request, $allowedRoles);
        }

        return $next($request);
    }

    /**
     * @param  array<int, string>  $allowedRoles
     */
    private function deny(Request $request, array $allowedRoles): Response
    {
        $message = $allowedRoles === Roles::adminRoles()
            ? 'Anda tidak memiliki akses ke fitur penggajian.'
            : 'Anda tidak memiliki akses ke fitur ini.';

        if ($request->expectsJson()) {
            abort(403, $message);
        }

        return redirect()
            ->route('dashboard')
            ->with('error', $message)
            ->with('flash.banner', $message)
            ->with('flash.bannerStyle', 'danger');
    }
}
