# Sistema de Administración - OCIANN Vault

Documentación del sistema de administración y gestión de usuarios, contenido y configuración.

## 📋 Descripción

El sistema de administración permite a los administradores y super administradores gestionar usuarios, contenido, grupos y configurar el sistema. Proporciona herramientas de monitoreo, auditoría y reportes de seguridad.

**SaaS Multi-Tenant:** Se distingue **platform super admin** (`organization_id` null) vs **org admin** (super admin de una organización). El platform super admin ve Organizaciones, Seguridad, Configuración y métricas globales. El org admin solo ve su organización: usuarios, vault, grupos, auditoría y códigos de invitación. Todo (usuarios, vault, grupos, auditoría) se filtra por `organization_id` cuando el usuario es org admin.

## ✨ Funcionalidades Implementadas

### Roles y Permisos
- **Roles**: `user` (normal), `admin` (administrador), `super_admin` (super administrador)
- **Platform super admin** vs **org admin**: `User::isPlatformSuperAdmin()` (org null + super_admin), `User::isOrgAdmin()` (tiene org + admin/super_admin)
- **Middleware**: `AdminMiddleware` (admin/super_admin), `PlatformSuperAdminMiddleware` (solo platform super admin), `OrgAdminMiddleware` (solo org admin)
- **Policy**: `AdminPolicy` para autorización granular
- **Seeder**: `SuperAdminSeeder` crea el primer **platform** super admin (`organization_id` null)

### Gestión de Usuarios (Fase 1, 2 y 4 - Completado) ✅
- **Lista de usuarios**: Tabla con paginación, filtros y estadísticas
- **Vista de detalle**: Información completa, estadísticas, items recientes, grupos y actividad
- **Edición**: Actualizar nombre, email y verificación manual de email
- **Filtros**: Búsqueda por nombre/email, filtro por rol, filtro por estado (activos/inactivos), ordenamiento
- **Estadísticas**: Total usuarios, activos, inactivos, por rol, verificados, con 2FA
- **Cambio de rol**: Cambiar rol de usuario (solo super admin) con validaciones
- **Reset de contraseña**: Resetear contraseña de usuario desde panel de admin
- **Activación/Desactivación**: Activar o desactivar usuarios (previene login de usuarios inactivos)
- **Eliminación**: Soft delete de usuarios con protección del último super admin
- **Dropdown de acciones**: Menú contextual con todas las acciones disponibles
- **Modales**: Modales para cambio de rol y reset de contraseña
- **Crear usuario manual** (org admin): `GET /admin/users/create`, `POST /admin/users`. Formulario nombre, email, contraseña. Solo org admin; usuario creado en la org del admin. Ver `UserManagementService`, `StoreUserRequest`.
- **Scoping por org**: Org admin solo ve y gestiona usuarios de su organización; platform super admin ve todos.

### Dashboard de Administración
- **Métricas por org o global**: Org admin ve solo datos de su org; platform super admin vista global. Usuarios, vault, grupos, comparticiones, actividad 30 días, eventos recientes, gráfico diario.
- **Códigos de invitación** (solo org admin): Card con total/sin usar y enlace a gestionar códigos.
- **Logs de auditoría recientes**: Últimos 15 logs (filtrados por org si org admin).
- Información del sistema: versión Laravel, PHP, entorno, verificados, 2FA, admins.
- Accesos rápidos (Configuración solo platform super admin).

### Auditoría y Monitoreo ✅
- **Panel de Auditoría:**
  - Vista integrada en panel de administración
  - Filtros avanzados: usuario, acción, tipo de modelo, fechas
  - Exportación a CSV
  - Compatible con modo oscuro/claro
- **Dashboard de Actividad del Sistema:**
  - Métricas principales: usuarios activos, items, grupos, comparticiones
  - Actividad de últimos 30 días: items creados/actualizados, grupos, usuarios, miembros, comparticiones
  - Gráfico interactivo de actividad diaria (Chart.js) con 4 series: Items, Grupos, Usuarios, Comparticiones
  - Eventos recientes de últimos 7 días con timeline visual
  - Diseño responsive y moderno

## 🏗️ Arquitectura

### Controladores

