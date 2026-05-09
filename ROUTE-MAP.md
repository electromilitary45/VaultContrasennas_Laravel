# Route Map - OCIANN Vault

Mapa técnico del proyecto: estado de implementación, migraciones, controladores, servicios y componentes.

**Última actualización:** 2026-02-05 (Documentación y ROUTE-MAP/ROADMAP/README/ORDEN-COMMITS sincronizados; sección 19.7 y Próximos Pasos actualizados)

## 📋 Reglas de Desarrollo

### Auditoría y Logging (Regla Obligatoria)
**A partir de ahora, TODO desarrollo nuevo debe incluir logs de auditoría:**
- ✅ Todas las acciones administrativas deben registrarse en logs
- ✅ Incluir información del usuario que realiza la acción
- ✅ Incluir timestamp y contexto relevante
- ✅ Usar `Log::info()` o `Log::warning()` según corresponda
- ⏳ **Pendiente:** Agregar logs de auditoría a módulos existentes (ver sección de tareas pendientes)

> **Nota:** Para plan de producto y visión futura, ver [`ROADMAP.md`](ROADMAP.md)

---

## 📊 Estado General del Proyecto

- **Progreso Total:** ~98% del MVP
- **Fase Actual:** MVP Completo — SaaS multi-tenant (Fases 1–6), Dashboard admin por org, Seguridad/Config platform-only, Sistema de notificaciones (Fases 1–4) completado
- **Último Hito:** Sincronización de documentación (2026-02-05); ROUTE-MAP, ROADMAP, README y ORDEN-COMMITS actualizados

---

## ✅ Completado

### 1. Setup Inicial ✅
- [x] Instalación de Laravel 12.48.1
- [x] Configuración de MySQL
- [x] Generación de APP_KEY
- [x] Instalación de Bootstrap 5.3.8
- [x] Configuración de Vite
- [x] Migraciones base de Laravel (users, cache, jobs)
- [x] Layout minimalista tipo Apple
- [x] Estructura de documentación por módulos
- [x] Reglas de diseño y documentación

### 2. Documentación ✅
- [x] Documento inicial completo (`documentacion/01-setup-inicial/documentacionInicial.md`)
- [x] Plan de commits (`ORDEN-COMMITS.md`)
- [x] Estructura de carpetas de documentación
- [x] Reglas de diseño (`.cursor/rules/design-rules.md`)
- [x] Reglas de documentación (`.cursor/rules/documentation-rules.md`)
- [x] Reglas de código (`.cursor/rules/code-standards.md`)
- [x] README del proyecto
- [x] ROADMAP.md

### 3. Autenticación ✅
- [x] Instalación de Laravel Breeze
- [x] Vistas de autenticación personalizadas con Bootstrap minimalista
- [x] Layouts (guest y app) con estilo tipo Apple
- [x] Componentes actualizados a Bootstrap
- [x] Vistas de perfil actualizadas
- [x] Rutas de autenticación configuradas
- [x] Modo oscuro/claro con toggle
- [x] 2FA a nivel de usuario (TOTP)
- [x] Integración de 2FA en flujo de login
- [x] Gestión de códigos de respaldo
- [x] Avatar de usuario
- [x] Dashboard con estadísticas corregidas
- [x] Corrección del flujo de 2FA (preservación de token CSRF)
- [x] Manejo mejorado de errores 419
- [x] Código limpio y optimizado
- [x] Documentación de acceso como super admin en README

### 4. UI y Navegación ✅
- [x] Barra de navegación funcional con Bootstrap JS
- [x] Dropdowns de usuario y tema operativos
- [x] Dashboard mejorado con estadísticas
- [x] Items recientes y favoritos destacados
- [x] Accesos rápidos
- [x] Vista de grupos en dashboard

### 5. Estructura del Dominio ✅
- [x] Crear `app/Services/` (estructura simplificada)
- [x] Crear `app/Policies/`
- [x] `app/Models/` con todas las relaciones
- [x] `app/Http/Controllers/` con controladores delgados
- [x] Autoloading configurado automáticamente

### 6. Migraciones - Grupos ✅
- [x] `create_groups_table` - Ejecutada
- [x] `create_group_members_table` - Ejecutada
- [x] Modelos: `Group`, `GroupMember` - Creados con relaciones
- [x] Relaciones Eloquent configuradas
- [x] Métodos helper en modelos

### 7. Migraciones - Vault Items ✅
- [x] `create_vault_items_table` - Ejecutada
- [x] `create_vault_item_secrets_table` - Ejecutada
- [x] `create_vault_item_versions_table` - Ejecutada
- [x] Modelos: `VaultItem`, `VaultItemSecret`, `VaultItemVersion` - Creados con relaciones
- [x] Relaciones Eloquent configuradas

### 8. Migraciones - Compartición y Folders ✅
- [x] `create_item_shares_users_table` - Ejecutada
- [x] `create_item_shares_groups_table` - Ejecutada
- [x] `create_folders_table` - Ejecutada
- [x] `add_folder_foreign_key_to_vault_items_table` - Ejecutada
- [x] `add_group_id_to_folders_table` - Ejecutada (carpetas de grupo)
- [x] Modelos: `ItemShareUser`, `ItemShareGroup`, `Folder` - Creados con relaciones
- [x] Relaciones Eloquent configuradas

### 9. Servicios ✅
- [x] `CryptoService` - Cifrado/descifrado (AES-256-CBC con HMAC)
- [x] `VaultService` - CRUD de vault items con filtrado y compartición
- [x] `ShareService` - Compartición con usuarios y grupos
- [x] `FolderService` - Gestión de carpetas (personales y de grupo)
- [x] `TotpService` - Generación y validación de códigos TOTP

### 10. Policies y Autorización ✅
- [x] `VaultItemPolicy` - Control de acceso granular
- [x] Lógica de permisos (owner, share directo, share por grupo)
- [x] Registrado en `AppServiceProvider`
- [x] Métodos: `viewAny`, `view`, `create`, `update`, `delete`, `getPermission`

### 11. Controladores - Vault ✅
- [x] `VaultController` - CRUD completo
- [x] Form Requests: `StoreVaultItemRequest`, `UpdateVaultItemRequest`
- [x] Rutas en `routes/web.php`
- [x] Integración con servicios
- [x] Autorización con policies

### 12. Controladores - Grupos ✅
- [x] `GroupController` - CRUD completo
- [x] Gestión de miembros (invitar, remover, cambiar rol)
- [x] Vault de grupo (`itemsIndex`, `itemsCreate`, `itemsStore`)
- [x] Gestión de carpetas de grupo (`storeFolder`, `updateFolder`, `destroyFolder`)
- [x] Vista de administración (`admin`)
- [x] Form Requests: `StoreGroupRequest`, `UpdateGroupRequest`

### 13. Controladores - Carpetas ✅
- [x] `FolderController` - CRUD completo
- [x] Soporte AJAX para operaciones
- [x] Rutas para carpetas personales

### 14. Controladores - Dashboard ✅
- [x] `DashboardController` - Vista general con estadísticas
- [x] Items recientes y favoritos
- [x] Grupos del usuario
- [x] Estadísticas adicionales

### 15. Vistas - Vault Items ✅
- [x] Lista de items (`vault/index.blade.php`) - Con filtros AJAX y carpetas
- [x] Crear item (`vault/create.blade.php`) - Con campos personalizados por tipo y TOTP
- [x] Editar item (`vault/edit.blade.php`) - Con permisos condicionales
- [x] Detalle de item (`vault/show.blade.php`) - Con compartición, TOTP y botones copiar al portapapeles en campos secretos
- [x] Filtros y búsqueda - AJAX sin recargar página
- [x] Integración de carpetas en sidebar

