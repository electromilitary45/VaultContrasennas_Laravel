<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EnableTwoFactorRequest;
use App\Http\Requests\VerifyTwoFactorRequest;
use App\Services\TwoFactorService;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactorService,
        private TotpService $totpService
    ) {}

    /**
     * Mostrar página de configuración de 2FA.
     */
    public function index(): View
    {
        $user = Auth::user();
        $secret = null;
        $qrCodeUri = null;

        // Si no tiene 2FA habilitado, generar secreto temporal para mostrar QR
        if (!$user->hasTwoFactorEnabled()) {
            $secret = $this->twoFactorService->generateSecret($user);
            $qrCodeUri = $this->twoFactorService->generateQrCodeUri($user, $secret);
            
            // Guardar secreto temporal en sesión para verificación
            session(['two_factor_secret' => $secret]);
        }

        return view('profile.two-factor', [
            'user' => $user,
            'secret' => $secret,
            'qrCodeUri' => $qrCodeUri,
        ]);
    }

    /**
     * Habilitar 2FA después de verificar código.
     */
    public function enable(EnableTwoFactorRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $code = $request->validated()['code'];
        
        // Obtener secreto temporal de la sesión
        $secret = session('two_factor_secret');
        
        if (!$secret) {
            return redirect()->route('profile.two-factor')
                ->withErrors(['code' => 'Sesión expirada. Por favor, recarga la página.']);
        }

        // Verificar código
        if (!$this->totpService->verifyCode($secret, $code)) {
            return redirect()->route('profile.two-factor')
                ->withErrors(['code' => 'Código inválido. Por favor, intenta de nuevo.']);
        }

        // Habilitar 2FA
        $this->twoFactorService->enableTwoFactor($user, $secret);
        
        // Limpiar secreto temporal
        session()->forget('two_factor_secret');

        return redirect()->route('profile.edit')
            ->with('status', 'two-factor-enabled');
    }

    /**
     * Deshabilitar 2FA.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $this->twoFactorService->disableTwoFactor($user);

        // Registrar en logs de auditoría
        Log::info('Two-factor authentication disabled', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        return redirect()->route('profile.edit')
            ->with('status', 'two-factor-disabled');
    }

    /**
     * Regenerar códigos de respaldo.
     */
    public function regenerateBackupCodes(): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.two-factor')
                ->withErrors(['error' => '2FA no está habilitado.']);
        }

        $backupCodes = $this->twoFactorService->regenerateBackupCodes($user);

        // Registrar en logs de auditoría
        Log::info('Two-factor backup codes regenerated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        return redirect()->route('profile.two-factor')
            ->with('backup_codes', $backupCodes)
            ->with('status', 'backup-codes-regenerated');
    }
}
