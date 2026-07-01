<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $captchaRules = loginCaptchaRequired((string) $this->input('email'), $this->ip())
            ? ['required', 'string']
            : ['nullable', 'string'];

        return [
            'email' => ['required'],
            'password' => ['required', 'string'],
            'captcha' => $captchaRules,
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();
        $login = $this->input('email') ?? $this->input('mobile');

        if (loginCaptchaRequired((string) $this->input('email'), $this->ip()) && ! loginCaptchaCheck((string) $this->input('captcha'))) {
            $this->recordFailedLogin((string) $login);
            RateLimiter::hit($this->throttleKey(), 900);

            throw ValidationException::withMessages([
                'captcha' => 'The security code is incorrect.',
            ]);
        }

        $this->session()->forget('login_captcha_hash');

        // Get login input (email or mobile)
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        // Attempt authentication
        if (!Auth::attempt([$loginField => $login, 'password' => $this->input('password')], $this->boolean('remember'))) {
            $this->recordFailedLogin((string) $login);
            RateLimiter::hit($this->throttleKey(), 900);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
        RateLimiter::clear($this->throttleKey());
        $this->session()->forget(['login_captcha_hash', 'login_captcha_login', 'login_failed_attempts']);
    }


    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        $login = $this->input('email') ?? $this->input('mobile');

        return loginThrottleKey((string) $login, $this->ip());
    }

    protected function recordFailedLogin(string $login): void
    {
        $previousLogin = (string) $this->session()->get('login_captcha_login', '');
        $attempts = hash_equals(Str::lower($previousLogin), Str::lower($login))
            ? (int) $this->session()->get('login_failed_attempts', 0)
            : 0;

        $this->session()->put('login_captcha_login', $login);
        $this->session()->put('login_failed_attempts', $attempts + 1);
    }
}
