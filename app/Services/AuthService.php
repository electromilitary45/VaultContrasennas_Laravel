<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * Servicio de autenticación
 *
 * Contiene toda la lógica de negocio relacionada con autenticación,
 * registro, recuperación de contraseña y verificación de email.
 */
class AuthService
{
    public function __construct(
        private InvitationCodeService $invitationCodeService
    ) {}

    /**
     * Registrar un nuevo usuario (legacy, sin código). Mantenido por compatibilidad.
     *
     * @param array<string, mixed> $data Datos del usuario (name, email, password)
     * @return User Usuario creado
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        return $user;
    }

    /**
     * Registrar usuario con código de invitación. Crea usuario en la org del código y marca código como usado.
     *
     * @param array{invitation_code: string, name: string, email: string, password: string} $data
     * @return User Usuario creado
     */
    public function registerWithInvitationCode(array $data): User
    {
        $inv = $this->invitationCodeService->findValidUnusedCode($data['invitation_code']);
        if (!$inv) {
            throw ValidationException::withMessages([
                'invitation_code' => ['El código de invitación no es válido o ya fue utilizado.'],
            ]);
        }

        return DB::transaction(function () use ($inv, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'user',
                'organization_id' => $inv->organization_id,
            ]);

            $inv->update([
                'used_at' => now(),
                'used_by_user_id' => $user->id,
            ]);

            event(new Registered($user));

            Log::info('Usuario registrado con código de invitación', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'organization_id' => $inv->organization_id,
                'invitation_code_id' => $inv->id,
                'timestamp' => now(),
            ]);

            return $user;
        });
    }

    /**
     * Autenticar un usuario
     *
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return bool True si la autenticación fue exitosa
     * @throws ValidationException
     */
    public function authenticate(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt(
            ['email' => $email, 'password' => $password, 'is_active' => true],
            $remember
        );
    }

    /**
     * Cerrar sesión del usuario actual
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Enviar enlace de recuperación de contraseña
     *
     * @param string $email
     * @return string Estado de la operación
     */
    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Restablecer contraseña de un usuario
     *
     * @param array<string, mixed> $data Datos del reset (email, password, token)
     * @return string Estado de la operación
     */
    public function resetPassword(array $data): string
    {
        return Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }

    /**
     * Actualizar contraseña del usuario autenticado
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return void
     * @throws ValidationException Si la contraseña actual es incorrecta
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('La contraseña actual es incorrecta.'),
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Forzar cambio de contraseña (sin pedir la actual). Usado en primer login con temporal.
     * Actualiza la contraseña y limpia must_change_password.
     */
    public function forceChangePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
        ]);
    }

    /**
     * Verificar email del usuario
     *
     * @param User $user
     * @param int $id
     * @param string $hash
     * @return bool True si la verificación fue exitosa
     */
    public function verifyEmail(User $user, int $id, string $hash): bool
    {
        if (!hash_equals((string) $id, (string) $user->getKey())) {
            return false;
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return false;
        }

        if ($user->hasVerifiedEmail()) {
            return true; // Ya está verificado, no es un error
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
            return true;
        }

        return false;
    }

    /**
     * Reenviar email de verificación
     *
     * @param User $user
     * @return void
     */
    public function resendVerificationEmail(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
    }

    /**
     * Confirmar contraseña del usuario
     *
     * @param User $user
     * @param string $password
     * @return bool True si la contraseña es correcta
     */
    public function confirmPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * Obtener reglas de validación para registro
     *
     * @return array<string, mixed>
     */
    public function getRegistrationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }

    /**
     * Obtener reglas de validación para reset de contraseña
     *
     * @return array<string, mixed>
     */
    public function getPasswordResetRules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }
}
