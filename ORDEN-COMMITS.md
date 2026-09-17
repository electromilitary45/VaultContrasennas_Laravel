# Orden Recomendado de Commits - PassVault

Este documento describe el orden recomendado para hacer commits incrementales y valiosos durante el desarrollo del proyecto.

## ✅ Commits Completados

### Commit 1: Instalación base de Laravel ✅
**Estado:** Completado
- Instalación completa del framework Laravel 12.48.1
- Estructura de directorios estándar
- Archivos de configuración base

### Commit 2: Configuración inicial del entorno ✅
**Estado:** Completado
- Configuración `.env` con conexión MySQL
- Generación de `APP_KEY`
- Configuración de base de datos `vault_contrasenna`
- Actualización de `APP_NAME` a "PassVault"

### Commit 3: Instalación y configuración de Bootstrap ✅
**Estado:** Completado
- Desinstalación de Tailwind CSS
- Instalación de Bootstrap 5.3.8 y @popperjs/core
- Configuración de Vite para Bootstrap
- Actualización de `resources/css/app.css` y `resources/js/app.js`
- Actualización de `vite.config.js`

### Commit 4: Migraciones base de Laravel ✅
**Estado:** Completado
- Ejecución de migraciones base (users, cache, jobs)
- Verificación de conexión MySQL

### Commit 5: Layout minimalista y reglas de diseño ✅
**Estado:** Completado
- Creación de archivo de reglas de diseño (`.cursor/rules/design-rules.md`)
- Actualización de `welcome.blade.php` con estilo minimalista tipo Apple
- Uso exclusivo de clases Bootstrap (sin CSS personalizado)

### Commit 6: Sistema de autenticación ✅
**Estado:** Completado
- Instalación de Laravel Breeze v2.3.8
- Ejecución de `php artisan breeze:install blade`
- Personalización completa de vistas con Bootstrap minimalista tipo Apple
- Layouts actualizados: `guest.blade.php` y `app.blade.php`
- Vistas de autenticación: login, register, forgot-password, reset-password, verify-email, confirm-password
- Componentes actualizados: text-input, primary-button, input-label, input-error, auth-session-status
- Vistas de perfil actualizadas con Bootstrap
- Rutas de autenticación configuradas en `routes/auth.php`
- Navigation bar con Bootstrap minimalista

