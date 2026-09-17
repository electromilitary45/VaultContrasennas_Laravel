# PassVault

> Open Source Password Vault - Un gestor de contraseñas seguro y open source

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-purple.svg)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📋 Descripción

PassVault es una aplicación web open source para gestionar contraseñas y secretos de forma segura. Diseñada con Laravel y siguiendo principios de seguridad por diseño, permite organizar, compartir y auditar accesos a credenciales de manera eficiente.

## ✨ Características Principales

### 🔐 Gestión de Contraseñas
- **Múltiples tipos de items**: Autenticación (Login), Tarjetas, API Keys, SSH Keys, Notas Seguras
- **Cifrado seguro**: Todos los secretos se almacenan cifrados usando AES-256-CBC con HMAC
- **TOTP/2FA**: Generación y gestión de códigos de autenticación de dos factores con QR codes
- **Campos dinámicos**: Formularios adaptativos según el tipo de item seleccionado
- **Filtros avanzados**: Búsqueda, filtrado por tipo, favoritos, carpetas (AJAX sin recargar)

### 📁 Organización
- **Carpetas jerárquicas**: Organiza tus items en carpetas con estructura de hasta 5 niveles
- **Carpetas personales**: Cada usuario tiene sus propias carpetas
- **Carpetas de grupo**: Carpetas compartidas dentro de grupos
- **Vista integrada**: Navegación por carpetas directamente en el vault

### 👥 Compartición y Colaboración
- **Compartición con usuarios**: Comparte items individuales con otros usuarios
- **Compartición con grupos**: Comparte items con grupos completos
- **Permisos granulares**: Control de acceso con niveles `view`, `edit`, `admin`, `owner`
- **Gestión de permisos**: Actualiza permisos de compartición en tiempo real
- **Revocación de acceso**: Revoca accesos compartidos cuando sea necesario
- **Notificaciones de compartición**: Notificaciones en tiempo real cuando se comparte un item contigo o con tu grupo

### 🏢 Gestión de Grupos
- **Creación de grupos**: Crea grupos para colaborar con equipos
- **Roles de miembros**: `owner`, `admin`, `member`, `viewer` con permisos específicos
- **Sistema de invitaciones**: Invita usuarios a grupos; deben aceptar la invitación
- **Solicitudes de acceso**: Usuarios pueden solicitar acceso a grupos; owners/admins aprueban o rechazan
- **Vault de grupo**: Vista dedicada para items compartidos con el grupo
- **Carpetas de grupo**: Organiza items del grupo en carpetas compartidas
- **Creación desde grupo**: Crea items directamente desde el grupo (se comparten automáticamente)
- **Notificaciones de grupo**: Notificaciones en tiempo real para invitaciones, solicitudes y nuevos items en grupos

### 🎨 Interfaz de Usuario
- **Diseño minimalista**: UI tipo Apple con Bootstrap 5
- **Modo oscuro/claro**: Toggle automático con detección de preferencias del sistema
- **Responsive**: Diseño adaptativo para móviles, tablets y desktop
- **AJAX**: Filtros y búsqueda sin recargar página
- **Dashboard mejorado**: Vista general con estadísticas, items recientes y favoritos

### 🔔 Sistema de Notificaciones
- **Notificaciones en tiempo real**: WebSockets con Laravel Reverb para actualizaciones instantáneas
- **Bandeja de notificaciones**: Página dedicada con todas las notificaciones, filtros y acciones rápidas
- **Dropdown en navbar**: Acceso rápido a notificaciones recientes desde cualquier página
- **Tipos de notificaciones**:
  - Invitaciones a grupos (aceptar/rechazar)
  - Solicitudes de acceso a grupos (aprobar/rechazar)
  - Items compartidos contigo o con tu grupo
  - Nuevos items creados en grupos
- **Acciones rápidas**: Botones directos desde notificaciones para ver items, grupos o gestionar invitaciones
- **Persistencia**: Notificaciones guardadas en base de datos, marcadas como leídas/no leídas

### 🏢 SaaS Multi-Tenant
- **Organizaciones**: Platform super admin crea organizaciones; al crear una, se genera el super admin de esa org (credenciales mostradas una vez).
- **Códigos de invitación**: Registro solo con código; org admins generan códigos. Sin registro abierto.
- **Creación manual de usuarios**: Org admins pueden crear usuarios en su organización.
- **Aislamiento por org**: Vault, grupos, carpetas y auditoría filtrados por `organization_id`. Platform admin ve vista global; org admin solo su org.
- **Panel admin**: Dashboard con métricas por org; Seguridad y Configuración solo para platform super admin.

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 12.x
- **Base de Datos**: MySQL 8.0+
- **Frontend**: Bootstrap 5, Vite, Alpine.js
- **PHP**: 8.2 o superior
- **Cifrado**: Laravel Crypt (AES-256-CBC)
- **TOTP**: spomky-labs/otphp
- **WebSockets**: Laravel Reverb (notificaciones en tiempo real)

