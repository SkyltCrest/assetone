<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('role:administrator,asset_officer')
     *
     * Administrators always pass, regardless of which roles are listed —
     * the administrator is a superuser and can do everything every other
     * role can do, so a route restricted to any other role never needs to
     * remember to also list 'administrator'.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'You do not have permission to access this page.');
        }

        if ($user->isAdministrator()) {
            return $next($request);
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
