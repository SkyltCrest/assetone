<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventCachingOfPrivatePages
{
    /**
     * Stop the browser keeping a copy of pages shown to a logged-in user, so
     * the Back button after logout asks the server again (and lands on the
     * login page) instead of redisplaying the old page from its cache.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Leave alone anything that asked to be cached on purpose (photos).
        if ($request->user() && ! $response->headers->hasCacheControlDirective('max-age')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        return $response;
    }
}
