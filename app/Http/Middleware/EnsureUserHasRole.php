<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Only let users with one of the given roles through.
     * Usage: ->middleware('role:administrator,asset_officer')
     * Administrators always pass, since they're a superuser.
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
