<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;

/**
 * Servicio para cifrado y descifrado de secretos del vault.
 * 
 * Utiliza Laravel Encryption (AES-256-CBC con HMAC) para cifrar/descifrar
 * los datos sensibles de los items del vault.
 * 
 * Características:
 * - Cifrado AES-256-CBC con autenticación HMAC
 * - Generación automática de IV (vector de inicialización)
 * - Soporte para versiones de cifrado (migración futura)
 * - Manejo seguro de errores de descifrado
 */
class CryptoService
{
    /**
     * Versión actual del algoritmo de cifrado.
     */
    private const CRYPTO_VERSION = 'v1';

    /**
     * Cifrar datos sensibles.
     *
     * @param array|string $data Datos a cifrar (array será convertido a JSON)
     * @return array{ ciphertext: string, iv: string, salt: string|null, crypto_version: string }
     * @throws EncryptException Si falla el cifrado
     */
    public function encrypt(array|string $data): array
    {
        try {
            // Si el array está vacío, usar un objeto JSON vacío
            if (is_array($data) && empty($data)) {
                $plaintext = '{}';
            } else {
                // Convertir array a JSON si es necesario
                $plaintext = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
            }
            
            // Asegurar que plaintext no esté vacío
            if (empty($plaintext)) {
                $plaintext = '{}';
            }
            
            // Cifrar usando Laravel Encryption (AES-256-CBC con HMAC)
            // Laravel maneja automáticamente el IV y la autenticación
            $ciphertext = Crypt::encryptString($plaintext);
            
            // Generar IV aleatorio (32 bytes en hex = 64 caracteres)
            // Nota: Laravel Encryption ya incluye el IV en el payload cifrado,
            // pero lo guardamos por separado para compatibilidad futura y auditoría
            $iv = bin2hex(random_bytes(16));
            
            // Generar salt aleatorio (opcional, para futuras mejoras de seguridad)
            $salt = bin2hex(random_bytes(16));
            
            return [
                'ciphertext' => $ciphertext,
                'iv' => $iv,
                'salt' => $salt,
                'crypto_version' => self::CRYPTO_VERSION,
            ];
        } catch (\Exception $e) {
            throw new EncryptException('Error al cifrar datos: ' . $e->getMessage());
        }
    }

    /**
     * Descifrar datos cifrados.
     *
     * @param string $ciphertext Texto cifrado
     * @param string|null $cryptoVersion Versión del cifrado (opcional, para compatibilidad futura)
     * @return array|string Datos descifrados (array si era JSON, string si era texto plano)
     * @throws DecryptException Si falla el descifrado o los datos están corruptos
     */
    public function decrypt(string $ciphertext, ?string $cryptoVersion = null): array|string
    {
        try {
            // Descifrar usando Laravel Encryption
            $plaintext = Crypt::decryptString($ciphertext);
            
            // Intentar decodificar como JSON, si falla retornar como string
            $decoded = json_decode($plaintext, true);
            
            // Si json_decode retorna null y el string no es 'null', es texto plano
            if ($decoded === null && $plaintext !== 'null' && !is_numeric($plaintext)) {
                return $plaintext;
            }
            
            // Si es un array válido, retornarlo
            if (is_array($decoded)) {
                return $decoded;
            }
            
            // Si es un valor JSON válido pero no array (número, boolean, null), retornar como string
            return $plaintext;
        } catch (DecryptException $e) {
            // Re-lanzar excepciones de descifrado de Laravel
            throw $e;
        } catch (\Exception $e) {
            throw new DecryptException('Error al descifrar datos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener la versión actual del algoritmo de cifrado.
     *
     * @return string
     */
    public function getCryptoVersion(): string
    {
        return self::CRYPTO_VERSION;
    }

    /**
     * Verificar si un ciphertext está cifrado con la versión actual.
     *
     * @param string $cryptoVersion Versión a verificar
     * @return bool
     */
    public function isCurrentVersion(string $cryptoVersion): bool
    {
        return $cryptoVersion === self::CRYPTO_VERSION;
    }

    /**
     * Migrar datos de una versión antigua a la versión actual.
     * 
     * Por ahora solo soporta v1, pero este método permite
     * agregar migraciones futuras cuando se actualice el algoritmo.
     *
     * @param string $ciphertext Texto cifrado antiguo
     * @param string $oldVersion Versión antigua
     * @return array{ ciphertext: string, iv: string, salt: string|null, crypto_version: string }
     * @throws \Exception Si la versión no es soportada
     */
    public function migrate(string $ciphertext, string $oldVersion): array
    {
        // Por ahora solo soportamos v1
        if ($oldVersion === self::CRYPTO_VERSION) {
            throw new \Exception('Los datos ya están en la versión actual.');
        }
        
        // Descifrar con la versión antigua
        $plaintext = $this->decrypt($ciphertext, $oldVersion);
        
        // Cifrar con la versión actual
        return $this->encrypt($plaintext);
    }
}
