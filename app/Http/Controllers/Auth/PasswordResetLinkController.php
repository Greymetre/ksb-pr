<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $exception) {
            Log::error('Password reset email could not be sent.', [
                'email' => $request->input('email'),
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'We could not send the password reset email right now. Please try again later.');
        }

        if ($status !== Password::RESET_LINK_SENT && $status !== Password::INVALID_USER) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'We could not send the password reset email right now. Please try again later.');
        }

        return back()->with('status', 'If an account exists for this email address, a password reset link will be sent.');
    }
}