#### `AdminUserController`
- `index()` - Lista de usuarios con filtros (rol, estado, búsqueda) y paginación
- `show()` - Detalle completo del usuario con estadísticas
- `edit()` - Formulario de edición
- `update()` - Actualizar información del usuario
- `changeRole()` - Cambiar rol de usuario (solo super admin)
- `resetPassword()` - Resetear contraseña de usuario
- `activate()` - Activar usuario
- `deactivate()` - Desactivar usuario
- `destroy()` - Eliminar usuario (soft delete)

#### `AdminVaultController`
- `index()` - Lista de vault items (solo metadatos) con filtros y búsqueda; **filtrado por org** si org admin
- `show()` - Ver detalle de item con comparticiones; `ensureVaultItemInOrg` para org admin
- `destroy()` - Eliminar item (con logs de auditoría)

#### `AdminGroupController`
- `index()` - Lista de grupos con filtros y búsqueda; **filtrado por org** si org admin
- `show()` - Ver detalle de grupo con miembros y estadísticas; `ensureGroupInOrg` para org admin
- `destroy()` - Eliminar grupo (con logs de auditoría)

#### `AuditController`
- `index()` - Lista de logs de auditoría con filtros; **filtrado por org** si org admin
- `export()` - Exportar logs a CSV (mismos filtros)

#### `AdminSecurityController`
- `index()` - Dashboard principal de reportes de seguridad
- `weakPasswords()` - Reporte de contraseñas débiles
- `reusedPasswords()` - Reporte de contraseñas reutilizadas
- `inactiveUsers()` - Reporte de usuarios inactivos
- `failedLogins()` - Reporte de intentos de login fallidos
- `securityReport()` - Reporte general de seguridad

### Políticas

#### `AdminPolicy`
- `accessAdminPanel()` - Acceso al panel de admin
- `manageUsers()` - Gestionar usuarios
- `changeUserRole()` - Cambiar roles (solo super admin)
- `manageAdmins()` - Gestionar otros admins (solo super admin)
- `viewAllVaultItems()` - Ver metadatos de todos los items
- `deleteAnyVaultItem()` - Eliminar items
- `manageSystemSettings()` - Configurar sistema (solo super admin)

### Middleware

#### `AdminMiddleware`
- Verifica que el usuario esté autenticado
- Verifica que el usuario tenga rol `admin` o `super_admin`
- Retorna 403 si no tiene permisos

#### `PlatformSuperAdminMiddleware`
- Verifica que el usuario sea **platform super admin** (`organization_id` null + `super_admin`)
- Usado para: Organizaciones, **Seguridad**, **Configuración**. Org admin recibe 403 en esas rutas.

#### `OrgAdminMiddleware`
- Verifica que el usuario sea **org admin** (tiene `organization_id` + admin/super_admin)
- Usado para: Códigos de invitación, crear usuario manual (`/admin/users/create`, `POST /admin/users`)

#### `SuperAdminMiddleware`
- Verifica rol `super_admin`. Ya no se usa para Settings; Settings usa `platform-super-admin`.

## 🔄 Flujo de Trabajo

### Acceso al Panel de Administración
1. Usuario con rol admin/super_admin inicia sesión
2. En el dropdown de usuario, aparece "Panel de Administración"
3. Al hacer clic, redirige a `/admin/dashboard`
4. **Sidebar**: "Organizaciones" solo platform super admin; "Códigos de invitación" solo org admin; "Seguridad" y "Configuración" solo platform super admin. Resto (Dashboard, Usuarios, Vault, Grupos, Auditoría) según permisos.

### Gestión de Usuarios
1. **Listar usuarios**: `/admin/users`
   - Ver tabla con todos los usuarios
   - Aplicar filtros (búsqueda, rol, ordenamiento)
   - Ver estadísticas generales

2. **Ver detalle**: `/admin/users/{user}`
   - Información completa del usuario
   - Estadísticas (items, grupos, comparticiones)
   - Items recientes (solo información, sin navegación)
   - Grupos del usuario (solo información con conteo de miembros, sin navegación)
   - Actividad reciente (logs de auditoría)