### 16. Vistas - Grupos ✅
- [x] Lista de grupos (`groups/index.blade.php`) - Simplificada
- [x] Crear grupo (`groups/create.blade.php`)
- [x] Editar grupo (`groups/edit.blade.php`)
- [x] Administración (`groups/admin.blade.php`) - Gestión de miembros
- [x] Vault de grupo (`groups/items/vault.blade.php`) - Items del grupo
- [x] Crear item desde grupo (`groups/items/create.blade.php`)

### 17. Vistas - Carpetas ✅
- [x] Integración en vault (`vault/index.blade.php`)
- [x] Integración en grupos (`groups/items/vault.blade.php`)
- [x] Modales para crear/editar carpetas
- [x] Navegación jerárquica

### 18. Compartición ✅
- [x] Métodos en `VaultController` para compartir
- [x] Vistas/modales para compartir en `show.blade.php`
- [x] Lista de usuarios/grupos con acceso
- [x] Revocación de acceso
- [x] Actualización de permisos
- [x] Indicadores visuales de items compartidos

### 19. TOTP/2FA ✅
- [x] Generación de secretos TOTP para vault items
- [x] Generación de QR codes para vault items
- [x] Integración en formularios de creación/edición
- [x] Visualización en vista de detalle
- [x] Compatible con Google Authenticator, Microsoft Authenticator
- [x] 2FA a nivel de usuario (autenticación de cuenta)
- [x] Integración de 2FA en flujo de login
- [x] Gestión de códigos de respaldo para usuario
- [x] Cifrado de secretos TOTP de usuario

### 20. Dashboard Mejorado ✅
- [x] Estadísticas principales
- [x] Items recientes (limitados)
- [x] Favoritos destacados
- [x] Accesos rápidos
- [x] Grupos del usuario
- [x] Estadísticas adicionales

### 21. SaaS Multi-Tenant — Fase 1 ✅
- [x] Migraciones: `organizations`, `organization_id` en users/groups/vault_items/folders/audit_logs, `organization_invitation_codes`
- [x] Modelos: `Organization`, `OrganizationInvitationCode`; relaciones en `User`, `Group`, `VaultItem`, `Folder`, `AuditLog`
- [x] Super admin de plataforma con `organization_id` NULL (Seeder sin cambios; columna añadida después)
- [x] Ver [`documentacion/09-saas-multi-tenant/plan-implementacion-fases.md`](documentacion/09-saas-multi-tenant/plan-implementacion-fases.md) para fases 2–6

### 22. SaaS Multi-Tenant — Fase 2 ✅
- [x] `PlatformSuperAdminMiddleware` y `User::isPlatformSuperAdmin()`
- [x] `OrganizationService::createOrganization()` (org + super admin de org, credenciales temporales)
- [x] `Admin\OrganizationController`: index, create, store, show, edit, update
- [x] Form Requests: `StoreOrganizationRequest`, `UpdateOrganizationRequest`
- [x] Rutas `admin.organizations.*` con middleware `platform-super-admin`
- [x] Vistas: index, create, created (credenciales), show, edit. Sidebar "Organizaciones" solo platform super admin.
- [x] Logs de auditoría al crear y actualizar organización.

### 23. SaaS Multi-Tenant — Fase 3 ✅
- [x] `OrgAdminMiddleware` y `User::isOrgAdmin()`
- [x] `InvitationCodeService`: generar códigos, `findValidUnusedCode()`
- [x] `Admin\InvitationCodeController`: index (listar), store (generar). Rutas `admin.invitation-codes.*` con middleware `org-admin`
- [x] Vista `admin.invitation-codes.index`: lista de códigos, "Generar código", mostrar código y URL tras crear
- [x] Sidebar "Códigos de invitación" solo para org admins
- [x] Registro con código: `RegisterWithInvitationCodeRequest`, `AuthService::registerWithInvitationCode()`
- [x] Vista `auth.register`: código + nombre + email + contraseña. Prefill desde `?code=`. Mensaje "Necesitas un código de invitación..."
- [x] Sin registro abierto; enlaces "Registrarse" siguen apuntando a `/register`
- [x] Logs al generar y al usar código en registro.

### 24. SaaS Multi-Tenant — Fase 4 ✅
- [x] `UserManagementService::createUserManually()`: crear usuario en org del admin, rol `user`, log.
- [x] `StoreUserRequest`: validación nombre, email, contraseña; autorización `isOrgAdmin()`.
- [x] Rutas `GET /admin/users/create`, `POST /admin/users` con middleware `org-admin`.
- [x] `AdminUserController::create()`, `store()`; inyección de `UserManagementService`.
- [x] Vista `admin.users.create`: formulario nombre, email, contraseña (+ confirmación). Mensaje sobre inicio de sesión.
- [x] Botón "Crear usuario" en `admin.users.index` solo para `isOrgAdmin()`. Flash de éxito al crear.

### 25. SaaS Multi-Tenant — Fase 5 ✅
- [x] **Admin usuarios:** index, stats y listado filtrados por `organization_id` si org admin; `ensureUserInOrg()` en show, edit, update, changeRole, resetPassword, activate, deactivate, destroy.
- [x] **Admin vault:** index y stats por org; usuarios del filtro solo de la org; `ensureVaultItemInOrg()` en show y destroy.
- [x] **Admin grupos:** index y stats por org; usuarios del filtro solo de la org; `ensureGroupInOrg()` en show y destroy.
- [x] **Auditoría:** `AuditService` filtra por `organization_id` en getLogs, filterLogs, getStats; `AuditController` inyecta org en filtros y export; usuarios del filtro solo de la org.
- [x] `AuditLog::scopeForOrganization()`. Sidebar ya diferenciado: Organizaciones (platform), Códigos (org admin).

### 26. SaaS Multi-Tenant — Fase 6 ✅
- [x] **VaultService:** listItems y findItem con scope por org; createItem asigna `organization_id`; grupos del usuario filtrados por org.
- [x] **FolderService:** todas las consultas y create/update/delete con scope por org.
- [x] **GroupController:** index, store, show, admin, edit, update, destroy, inviteMember, removeMember, updateMemberRole, itemsIndex, itemsCreate, itemsStore, storeFolder, updateFolder, destroyFolder con `ensureGroupInOrg` y scope por org; creación con `organization_id`.
- [x] **DashboardController:** getStats, getUserGroups, getRecentShares con scope por org.
- [x] **VaultController:** availableUsers y availableGroups por org; validación org al compartir con usuario o grupo.
- [x] **VaultItemPolicy:** view, update, delete, restore, forceDelete, getPermission con comprobación de org.
- [x] **AuditService::log** y listeners de vault: `organization_id` en `audit_logs`.

### 27. Dashboard Admin — Métricas por org + logs recientes ✅
- [x] **AdminDashboardController:** scope por org (org admin) vs global (platform super admin). Métricas usuarios, vault, grupos, sharing; invitation codes (solo org admin).
- [x] Actividad 30 días, eventos recientes y actividad diaria (gráfico) con mismos filtros por org.
- [x] Logs recientes: últimos 15 vía `AuditService::getLogs`, filtrados por org cuando org admin.
- [x] **Vista admin/dashboard:** card "Códigos de invitación" (org admin); sección "Logs de auditoría recientes" con enlace a auditoría.