**Archivos creados/modificados:**
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/auth/*.blade.php` (todas las vistas)
- `resources/views/components/*.blade.php` (componentes actualizados)
- `resources/views/profile/*.blade.php` (vistas de perfil)
- `routes/auth.php`

---

## 📋 Próximos Commits Recomendados

---

### Commit 7: Estructura de directorios del dominio ✅
**Estado:** Completado
**Descripción:** Crear estructura de directorios simplificada
- ✅ Crear `app/Services/` - Lógica de negocio
- ✅ Crear `app/Policies/` - Autorización
- ✅ `app/Models/` - Ya existe (modelos Eloquent)
- ✅ `app/Http/Controllers/` - Ya existe (controladores)
- ✅ Autoloading configurado automáticamente por Composer

**Estructura final:**
```
app/
├── Models/          # Modelos Eloquent (User, VaultItem, Group, etc.)
├── Services/        # Lógica de negocio (VaultService, ShareService, etc.)
├── Policies/        # Autorización (VaultItemPolicy, GroupPolicy, etc.)
└── Http/
    ├── Controllers/ # Controladores delgados
    └── Requests/    # Form Requests para validación
```

---

### Commit 8: Migraciones del dominio - Grupos ✅
**Estado:** Completado
**Descripción:** Crear migraciones para grupos y miembros
- ✅ `create_groups_table` - Tabla de grupos con owner, name, description
- ✅ `create_group_members_table` - Tabla de miembros con roles y estados
- ✅ Migraciones ejecutadas exitosamente
- ✅ Modelo `Group` con relaciones y métodos helper
- ✅ Modelo `GroupMember` con relaciones y métodos de autorización
- ✅ Relaciones agregadas al modelo `User`

**Archivos creados:**
- `database/migrations/2026_01_21_225414_create_groups_table.php`
- `database/migrations/2026_01_21_225422_create_group_members_table.php`
- `app/Models/Group.php`
- `app/Models/GroupMember.php`

**Archivos modificados:**
- `app/Models/User.php` - Agregadas relaciones con grupos

---

### Commit 8.5: Navegación funcional y rutas del dashboard ✅
**Estado:** Completado
**Descripción:** Corregir funcionalidad de navegación y agregar enlaces al dashboard
- ✅ Importar Bootstrap JS correctamente en `bootstrap.js`
- ✅ Agregar funcionalidad a tarjetas del dashboard (enlaces clicables)
- ✅ Crear rutas placeholder para Vault, Carpetas y Grupos
- ✅ Crear vistas placeholder con mensajes informativos

**Archivos modificados:**
- `resources/js/bootstrap.js` - Importación de Bootstrap JS
- `resources/views/dashboard.blade.php` - Tarjetas con enlaces funcionales
- `routes/web.php` - Rutas placeholder agregadas

**Archivos creados:**
- `resources/views/vault/create.blade.php` - Vista placeholder
- `resources/views/folders/index.blade.php` - Vista placeholder
- `resources/views/groups/index.blade.php` - Vista placeholder

---

### Commit 9: Migraciones del dominio - Vault Items ✅
**Estado:** Completado
**Descripción:** Crear migraciones para vault items y secretos
- ✅ `create_vault_items_table` - Ejecutada
- ✅ `create_vault_item_secrets_table` - Ejecutada
- ✅ `create_vault_item_versions_table` - Ejecutada
- ✅ Migraciones ejecutadas exitosamente
- ✅ Modelos: `VaultItem`, `VaultItemSecret`, `VaultItemVersion` - Creados con relaciones
- ✅ Relaciones Eloquent configuradas
- ✅ Relaciones agregadas al modelo `User`

**Archivos creados:**
- `database/migrations/2026_01_21_232258_create_vault_items_table.php`
- `database/migrations/2026_01_21_232309_create_vault_item_secrets_table.php`
- `database/migrations/2026_01_21_232315_create_vault_item_versions_table.php`
- `app/Models/VaultItem.php`
- `app/Models/VaultItemSecret.php`
- `app/Models/VaultItemVersion.php`

**Archivos modificados:**
- `app/Models/User.php` - Agregadas relaciones con vault items

---

### Commit 10: Migraciones del dominio - Compartición y Folders
**Descripción:** Crear migraciones para compartición y carpetas
- `create_item_shares_users_table`
- `create_item_shares_groups_table`
- `create_folders_table`
- Ejecutar migraciones
- Crear modelos básicos: `ItemShareUser`, `ItemShareGroup`, `Folder`

---

### Commit 11: Migraciones del dominio - Auditoría
**Descripción:** Crear migración para logs de auditoría
- `create_audit_logs_table`
- Ejecutar migración
- Crear modelo: `AuditLog`

---

### Commit 12: Relaciones entre modelos
**Descripción:** Definir relaciones Eloquent entre modelos
- Relaciones en `User`, `Group`, `GroupMember`
- Relaciones en `VaultItem`, `VaultItemSecret`, `VaultItemVersion`
- Relaciones en `ItemShareUser`, `ItemShareGroup`
- Relaciones en `Folder`
- Relaciones en `AuditLog`

---

### Commit 13: Servicio de cifrado (CryptoService)
**Descripción:** Implementar servicio básico de cifrado
- Crear `app/Services/CryptoService.php`
- Implementar métodos de cifrado/descifrado
- Usar `Crypt` facade de Laravel
- Tests básicos

---

### Commit 14: VaultService - CRUD básico
**Descripción:** Implementar servicio para gestión de vault items
- Crear `app/Services/VaultService.php`
- Implementar métodos: `create`, `update`, `delete`, `find`
- Integración con CryptoService
- Manejo de versiones básico

---

### Commit 15: Policies de autorización
**Descripción:** Crear policies para control de acceso
- Crear `app/Policies/VaultItemPolicy.php`
- Implementar métodos: `view`, `update`, `delete`, `share`
- Lógica de permisos (owner, share directo, share por grupo)
- Registrar policies en `AuthServiceProvider`

---

### Commit 16: Controladores - VaultController
**Descripción:** Crear controlador para vault items
- Crear `app/Http/Controllers/VaultController.php`
- Implementar métodos: `index`, `show`, `create`, `store`, `edit`, `update`, `destroy`
- Crear Form Requests: `StoreVaultItemRequest`, `UpdateVaultItemRequest`
- Rutas en `routes/web.php`

---

### Commit 17: Vistas - Lista de Vault Items
**Descripción:** Crear vista para listar vault items
- Crear `resources/views/vault/index.blade.php`
- Tabla/listado minimalista con Bootstrap
- Filtros básicos (tipo, favoritos)
- Búsqueda básica

---

### Commit 18: Vistas - Crear/Editar Vault Item
**Descripción:** Crear vistas para crear y editar items
- Crear `resources/views/vault/create.blade.php`
- Crear `resources/views/vault/edit.blade.php`
- Formulario minimalista con Bootstrap
- Validación en frontend básica

---

### Commit 19: Vistas - Detalle de Vault Item
**Descripción:** Crear vista de detalle con controles de seguridad
- Crear `resources/views/vault/show.blade.php`
- Botón mostrar/ocultar contraseña
- Historial de versiones básico
- Información de compartición

---

### Commit 20: ShareService - Compartición básica
**Descripción:** Implementar servicio de compartición
- Crear `app/Services/ShareService.php`
- Métodos: `shareToUser`, `shareToGroup`, `revokeShare`
- Validación de permisos

---

### Commit 21: Controladores y Vistas - Compartición
**Descripción:** Implementar UI para compartición
- Métodos en `VaultController` para compartir
- Vistas/modales para compartir
- Lista de usuarios/grupos con acceso

---

### Commit 22: GroupController y Vistas
**Descripción:** Implementar gestión de grupos
- Crear `app/Http/Controllers/GroupController.php`
- CRUD de grupos
- Gestión de miembros
- Vistas minimalistas

---

### Commit 23: AuditService y Events
**Descripción:** Implementar sistema de auditoría
- Crear `app/Services/AuditService.php`
- Crear eventos: `VaultItemCreated`, `VaultItemViewed`, etc.
- Crear listeners para registrar logs
- Registrar eventos y listeners

---

### Commit 24: Vistas - Auditoría
**Descripción:** Crear vistas para consultar logs
- Crear `app/Http/Controllers/AuditController.php`
- Vista de logs por usuario/grupo
- Filtros básicos
- Export CSV básico

---

### Commits posteriores (resumen) ✅

**Gestión de Usuarios - Fase 2:** AdminUserController avanzado (changeRole, resetPassword, activate, deactivate, destroy), migración `is_active`, Form Request ChangeRoleRequest, logs de auditoría en admin.

**Gestión de Contenido Admin:** AdminVaultController y AdminGroupController (index, show, destroy), búsqueda global, vistas con filtros y estadísticas, logs de auditoría.

**Logs de Auditoría en Módulos:** Vault, Grupos, Compartición, Carpetas, Autenticación y Administración con eventos registrados.

**Dashboard de Actividad:** AdminDashboardController::activity(), métricas, gráficos Chart.js, eventos recientes, vista `admin/dashboard/activity`.

**Configuración del Sistema:** AdminSettingsController, tabla `system_settings`, vista admin/settings con tabs (General, Email/SMTP, Seguridad, Mantenimiento), middleware platform-super-admin.

**SaaS Multi-Tenant (Fases 1–6):** Organizaciones, códigos de invitación, registro con código, creación manual de usuarios, scoping por org (vault, grupos, carpetas, auditoría), dashboard admin por org, seguridad/config solo platform.

**Notificaciones (Fases 1–4):** Sistema base (Reverb, dropdown, página), invitaciones a grupos (aceptar/rechazar), solicitudes de acceso a grupos, notificaciones de compartición y creación en grupos.

---

## 📝 Notas Importantes

1. **Hacer commits frecuentes:** Cada commit debe representar una funcionalidad completa y probada
2. **Mensajes descriptivos:** Usar mensajes claros que expliquen qué se hizo
3. **Probar antes de commitear:** Asegurarse de que todo funciona antes de hacer commit
4. **Seguir las reglas de diseño:** Consultar `.cursor/rules/design-rules.md` antes de crear componentes visuales
5. **No mezclar funcionalidades:** Cada commit debe enfocarse en una sola cosa

---

---

### Commit 6.5: Corrección del flujo de 2FA y limpieza de código ✅
**Estado:** Completado
**Descripción:** Corregir problema de token CSRF en flujo de 2FA y limpiar código de depuración
- ✅ Preservación de token CSRF durante flujo de 2FA
- ✅ Eliminación de regeneración innecesaria de token
- ✅ Manejo mejorado de errores 419 (CSRF token expired)
- ✅ Eliminación de logs de depuración excesivos
- ✅ Simplificación de JavaScript en formulario de 2FA
- ✅ Código limpio y listo para producción

**Archivos modificados:**
- `app/Http/Controllers/AuthController.php` - Preservación de token CSRF, eliminación de logs
- `resources/views/auth/two-factor-login.blade.php` - JavaScript simplificado
- `bootstrap/app.php` - Manejo de errores 419 simplificado

---

### Commit 6.6: Vistas placeholder de administración y correcciones ✅
**Estado:** Completado
**Descripción:** Crear vistas placeholder para panel de administración y corregir problemas
- ✅ Vistas placeholder para gestión de usuarios (`admin/users/index.blade.php`)
- ✅ Vistas placeholder para vault items (`admin/vault/index.blade.php`)
- ✅ Vistas placeholder para grupos (`admin/groups/index.blade.php`)
- ✅ Vistas placeholder para seguridad (`admin/security/index.blade.php`)
- ✅ Vistas placeholder para configuración (`admin/settings/index.blade.php`)
- ✅ Corrección de `AuditController` (eliminación de middleware en constructor)
- ✅ Actualización de vista de auditoría para usar `admin-layout`
- ✅ Agregada ruta de exportación en grupo de admin
- ✅ Corrección de ruta de settings (verificación directa de super admin)
- ✅ Eliminación del toggle de layout (redundante, acceso desde dropdown de usuario)
- ✅ Documentación actualizada en README sobre acceso como super admin

**Archivos creados:**
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/vault/index.blade.php`
- `resources/views/admin/groups/index.blade.php`
- `resources/views/admin/security/index.blade.php`
- `resources/views/admin/settings/index.blade.php`

**Archivos modificados:**
- `app/Http/Controllers/AuditController.php` - Eliminado middleware del constructor
- `resources/views/audit/index.blade.php` - Cambiado a admin-layout
- `routes/web.php` - Agregada ruta de exportación de auditoría en admin, corrección de settings
- `resources/views/layouts/navigation.blade.php` - Eliminado toggle de layout
- `resources/views/layouts/admin-layout.blade.php` - Eliminado toggle de layout
- `README.md` - Agregada sección sobre acceso como super admin

**Archivos eliminados:**
- `resources/views/components/layout-toggle.blade.php`

---

### Commit 6.7: Gestión de Usuarios - Fase 1 (CRUD Básico) ✅
**Estado:** Completado
**Descripción:** Implementar CRUD básico de usuarios en panel de administración
- ✅ `AdminUserController` con métodos: `index()`, `show()`, `edit()`, `update()`
- ✅ Vista `admin/users/index.blade.php` - Lista de usuarios con filtros, paginación y estadísticas
- ✅ Vista `admin/users/show.blade.php` - Detalle completo con estadísticas, items recientes, grupos y actividad
- ✅ Vista `admin/users/edit.blade.php` - Formulario de edición con verificación manual de email
- ✅ Form Request `UpdateUserRequest` - Validación de nombre y email
- ✅ Filtros: búsqueda por nombre/email, filtro por rol, ordenamiento
- ✅ Estadísticas generales y por usuario
- ✅ Compatibilidad completa con modo oscuro/claro
- ✅ Rutas agregadas en grupo de admin

**Archivos creados:**
- `app/Http/Controllers/AdminUserController.php`
- `app/Http/Requests/Admin/UpdateUserRequest.php`
- `resources/views/admin/users/show.blade.php`
- `resources/views/admin/users/edit.blade.php`

**Archivos modificados:**
- `resources/views/admin/users/index.blade.php` - Reemplazado placeholder con implementación completa
- `routes/web.php` - Agregadas rutas de gestión de usuarios

**Funcionalidades implementadas:**
- Lista de usuarios con paginación (15 por página)
- Filtros: búsqueda, rol, ordenamiento
- Vista de detalle con estadísticas completas
- Edición de información básica (nombre, email)
- Verificación manual de email desde admin
- Compatibilidad total con modo oscuro/claro

---

**Última actualización:** 2026-02-05 — Sincronización con ROUTE-MAP; añadido resumen de commits posteriores (Fase 2 usuarios, gestión contenido, auditoría, dashboard, configuración, SaaS, notificaciones).