3. **Editar usuario**: `/admin/users/{user}/edit`
   - Actualizar nombre y email
   - Verificar/desverificar email manualmente
   - Ver información de solo lectura (rol, ID, etc.)

## 📍 Rutas

### Panel de Administración
- `GET /admin/dashboard` - Dashboard principal (métricas por org o global)
- `GET /admin/users` - Lista de usuarios (filtrada por org si org admin)
- `GET /admin/users/create` - Crear usuario (solo org admin)
- `POST /admin/users` - Crear usuario (solo org admin)
- `GET /admin/users/{user}` - Detalle de usuario
- `GET /admin/users/{user}/edit` - Editar usuario
- `PUT /admin/users/{user}` - Actualizar usuario
- `POST /admin/users/{user}/change-role` - Cambiar rol de usuario (solo super admin)
- `POST /admin/users/{user}/reset-password` - Resetear contraseña
- `POST /admin/users/{user}/activate` - Activar usuario
- `POST /admin/users/{user}/deactivate` - Desactivar usuario
- `DELETE /admin/users/{user}` - Eliminar usuario
- `GET /admin/vault` - Lista de vault items (filtrada por org si org admin)
- `GET /admin/vault/{vaultItem}` - Detalle de vault item
- `DELETE /admin/vault/{vaultItem}` - Eliminar vault item
- `GET /admin/groups` - Lista de grupos (filtrada por org si org admin)
- `GET /admin/groups/{group}` - Detalle de grupo
- `DELETE /admin/groups/{group}` - Eliminar grupo
- `GET /admin/audit` - Auditoría (filtrada por org si org admin)
- `GET /admin/audit/export` - Exportar auditoría
- `GET /admin/invitation-codes` - Códigos de invitación (solo org admin)
- `POST /admin/invitation-codes` - Generar código (solo org admin)
- `GET /admin/organizations` - Organizaciones (solo platform super admin)
- `GET /admin/organizations/create`, `POST`, `show`, `edit`, `update` - CRUD organizaciones (solo platform super admin)
- `GET /admin/security` - Dashboard de reportes de seguridad (**solo platform super admin**)
- `GET /admin/security/*` - Reportes de seguridad (todos **solo platform super admin**)
- `GET /admin/settings`, `PUT /admin/settings/*` - Configuración del sistema (**solo platform super admin**)

## 🎨 UI/UX

### Layout de Administración
- Sidebar fijo con navegación
- Header con breadcrumbs y acciones
- Contenido principal con scroll independiente
- Compatible con modo oscuro/claro

### Vistas de Usuarios
- **Lista**: Tabla responsive con avatares, badges de rol, indicadores de estado
- **Detalle**: Tarjetas de información, estadísticas, tablas de items y grupos
  - **Grupos**: Información solo de lectura (sin navegación) con conteo de miembros
  - **Items recientes**: Lista de items del usuario con información básica
  - **Actividad**: Logs de auditoría relacionados con el usuario
- **Edición**: Formulario minimalista con validación en tiempo real

### Indicadores Visuales
- Badges de rol: Super Admin (rojo), Admin (amarillo), Usuario (gris)
- Estado de usuario: Activo (verde), Inactivo (rojo)
- Estado de verificación: Verificado (verde), Sin verificar (amarillo)
- 2FA: Badge azul cuando está activado

## 🔒 Seguridad

### Medidas Implementadas
- ✅ Middleware de autenticación en todas las rutas
- ✅ Verificación de rol admin/super_admin
- ✅ Policies para autorización granular
- ✅ Solo super admin puede cambiar roles
- ✅ **Solo platform super admin** puede acceder a Organizaciones, Seguridad y Configuración
- ✅ Org admin solo accede a usuarios, vault, grupos, auditoría y códigos de invitación de su org
- ✅ Validación de datos con Form Requests
- ✅ Protección CSRF en todos los formularios
- ✅ Protección del último super admin (no se puede eliminar/desactivar)
- ✅ Prevención de auto-eliminación/desactivación
- ✅ Verificación de usuarios activos en autenticación (usuarios inactivos no pueden iniciar sesión)
- ✅ Soft delete para usuarios (marca como inactivo y modifica email)

### Permisos por Rol