### 28. Seguridad y Configuración solo Platform Super Admin ✅
- [x] **Sidebar:** "Seguridad" y "Configuración" visibles solo si `isPlatformSuperAdmin()`.
- [x] **Rutas:** `admin/security*` y `admin/settings*` con middleware `platform-super-admin` (org admin recibe 403).
- [x] **Dashboard admin:** enlace "Configuración del Sistema" en Accesos rápidos solo para platform super admin.

### 29. Contraseña temporal + Documentación organizaciones + Regenerar ✅
- [x] **Forzar cambio en primer login:** Migración `must_change_password` en users. Org admin creado con `must_change_password` = true. Tras login (o 2FA), redirección a `/password/change-required`. Formulario nueva + confirmar; `AuthService::forceChangePassword()`. Middleware `ensure-password-changed` en rutas de app; excluye change-required y logout. Vista `auth.change-password-required`. Mensaje en "Organización creada" actualizado.
- [x] **Documentación organizaciones:** Módulo `documentacion/10-organizaciones/README.md` (CRUD, flujo creación, primer login, regenerar). Actualizado `documentacion/README.md`.
- [x] **Regenerar contraseña temporal:** `OrganizationService::regenerateTemporaryPassword()`. `POST /admin/organizations/{organization}/regenerate-password`, `OrganizationController::regeneratePassword`. Vista `admin.organizations.password-regenerated`. Botón en `admin.organizations.show`. Log y `AuditService::log` (`organization_password_regenerated`).

### 30. Modales en lugar de alert/confirm ✅
- [x] **Componentes:** `<x-confirm-modal>` (Blade) para confirmaciones de formularios. `<x-global-modals>` (alert + confirm JS). `resources/js/modals.js` con `showAlert(title, message)` y `showConfirm({ title, message, onConfirm })`. Incluido en layouts app, admin, guest.
- [x] **Reemplazo completo:** Todas las vistas actualizadas: `onsubmit="return confirm(...)"` y `onclick="return confirm(...)"` → formularios + `<x-confirm-modal>`. `alert()` y `confirm()` en JS → `showAlert()` y `showConfirm()`. Archivos: admin (org, groups, vault, users), vault (show, index, create, edit, partials), groups (index, admin, items, partials), profile (edit, two-factor).
- [x] **Reglas:** Actualizado `.cursor/rules/design-rules.md` con sección "Modales en lugar de alertas JS". Actualizado `.cursor/rules/code-standards.md` con checklist item.

### 31. Sistema de Notificaciones e Invitaciones 🚧
- [x] **Fase 1: Sistema base de notificaciones** ✅ (completada)
- [x] **Fase 2: Invitaciones a grupos (aceptar/rechazar)** ✅
  - [x] Migración `create_notifications_table` (user_id, organization_id, type, title, message, data JSON, read, read_at, action_url, action_label)
  - [x] Modelo `Notification` (relaciones, scopes unread/read/forOrganization, métodos markAsRead/markAsUnread)
  - [x] `NotificationService` (create, markAsRead, markAllAsRead, getUnreadCount, getNotifications, deleteOldRead)
  - [x] Evento `NotificationCreated` con `ShouldBroadcast` (canal privado `App.Models.User.{userId}`)
  - [x] Laravel Echo configurado en `app.js` (escucha canal privado, dispara evento `notification-received`)
  - [x] Componente `<x-notifications-dropdown>` en navbar (solo no leídas, badge, marcar como leída por AJAX — la notificación desaparece del dropdown)
  - [x] Vista `notifications/index.blade.php` (agrupada por fecha, filtros todas/no leídas, acciones rápidas)
  - [x] `NotificationController` (index, unreadCount AJAX, markAsRead, markAllAsRead)
  - [x] Rutas: `GET /notifications`, `GET /notifications/unread-count`, `POST /notifications/{notification}/read`, `POST /notifications/read-all`
  - [x] JavaScript `notifications.js` (actualizar contador, mostrar toast, escuchar eventos WebSocket)
  - [x] Componente toast para nuevas notificaciones (Bootstrap toast, auto-ocultar 6 segundos)
  - [x] Comando de prueba `php artisan notifications:test` (crear notificaciones de prueba)
  - [x] Variables globales en layouts (userId, reverbAppKey, reverbHost, etc.) para frontend
- [x] **Fase 2: Invitaciones a grupos (aceptar/rechazar)** ✅
  - [x] Modificado `GroupController::inviteMember()` (crea con `status = 'invited'`, crea notificación)
  - [x] Métodos `acceptInvitation`, `rejectInvitation`, `resendInvitation` en `GroupController`
  - [x] Rutas: `POST /groups/{group}/invitations/accept`, `POST /groups/{group}/invitations/reject`, `POST /groups/{group}/invitations/{user}/resend`
  - [x] Pestaña "Invitaciones Pendientes" en `groups/index.blade.php` (tabs Bootstrap)
  - [x] Notificaciones al invitar, aceptar y rechazar (vía `NotificationService`)
  - [x] Acciones rápidas desde notificaciones (botones aceptar/rechazar en dropdown y página)
  - [x] `GroupController::index()` actualizado para pasar `pendingInvitations` y `activeTab`
- [x] **Fase 3: Solicitudes de acceso a grupos (solicitar, aprobar/rechazar)** ✅
  - [x] Migración `create_group_access_requests_table` (group_id, user_id, organization_id, status, message, etc.)
  - [x] Modelo `GroupAccessRequest` (relaciones, scopes, métodos accept/reject)
  - [x] `GroupAccessRequestService` (createRequest, acceptRequest, rejectRequest, getPendingRequestsForGroup, etc.)
  - [x] `GroupAccessRequestController` (store, accept, reject, index, pendingForGroup)
  - [x] Rutas: `POST /groups/{group}/access-requests`, `POST /groups/{group}/access-requests/{accessRequest}/accept`, `POST /groups/{group}/access-requests/{accessRequest}/reject`, `GET /groups/access-requests`, `GET /groups/{group}/access-requests/pending`
  - [x] Pestaña "Grupos Disponibles" en `groups/index.blade.php` (lista, botón solicitar acceso, badge pendiente)
  - [x] Pestaña "Solicitudes" para owners/admins (lista de solicitudes pendientes, botones aceptar/rechazar)
  - [x] Notificaciones al solicitar, aceptar y rechazar (vía `NotificationService`)
  - [x] `GroupController::index()` actualizado para pasar `availableGroups` y `pendingAccessRequests`
- [x] **Fase 4: Notificaciones de compartición y creación de items** ✅
  - [x] Modificado `ShareService::shareWithUser()` (crea notificación `item_shared_user` para el usuario con quien se comparte)
  - [x] Modificado `ShareService::shareWithGroup()` (crea notificaciones `item_shared_group` para todos los miembros activos del grupo, excepto el que comparte)
  - [x] Modificado `GroupController::itemsStore()` (crea notificaciones `item_created_in_group` para todos los miembros activos del grupo, excepto el creador)
  - [x] Action URLs y labels configurados: `item_shared_user`/`item_shared_group` → `route('vault.show', $item)`, `item_created_in_group` → `route('groups.items.index', $group)`
  - [x] Iconos y colores ya configurados en vistas de notificaciones (icono `bi-share`, color `text-info`)
  - [x] Acciones rápidas ya implementadas en dropdown y página de notificaciones (botones según `action_label`)
- [x] **Documentación:** Módulo `documentacion/11-notificaciones-invitaciones/` creado. Ver [`plan-implementacion.md`](documentacion/11-notificaciones-invitaciones/plan-implementacion.md) para detalles.

