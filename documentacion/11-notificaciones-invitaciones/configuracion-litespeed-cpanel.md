# Configuración de LiteSpeed para WebSockets (Laravel Reverb) desde cPanel

**Última actualización:** 2026-01-25

## 📋 Resumen

Esta guía explica cómo configurar LiteSpeed desde cPanel para que funcione con Laravel Reverb (WebSockets) y las notificaciones en tiempo real.

---

## 🔧 Opción 1: Configuración desde LiteSpeed WebAdmin (Recomendado)

Si tienes acceso al **LiteSpeed WebAdmin** desde cPanel:

### Paso 1: Acceder a LiteSpeed WebAdmin

1. En cPanel, busca **"LiteSpeed Web Cache Manager"** o **"LiteSpeed WebAdmin"**
2. O accede directamente a: `https://tu-servidor:7080` (puerto por defecto de LiteSpeed WebAdmin)

### Paso 2: Configurar Proxy para WebSockets

1. Ve a **Virtual Hosts** → Selecciona tu dominio (`passvault.example.com`)
2. Ve a la sección **"Script Handler"** o **"Context"**
3. Crea un nuevo contexto con estas configuraciones:

**Tipo:** `proxy`
**URI:** `/app/`
**Backend:** `http://127.0.0.1:8080`
**Proxy Header:** `Host $host`
**Proxy Header:** `X-Real-IP $remote_addr`
**Proxy Header:** `X-Forwarded-For $proxy_add_x_forwarded_for`
**Proxy Header:** `X-Forwarded-Proto $scheme`
**Proxy Header:** `Upgrade $http_upgrade`
**Proxy Header:** `Connection "Upgrade"`

### Paso 3: Guardar y Recargar

1. Guarda los cambios
2. Recarga la configuración de LiteSpeed
3. Reinicia LiteSpeed si es necesario

---

## 🔧 Opción 2: Usar .htaccess (Ya configurado)

El archivo `.htaccess` en la raíz del proyecto ya incluye reglas de proxy para WebSockets. Sin embargo, **LiteSpeed necesita tener habilitado el módulo de proxy**.

### Verificar que el módulo de proxy esté habilitado

Si las reglas de `.htaccess` no funcionan, puede ser que el módulo de proxy no esté habilitado en LiteSpeed. En este caso, usa la **Opción 1** (WebAdmin).

---

## 🔧 Opción 3: Solicitar al Administrador del Servidor

Si no tienes acceso al LiteSpeed WebAdmin, contacta al administrador del servidor y pide que configure:

**Proxy WebSocket para Laravel Reverb:**
- **Ruta:** `/app/`
- **Backend:** `http://127.0.0.1:8080`
- **Headers necesarios:** Upgrade, Connection, Host, X-Real-IP, X-Forwarded-For, X-Forwarded-Proto

---

## ✅ Verificación

### 1. Verificar que Reverb está corriendo

```bash
# Verificar proceso
ps aux | grep reverb

# Verificar puerto
netstat -tulpn | grep 8080
```

### 2. Probar conexión WebSocket

Abre la consola del navegador en `https://passvault.example.com` y verifica que no hay errores de conexión WebSocket.

Deberías ver algo como:
```
Connecting to wss://passvault.example.com/app/...
Connected to Reverb
```

### 3. Probar notificaciones

1. Inicia sesión en la aplicación
2. Abre la consola del navegador
3. Ejecuta el comando de prueba de notificaciones (si está disponible)
4. Verifica que las notificaciones aparecen en tiempo real

---

## 🔧 Configuración del .env

Asegúrate de que tu `.env` tenga estas configuraciones correctas:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=984099
REVERB_APP_KEY=0ilihojpz9kt4m8ftfrv
REVERB_APP_SECRET=hwkm3cr4orqffx9pmm33
REVERB_HOST=passvault.example.com
REVERB_PORT=8080
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**Importante:**
- `REVERB_HOST` debe ser solo el dominio, **sin** `https://` ni comillas
- `REVERB_SCHEME` debe ser `https` en producción
- `REVERB_PORT` es el puerto interno donde corre Reverb (8080)

---

## 🚀 Ejecutar Reverb como Servicio

Reverb debe estar corriendo como un servicio. Ver la guía de producción:

📚 Ver: [`produccion-reverb.md`](produccion-reverb.md) para configurar Reverb como servicio (Supervisor o systemd).

---

## ❌ Problemas Comunes

### WebSockets no conectan

1. **Verificar que Reverb está corriendo:**
   ```bash
   ps aux | grep reverb
   ```

2. **Verificar configuración del proxy en LiteSpeed:**
   - Asegúrate de que el proxy esté configurado correctamente
   - Verifica que el puerto 8080 esté accesible desde localhost

3. **Verificar firewall:**
   - El puerto 8080 debe estar abierto solo para localhost (127.0.0.1)

4. **Verificar logs:**
   ```bash
   tail -f /home/vault/public_html/storage/logs/reverb.log
   ```

### Error 502 Bad Gateway

- Reverb no está corriendo o no está escuchando en el puerto 8080
- Verifica: `netstat -tulpn | grep 8080`

### Error de conexión en el navegador

- Verifica que `REVERB_HOST` en `.env` sea correcto (sin `https://`)
- Verifica que `REVERB_SCHEME` sea `https` en producción
- Verifica la configuración del proxy en LiteSpeed

---

## 📚 Referencias

- [Laravel Reverb Docs](https://laravel.com/docs/12.x/reverb)
- [LiteSpeed WebSocket Support](https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:config:websocket)
- [`produccion-reverb.md`](produccion-reverb.md) - Configuración completa de Reverb en producción
