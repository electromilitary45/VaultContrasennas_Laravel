<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

/**
 * Servicio para gestión de 2FA/TOTP del usuario
 * 
 * Extiende la funcionalidad de TotpService para gestionar
 * la autenticación de dos factores a nivel de usuario.
 */
class TwoFactorService
{
    public function __construct(
        private TotpService $totpService
    ) {}

    /**
     * Generar secreto TOTP para un usuario.
     *
     * @param User $user
     * @return string Secreto en formato base32
     */
    public function generateSecret(User $user): string
    {
        return $this->totpService->generateSecret();
    }

    /**
     * Generar URI para QR code del usuario.
     *
     * @param User $user
     * @param string $secret
     * @return string URI para generar QR
     */
    public function generateQrCodeUri(User $user, string $secret): string
    {
        $label = $user->email;
        $issuer = config('app.name', 'PassVault');
        
        return $this->totpService->generateUri($secret, $label, $issuer);
    }

    /**
     * Generar códigos de respaldo.
     *
     * @param int $count Número de códigos a generar (por defecto 8)
     * @return array<string> Códigos de respaldo
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            // Generar código de 8 dígitos
            $codes[] = str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        }
        return $codes;
    }

    /**
     * Habilitar 2FA para un usuario.
     *
     * @param User $user
     * @param string $secret Secreto TOTP (ya verificado)
     * @return void
     */
    public function enableTwoFactor(User $user, string $secret): void
    {
        // Cifrar el secreto antes de guardarlo
        $encryptedSecret = Crypt::encryptString($secret);
        
        // Generar códigos de respaldo
        $backupCodes = $this->generateBackupCodes();
        
        $user->update([
            'totp_secret' => $encryptedSecret,
            'totp_enabled' => true,
            'totp_backup_codes' => $backupCodes,
            'totp_verified_at' => now(),
        ]);
    }

    /**
     * Verificar código TOTP del usuario.
     *
     * @param User $user
     * @param string $code Código a verificar
     * @param bool $allowBackupCode Si permite usar códigos de respaldo
     * @return bool True si el código es válido
     */
    public function verifyCode(User $user, string $code, bool $allowBackupCode = true): bool
    {
        if (!$user->hasTwoFactorEnabled()) {
            return false;
        }

        // Descifrar el secreto
        try {
            $secret = Crypt::decryptString($user->totp_secret);
        } catch (\Exception $e) {
            return false;
        }

        // Verificar código TOTP
        if ($this->totpService->verifyCode($secret, $code)) {
            return true;
        }

        // Si no es válido y se permiten códigos de respaldo, verificar
        if ($allowBackupCode && $user->totp_backup_codes) {
            $backupCodes = $user->totp_backup_codes;
            $index = array_search($code, $backupCodes);
            
            if ($index !== false) {
                // Eliminar el código usado
                unset($backupCodes[$index]);
                $user->update(['totp_backup_codes' => array_values($backupCodes)]);
                return true;
            }
        }

        return false;
    }

    /**
     * Deshabilitar 2FA para un usuario.
     *
     * @param User $user
     * @return void
     */
    public function disableTwoFactor(User $user): void
    {
        $user->update([
            'totp_secret' => null,
            'totp_enabled' => false,
            'totp_backup_codes' => null,
            'totp_verified_at' => null,
        ]);
    }

    /**
     * Regenerar códigos de respaldo.
     *
     * @param User $user
     * @return array<string> Nuevos códigos de respaldo
     */
    public function regenerateBackupCodes(User $user): array
    {
        $backupCodes = $this->generateBackupCodes();
        $user->update(['totp_backup_codes' => $backupCodes]);
        
        return $backupCodes;
    }

    /**
     * Obtener el secreto descifrado del usuario (solo para mostrar QR).
     *
     * @param User $user
     * @return string|null
     */
    public function getDecryptedSecret(User $user): ?string
    {
        if (!$user->totp_secret) {
            return null;
        }

        try {
            return Crypt::decryptString($user->totp_secret);
        } catch (\Exception $e) {
            return null;
        }
    }
}