### 32. Extensión de navegador (MVP base) ✅
- [x] API para extensión: `ExtensionApiController` con `items()` y `show()`; rutas `GET /api/extension/vault/items`, `GET /api/extension/vault/items/{id}` (auth + ensure-password-changed); reutiliza `VaultService` y `VaultItemPolicy`.
- [x] CORS para extensión: middleware `ExtensionCorsMiddleware` (origen `chrome-extension://...`, credenciales).
- [x] Carpeta `browser-extension/`: Manifest V3, popup (lista, búsqueda, copiar usuario/contraseña), content script (detección de login y botón "Rellenar con OCIANN Vault").
- [x] Página **Integraciones** (`/integrations`), entrada en la navegación, tarjeta extensión Chrome con versión e instalación manual.
- [x] `public/integrations/browser-extension.json` con versión 1.0.0. Ver [`documentacion/12-extension-navegador/`](documentacion/12-extension-navegador/).

---

## 🚧 En Progreso

### Actualmente trabajando en:
- _(Nada activo; próximo foco en mejoras de seguridad, import/export o tests.)_

---

## 📋 Pendiente

### 19. Sistema de Administración (Nuevo Módulo)

#### 19.1. Roles y Permisos de Administración ✅
- [x] Migración: Agregar campo `role` a tabla `users` (enum: `user`, `admin`, `super_admin`)
- [x] Modelo `User`: Métodos helper `isAdmin()`, `isSuperAdmin()`, `isUser()`, `canManageAdmins()`
- [x] Middleware `AdminMiddleware` para proteger rutas de admin
- [x] Policy `AdminPolicy` para autorización granular
- [x] Seeder para crear primer super admin
- [x] Actualizar `VaultItemPolicy` para considerar admins (solo metadatos, NO secretos)
- [x] Selector de layout (toggle admin/normal) en navegación
- [x] Layout `admin-layout.blade.php` para panel de administración
- [x] Componente `AdminLayout` para vistas de admin
- [x] Rutas básicas de administración con prefijo `/admin` y middleware
- [x] Dashboard básico de administración

#### 19.2. Panel de Administración - Gestión de Usuarios ✅
- [x] `AdminUserController` - CRUD completo de usuarios (Fase 1 y 2)
  - [x] `index()` - Lista de usuarios con filtros (rol, estado, búsqueda, ordenamiento)
  - [x] `show()` - Ver perfil completo de usuario con estadísticas
  - [x] `edit()` - Editar información básica de usuario
  - [x] `update()` - Actualizar usuario
  - [x] `destroy()` - Eliminar usuario (soft delete con protección de último super admin)
  - [x] `activate()` / `deactivate()` - Activar/desactivar usuarios
  - [x] `resetPassword()` - Resetear contraseña de usuario
  - [x] `changeRole()` - Cambiar rol de usuario (solo super admin)
  - [x] Estadísticas por usuario (items, grupos, comparticiones, logs)
  - [x] Filtro de estado (activos/inactivos)
  - [x] Estadísticas de usuarios activos/inactivos
- [x] Migración: `add_is_active_to_users_table` - Campo `is_active` con default true
- [x] Modelo `User`: Campo `is_active` en fillable y casts, método helper `isActive()`
- [x] Vista `admin/users/index.blade.php` - Lista de usuarios con filtros, paginación y estadísticas mejoradas
- [x] Vista `admin/users/show.blade.php` - Detalle de usuario con dropdown de acciones y modales
  - [x] Información de grupos sin botones de navegación (solo informativa)
  - [x] Conteo de miembros activos en grupos
- [x] Vista `admin/users/edit.blade.php` - Editar usuario con información de estado
- [x] Form Request: `UpdateUserRequest` - Validación de nombre y email
- [x] Form Request: `ChangeRoleRequest` - Validación y autorización para cambio de rol
- [x] Autenticación: Verificación de `is_active` en `LoginRequest` y `AuthService`
- [x] Rutas: Todas las rutas de gestión avanzada implementadas
- [x] Compatibilidad con modo oscuro/claro en todas las vistas
- [x] Mejoras de UI: Headers mejorados, botones organizados, toggle de tema unificado

#### 19.3. Panel de Administración - Gestión de Contenido ✅
- [x] `AdminVaultController` - Gestión de vault items
  - [x] `index()` - Lista de todos los items (solo metadatos, sin secretos) con filtros y búsqueda global
  - [x] `show()` - Ver detalle de item (sin mostrar secretos) con comparticiones
  - [x] `destroy()` - Eliminar item (con confirmación y logs de auditoría)
  - [x] Búsqueda global implementada en index()
- [x] `AdminGroupController` - Gestión de grupos
  - [x] `index()` - Lista de todos los grupos con filtros y búsqueda
  - [x] `show()` - Ver detalle de grupo con miembros y estadísticas
  - [x] `destroy()` - Eliminar grupo (con confirmación y logs de auditoría)
- [x] Vista `admin/vault/index.blade.php` - Lista de items con filtros, estadísticas y paginación
- [x] Vista `admin/vault/show.blade.php` - Detalle de item con comparticiones (usuarios y grupos)
- [x] Vista `admin/groups/index.blade.php` - Lista de grupos con filtros, estadísticas y paginación
- [x] Vista `admin/groups/show.blade.php` - Detalle de grupo con miembros y estadísticas
- [x] Rutas: Todas las rutas de gestión de contenido implementadas
- [x] Logs de auditoría: Eliminación de items y grupos registrados

#### 19.4. Panel de Administración - Auditoría y Monitoreo
- [x] `AuditController` - Integrado en panel de admin
  - [x] `index()` - Acceso completo a logs con filtros avanzados
  - [x] `export()` - Exportación masiva de logs (CSV)
- [x] Vista `audit/index.blade.php` - Panel de auditoría (usando admin-layout)
- [x] Ruta de exportación en grupo de admin (`admin.audit.export`)
- [x] Dashboard de actividad del sistema ✅
  - [x] Métricas: usuarios activos, items creados, grupos, comparticiones
  - [x] Gráficos de actividad (últimos 30 días) con Chart.js
  - [x] Eventos recientes (items, grupos, usuarios de últimos 7 días)
  - [x] Actividad diaria desglosada por tipo (items, grupos, usuarios, comparticiones)
  - [x] Vista `admin/dashboard/activity.blade.php` con métricas y gráficos interactivos

#### 19.5. Panel de Administración - Reportes de Seguridad
- [x] `AdminSecurityController` - Reportes de seguridad
  - [x] `weakPasswords()` - Items con contraseñas débiles
  - [x] `reusedPasswords()` - Contraseñas reutilizadas
  - [x] `inactiveUsers()` - Usuarios inactivos
  - [x] `failedLogins()` - Intentos de login fallidos
  - [x] `securityReport()` - Reporte general de seguridad
- [x] Vista `admin/security/index.blade.php` - Dashboard de seguridad
- [x] Vista `admin/security/weak-passwords.blade.php` - Reporte de contraseñas débiles
- [x] Vista `admin/security/reused-passwords.blade.php` - Reporte de contraseñas reutilizadas
- [x] Vista `admin/security/inactive-users.blade.php` - Reporte de usuarios inactivos
- [x] Vista `admin/security/failed-logins.blade.php` - Reporte de intentos de login fallidos
- [x] Vista `admin/security/report.blade.php` - Reporte general de seguridad
- [x] Logging de intentos de login fallidos en `LoginRequest`
- [ ] Servicio `SecurityReportService` - Lógica de análisis de seguridad (opcional, lógica actualmente en controlador)