| Acción | Usuario | Admin | Super Admin (org) | Platform Super Admin |
|--------|---------|-------|-------------------|----------------------|
| Ver panel de admin | ❌ | ✅ | ✅ | ✅ |
| Gestionar usuarios (su org) | ❌ | ✅ | ✅ | ✅ |
| Crear usuario / Códigos invitación | ❌ | ❌ | ✅ | — |
| Organizaciones, Seguridad, Configuración | ❌ | ❌ | ❌ | ✅ |
| Cambiar roles | ❌ | ❌ | ✅ | ✅ |
| Ver metadatos / Eliminar items | ❌ | ✅ | ✅ (solo su org) | ✅ (global) |

## 📝 Archivos Principales

- `app/Http/Controllers/AdminUserController.php` - Gestión de usuarios (scoping por org)
- `app/Http/Controllers/Admin/AdminVaultController.php` - Gestión de vault items (scoping por org)
- `app/Http/Controllers/Admin/AdminGroupController.php` - Gestión de grupos (scoping por org)
- `app/Http/Controllers/Admin/AdminDashboardController.php` - Dashboard (métricas por org o global)
- `app/Http/Controllers/Admin/AdminSecurityController.php` - Reportes de seguridad (solo platform super admin)
- `app/Http/Controllers/Admin/AdminSettingsController.php` - Configuración del sistema (solo platform super admin)
- `app/Http/Controllers/Admin/OrganizationController.php` - CRUD organizaciones (solo platform super admin)
- `app/Http/Controllers/Admin/InvitationCodeController.php` - Códigos de invitación (solo org admin)
- `app/Http/Controllers/AuditController.php` - Auditoría (scoping por org)
- `app/Http/Middleware/AdminMiddleware.php` - Protección rutas admin
- `app/Http/Middleware/PlatformSuperAdminMiddleware.php` - Solo platform super admin (Organizaciones, Seguridad, Configuración)
- `app/Http/Middleware/OrgAdminMiddleware.php` - Solo org admin (códigos invitación, crear usuario)
- `app/Services/UserManagementService.php` - Crear usuario manual en org
- `app/Models/SystemSetting.php` - Modelo para configuraciones del sistema
- `app/Policies/AdminPolicy.php` - Policy de autorización
- `app/Http/Requests/Admin/UpdateUserRequest.php` - Validación de actualización
- `app/Http/Requests/Admin/ChangeRoleRequest.php` - Validación de cambio de rol
- `app/Models/User.php` - Modelo con campo `is_active` y método helper
- `app/Models/SystemSetting.php` - Modelo para configuraciones del sistema
- `database/migrations/2026_01_23_191604_add_is_active_to_users_table.php` - Migración de estado
- `database/migrations/2026_01_23_224049_create_system_settings_table.php` - Migración de tabla de configuraciones
- `resources/views/admin/users/index.blade.php` - Lista de usuarios con filtros mejorados
- `resources/views/admin/users/show.blade.php` - Detalle de usuario (información de grupos solo de lectura) con acciones
- `resources/views/admin/users/edit.blade.php` - Editar usuario
- `resources/views/admin/dashboard.blade.php` - Dashboard principal
- `resources/views/admin/security/index.blade.php` - Dashboard de reportes de seguridad
- `resources/views/admin/security/weak-passwords.blade.php` - Reporte de contraseñas débiles
- `resources/views/admin/security/reused-passwords.blade.php` - Reporte de contraseñas reutilizadas
- `resources/views/admin/security/inactive-users.blade.php` - Reporte de usuarios inactivos
- `resources/views/admin/security/report.blade.php` - Reporte general de seguridad
- `resources/views/admin/settings/index.blade.php` - Panel de configuración del sistema
- `resources/views/layouts/admin-layout.blade.php` - Layout de administración
- `resources/views/components/theme-toggle.blade.php` - Componente unificado de tema

## 🚧 Funcionalidades Pendientes

### Gestión de Usuarios (Fase 3 - Futuro)
- Ver historial completo de actividad
- Limpiar sesiones activas
- Exportar datos del usuario (GDPR)
- Restaurar usuario eliminado
- Historial de cambios de rol

