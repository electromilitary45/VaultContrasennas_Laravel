<?php

declare(strict_types=1);

namespace App\Services;

use OTPHP\TOTP;
use ParagonIE\ConstantTime\Encoding;

/**
 * Servicio para gestión de TOTP (Time-based One-Time Password)
 * 
 * Proporciona funcionalidades para generar y validar códigos TOTP
 * compatibles con Google Authenticator, Microsoft Authenticator, etc.
 */
class TotpService
{
    /**
     * Generar un código TOTP a partir de un secreto.
     *
     * @param string $secret Secreto TOTP (puede estar en formato base32 o raw)
     * @param int|null $timestamp Timestamp específico (opcional, por defecto tiempo actual)
     * @return string Código de 6 dígitos
     */
    public function generateCode(string $secret, ?int $timestamp = null): string
    {
        try {
            // Normalizar el secreto (eliminar espacios, convertir a mayúsculas)
            $normalizedSecret = $this->normalizeSecret($secret);
            
            // Crear instancia TOTP desde secreto Base32
            $totp = TOTP::createFromSecret($normalizedSecret);
            
            // Generar código
            if ($timestamp !== null) {
                return $totp->at($timestamp);
            }
            
            return $totp->now();
        } catch (\Exception $e) {
            throw new \RuntimeException('Error al generar código TOTP: ' . $e->getMessage());
        }
    }

    /**
     * Validar un código TOTP.
     *
     * @param string $secret Secreto TOTP
     * @param string $code Código a validar
     * @param int $window Ventana de tiempo en periodos (por defecto 1 = ±30 segundos)
     * @return bool True si el código es válido
     */
    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        try {
            $normalizedSecret = $this->normalizeSecret($secret);
            $totp = TOTP::createFromSecret($normalizedSecret);
            
            return $totp->verify($code, null, $window);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generar URI para QR code (otpauth://).
     *
     * @param string $secret Secreto TOTP
     * @param string $label Etiqueta para el QR (ej: "Gmail:usuario@gmail.com")
     * @param string $issuer Emisor (ej: "Gmail")
     * @return string URI para generar QR
     */
    public function generateUri(string $secret, string $label, string $issuer = ''): string
    {
        try {
            $normalizedSecret = $this->normalizeSecret($secret);
            $totp = TOTP::createFromSecret($normalizedSecret);
            $totp->setLabel($label);
            
            if (!empty($issuer)) {
                $totp->setIssuer($issuer);
            }
            
            return $totp->getProvisioningUri();
        } catch (\Exception $e) {
            throw new \RuntimeException('Error al generar URI TOTP: ' . $e->getMessage());
        }
    }

    /**
     * Generar un secreto TOTP aleatorio.
     *
     * @param int $length Longitud del secreto en bytes (por defecto 20 bytes = 160 bits)
     * @return string Secreto en formato base32
     */
    public function generateSecret(int $length = 20): string
    {
        $randomBytes = random_bytes($length);
        return Encoding::base32EncodeUpper($randomBytes);
    }

    /**
     * Normalizar un secreto TOTP.
     * 
     * Elimina espacios, convierte a mayúsculas y valida formato.
     *
     * @param string $secret
     * @return string
     */
    private function normalizeSecret(string $secret): string
    {
        // Eliminar espacios y convertir a mayúsculas
        $normalized = strtoupper(str_replace(' ', '', trim($secret)));
        
        // Si el secreto no está en base32, intentar convertirlo
        // (algunos servicios proporcionan el secreto en formato hexadecimal o raw)
        if (!preg_match('/^[A-Z2-7]+$/', $normalized)) {
            // Si parece hexadecimal, convertir a base32
            if (preg_match('/^[0-9A-Fa-f]+$/', $secret)) {
                $hex = str_replace(' ', '', $secret);
                $bytes = hex2bin($hex);
                $normalized = Encoding::base32EncodeUpper($bytes);
            } else {
                // Si es texto plano, codificar directamente
                $normalized = Encoding::base32EncodeUpper($secret);
            }
        }
        
        return $normalized;
    }

    /**
     * Obtener el tiempo restante hasta el próximo código (en segundos).
     *
     * @return int Segundos restantes (0-29)
     */
    public function getRemainingTime(): int
    {
        return 30 - (time() % 30);
    }
}