#### 19.6. Panel de Administración - Configuración del Sistema ✅
- [x] `AdminSettingsController` - Configuración del sistema
  - [x] `index()` - Vista de configuración con tabs
  - [x] `update()` - Actualizar configuración general
  - [x] `updateEmail()` - Configuración de email/SMTP
  - [x] `updateSecurity()` - Políticas de seguridad
  - [x] `updateMaintenance()` - Modo de mantenimiento
- [x] Migración: Tabla `system_settings` para configuración
- [x] Modelo `SystemSetting` - Configuración del sistema
- [x] Vista `admin/settings/index.blade.php` - Panel de configuración completo con tabs
- [x] Ruta protegida con middleware `super-admin`
- [x] Middleware `SuperAdminMiddleware` creado y registrado
- [x] Formularios para: General, Email/SMTP, Seguridad, Mantenimiento

#### 19.7. Panel de Administración - Dashboard Principal ✅
- [x] `AdminDashboardController` - Dashboard de administración (véase sección 27)
  - [x] `index()` - Vista principal con métricas y resumen por org/global
  - [x] Métricas: usuarios, vault, grupos, comparticiones; códigos de invitación (org admin)
  - [x] Actividad 30 días, eventos recientes, actividad diaria (gráfico), logs recientes
- [x] Vista `admin/dashboard.blade.php` - Dashboard con tarjetas de métricas, gráficos, eventos recientes y accesos rápidos

#### 19.8. Selector de Layout y Navegación ✅
- [x] Layout `admin-layout.blade.php` específico para administración
- [x] Componente `AdminLayout` para vistas de admin
- [x] Layout `app-layout.blade.php` para usuarios normales
- [x] Rutas de administración en `routes/web.php` (prefijo `/admin`)
- [x] Middleware `admin` aplicado a todas las rutas
- [x] Navegación de administración en `layouts/navigation.blade.php`
- [x] Menú lateral de administración (sidebar) con todas las secciones
- [x] Enlace a panel de admin en dropdown de usuario (solo para admins)
- [x] Toggle de layout eliminado (redundante, acceso directo desde dropdown)

#### 19.9. Servicios y Lógica de Negocio
- [ ] `AdminService` - Lógica de negocio para administración
  - [ ] Métodos para estadísticas
  - [ ] Métodos para reportes
  - [ ] Métodos para gestión de usuarios
- [ ] `SecurityReportService` - Análisis de seguridad
- [ ] `SystemSettingsService` - Gestión de configuración

#### 19.10. Seguridad y Auditoría de Admins
- [ ] Registrar todas las acciones de admin en `audit_logs`
- [ ] Eventos específicos: `AdminActionPerformed`
- [ ] Listener para registrar acciones de admin
- [ ] Requerir 2FA obligatorio para admins (configurable)
- [ ] Rate limiting en acciones sensibles de admin
- [ ] Logs especiales para acciones de admin (marcar en audit_logs)

#### 19.11. Funciones Premium de Gestión de Usuarios (Completas)
- [ ] `activity()` - Ver historial completo de actividad del usuario
- [ ] `clearSessions()` - Limpiar todas las sesiones activas
- [ ] `verifyEmail()` / `unverifyEmail()` - Verificación manual de email
- [ ] `sendNotification()` - Enviar notificación personalizada al usuario
- [ ] `exportData()` - Exportar todos los datos del usuario (GDPR, sin secretos)
- [ ] `userItems()` - Ver lista de items del usuario (solo metadatos)
- [ ] `userGroups()` - Ver grupos del usuario (owner + miembro)
- [ ] `transferOwnership()` - Transferir ownership de items/grupos
- [ ] `suspend()` / `unsuspend()` - Suspensión temporal con auto-reactivación
- [ ] Vistas adicionales para todas estas funciones

### 20. Funciones Premium Avanzadas (Open Source = Todo Incluido)

#### 20.1. Almacenamiento de Archivos
- [ ] Migración: Tabla `file_attachments` para archivos adjuntos
- [ ] Modelo `FileAttachment` con cifrado
- [ ] `FileService` - Gestión de archivos cifrados
- [ ] Adjuntar archivos a vault items (cifrados)
- [ ] Límite de tamaño por archivo (configurable)
- [ ] Límite total de almacenamiento por usuario
- [ ] Vista de archivos adjuntos en items

#### 20.2. Acceso de Emergencia
- [ ] Migración: Tabla `emergency_access` para acceso de emergencia
- [ ] Modelo `EmergencyAccess` - Solicitudes de acceso de emergencia
- [ ] `EmergencyAccessController` - Gestión de acceso de emergencia
- [ ] Solicitar acceso de emergencia a otro usuario
- [ ] Aprobar/rechazar solicitudes
- [ ] Período de espera antes de acceso (días configurables)
- [ ] Notificaciones de acceso de emergencia
- [ ] Vista de contactos de emergencia

#### 20.3. Autenticación Avanzada (2FA Premium)
- [ ] Soporte para YubiKey (WebAuthn/FIDO2)
- [ ] Soporte para Duo Security
- [ ] Soporte para FIDO U2F
- [ ] Múltiples métodos 2FA simultáneos
- [ ] Prioridad de métodos 2FA
- [ ] Backup de métodos 2FA

#### 20.4. Reportes de Seguridad Avanzados
- [ ] Integración con Have I Been Pwned API
- [ ] Detección de contraseñas comprometidas
- [ ] Reporte de salud del vault (Exposed, Reused, Weak Passwords)
- [ ] Análisis de fortaleza de contraseñas (score)
- [ ] Detección de contraseñas similares (Levenshtein)
- [ ] Reporte de dark web monitoring (si se implementa)
- [ ] Exportación de reportes (PDF/CSV)

#### 20.5. Búsqueda y Filtros Avanzados
- [ ] Búsqueda global mejorada (todos los items)
- [ ] Búsqueda por contenido (sin descifrar, solo metadatos)
- [ ] Filtros avanzados combinados
- [ ] Guardar búsquedas favoritas
- [ ] Búsqueda por tags/etiquetas (si se implementa)
- [ ] Búsqueda por fecha de creación/actualización

#### 20.6. Tags y Etiquetas
- [ ] Migración: Tabla `tags` y `vault_item_tags`
- [ ] Modelo `Tag` - Sistema de etiquetas
- [ ] Asignar múltiples tags a items
- [ ] Filtrar por tags
- [ ] Gestión de tags (crear, editar, eliminar)
- [ ] Tags de colores para organización visual
- [ ] Tags compartidos en grupos

#### 20.7. Plantillas de Items
- [ ] Migración: Tabla `item_templates`
- [ ] Modelo `ItemTemplate` - Plantillas predefinidas
- [ ] Crear items desde plantillas
- [ ] Plantillas personalizadas por usuario
- [ ] Plantillas compartidas en grupos
- [ ] Campos pre-rellenados desde plantillas

#### 20.8. Generador de Contraseñas Avanzado
- [ ] Generador mejorado con más opciones
- [ ] Generar contraseñas pronunciables
- [ ] Generar passphrases (frases de contraseña)
- [ ] Historial de contraseñas generadas (opcional)
- [ ] Análisis de fortaleza en tiempo real
- [ ] Sugerencias de mejora de contraseñas

#### 20.9. Historial de Versiones Visual
- [ ] Vista de historial de versiones mejorada
- [ ] Comparación visual entre versiones
- [ ] Restaurar a versión anterior
- [ ] Ver quién hizo cada cambio
- [ ] Timeline visual de cambios

