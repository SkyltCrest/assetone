<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the forgot password page.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link.
     *
     * Note: locally (MAIL_MAILER=log), the link goes to storage/logs/laravel.log
     * instead of a real email. Production sends over SMTP (Brevo relay).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            // A mail-transport failure (bad credentials, provider outage) would
            // otherwise surface as a raw 500. Log it and show the same neutral
            // message so the page still behaves.
            Log::error('Password reset link could not be sent: '.$e->getMessage());
        }

        // Same message either way, so this can't be used to check which emails are registered.
        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }
}
