<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

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
     * instead of a real email. Set MAIL_MAILER=smtp to send for real.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Same message either way, so this can't be used to check which emails are registered.
        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }
}
