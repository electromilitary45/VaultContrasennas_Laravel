# Configuración de Reverb en Producción - PassVault

**Última actualización:** 2026-01-25

## 📋 Resumen

Esta guía explica cómo configurar Laravel Reverb (WebSockets) en un servidor de producción para que funcione de forma estable y segura.

**⚠️ Importante:** En producción, siempre usa `npm run build` (NO `npm run dev`). El comando `dev` es solo para desarrollo con hot reload.

---

## 🔧 1. Configuración de Variables de Entorno

### Actualizar `.env` en el servidor

```env
# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb Configuration (Producción)
REVERB_APP_ID=tu-app-id-produccion
REVERB_APP_KEY=tu-app-key-produccion
REVERB_APP_SECRET=tu-app-secret-produccion
REVERB_HOST=tu-dominio.com
REVERB_PORT=8080
REVERB_SCHEME=https

# Variables para Vite (frontend)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**Importante:**
- Usa valores diferentes a desarrollo (genera nuevas claves)
- `REVERB_HOST` debe ser tu dominio real (ej: `passvault.example.com`)
- `REVERB_SCHEME` debe ser `https` en producción
- `REVERB_PORT` puede ser `8080` (interno) o `443` si usas HTTPS directo

---

## 🚀 2. Ejecutar Reverb como Servicio

Reverb debe ejecutarse como un servicio que se reinicia automáticamente si falla.

### Opción 1: Supervisor (Linux - Recomendado)

Supervisor es ideal para mantener procesos PHP corriendo.

#### Instalar Supervisor

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor
```

#### Crear Configuración

Crear archivo `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
process_name=%(program_name)s
command=php /home/vault/public_html/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/home/vault/public_html
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/home/vault/public_html/storage/logs/reverb.log
stopwaitsecs=3600
```

**Ajustar:**
- `command`: Ruta completa a `artisan` y ruta del proyecto
- `directory`: Ruta del proyecto
- `user`: Usuario del servidor web (normalmente `www-data` o `apache`)
- `stdout_logfile`: Ruta donde guardar logs

#### Activar el Servicio

```bash
# Recargar configuración
sudo supervisorctl reread
sudo supervisorctl update

# Iniciar Reverb
sudo supervisorctl start reverb

# Verificar estado
sudo supervisorctl status reverb

# Ver logs en tiempo real
sudo supervisorctl tail -f reverb
```

#### Comandos Útiles

```bash
# Reiniciar Reverb
sudo supervisorctl restart reverb

# Detener Reverb
sudo supervisorctl stop reverb

# Ver logs
tail -f /home/vault/public_html/storage/logs/reverb.log
```

---

### Opción 2: systemd (Linux)

Alternativa moderna a Supervisor, usa systemd.

#### Crear Servicio

Crear archivo `/etc/systemd/system/reverb.service`:

```ini
[Unit]
Description=Laravel Reverb Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/home/vault/public_html
ExecStart=/usr/bin/php /home/vault/public_html/artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=3
StandardOutput=append:/home/vault/public_html/storage/logs/reverb.log
StandardError=append:/home/vault/public_html/storage/logs/reverb-error.log

[Install]
WantedBy=multi-user.target
```

**Ajustar:**
- `User`/`Group`: Usuario del servidor web
- `WorkingDirectory`: Ruta del proyecto
- `ExecStart`: Ruta completa a `artisan`

#### Activar el Servicio

```bash
# Recargar systemd
sudo systemctl daemon-reload

# Habilitar inicio automático
sudo systemctl enable reverb

# Iniciar servicio
sudo systemctl start reverb

# Verificar estado
sudo systemctl status reverb

# Ver logs
sudo journalctl -u reverb -f
```

#### Comandos Útiles

```bash
# Reiniciar
sudo systemctl restart reverb

# Detener
sudo systemctl stop reverb

# Ver logs en tiempo real
sudo journalctl -u reverb -f
```

---

### Opción 3: Windows Service

Si tu servidor es Windows, usa **NSSM** (Non-Sucking Service Manager).

#### Instalar NSSM

1. Descargar desde: https://nssm.cc/download
2. Extraer y copiar `nssm.exe` a una carpeta en PATH (ej: `C:\Windows\System32`)

#### Crear Servicio

```bash
# En PowerShell como Administrador
nssm install Reverb "C:\php\php.exe" "C:\ruta\a\tu\proyecto\artisan reverb:start --host=0.0.0.0 --port=8080"

# Configurar directorio de trabajo
nssm set Reverb AppDirectory "C:\ruta\a\tu\proyecto"

# Iniciar servicio
nssm start Reverb
```

---

## 🌐 3. Configuración del Servidor Web (Proxy WebSockets)

El servidor web (Nginx/Apache) debe hacer proxy de las conexiones WebSocket a Reverb.

### Nginx (Recomendado)

#### Configuración del Virtual Host

