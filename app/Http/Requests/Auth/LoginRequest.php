<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $credentials['is_active'] = true; // Solo permitir login de usuarios activos

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Verificar si el usuario existe pero está inactivo
            $user = \App\Models\User::where('email', $this->input('email'))->first();
            if ($user && !$user->is_active) {
                // Log intento de login fallido (usuario inactivo)
                Log::warning('Failed login attempt: Inactive account', [
                    'email' => $this->input('email'),
                    'user_id' => $user->id,
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'reason' => 'account_inactive',
                    'timestamp' => now(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
                ]);
            }

            // Log intento de login fallido (credenciales inválidas)
            Log::warning('Failed login attempt: Invalid credentials', [
                'email' => $this->input('email'),
                'user_exists' => $user !== null,
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'reason' => 'invalid_credentials',
                'timestamp' => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
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
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
