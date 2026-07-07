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

        $email = strtolower(trim($request->input('email')));

        Log::info('Password reset email send requested.', $this->mailLogContext($email));

        try {
            $status = Password::sendResetLink(['email' => $email]);
        } catch (Throwable $exception) {
            Log::error('Password reset email could not be sent.', [
                'email' => $email,
                'mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.' . config('mail.default') . '.host'),
                'mail_port' => config('mail.mailers.' . config('mail.default') . '.port'),
                'mail_encryption' => config('mail.mailers.' . config('mail.default') . '.encryption'),
                'from_address' => config('mail.from.address'),
                'exception' => get_class($exception),
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'We could not send the password reset email right now. Please try again later.');
        }

        Log::info('Password reset email send completed.', $this->mailLogContext($email, $status));

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Password reset email was accepted by the configured mailer.', [
                'email' => $email,
            ]);
        }

        if ($status === Password::INVALID_USER) {
            Log::warning('Password reset email was not sent because the email was not found.', [
                'email' => $email,
                'status' => $status,
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT && $status !== Password::INVALID_USER) {
            Log::warning('Password reset email returned an unexpected status.', [
                'email' => $email,
                'status' => $status,
            ]);

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'We could not send the password reset email right now. Please try again later.');
        }

        return back()->with('status', 'If an account exists for this email address, a password reset link will be sent.');
    }

    private function mailLogContext(string $email, ?string $status = null): array
    {
        $mailer = config('mail.default');

        return array_filter([
            'email' => $email,
            'status' => $status,
            'mailer' => $mailer,
            'mail_host' => config("mail.mailers.{$mailer}.host"),
            'mail_port' => config("mail.mailers.{$mailer}.port"),
            'mail_encryption' => config("mail.mailers.{$mailer}.encryption"),
            'from_address' => config('mail.from.address'),
        ], function ($value) {
            return $value !== null && $value !== '';
        });
    }
}