Agregar al archivo de configuración de tu sitio (ej: `/etc/nginx/sites-available/vault`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name passvault.example.com;
    
    # Redirigir HTTP a HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name passvault.example.com;
    
    root /home/vault/public_html/public;
    index index.php;
    
    # SSL Configuration (ajustar rutas)
    ssl_certificate /etc/letsencrypt/live/passvault.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/passvault.example.com/privkey.pem;
    
    # Proxy WebSocket para Reverb
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
    
    # Laravel Application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Importante:**
- La ruta `/app/` es el prefijo por defecto de Reverb
- El proxy apunta a `http://127.0.0.1:8080` (puerto interno de Reverb)
- `proxy_read_timeout 86400` permite conexiones WebSocket de larga duración

#### Recargar Nginx

```bash
# Verificar configuración
sudo nginx -t

# Recargar
sudo systemctl reload nginx
```

---

### Apache

Si usas Apache, necesitas habilitar módulos de proxy.

#### Habilitar Módulos

```bash
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Configuración del Virtual Host

Agregar al archivo de configuración (ej: `/etc/apache2/sites-available/vault.conf`):

```apache
<VirtualHost *:443>
    ServerName passvault.example.com
    DocumentRoot /home/vault/public_html/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/passvault.example.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/passvault.example.com/privkey.pem
    
    # Proxy WebSocket para Reverb
    <Location "/app/">
        ProxyPass ws://127.0.0.1:8080/app/
        ProxyPassReverse ws://127.0.0.1:8080/app/
        ProxyPreserveHost On
    </Location>
    
    # Laravel Application
    <Directory /home/vault/public_html/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # PHP-FPM
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
```

#### Recargar Apache

```bash
# Verificar configuración
sudo apache2ctl configtest

# Recargar
sudo systemctl reload apache2
```

---

## 📦 4. Actualizar Script de Deploy

Actualizar `.github/workflows/deploy.yml` o tu script de deploy para incluir Reverb:

```yaml
script: |
  cd /home/vault/public_html
  git pull origin main
  composer install --no-dev --optimize-autoloader
  npm ci
  npm run build  # ⚠️ IMPORTANTE: En producción usar 'build', NO 'dev'
  php artisan migrate --force
  php artisan optimize:clear
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  # Reiniciar Reverb después del deploy
  sudo supervisorctl restart reverb || sudo systemctl restart reverb || true
```

**⚠️ Importante:**
- **NO usar `npm run dev`** en producción (eso es para desarrollo con hot reload)
- **Usar `npm run build`** que compila y optimiza los assets para producción
- Los assets compilados se guardan en `public/build/`

---

## 🔒 5. Consideraciones de Seguridad

### Firewall

Asegúrate de que el puerto 8080 solo sea accesible desde localhost:

```bash
# UFW (Ubuntu)
sudo ufw allow from 127.0.0.1 to any port 8080

# firewalld (CentOS/RHEL)
sudo firewall-cmd --permanent --add-rich-rule='rule family="ipv4" source address="127.0.0.1" port port="8080" protocol="tcp" accept'
sudo firewall-cmd --reload
```

### Variables de Entorno

- **Nunca** subas `.env` a Git
- Usa valores diferentes en producción vs desarrollo
- Genera claves seguras (32+ caracteres aleatorios)

### Logs

Los logs de Reverb pueden contener información sensible. Asegúrate de:
- Rotar logs regularmente
- Restringir permisos: `chmod 600 storage/logs/reverb.log`
- No exponer logs públicamente

---

## 📊 6. Monitoreo y Troubleshooting

### Verificar que Reverb está Corriendo

```bash
# Supervisor
sudo supervisorctl status reverb

# systemd
sudo systemctl status reverb

# Ver procesos
ps aux | grep reverb
```

### Ver Logs

```bash
# Logs de Reverb
tail -f /home/vault/public_html/storage/logs/reverb.log

# Logs de Supervisor
sudo supervisorctl tail -f reverb

# Logs de systemd
sudo journalctl -u reverb -f
```

### Probar Conexión WebSocket

Desde el navegador, abrir consola y verificar que no hay errores de conexión WebSocket.

O usar herramienta como `wscat`:

```bash
npm install -g wscat
wscat -c wss://passvault.example.com/app/your-key
```

### Problemas Comunes

**Reverb no inicia:**
- Verificar permisos del usuario
- Verificar que el puerto 8080 no está en uso: `netstat -tulpn | grep 8080`
- Verificar logs de errores

**WebSockets no conectan:**
- Verificar configuración del proxy en Nginx/Apache
- Verificar que Reverb está corriendo
- Verificar firewall
- Verificar variables `.env` (REVERB_HOST, REVERB_PORT, REVERB_SCHEME)

**Conexiones se desconectan:**
- Aumentar `proxy_read_timeout` en Nginx
- Verificar recursos del servidor (memoria, CPU)

---

## ✅ Checklist de Producción

- [ ] Variables `.env` configuradas (REVERB_*)
- [ ] Reverb ejecutándose como servicio (Supervisor/systemd)
- [ ] Servidor web configurado como proxy (Nginx/Apache)
- [ ] Firewall configurado (puerto 8080 solo localhost)
- [ ] SSL/HTTPS configurado
- [ ] Logs configurados y rotando
- [ ] Script de deploy actualizado
- [ ] Monitoreo configurado
- [ ] Pruebas de conexión WebSocket realizadas

---

## 🔗 Referencias

- [Laravel Reverb Docs](https://laravel.com/docs/12.x/reverb)
- [Nginx WebSocket Proxy](https://nginx.org/en/docs/http/websocket.html)
- [Apache WebSocket Proxy](https://httpd.apache.org/docs/2.4/mod/mod_proxy_wstunnel.html)
- [Supervisor Documentation](http://supervisord.org/)
