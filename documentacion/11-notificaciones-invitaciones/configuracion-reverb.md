# Configuración de Laravel Reverb - Estado Actual

**Última actualización:** 2026-01-25

## ✅ Estado de Instalación

- [x] **Paquete instalado:** `laravel/reverb` v1.7.0 instalado vía Composer
- [x] **Configuración publicada:** `config/broadcasting.php` con configuración de Reverb
- [x] **Rutas de canales:** `routes/channels.php` creado
- [x] **Default driver:** Configurado como `reverb` en `config/broadcasting.php`
- [x] **Variables `.env`:** Configuradas (REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET, etc.)
- [x] **`.env.example`:** Actualizado con variables de Reverb
- [x] **Comandos disponibles:** `reverb:install`, `reverb:start`, `reverb:restart`
- [x] **Inicio automático:** Agregado al script `composer dev` (se inicia automáticamente en desarrollo)

## ⚙️ Configuración Actual

### `config/broadcasting.php`

```php
'default' => env('BROADCAST_CONNECTION', 'reverb'),

'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST', 'localhost'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
        'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
    ],
],
```

### `routes/channels.php`

Canal privado básico configurado:
```php
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

## 📝 Variables de Entorno

**✅ Configuradas en `.env`:**

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=984099
REVERB_APP_KEY=0ilihojpz9kt4m8ftfrv
REVERB_APP_SECRET=hwkm3cr4orqffx9pmm33
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Variables para Vite (frontend)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**✅ También agregadas a `.env.example`** (sin valores sensibles, solo estructura).

## ✅ Configuración Completa

Todas las claves y variables están configuradas. No es necesario generar nuevas claves.

## 🚀 Iniciar el Servidor Reverb

### Desarrollo Local (Automático) ✅

**Reverb se inicia automáticamente** cuando ejecutas:

```bash
composer dev
```

Este comando inicia todos los servicios necesarios en paralelo:
- ✅ Servidor Laravel (`php artisan serve`)
- ✅ Cola de trabajos (`php artisan queue:listen`)
- ✅ Logs en tiempo real (`php artisan pail`)
- ✅ **Servidor Reverb** (`php artisan reverb:start`) ← **Nuevo**
- ✅ Vite dev server (`npm run dev`)

**No necesitas iniciar Reverb manualmente** en desarrollo. Solo ejecuta `composer dev` y todo funcionará.

### Inicio Manual (Opcional)

Si necesitas iniciar solo Reverb manualmente:

```bash
php artisan reverb:start
```

**Desarrollo local:**
- Host: `localhost`
- Puerto: `8080`
- Esquema: `http`

### Producción

En producción, Reverb debe ejecutarse como un servicio que se reinicia automáticamente.

**📚 Guía completa:** Ver [`produccion-reverb.md`](produccion-reverb.md) para configuración detallada de producción (Supervisor, systemd, Nginx/Apache, seguridad, monitoreo).

#### Opción 1: Supervisor (Linux - Recomendado)

Crear archivo `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
process_name=%(program_name)s
command=php /ruta/a/tu/proyecto/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/ruta/a/tu/proyecto/storage/logs/reverb.log
stopwaitsecs=3600
```

Luego:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

#### Opción 2: systemd (Linux)

Crear archivo `/etc/systemd/system/reverb.service`:

```ini
[Unit]
Description=Laravel Reverb Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/ruta/a/tu/proyecto
ExecStart=/usr/bin/php /ruta/a/tu/proyecto/artisan reverb:start
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Luego:
```bash
sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl start reverb
```

#### Opción 3: Windows Service

Usar herramientas como **NSSM** (Non-Sucking Service Manager) o **WinSW** para crear un servicio de Windows.

**Producción:**
- Host: tu dominio
- Puerto: `443` (HTTPS) o `8080` (HTTP)
- Esquema: `https` o `http`

## ✅ Verificación

1. **Verificar comandos disponibles:**
   ```bash
   php artisan list | Select-String -Pattern "reverb"
   ```
   Debería mostrar: `reverb:install`, `reverb:start`, etc.

2. **Iniciar servidor (desarrollo):**
   ```bash
   composer dev
   ```
   Reverb se iniciará automáticamente junto con los demás servicios.
   
   O manualmente:
   ```bash
   php artisan reverb:start
   ```
   Debería mostrar: `Reverb server started on http://localhost:8080`

3. **Verificar en navegador:**
   - Abrir consola del navegador
   - No debería haber errores de conexión WebSocket

## 📚 Próximos Pasos

Una vez Reverb esté funcionando, proceder con la **Fase 1** del plan de implementación:

Ver: [`plan-implementacion.md`](plan-implementacion.md#-fase-1-sistema-base-de-notificaciones)

---

## 🔗 Referencias

- [Laravel Reverb Docs](https://laravel.com/docs/12.x/reverb)
- [Laravel Broadcasting Docs](https://laravel.com/docs/12.x/broadcasting)