### Sistema de Notificaciones por Email
- ⏳ Configurar sistema de correo (SMTP/Mailgun/etc)
- ⏳ Notificación cuando admin resetea contraseña
- ⏳ Notificación cuando se cambia rol de usuario
- ⏳ Notificación cuando se activa/desactiva usuario
- ⏳ Notificación cuando se elimina usuario

### Logs de Auditoría ✅
- ✅ Logs de cambio de rol (implementado con validaciones)
- ✅ Logs de reset de contraseña (implementado)
- ✅ Logs de activación de usuarios (implementado)
- ✅ Logs de desactivación de usuarios (implementado con validaciones)
- ✅ Logs de eliminación de usuarios (implementado con validaciones)
- ✅ Logs de edición de usuarios (implementado con cambios de nombre, email y verificación)
- ✅ Logs de intentos bloqueados (auto-eliminación, último super admin) usando Log::warning()
- ⏳ Logs de acceso al panel de administración (futuro)

### Gestión de Contenido ✅
- **Vault Items:**
  - Lista de todos los items (solo metadatos, sin secretos)
  - Búsqueda global por título o propietario
  - Filtros por tipo, propietario, ordenamiento
  - Ver detalle de item con comparticiones (usuarios y grupos)
  - Eliminar items desde admin (con logs de auditoría)
  - Estadísticas: total, por tipo, favoritos, con TOTP
- **Grupos:**
  - Lista de todos los grupos
  - Búsqueda por nombre, descripción o propietario
  - Filtros por propietario y ordenamiento
  - Ver detalle de grupo con miembros y estadísticas
  - Eliminar grupos desde admin (con logs de auditoría)
  - Estadísticas: total grupos, total miembros, grupos con items

### Reportes de Seguridad ✅
- ✅ **Contraseñas débiles**: Análisis local de fortaleza de contraseñas
  - Detección de contraseñas cortas (< 8 y < 12 caracteres)
  - Falta de mayúsculas, minúsculas, números o caracteres especiales
  - Contraseñas comunes
  - Solo números o solo letras
- ✅ **Contraseñas reutilizadas**: Detección de contraseñas duplicadas entre items
- ✅ **Usuarios inactivos**: Usuarios que no han iniciado sesión en X días (30, 90, 180, 365)
  - Basado en tabla `sessions` para obtener último login real
  - Muestra días inactivos, items en vault, estado de 2FA
- ✅ **Intentos de login fallidos**: Reporte de intentos de autenticación fallidos
  - Lectura de logs de Laravel para detectar intentos fallidos
  - Filtro por días (1, 7, 30, 90)
  - Muestra email, IP, user agent, razón del fallo (credenciales inválidas, cuenta inactiva)
  - Logging automático en `LoginRequest` para todos los intentos fallidos
- ✅ **Reporte general**: Vista consolidada con métricas principales
- ⏳ **Integración con Have I Been Pwned**: Pwned Passwords API (gratis) - Fase 2

### Configuración del Sistema ✅
- **Configuración General:**
  - Nombre de la aplicación
  - URL de la aplicación
  - Zona horaria
  - Idioma (Español/English)
- **Email/SMTP:**
  - Configuración de mailer (SMTP, Sendmail, Mailgun, SES)
  - Host, puerto, encriptación
  - Usuario y contraseña (cifrada)
  - Dirección y nombre del remitente
- **Políticas de Seguridad:**
  - Longitud mínima de contraseña
  - Requisitos de contraseña (mayúsculas, minúsculas, números, símbolos)
  - Duración de sesión
  - Intentos máximos de login
  - Duración de bloqueo
  - Requerir 2FA para administradores
- **Modo de Mantenimiento:**
  - Activar/desactivar modo de mantenimiento
  - Mensaje personalizado
  - IPs permitidas durante mantenimiento

## 🔗 Referencias

- [Laravel Authorization](https://laravel.com/docs/authorization)
- [Laravel Policies](https://laravel.com/docs/policies)
- [Bootstrap 5 Tables](https://getbootstrap.com/docs/5.3/content/tables/)
- [`documentacion/09-saas-multi-tenant/`](../09-saas-multi-tenant/) — Modelo multi-tenant, platform vs org admin, scoping
