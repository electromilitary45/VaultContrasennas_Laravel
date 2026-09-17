# Autenticación - PassVault

Documentación del sistema de autenticación y seguridad de acceso.

## 📋 Descripción

El sistema de autenticación de PassVault está basado en Laravel Breeze y proporciona autenticación segura con soporte para autenticación de dos factores (2FA) a nivel de usuario.

## ✨ Funcionalidades Implementadas

### Autenticación Básica
- **Laravel Breeze**: Sistema de autenticación completo
- **Registro de usuarios**: Solo con **código de invitación** (no hay registro abierto). El org admin genera códigos; el usuario se registra en `/register` con código + nombre + email + contraseña. Ver `documentacion/09-saas-multi-tenant/`.
- **Login/Logout**: Gestión de sesiones seguras
- **Recuperación de contraseña**: Reset de contraseña por email
- **Verificación de email**: Confirmación de dirección de correo

### Autenticación de Dos Factores (2FA)
- **TOTP a nivel de usuario**: 2FA opcional por usuario
- **Integración en login**: Verificación obligatoria si está activado
- **Generación de QR codes**: Compatible con Google Authenticator, Microsoft Authenticator
- **Códigos de respaldo**: Generación de códigos de emergencia
- **Gestión desde perfil**: Activación/desactivación desde configuración de perfil
- **Almacenamiento seguro**: Secretos TOTP cifrados con Laravel Crypt

### Perfil de Usuario
- **Avatar**: Subida y gestión de imagen de perfil
- **Información personal**: Nombre y email editables
- **Configuración 2FA**: Panel dedicado para gestión de autenticación de dos factores
- **Indicadores visuales**: Badges que muestran estado de verificación y 2FA

## 🏗️ Arquitectura

### AuthController
Controlador unificado que maneja:
- Login/Logout
- **Registro con código de invitación** (`RegisterWithInvitationCodeRequest`, `AuthService::registerWithInvitationCode`)
- Recuperación de contraseña
- Verificación de email
- **Flujo de 2FA en login**: Verificación de código TOTP durante autenticación

### TwoFactorController
Controlador dedicado para gestión de 2FA:
- Configuración inicial
- Activación/Desactivación
- Regeneración de códigos de respaldo
- Visualización de QR code

### TwoFactorService
Servicio que encapsula la lógica de negocio:
- Generación de secretos TOTP
- Generación de QR codes
- Validación de códigos
- Gestión de códigos de respaldo
- Cifrado de secretos

## 🔄 Flujo de Autenticación

### Login Normal (sin 2FA)
1. Usuario ingresa email/password
2. Validación de credenciales
3. Regeneración de sesión
4. Redirección al dashboard

### Login con 2FA
1. Usuario ingresa email/password
2. Validación de credenciales
3. **Si 2FA está activado:**
   - Preservar token CSRF antes del logout
   - Guardar intento de login en sesión (`login.id`, `login.remember`, `login.csrf_token`)
   - Cerrar sesión temporalmente (mantiene la sesión y token CSRF)
   - Redirigir a página de verificación 2FA
4. Usuario ingresa código TOTP (6 dígitos)
5. Validación del código
6. Si es válido: completar login, limpiar datos temporales, regenerar sesión y redirigir al dashboard
7. Si es inválido: mostrar error y permitir reintento
8. Si hay error 419 (CSRF token expired): regenerar token y redirigir con mensaje de error

### Activación de 2FA
1. Usuario accede a configuración de 2FA desde perfil
2. Sistema genera secreto temporal y muestra QR code
3. Usuario escanea QR con app de autenticación
4. Usuario ingresa código de verificación
5. Si es válido: activar 2FA, guardar secreto cifrado, generar códigos de respaldo
6. Mostrar códigos de respaldo para guardar

## 🔒 Seguridad

### Medidas Implementadas
- ✅ Contraseñas hasheadas (bcrypt)
- ✅ Sesiones seguras con regeneración
- ✅ Tokens CSRF en todos los formularios
- ✅ Preservación de token CSRF durante flujo de 2FA
- ✅ Manejo robusto de errores 419 (CSRF token expired)
- ✅ Secretos TOTP cifrados en base de datos
- ✅ Códigos de respaldo hasheados
- ✅ Verificación de código antes de activar 2FA
- ✅ Requerimiento de contraseña para desactivar 2FA

### Almacenamiento
- `totp_secret`: Secreto TOTP cifrado con Laravel Crypt
- `totp_enabled`: Flag booleano de activación
- `totp_backup_codes`: Array JSON de códigos hasheados
- `totp_verified_at`: Timestamp de última verificación

## 🔧 Implementación Técnica

### Preservación de Token CSRF
El flujo de 2FA preserva el token CSRF para evitar errores 419:
1. **Antes del logout**: Se guarda el token CSRF en sesión (`login.csrf_token`)
2. **Durante logout**: Se cierra la sesión de autenticación pero se mantiene la sesión HTTP
3. **En formulario 2FA**: Se sincroniza el token preservado si cambió después del logout
4. **En verificación**: Se usa el token preservado para validar el formulario
5. **Después del login**: Se limpian los datos temporales y se regenera la sesión completa

### Manejo de Errores 419
Si ocurre un error 419 (CSRF token expired):
- Se regenera el token CSRF automáticamente
- Se redirige al formulario de 2FA con mensaje de error
- El usuario puede reintentar ingresando el código nuevamente

## 📝 Archivos Principales

- `app/Http/Controllers/AuthController.php` - Controlador de autenticación (preservación de token CSRF)
- `app/Http/Controllers/TwoFactorController.php` - Controlador de 2FA
- `app/Services/TwoFactorService.php` - Servicio de 2FA
- `app/Models/User.php` - Modelo de usuario con métodos helper
- `resources/views/auth/two-factor-login.blade.php` - Vista de verificación 2FA en login (JavaScript simplificado)
- `resources/views/profile/two-factor.blade.php` - Vista de configuración 2FA
- `bootstrap/app.php` - Manejo de errores 419 para ruta de 2FA

## 🔗 Referencias

- [Laravel Breeze](https://laravel.com/docs/breeze)
- [Laravel Authentication](https://laravel.com/docs/authentication)
- [TOTP RFC 6238](https://tools.ietf.org/html/rfc6238)
- [`documentacion/09-saas-multi-tenant/`](../09-saas-multi-tenant/) — Registro con código de invitación, organizaciones, org admin