## 📦 Requisitos

- PHP 8.2 o superior
- Composer
- Node.js y npm
- MySQL 8.0 o superior
- Extensiones PHP: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/vault-contrasenna.git
cd vault-contrasenna
```

### 2. Instalar dependencias

```bash
# Dependencias PHP
composer install

# Dependencias Node.js
npm install
```

### 3. Configurar entorno

```bash
# Copiar archivo de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 4. Configurar base de datos

Edita el archivo `.env` y configura tus credenciales de MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vault_contrasenna
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 5. Ejecutar migraciones

```bash
php artisan migrate
```

### 6. Iniciar servidor de desarrollo

**Opción recomendada (todo en uno):**
```bash
composer dev
```

Este comando inicia **todos los servicios necesarios** en paralelo:
- ✅ Servidor Laravel (puerto 8000)
- ✅ Cola de trabajos
- ✅ **Servidor Reverb (WebSockets)** en `http://localhost:8080`
- ✅ Vite dev server (compila CSS/JS en tiempo real)

**Nota:** Los logs se pueden ver en `storage/logs/laravel.log` o usando `php artisan log:tail` (si está disponible en tu sistema).

**Opción manual (si prefieres controlar cada servicio):**
```bash
# Terminal 1: Servidor Laravel
php artisan serve
# O en un puerto específico (ejemplo: 8001)
php artisan serve --port=8001

# Terminal 2: Cola de trabajos
php artisan queue:listen

# Terminal 3: Servidor Reverb (WebSockets)
php artisan reverb:start

# Terminal 4: Vite (compilación frontend)
npm run dev
```

### 7. Compilar assets (solo frontend)

**Solo si necesitas compilar assets sin iniciar el servidor Laravel:**

```bash
# Desarrollo (hot reload)
npm run dev

# Producción (compilación optimizada)
npm run build
```

**Nota:** 
- `npm run dev` solo compila CSS/JS con hot reload (solo para desarrollo).
- Si necesitas el servidor Laravel, cola y Reverb, usa `composer dev` (que incluye `npm run dev` automáticamente).
- **En producción, siempre usa `npm run build`** (NO `npm run dev`). El comando `build` compila y optimiza los assets para producción.

La aplicación estará disponible en `http://localhost:8000` (o el puerto que configures)

## 👤 Acceso como Super Administrador

### Crear Super Administrador (Platform)

El sistema incluye un seeder para crear el primer **platform super admin** (`organization_id` null). Se ejecuta automáticamente al correr las migraciones:

```bash
php artisan migrate
# El SuperAdminSeeder se ejecuta desde la migración add_role
```

O manualmente:

```bash
php artisan db:seed --class=SuperAdminSeeder
```

### Credenciales por Defecto

- **Email:** `admin@vault.local`
- **Contraseña:** `admin123`

Este usuario es **platform super admin**: ve Organizaciones, Seguridad, Configuración y métricas globales. Los **org admins** (super admin de cada organización) solo ven usuarios, vault, grupos, auditoría y códigos de invitación de su org.

### Registro de Nuevos Usuarios

No hay registro abierto. Opciones:
- **Código de invitación:** Un org admin genera un código en Admin → Códigos de invitación; el usuario se registra en `/register` con ese código.
- **Creación manual:** Org admin crea el usuario en Admin → Usuarios → Crear usuario.

### Iniciar Sesión y Panel de Administración

1. Accede a la aplicación (p. ej. `http://localhost:8000`).
2. Inicia sesión con las credenciales correspondientes.
3. En la barra de navegación, avatar → "Panel de Administración", o ve a `/admin/dashboard`.

### Cambiar Contraseña

**⚠️ IMPORTANTE:** Cambia la contraseña del super administrador inmediatamente después del primer login:

1. Haz clic en tu avatar/usuario en la navegación
2. Selecciona "Perfil"
3. Ve a la sección "Actualizar Contraseña"
4. Ingresa tu contraseña actual y la nueva contraseña
5. Guarda los cambios

### Notas de Seguridad

- **Platform super admin:** acceso a Organizaciones, Seguridad, Configuración y vista global. **Org admin:** solo su organización (usuarios, vault, grupos, auditoría, códigos de invitación).
- Solo los super administradores pueden cambiar roles de usuarios.
- En producción, usa contraseña fuerte y única; cambia la del seeder cuanto antes.

## 📚 Documentación

