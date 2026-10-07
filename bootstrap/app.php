<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Render (and most PaaS hosts) terminate HTTPS at their edge proxy and
        // forward requests to the container over plain HTTP. Without this,
        // Laravel has no way to know the original request was secure, and
        // generates http:// URLs (form actions, redirects) even on a site
        // served entirely over https.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'active' => \App\Http\Middleware\EnsureAccountIsActive::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureAccountIsActive::class,
            \App\Http\Middleware\PreventCachingOfPrivatePages::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // A stale browser tab (left open too long, or one of several tabs opened
        // before the session cookie was set) carries an outdated CSRF token and
        // would otherwise hit the raw "419 Page Expired" screen. Send the user
        // back to the form they came from with their input preserved and a plain
        // explanation instead.
        //
        // Laravel has already turned the TokenMismatchException into a 419
        // HttpException by the time render callbacks run, so match on that.
        $exceptions->render(function (HttpException $e, Request $request) {
            if (! $e->getPrevious() instanceof TokenMismatchException) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your session expired. Please reload and try again.'], 419);
            }

            // A logout that arrives with a dead token (session already ended in
            // another tab, timed out, or the form was submitted twice) has
            // nothing left to do: the login page is where it was heading anyway.
            if ($request->routeIs('logout')) {
                return redirect()->route('login');
            }

            return redirect()
                ->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('error', 'Your session expired. Please try again.');
        });
    })->create();
