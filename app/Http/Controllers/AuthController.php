<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterWithInvitationCodeRequest;
use App\Http\Requests\VerifyTwoFactorRequest;
use App\Services\AuthService;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controlador de autenticación unificado
 * 
 * Maneja todas las operaciones relacionadas con autenticación:
 * login, registro, recuperación de contraseña, verificación de email, etc.
 */
class AuthController extends Controller
{
    /**
     * Constructor - Inyección de dependencias
     *
     * @param AuthService $authService
     * @param TwoFactorService $twoFactorService
     */
    public function __construct(
        private AuthService $authService,
        private TwoFactorService $twoFactorService
    ) {}

    /**
     * Mostrar formulario de login
     *
     * @return View
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Procesar login
     *
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        
        $user = Auth::user();
        
        // Si el usuario tiene 2FA habilitado, requerir verificación
        if ($user->hasTwoFactorEnabled()) {
            $userId = $user->id;
            $remember = $request->boolean('remember');
            
            // Preservar token CSRF antes del logout para mantener consistencia
            $csrfToken = $request->session()->token();
            
            // Guardar datos en sesión antes del logout
            $request->session()->put('login.id', $userId);
            $request->session()->put('login.remember', $remember);
            $request->session()->put('login.csrf_token', $csrfToken);
            
            // Cerrar sesión temporalmente (mantiene la sesión y token CSRF)
            Auth::guard('web')->logout();
            
            return redirect()->route('two-factor.login');
        }
        
        // Si no tiene 2FA, login normal
        $request->session()->regenerate();

        if ($user->hasMustChangePassword()) {
            return redirect()->route('password.change-required');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Mostrar formulario de verificación de 2FA durante el login.
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function showTwoFactorLogin(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('login.id')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Sesión de login expirada. Por favor, inicia sesión nuevamente.']);
        }

        $userId = $request->session()->get('login.id');
        $user = \App\Models\User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget(['login.id', 'login.remember', 'login.csrf_token']);
            return redirect()->route('login')
                ->withErrors(['email' => 'Usuario no encontrado o 2FA no habilitado.']);
        }

        // Sincronizar token CSRF preservado si cambió después del logout
        $currentToken = $request->session()->token();
        $preservedToken = $request->session()->get('login.csrf_token');
        
        if ($preservedToken && $preservedToken !== $currentToken) {
            $request->session()->put('login.csrf_token', $currentToken);
        } elseif (!$preservedToken) {
            $request->session()->put('login.csrf_token', $currentToken);
        }

        return view('auth.two-factor-login', [
            'user' => $user,
        ]);
    }

    /**
     * Verificar código 2FA y completar el login.
     *
     * @param VerifyTwoFactorRequest $request
     * @return RedirectResponse
     */
    public function verifyTwoFactorLogin(VerifyTwoFactorRequest $request): RedirectResponse
    {
        if (!$request->session()->has('login.id')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Sesión de login expirada. Por favor, inicia sesión nuevamente.']);
        }

        $userId = $request->session()->get('login.id');
        $remember = $request->session()->get('login.remember', false);
        $user = \App\Models\User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            $request->session()->forget(['login.id', 'login.remember', 'login.csrf_token']);
            return redirect()->route('login')
                ->withErrors(['email' => 'Usuario no encontrado o 2FA no habilitado.']);
        }

        $code = $request->validated()['code'];

        if (!$this->twoFactorService->verifyCode($user, $code, true)) {
            return back()->withErrors(['code' => 'Código inválido. Por favor, intenta de nuevo.']);
        }

        // Código válido, completar login
        Auth::login($user, $remember);
        
        // Limpiar datos de sesión temporal y regenerar sesión
        $request->session()->forget(['login.id', 'login.remember', 'login.csrf_token']);
        $request->session()->regenerate();

        // Registrar en logs de auditoría
        Log::info('User logged in with 2FA', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remember' => $remember,
            'timestamp' => now(),
        ]);

        if ($user->hasMustChangePassword()) {
            return redirect()->route('password.change-required');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Cerrar sesión
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Registrar en logs de auditoría antes de cerrar sesión
        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        }
        
        $this->authService->logout($request);

        return redirect('/');
    }

    /**
     * Mostrar formulario de registro (solo con código de invitación).
     *
     * @return View
     */
    public function showRegister(Request $request): View
    {
        return view('auth.register', [
            'prefillCode' => $request->query('code'),
        ]);
    }

    /**
     * Procesar registro con código de invitación.
     *
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function register(RegisterWithInvitationCodeRequest $request): RedirectResponse
    {
        $user = $this->authService->registerWithInvitationCode($request->validated());
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Mostrar formulario de recuperación de contraseña
     *
     * @return View
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Enviar enlace de recuperación de contraseña
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = $this->authService->sendPasswordResetLink($request->email);

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    /**
     * Mostrar formulario de reset de contraseña
     *
     * @param Request $request
     * @return View
     */
    public function showResetPassword(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Procesar reset de contraseña
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->authService->getPasswordResetRules());

        $status = $this->authService->resetPassword($validated);

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    /**
     * Mostrar prompt de verificación de email
     *
     * @param Request $request
     * @return RedirectResponse|View
     */
    public function showEmailVerification(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard', absolute: false))
            : view('auth.verify-email');
    }

    /**
     * Verificar email del usuario
     *
     * @param Request $request
     * @param int $id
     * @param string $hash
     * @return RedirectResponse
     */
    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = $request->user();

        if ($this->authService->verifyEmail($user, $id, $hash)) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        return redirect()->route('verification.notice')->with('error', __('El enlace de verificación no es válido.'));
    }

    /**
     * Reenviar email de verificación
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        $this->authService->resendVerificationEmail($request->user());

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Mostrar formulario de confirmación de contraseña
     *
     * @return View
     */
    public function showConfirmPassword(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirmar contraseña del usuario
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function confirmPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!$this->authService->confirmPassword($request->user(), $request->password)) {
            throw ValidationException::withMessages([
                'password' => __('La contraseña es incorrecta.'),
            ]);
        }

        $request->session()->passwordConfirmed();

        return redirect()->intended();
    }

    /**
     * Mostrar formulario de cambio obligatorio de contraseña (primer login con temporal).
     */
    public function showChangeRequired(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasMustChangePassword()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return view('auth.change-password-required');
    }

    /**
     * Guardar nueva contraseña (flujo de cambio obligatorio).
     */
    public function storeChangeRequired(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasMustChangePassword()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $this->authService->forceChangePassword($user, $validated['password']);

        Log::info('User forced password change completed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('status', 'password-changed');
    }

    /**
     * Actualizar contraseña del usuario autenticado
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user = $request->user();
        
        $this->authService->updatePassword(
            $user,
            $validated['current_password'],
            $validated['password']
        );

        // Registrar en logs de auditoría
        Log::info('User password updated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        return back()->with('status', 'password-updated');
    }
}
