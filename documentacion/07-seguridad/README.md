# Seguridad - OCIANN Vault

Documentación del sistema de seguridad, cifrado y protección de datos.

## 📋 Descripción

El sistema de seguridad de OCIANN Vault implementa múltiples capas de protección para garantizar la confidencialidad e integridad de los secretos almacenados.

## ✨ Funcionalidades Implementadas

### Cifrado de Secretos
- **Algoritmo**: AES-256-CBC
- **Integridad**: HMAC-SHA256
- **Servicio**: `CryptoService`
- Todos los secretos se almacenan cifrados en la base de datos
- Cifrado/descifrado transparente para el usuario

### Autorización Granular
- **Policies de Laravel**: `VaultItemPolicy`
- **Permisos**: `view`, `edit`, `admin`, `owner`
- Verificación de acceso en cada operación
- Soporte para ownership, shares directos y shares por grupo

### Protección de Formularios
- **CSRF**: Todos los formularios protegidos
- **Validación**: Form Requests con reglas específicas
- **Sanitización**: Inputs sanitizados antes de procesar

### Autenticación
- **Laravel Breeze**: Sistema de autenticación robusto
- **Sesiones seguras**: Configuración segura de sesiones
- **TOTP/2FA a nivel de usuario**: Autenticación de dos factores opcional por usuario
- **2FA en login**: Verificación obligatoria durante login si está activado
- **Códigos de respaldo**: Sistema de códigos de emergencia
- **Secretos cifrados**: Secretos TOTP almacenados cifrados en base de datos

## 🏗️ Arquitectura

### CryptoService

```php
class CryptoService
{
    public function encrypt(array $data): array
    {
        // Serializa datos a JSON
        // Cifra con Laravel Crypt (AES-256-CBC)
        // Genera HMAC para verificación
        // Retorna ciphertext + hmac
    }
    
    public function decrypt(array $encryptedData): array
    {
        // Verifica HMAC
        // Descifra con Laravel Crypt
        // Retorna datos originales
    }
}
```

### VaultItemPolicy

```php
class VaultItemPolicy
{
    public function view(User $user, VaultItem $item): bool
    {
        // Verifica ownership
        // Verifica share directo
        // Verifica share por grupo
        // Retorna true si tiene acceso
    }
    
    public function getPermission(User $user, VaultItem $item): string
    {
        // Determina permiso efectivo
        // Retorna: owner, admin, edit, view
    }
}
```

## 🔒 Medidas de Seguridad

### Almacenamiento
- ✅ Secretos cifrados en base de datos
- ✅ Verificación de integridad con HMAC
- ✅ Clave de cifrado en `APP_KEY` (no en base de datos)
- ✅ Soft delete para items (no eliminación física inmediata)

### Acceso
- ✅ Autorización en cada operación
- ✅ Verificación de permisos granular
- ✅ No exposición de secretos en logs
- ✅ Protección CSRF en formularios

### Autenticación
- ✅ Contraseñas hasheadas (bcrypt)
- ✅ Sesiones seguras con regeneración
- ✅ TOTP/2FA opcional a nivel de usuario
- ✅ 2FA integrado en flujo de login
- ✅ Secretos TOTP cifrados
- ✅ Códigos de respaldo hasheados
- ✅ Middleware de autenticación

## 📝 Mejores Prácticas Implementadas

1. **Cifrado en reposo**: Todos los secretos cifrados antes de guardar
2. **Principio de menor privilegio**: Permisos mínimos necesarios
3. **Verificación de integridad**: HMAC en cada operación
4. **No logging de secretos**: No se registran datos sensibles
5. **Validación estricta**: Form Requests con reglas específicas
6. **Autorización granular**: Policies para cada operación

## ⚠️ Consideraciones de Seguridad

### Implementado
- Cifrado de secretos
- Autorización granular
- Protección CSRF
- Validación robusta

### Pendiente
- Rate limiting en endpoints sensibles
- Logging de intentos de acceso fallidos
- Auditoría completa de accesos
- Política de contraseñas fuerte
- Sesiones concurrentes
- Encriptación de backups

## 🔐 Configuración

### Variables de Entorno

```env
APP_KEY=base64:...  # Clave de cifrado (generada automáticamente)
APP_ENV=production   # Entorno de producción
APP_DEBUG=false     # Desactivar debug en producción
```

### Configuración de Sesión

```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'strict',
```

## 📚 Referencias

- [Laravel Encryption](https://laravel.com/docs/encryption)
- [Laravel Policies](https://laravel.com/docs/authorization#creating-policies)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- Los **reportes de seguridad** del panel de administración (contraseñas débiles, reutilizadas, usuarios inactivos, intentos fallidos) son solo para **platform super admin**. Ver [`documentacion/08-administracion/`](../08-administracion/).
