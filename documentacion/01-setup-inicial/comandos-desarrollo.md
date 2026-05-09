# Comandos de Desarrollo - OCIANN Vault

**Última actualización:** 2026-01-25

## 🚀 Iniciar Servidor de Desarrollo

### Opción Recomendada: `composer dev` (Todo en uno)

```bash
composer dev
```

Este comando inicia **todos los servicios necesarios** en paralelo usando `concurrently`:

| Servicio | Comando | Puerto/URL | Descripción |
|----------|---------|------------|-------------|
| **Laravel** | `php artisan serve` | `http://localhost:8000` | Servidor PHP/Backend |
| **Queue** | `php artisan queue:listen` | - | Procesa trabajos en cola |
| **Reverb** | `php artisan reverb:start` | `http://localhost:8080` | Servidor WebSockets (notificaciones) |
| **Vite** | `npm run dev` | - | Compilación frontend (CSS/JS) con hot reload |

**Ventajas:**
- ✅ Un solo comando para todo
- ✅ Todos los servicios se inician automáticamente
- ✅ Hot reload de frontend
- ✅ WebSockets funcionando desde el inicio

**Nota:** Los logs se pueden ver en `storage/logs/laravel.log`.

---

### Opción Manual: Servicios Separados

Si prefieres controlar cada servicio individualmente, abre múltiples terminales:

**Terminal 1 - Servidor Laravel:**
```bash
php artisan serve
# O en puerto específico:
php artisan serve --port=8001
```

**Terminal 2 - Cola de Trabajos:**
```bash
php artisan queue:listen
```

**Terminal 3 - Servidor Reverb (WebSockets):**
```bash
php artisan reverb:start
```

**Terminal 4 - Vite (Frontend):**
```bash
npm run dev
```

---

## 📦 Compilar Assets (Solo Frontend)

### `npm run dev` vs `composer dev`

| Comando | Qué hace | Cuándo usarlo |
|---------|----------|--------------|
| **`npm run dev`** | Solo compila CSS/JS con Vite (hot reload) | Si solo trabajas en frontend y Laravel ya está corriendo en otra terminal |
| **`composer dev`** | Inicia todo: Laravel + Queue + Reverb + Vite | Desarrollo completo (recomendado) |

**Ejemplo de uso de `npm run dev` solo:**
```bash
# Si ya tienes Laravel corriendo en otra terminal
npm run dev
```

**Ejemplo de uso de `composer dev` (recomendado):**
```bash
# Inicia todo el stack
composer dev
# Esto incluye npm run dev automáticamente
```

### Compilación para Producción

```bash
npm run build
```

Esto compila y optimiza todos los assets (CSS/JS) para producción en `public/build/`.

---

## 🔧 Otros Comandos Útiles

### Limpiar Caché
```bash
php artisan optimize:clear
# O individualmente:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Ver Logs
```bash
# Ver último log
tail -f storage/logs/laravel.log

# O en Windows (PowerShell)
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

### Migraciones
```bash
# Ejecutar migraciones
php artisan migrate

# Rollback última migración
php artisan migrate:rollback

# Ver estado de migraciones
php artisan migrate:status
```

### Seeders
```bash
# Ejecutar seeders
php artisan db:seed

# Ejecutar seeder específico
php artisan db:seed --class=SuperAdminSeeder
```

---

## 📝 Notas

- **Puerto Laravel:** Por defecto `8000`, configurable con `--port`
- **Puerto Reverb:** Por defecto `8080`, configurable en `.env` (`REVERB_PORT`)
- **Hot Reload:** Vite recarga automáticamente los cambios en CSS/JS
- **WebSockets:** Reverb debe estar corriendo para que las notificaciones en tiempo real funcionen

---

## 🔗 Referencias

- [Laravel Documentation](https://laravel.com/docs)
- [Vite Documentation](https://vitejs.dev/)
- [Laravel Reverb](https://laravel.com/docs/12.x/reverb)