#### 20.10. Compartir Carpetas Completas
- [ ] Compartir carpetas enteras con usuarios/grupos
- [ ] Permisos a nivel de carpeta
- [ ] Items nuevos en carpeta compartida se comparten automáticamente
- [ ] Gestión de compartición de carpetas

#### 20.11. Notificaciones en Tiempo Real
- [ ] Sistema de notificaciones (WebSockets o polling)
- [ ] Notificaciones de comparticiones nuevas
- [ ] Notificaciones de cambios en items compartidos
- [ ] Notificaciones de acceso de emergencia
- [ ] Centro de notificaciones en UI

#### 20.12. Atajos de Teclado
- [ ] Sistema de atajos de teclado globales
- [ ] Crear item rápido (Ctrl+K)
- [ ] Búsqueda rápida (Ctrl+F)
- [ ] Navegación con teclado
- [ ] Personalización de atajos

#### 20.13. Vistas Personalizadas
- [ ] Guardar vistas personalizadas de items
- [ ] Filtros guardados como vistas
- [ ] Compartir vistas con grupos
- [ ] Vistas predeterminadas por tipo de item

#### 20.14. Análisis y Estadísticas Avanzadas
- [ ] Dashboard con gráficos avanzados
- [ ] Estadísticas de uso por usuario
- [ ] Análisis de patrones de uso
- [ ] Reportes de actividad detallados
- [ ] Exportación de estadísticas

### 20. Sistema de Notificaciones por Email
- [ ] Configurar sistema de correo (SMTP/Mailgun/etc)
- [ ] Crear notificaciones para acciones administrativas
  - [ ] Notificación cuando admin resetea contraseña
  - [ ] Notificación cuando se cambia rol de usuario
  - [ ] Notificación cuando se activa/desactiva usuario
  - [ ] Notificación cuando se elimina usuario
- [ ] Plantillas de email para notificaciones
- [ ] Configuración de cola para envío de emails
- [ ] Testing de notificaciones

### 21. Logs de Auditoría por Módulo ✅
**Objetivo:** Agregar logs de auditoría completos a todos los módulos existentes de forma ordenada.

#### 21.1. Módulo Vault ✅
- [x] Logs de creación de items (`VaultController::store()`)
- [x] Logs de actualización de items (`VaultController::update()`)
- [x] Logs de eliminación de items (`VaultController::destroy()`)
- [x] Logs de compartir items con usuarios (`VaultController::share()`)
- [x] Logs de compartir items con grupos (`VaultController::share()`)
- [x] Logs de revocación de compartición (`VaultController::revokeShare()`)

#### 21.2. Módulo Grupos ✅
- [x] Logs de creación de grupos (`GroupController::store()`)
- [x] Logs de edición de grupos (`GroupController::update()`)
- [x] Logs de eliminación de grupos (`GroupController::destroy()`)
- [x] Logs de invitación de miembros (`GroupController::inviteMember()`)
- [x] Logs de eliminación de miembros (`GroupController::removeMember()`)
- [x] Logs de cambio de rol de miembro (`GroupController::updateMemberRole()`)

#### 21.3. Módulo Compartición ✅
- [x] Logs de compartir items con usuarios (`VaultController::share()`)
- [x] Logs de compartir items con grupos (`VaultController::share()`)
- [x] Logs de revocación de compartición (`VaultController::revokeShare()`)

#### 21.4. Módulo Carpetas ✅
- [x] Logs de creación de carpetas (`FolderController::store()` y `GroupController::storeFolder()`)
- [x] Logs de edición de carpetas (`FolderController::update()` y `GroupController::updateFolder()`)
- [x] Logs de eliminación de carpetas (`FolderController::destroy()` y `GroupController::destroyFolder()`)

#### 21.5. Módulo Autenticación ✅
- [x] Logs de login exitoso (`AuthController::login()`)
- [x] Logs de login con 2FA (`AuthController::verifyTwoFactorLogin()`)
- [x] Logs de logout (`AuthController::logout()`)
- [x] Logs de cambio de contraseña (`AuthController::updatePassword()`)
- [x] Logs de habilitación de 2FA (`TwoFactorController::enable()`)
- [x] Logs de deshabilitación de 2FA (`TwoFactorController::disable()`)
- [x] Logs de regeneración de códigos de respaldo (`TwoFactorController::regenerateBackupCodes()`)

#### 21.6. Módulo Administración ✅ (Completado - Gestión de Usuarios)
- [x] Logs de cambio de rol (AdminUserController) - Con validaciones de intentos bloqueados
- [x] Logs de reset de contraseña (AdminUserController)
- [x] Logs de activación de usuarios (AdminUserController)
- [x] Logs de desactivación de usuarios (AdminUserController) - Con validaciones de intentos bloqueados
- [x] Logs de eliminación de usuarios (AdminUserController) - Con validaciones de intentos bloqueados
- [x] Logs de edición de usuarios (AdminUserController) - Con cambios de nombre, email y verificación
- [ ] Logs de acceso al panel de administración (futuro)

### 22. Importación/Exportación
- [ ] Exportación de items (JSON/CSV)
- [ ] Importación desde Bitwarden/1Password
- [ ] Importación desde LastPass
- [ ] Importación desde Chrome/Edge
- [ ] Backup completo del vault (cifrado)
- [ ] Restauración desde backup
- [ ] Backup automático programado
- [ ] Backup incremental

### 23. API REST
- [ ] Endpoints de API completos
- [ ] Autenticación por tokens (API keys)
- [ ] Autenticación OAuth2
- [ ] Documentación con Swagger/OpenAPI
- [ ] Rate limiting para API
- [ ] Webhooks para eventos
- [ ] SDK para desarrolladores

---

## 🎯 Próximos Pasos (Siguiente Sprint)

### Estado: Sistema de Administración (núcleo completado)

**Commits ya completados (resumen):**
- ✅ AdminUserController con métodos avanzados: changeRole(), resetPassword(), activate(), deactivate(), destroy()
- ✅ Form Request ChangeRoleRequest con autorización
- ✅ Migración add_is_active_to_users_table ejecutada
- ✅ Sistema de activación/desactivación de usuarios
- ✅ Verificación de usuarios activos en autenticación
- ✅ Vistas mejoradas con dropdown de acciones y modales
- ✅ Filtros de estado (activos/inactivos) en lista
- ✅ Mejoras de UI: headers organizados, toggle de tema unificado
- ✅ Protecciones de seguridad: último super admin, auto-eliminación
- ✅ Mejoras de UX: Información de grupos solo de lectura (sin navegación) en vista de detalle
- ✅ **Logs de auditoría completos:** Todos los métodos de AdminUserController registran acciones con información del admin, usuario afectado y contexto
- ✅ **Logs de intentos bloqueados:** Se registran intentos fallidos (auto-eliminación, último super admin, etc.) con Log::warning()

**Último Commit Completado: Gestión de Contenido ✅**
- ✅ AdminVaultController creado con index(), show(), destroy()
- ✅ AdminGroupController creado con index(), show(), destroy()
- ✅ Búsqueda global implementada en ambos controladores
- ✅ Vistas completas con filtros, estadísticas y paginación
- ✅ Logs de auditoría en métodos destroy() de ambos controladores
- ✅ Rutas implementadas y funcionando

**Último Commit Completado: Logs de Auditoría en Todos los Módulos ✅**
- ✅ Logs agregados a módulo Vault (crear, actualizar, eliminar, compartir, revocar)
- ✅ Logs agregados a módulo Grupos (crear, editar, eliminar, invitar, remover, actualizar roles)
- ✅ Logs agregados a módulo Compartición (compartir, revocar - integrado en VaultController)
- ✅ Logs agregados a módulo Carpetas (crear, editar, eliminar - personales y de grupo)
- ✅ Logs agregados a módulo Autenticación (login, logout, cambio de contraseña, 2FA habilitar/deshabilitar, regenerar códigos)

