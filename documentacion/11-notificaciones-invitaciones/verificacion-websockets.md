# Verificación y Configuración de WebSockets para Notificaciones

**Última actualización:** 2026-01-25

## ✅ Laravel Reverb - Disponible para Laravel 12

Laravel Reverb es la solución **oficial y recomendada** de Laravel para WebSockets. Está disponible para Laravel 12.x y es completamente self-hosted (sin dependencias externas).

---

## 🔍 Cómo Verificar Disponibilidad

### 1. Verificar versión de Laravel

```bash
php artisan --version
```

Debería mostrar `Laravel Framework 12.x.x` (tu proyecto usa `^12.0` según `composer.json`).

### 2. Verificar si Reverb ya está instalado

```bash
php artisan list | grep reverb
```

Si no aparece nada, Reverb no está instalado aún.

### 3. Verificar en Packagist

- URL: https://packagist.org/packages/laravel/reverb
- Compatible con: Laravel 10.47+, 11.0+, **12.0+** ✅
- Instalaciones: +6.9 millones

---

## 📦 Instalación de Laravel Reverb

### Paso 1: Instalar Reverb

```bash
php artisan reverb:install
```

Este comando:
- Instala el paquete `laravel/reverb` vía Composer
- Publica archivos de configuración
- Crea archivos necesarios

### Paso 2: Configurar `.env`

Después de la instalación, se agregarán estas variables a tu `.env`:

```env
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Para producción (HTTPS)
# REVERB_SCHEME=https
# REVERB_HOST=tu-dominio.com
# REVERB_PORT=443
```

**Importante:** Las claves se generan automáticamente. **NO las compartas** ni las subas a Git.

### Paso 3: Configurar Broadcasting

En `config/broadcasting.php`, el driver debe ser `reverb`:

```php
'default' => env('BROADCAST_DRIVER', 'reverb'),
```

Y la conexión `reverb`:

```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
    ],
],
```

### Paso 4: Iniciar el servidor Reverb

**Desarrollo (local):**
```bash
php artisan reverb:start
```

**Producción (como servicio):**
- Usar Supervisor o systemd
- O ejecutar en background: `php artisan reverb:start --host=0.0.0.0 --port=8080`

---

## 🧪 Verificar que Funciona

### 1. Probar el servidor

```bash
# Terminal 1: Iniciar Reverb
php artisan reverb:start

# Debería mostrar:
# Reverb server started on http://localhost:8080
```

### 2. Probar Broadcasting (desde otro terminal)

```bash
php artisan tinker
```

```php
// En Tinker
broadcast(new \App\Events\TestEvent());
```

Si no hay errores, el servidor está funcionando.

### 3. Verificar en el navegador

Abrir la consola del navegador y verificar que no hay errores de conexión WebSocket.

---

## 🔄 Alternativas (si Reverb no funciona)

### Opción 1: Laravel Echo Server (self-hosted)

**Instalación:**
```bash
npm install -g laravel-echo-server
```

**Configuración:**
```bash
laravel-echo-server init
```

**Iniciar:**
```bash
laravel-echo-server start
```

**Ventajas:**
- Self-hosted
- Funciona con Laravel Broadcasting
- Requiere Node.js

**Desventajas:**
- No es oficial de Laravel
- Requiere Node.js adicional

### Opción 2: Pusher (servicio externo)

**Instalación:**
```bash
composer require pusher/pusher-php-server
```

**Configuración en `.env`:**
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

**Ventajas:**
- Muy fácil de configurar
- Escalable automáticamente
- Sin servidor propio

**Desventajas:**
- Requiere cuenta en Pusher (gratis hasta cierto límite)
- Dependencia externa
- Costos en producción a gran escala

### Opción 3: Ably (servicio externo)

Similar a Pusher, pero con mejor plan gratuito.

---

## 📋 Checklist de Verificación

Antes de empezar la Fase 1 del plan:

- [ ] Laravel 12.x instalado (`php artisan --version`)
- [ ] Reverb instalado (`php artisan reverb:install`)
- [ ] Variables `.env` configuradas (REVERB_*)
- [ ] `config/broadcasting.php` con driver `reverb`
- [ ] Servidor Reverb inicia sin errores (`php artisan reverb:start`)
- [ ] Frontend: Laravel Echo instalado (`npm install laravel-echo`)
- [ ] Frontend: Echo configurado en `resources/js/app.js`

---

## 🚀 Siguiente Paso

Una vez verificado que Reverb funciona (o elegida una alternativa), proceder con la **Fase 1** del plan de implementación.

Ver: [`plan-implementacion.md`](plan-implementacion.md#-fase-1-sistema-base-de-notificaciones)

---

## 📚 Referencias

- [Laravel Reverb Docs (Laravel 12)](https://laravel.com/docs/12.x/reverb)
- [Laravel Broadcasting Docs](https://laravel.com/docs/12.x/broadcasting)
- [Packagist: laravel/reverb](https://packagist.org/packages/laravel/reverb)
- [GitHub: laravel/reverb](https://github.com/laravel/reverb)
