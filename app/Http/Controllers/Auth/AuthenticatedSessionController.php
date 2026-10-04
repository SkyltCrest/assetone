<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /** Failed sign-ins allowed before a short lock-out, and how long it lasts. */
    private const MAX_TRIES = 5;

    private const LOCK_SECONDS = 30;

    /**
     * Show the login page.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Log the user in.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey($request), self::LOCK_SECONDS);

            return $this->failed($request, 'email', 'We could not find an account with that email address.');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), self::LOCK_SECONDS);

            return $this->failed($request, 'password', 'The password you entered is incorrect.');
        }

        if (! $user->isActive()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact an administrator.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Log the user out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Send the user back with the error, how many tries are left, and the
     * lock-out countdown once the last try has been used.
     */
    private function failed(Request $request, string $field, string $message): RedirectResponse
    {
        $key = $this->throttleKey($request);
        $left = RateLimiter::remaining($key, self::MAX_TRIES);

        if ($left <= 0) {
            $request->session()->flash('lock_seconds', RateLimiter::availableIn($key));
            $message = 'Account temporarily locked. Too many failed attempts.';
        } else {
            $message .= " {$left} attempt(s) left.";
        }

        return back()->withErrors([$field => $message])->onlyInput('email');
    }

    /**
     * Block further attempts once this email/IP pair has failed too many times.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), self::MAX_TRIES)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        $request->session()->flash('lock_seconds', $seconds);   // drives the countdown on the login page
        $minutes = ceil($seconds / 60);

        throw ValidationException::withMessages([
            'email' => $seconds > 60
                ? "Too many failed login attempts. Please try again in about {$minutes} minute(s)."
                : "Too many failed login attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    /**
     * Rate-limiting key for the current login attempt.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }
}