**Último Commit Completado: Dashboard de Actividad del Sistema ✅**
- ✅ AdminDashboardController con método activity()
- ✅ Métricas completas: usuarios, items, grupos, comparticiones
- ✅ Actividad de últimos 30 días desglosada
- ✅ Gráfico de actividad diaria con Chart.js (últimos 30 días)
- ✅ Eventos recientes de últimos 7 días (items, grupos, usuarios)
- ✅ Vista completa con diseño responsive

**Próximos focos sugeridos:**
1. **Sistema de Notificaciones por Email** (cuando se configure correo)
   - Notificación cuando admin resetea contraseña
   - Notificación cuando se cambia rol de usuario
   - Notificación cuando se activa/desactiva usuario
   - Notificaciones de seguridad generales

2. **Opcional:** `SecurityReportService` (extraer lógica de reportes de seguridad del controlador)

3. **Futuro:** Importación/Exportación, API REST, funciones premium (19.9–19.11, 20.x)

**Completado recientemente:** Configuración del sistema ✅, Gestión de contenido (Admin Vault/Groups) ✅, Logs de auditoría en todos los módulos ✅, Dashboard de actividad ✅, Sistema de notificaciones (Fases 1–4) ✅.

---

## 📈 Métricas

### Por Módulo

| Módulo | Estado | Progreso |
|--------|--------|----------|
| Setup Inicial | ✅ Completado | 100% |
| Documentación | ✅ Completado | 100% |
| Autenticación | ✅ Completado | 100% |
| Estructura Dominio | ✅ Completado | 100% |
| Migraciones - Grupos | ✅ Completado | 100% |
| Migraciones - Vault Items | ✅ Completado | 100% |
| Migraciones - Compartición | ✅ Completado | 100% |
| Migraciones - Folders | ✅ Completado | 100% |
| Panel de Administración - Fase 1 | ✅ Completado | 100% |
| Panel de Administración - Fase 2 | ✅ Completado | 100% |
| UI y Navegación | ✅ Completado | 100% |
| Servicios Core | ✅ Completado | 100% |
| Policies | ✅ Completado | 100% |
| Controladores | ✅ Completado | 100% |
| Vistas | ✅ Completado | 100% |
| Compartición | ✅ Completado | 100% |
| Grupos | ✅ Completado | 100% |
| Carpetas | ✅ Completado | 100% |
| TOTP/2FA | ✅ Completado | 100% |
| Dashboard | ✅ Completado | 100% |
| Auditoría | ✅ Completado | 100% |
| Sistema de Administración | 🚧 En Progreso | 90% |
| Importación/Exportación | ⏳ Pendiente | 0% |
| API REST | ⏳ Pendiente | 0% |

### Por Funcionalidad MVP

| Funcionalidad | Estado | Prioridad |
|---------------|--------|-----------|
| Autenticación básica | ✅ Completado | 🔴 Alta |
| 2FA a nivel de usuario | ✅ Completado | 🔴 Alta |
| CRUD Vault Items | ✅ Completado | 🔴 Alta |
| Cifrado de secretos | ✅ Completado | 🔴 Alta |
| Compartición básica | ✅ Completado | 🔴 Alta |
| Gestión de grupos | ✅ Completado | 🔴 Alta |
| Carpetas jerárquicas | ✅ Completado | 🔴 Alta |
| TOTP/2FA (vault items) | ✅ Completado | 🟡 Media |
| 2FA a nivel de usuario | ✅ Completado | 🔴 Alta |
| Dashboard mejorado | ✅ Completado | 🟡 Media |
| Auditoría básica | ✅ Completado | 🔴 Alta |
| Sistema de Administración | 🚧 En Progreso | 🔴 Alta |
| Importación/Exportación | ⏳ Pendiente | 🟡 Media |
| API REST | ⏳ Pendiente | 🟢 Baja |

---

## 🔄 Historial de Cambios

### 2026-01-21
- ✅ Setup inicial de Laravel completado
- ✅ Bootstrap instalado y configurado
- ✅ Layout minimalista implementado
- ✅ Estructura de documentación creada
- ✅ Reglas de diseño, documentación y código establecidas
- ✅ Laravel Breeze instalado y configurado
- ✅ Sistema de autenticación completo con Bootstrap minimalista
- ✅ Vistas de login, register, recuperación de contraseña personalizadas
- ✅ Dashboard y perfil de usuario implementados
- ✅ Modo oscuro/claro con detección automática
- ✅ Estructura de directorios simplificada (Models, Services, Policies)
- ✅ Migraciones de grupos ejecutadas
- ✅ Modelos Group y GroupMember con relaciones completas

### 2026-01-22
- ✅ Bootstrap JS importado correctamente
- ✅ Barra de navegación funcional con dropdowns operativos
- ✅ Tarjetas del dashboard con funcionalidad y enlaces
- ✅ Rutas placeholder para Vault, Carpetas y Grupos
- ✅ Vistas placeholder creadas
- ✅ Migraciones de Vault Items ejecutadas
- ✅ Modelos VaultItem, VaultItemSecret, VaultItemVersion creados
- ✅ VaultController completo con CRUD
- ✅ VaultService implementado
- ✅ Form Requests con validaciones por tipo
- ✅ Vistas de Vault Items con campos personalizados
- ✅ Filtros AJAX sin recargar página
- ✅ Migraciones de compartición y folders ejecutadas
- ✅ Modelos ItemShareUser, ItemShareGroup, Folder creados
- ✅ CryptoService implementado (cifrado AES-256-CBC)
- ✅ ShareService implementado
- ✅ FolderService implementado
- ✅ TotpService implementado
- ✅ VaultItemPolicy implementado
- ✅ Sistema de compartición completo
- ✅ Gestión de grupos completa
- ✅ Carpetas jerárquicas integradas
- ✅ TOTP/2FA con QR codes para vault items
- ✅ Dashboard mejorado
- ✅ Vault de grupo con items y carpetas
- ✅ Vista de administración de grupos separada
- ✅ Flujo optimizado: entrada a grupo → vault primero
- ✅ 2FA a nivel de usuario (TOTP) implementado
- ✅ Integración de 2FA en flujo de login
- ✅ TwoFactorService y TwoFactorController creados
- ✅ Gestión de códigos de respaldo
- ✅ Avatar de usuario implementado
- ✅ Dashboard con estadísticas corregidas (excluyendo items eliminados)
- ✅ Navegación mejorada con indicadores de 2FA
- ✅ Sistema de roles implementado (user, admin, super_admin)
- ✅ Middleware y policies de administración
- ✅ Layout de administración con sidebar
- ✅ Dashboard básico de administración
- ✅ Vistas placeholder para todas las secciones de admin
- ✅ Integración de auditoría en panel de admin
- ✅ Acceso al panel desde dropdown de usuario

