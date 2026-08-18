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
     * Display the forgot password view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * Note: with MAIL_MAILER=log (the default for local dev), the reset link
     * is written to storage/logs/laravel.log instead of actually emailing out.
     * Switch MAIL_MAILER to smtp and configure real credentials to send it for real.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Always show the same generic message, whether or not the email exists,
        // so the form can't be used to enumerate registered accounts.
        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }
}