- [`documentacion/01-setup-inicial/documentacionInicial.md`](documentacion/01-setup-inicial/documentacionInicial.md) - Documento de arranque completo con arquitectura y esquema de datos
- [`documentacion/09-saas-multi-tenant/`](documentacion/09-saas-multi-tenant/) - SaaS multi-tenant: decisiones, plan por fases (Fases 1–6 completadas)
- [`ROUTE-MAP.md`](ROUTE-MAP.md) - Mapa técnico: estado de implementación, migraciones, servicios, controladores
- [`ROADMAP.md`](ROADMAP.md) - Plan de producto: funcionalidades futuras, mejoras y visión
- [`ORDEN-COMMITS.md`](ORDEN-COMMITS.md) - Plan de commits incrementales recomendados
- [`documentacion/`](documentacion/) - Documentación organizada por módulos

## 📋 Reglas del Proyecto

Las reglas y estándares del proyecto se encuentran en `.cursor/rules/`:

- **Diseño:** [`design-rules.md`](.cursor/rules/design-rules.md) - Estilo minimalista tipo Apple, Bootstrap, UI/UX
- **Código:** [`code-standards.md`](.cursor/rules/code-standards.md) - Clean code, MVC, comentarios, mejores prácticas
- **Documentación:** [`documentation-rules.md`](.cursor/rules/documentation-rules.md) - Organización y convenciones de documentación

## 🏗️ Arquitectura

### Estructura de Servicios

El proyecto sigue una arquitectura de **Service Layer** para separar la lógica de negocio de los controladores:

- **`VaultService`**: Gestión de items del vault (CRUD, filtrado, cifrado)
- **`CryptoService`**: Cifrado y descifrado de secretos (AES-256-CBC)
- **`ShareService`**: Lógica de compartición (usuarios y grupos)
- **`FolderService`**: Gestión de carpetas (jerárquicas, personales y de grupo)
- **`TotpService`**: Generación y validación de códigos TOTP

### Modelos Principales

- **`VaultItem`**: Items del vault con relaciones a secretos, versiones, carpetas y comparticiones
- **`Group`**: Grupos de usuarios con miembros y roles
- **`Folder`**: Carpetas jerárquicas (personales y de grupo)
- **`ItemShareUser`**: Comparticiones directas con usuarios
- **`ItemShareGroup`**: Comparticiones con grupos

### Políticas de Autorización

- **`VaultItemPolicy`**: Control de acceso granular basado en ownership, shares directos y shares por grupo
- Permisos: `view`, `edit`, `admin`, `owner`

## 🔒 Seguridad

- ✅ **Cifrado de secretos**: Todos los secretos se almacenan cifrados en la base de datos
- ✅ **Autorización granular**: Policies de Laravel para control de acceso
- ✅ **Protección CSRF**: Todos los formularios protegidos
- ✅ **Validación robusta**: Form Requests con validaciones específicas
- ✅ **Gestión de usuarios**: Panel de administración completo con control de acceso
- ✅ **Control de estado de usuarios**: Sistema de activación/desactivación que previene login de usuarios inactivos
- ✅ **Protección de roles**: Validaciones para prevenir cambios peligrosos en roles de administración
- ✅ **Logs de auditoría**: Registro de acciones administrativas (cambio de rol, reset de contraseña)
- ✅ **Auditoría por módulos**: Logs en Vault, Grupos, Compartición, Carpetas, Autenticación y Administración
- ⏳ **Notificaciones por email**: Sistema de notificaciones cuando se configure correo
- ⏳ **Rate limiting**: Límites de tasa en endpoints sensibles (pendiente)

### Regla de Desarrollo: Auditoría
**A partir de ahora, TODO desarrollo nuevo debe incluir logs de auditoría para acciones importantes.**

## 🗺️ Roadmap

Ver [`ROADMAP.md`](ROADMAP.md) para el roadmap completo con próximas mejoras.

### Estado Actual

- ✅ Autenticación completa
- ✅ CRUD de Vault Items
- ✅ Cifrado de secretos
- ✅ Sistema de compartición (usuarios y grupos)
- ✅ Gestión de grupos
- ✅ Carpetas jerárquicas
- ✅ TOTP/2FA
- ✅ Dashboard mejorado
- ✅ Panel de Administración (Fase 1 y 2 completadas)
  - ✅ Gestión de usuarios (CRUD completo)
  - ✅ Cambio de roles (solo super admin)
  - ✅ Reset de contraseñas
  - ✅ Activación/desactivación de usuarios
  - ✅ Eliminación de usuarios (soft delete)
  - ✅ Filtros avanzados y estadísticas
- ✅ Sistema de auditoría por módulos (Vault, Grupos, Compartición, Carpetas, Auth, Admin)
- ⏳ Importación/Exportación
- ⏳ API REST

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Autores
- *Derek Sebastian Leiva Villalobos*

## 🙏 Agradecimientos

- Laravel Framework
- Bootstrap Team
- Comunidad open source

---

**⚠️ Nota de Seguridad**: Este es un proyecto en desarrollo activo. No usar en producción sin una revisión completa de seguridad.