### 2026-01-25
- ✅ **Fase 1: Sistema Base de Notificaciones completado**
  - Migración `create_notifications_table` (user_id, organization_id, type, title, message, data JSON, read, read_at, action_url, action_label)
  - Modelo `Notification` (relaciones, scopes, métodos markAsRead/markAsUnread)
  - `NotificationService` (create, markAsRead, markAllAsRead, getUnreadCount, getNotifications)
  - Evento `NotificationCreated` con `ShouldBroadcast` (canal privado `App.Models.User.{userId}`)
  - Laravel Echo configurado (escucha canal privado, dispara evento `notification-received`)
  - Componente `<x-notifications-dropdown>` en navbar (icono con badge, últimas 10, acciones)
  - Vista `notifications/index.blade.php` (agrupada por fecha, filtros, acciones rápidas)
  - `NotificationController` (index, unreadCount AJAX, markAsRead, markAllAsRead)
  - Rutas de notificaciones (GET /notifications, GET /notifications/unread-count, POST /notifications/{notification}/read, POST /notifications/read-all)
  - JavaScript `notifications.js` (actualizar contador, mostrar toast, eventos WebSocket)
  - Toast notifications (Bootstrap toast, auto-ocultar 6 segundos)
  - Comando de prueba `php artisan notifications:test`
  - Variables globales en layouts para frontend (userId, reverbAppKey, etc.)
  - Laravel Reverb instalado y configurado (agregado a `composer dev`)
- ✅ **Fase 2: Invitaciones a Grupos (Aceptar/Rechazar) completado**
  - Modificado `GroupController::inviteMember()` (crea `GroupMember` con `status = 'invited'`, crea notificación `group_invitation`)
  - Métodos `acceptInvitation`, `rejectInvitation`, `resendInvitation` en `GroupController`
  - Rutas: `POST /groups/{group}/invitations/accept`, `POST /groups/{group}/invitations/reject`, `POST /groups/{group}/invitations/{user}/resend`
  - Pestaña "Invitaciones Pendientes" en `groups/index.blade.php` (tabs Bootstrap)
  - Notificaciones al invitar, aceptar y rechazar (vía `NotificationService`)
  - Acciones rápidas desde notificaciones (botones aceptar/rechazar en dropdown y página)
  - `GroupController::index()` actualizado para pasar `pendingInvitations` y `activeTab`
- ✅ **Fase 3: Solicitudes de Acceso a Grupos completado**
  - Migración `create_group_access_requests_table` (group_id, user_id, organization_id, status, message, requested_at, responded_at, responded_by_user_id)
  - Modelo `GroupAccessRequest` (relaciones group, user, organization, respondedBy, scopes pending/accepted/rejected/forOrganization, métodos accept/reject)
  - `GroupAccessRequestService` (createRequest, acceptRequest, rejectRequest, getPendingRequestsForGroup, getPendingRequestsForUser, hasPendingRequest, getPendingRequestsForUserGroups)
  - `GroupAccessRequestController` (store, accept, reject, index, pendingForGroup)
  - Rutas: `POST /groups/{group}/access-requests`, `POST /groups/{group}/access-requests/{accessRequest}/accept`, `POST /groups/{group}/access-requests/{accessRequest}/reject`, `GET /groups/access-requests`, `GET /groups/{group}/access-requests/pending`
  - Pestaña "Grupos Disponibles" en `groups/index.blade.php` (lista de grupos donde usuario no es miembro, botón solicitar acceso, badge pendiente, botón volver a solicitar si rechazada)
  - Pestaña "Solicitudes" para owners/admins (lista de solicitudes pendientes de grupos que administra, botones aceptar/rechazar con modales)
  - Notificaciones al solicitar (`group_access_request` para owners/admins), aceptar (`group_access_request_accepted` para solicitante), rechazar (`group_access_request_rejected` para solicitante)
  - `GroupController::index()` actualizado para pasar `availableGroups` y `pendingAccessRequests`
- ✅ **Fase 4: Notificaciones de Compartición y Creación de Items completado**
  - Modificado `ShareService::shareWithUser()` (crea notificación `item_shared_user` para el usuario con quien se comparte, action_url a `route('vault.show', $item)`)
  - Modificado `ShareService::shareWithGroup()` (crea notificaciones `item_shared_group` para todos los miembros activos del grupo excepto el que comparte, action_url a `route('vault.show', $item)`)
  - Modificado `GroupController::itemsStore()` (crea notificaciones `item_created_in_group` para todos los miembros activos del grupo excepto el creador, action_url a `route('groups.items.index', $group)`)
  - Iconos y colores configurados en vistas de notificaciones (icono `bi-share`, color `text-info` para tipos de items)
  - Acciones rápidas ya implementadas en dropdown y página de notificaciones (botones según `action_label`)
  - Pestaña "Invitaciones Pendientes" en `groups/index.blade.php` (tabs Bootstrap: Mis Grupos, Invitaciones Pendientes, Grupos Disponibles)
  - Notificaciones al invitar (`group_invitation`), aceptar (`group_invitation_accepted`), rechazar (`group_invitation_rejected`)
  - Acciones rápidas desde notificaciones (botones aceptar/rechazar en dropdown y página `/notifications`)
  - `GroupController::index()` actualizado para pasar `pendingInvitations` y `activeTab`
  - Migración `create_notifications_table` (user_id, organization_id, type, title, message, data JSON, read, read_at, action_url, action_label)
  - Modelo `Notification` (relaciones, scopes, métodos markAsRead/markAsUnread)
  - `NotificationService` (create, markAsRead, markAllAsRead, getUnreadCount, getNotifications)
  - Evento `NotificationCreated` con `ShouldBroadcast` (canal privado `App.Models.User.{userId}`)
  - Laravel Echo configurado (escucha canal privado, dispara evento `notification-received`)
  - Componente `<x-notifications-dropdown>` en navbar (icono con badge, últimas 10, acciones)
  - Vista `notifications/index.blade.php` (agrupada por fecha, filtros, acciones rápidas)
  - `NotificationController` (index, unreadCount AJAX, markAsRead, markAllAsRead)
  - Rutas de notificaciones (GET /notifications, GET /notifications/unread-count, POST /notifications/{notification}/read, POST /notifications/read-all)
  - JavaScript `notifications.js` (actualizar contador, mostrar toast, eventos WebSocket)
  - Toast notifications (Bootstrap toast, auto-ocultar 6 segundos)
  - Comando de prueba `php artisan notifications:test`
  - Variables globales en layouts para frontend (userId, reverbAppKey, etc.)
  - Laravel Reverb instalado y configurado (agregado a `composer dev`)

### 2026-01-23
- ✅ Corrección del flujo de 2FA (preservación de token CSRF durante logout)
- ✅ Manejo mejorado de errores 419 (CSRF token expired)
- ✅ Eliminación de logs de depuración excesivos
- ✅ Simplificación de JavaScript en formulario de 2FA
- ✅ Código limpio y optimizado para producción
- ✅ Vistas placeholder para panel de administración (users, vault, groups, security, settings)
- ✅ Corrección de `AuditController` (eliminación de middleware en constructor)
- ✅ Actualización de vista de auditoría para usar `admin-layout`
- ✅ Agregada ruta de exportación de auditoría en grupo de admin
- ✅ Corrección de ruta de settings (verificación directa de super admin)
- ✅ Eliminación del toggle de layout (redundante)
- ✅ Documentación actualizada en README sobre acceso como super admin

---

## 📝 Notas

- Este documento es técnico y se enfoca en la implementación (código, migraciones, servicios)
- Debe actualizarse después de cada commit importante
- Marcar como completado cuando una funcionalidad esté 100% terminada y probada
- Actualizar métricas al finalizar cada sprint
- Mantener sincronizado con `ORDEN-COMMITS.md`
- Para plan de producto y features futuras, ver `ROADMAP.md`

---

## 🎨 Leyenda

- ✅ Completado
- 🚧 En Progreso
- ⏳ Pendiente
- 🔴 Alta Prioridad
- 🟡 Media Prioridad
- 🟢 Baja Prioridad
