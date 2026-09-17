# Plan de implementación por fases — SaaS Multi-Tenant

Plan incremental para convertir PassVault en multi-tenant. Cada fase es **probable visual y funcionalmente** antes de seguir.

**Reglas:** `.cursor/rules/code-standards.md`, `design-rules.md`, `documentation-rules.md`. Sin CSS/SCSS personalizado; solo Bootstrap. Logs de auditoría en acciones importantes. Revisión de integración al cerrar cada fase.

**Última actualización:** 2026-01-25  
**Referencias:** [`decisiones-mvp.md`](decisiones-mvp.md), [`decisiones-previas-codigo.md`](decisiones-previas-codigo.md)

---

## Estado de fases

| Fase | Descripción | Estado |
|------|-------------|--------|
| 1 | Modelo de datos: organizations, organization_id, invitation_codes | ✅ Completado |
| 2 | CRUD Organizaciones + super admin de org al crear | ✅ Completado |
| 3 | Códigos de invitación y registro con código | ✅ Completado |
| 4 | Crear usuario manual (org admin) | ✅ Completado |
| 5 | Scoping admin: plataforma vs organización | ✅ Completado |
| 6 | Scoping app: vault, grupos, carpetas por org | ✅ Completado |

---

## Fase 1: Modelo de datos base

**Objetivo:** Tablas y modelos listos. Sin cambios de UI. El sistema sigue funcionando como ahora, con org en contexto.

### 1.1 Migraciones

- [x] `create_organizations_table`: `id`, `name`, `logo` (nullable), `timestamps`.
- [x] `add_organization_id_to_users_table`: `organization_id` (FK nullable). Índice.
- [x] `create_organization_invitation_codes_table`: `id`, `organization_id` (FK), `code` (unique, index), `created_by_user_id` (nullable), `used_at` (nullable), `used_by_user_id` (nullable), `timestamps`.
- [x] `add_organization_id_to_groups_table`: `organization_id` (FK). Índice.
- [x] `add_organization_id_to_vault_items_table`: `organization_id` (FK). Índice.
- [x] `add_organization_id_to_folders_table`: `organization_id` (FK). Índice.
- [x] `add_organization_id_to_audit_logs_table`: `organization_id` (nullable). Índice.

### 1.2 Modelos y relaciones

- [x] `Organization`: `hasMany` users, groups, invitation codes. `fillable`, etc.
- [x] `User`: `belongsTo` Organization. Añadir `organization_id` a `$fillable`.
- [x] `OrganizationInvitationCode`: `belongsTo` Organization, `createdBy` User, `usedBy` User. Scopes: `unused()`, etc.
- [x] `Group`, `VaultItem`, `Folder`, `AuditLog`: `belongsTo` Organization; añadir `organization_id` a `$fillable` donde aplique.

### 1.3 Seed y datos existentes

- [x] SuperAdminSeeder: crear platform super admin con `organization_id = null`. Comprobar que no rompe nada.
- [x] Si hay datos de prueba: decidir si `migrate:fresh` y reseed, o migración de datos a una “org por defecto”. Por decisión actual, solo datos de prueba → `migrate:fresh` aceptable.

### 1.4 Cómo probar

1. `php artisan migrate:fresh` (el SuperAdminSeeder se ejecuta desde la migración `add_role`).
2. Login con super admin: `admin@vault.local` / `admin123`.
3. Verificar en DB: `users.organization_id` NULL para ese usuario (`php artisan tinker` → `User::where('role','super_admin')->first(['organization_id'])`).
4. Dashboard y flujo actual funcionan sin errores (`php artisan route:list`, navegar a `/login`, `/dashboard`).

---

## Fase 2: CRUD Organizaciones y super admin de org

**Objetivo:** El platform super admin puede crear organizaciones. Al crear una, se crea el super admin de esa org y se muestran credenciales una sola vez.

### 2.1 Backend

- [x] `Admin\OrganizationController`: `index`, `create`, `store`, `show`, `edit`, `update` según necesidad. Solo accesible por platform super admin.
- [x] `OrganizationService::createOrganization()` (o ampliar uno existente): `createOrganization(array $data)`: crea org, crea usuario super admin de la org (nombre/email decididos, p. ej. `admin@org-{id}.local` o similar), genera contraseña temporal, guarda y retorna credenciales para mostrar una vez.
- [x] Rutas bajo `admin`, middleware `platform-super-admin` que verifique platform super admin (`organization_id` null + `role` super_admin).
- [x] Form Request crear/editar: `StoreOrganizationRequest`, `UpdateOrganizationRequest`. Validar nombre, logo opcional.

### 2.2 Frontend

- [x] Vista lista de organizaciones (`admin.organizations.index`). Tabla con nombre, logo, acciones.
- [x] Vista formulario “Crear organización”: nombre, logo (opcional). Bootstrap, sin CSS propio.
- [x] Tras crear: pantalla o modal “Credenciales del super admin de la org” (email + contraseña temporal). Mensaje claro: “Guárdalas; no se volverán a mostrar.”
- [x] En el menú/sidebar de admin: enlace “Organizaciones” **solo** si el usuario es platform super admin.

### 2.3 Auditoría y logs

- [x] Log al crear y al actualizar organización (quién creó, org_id, user_id del nuevo admin).

### 2.4 Cómo probar

1. Login como platform super admin.
2. Ir a Organizaciones → Crear “Acme”.
3. Ver mensaje de éxito y credenciales del super admin de Acme. Cerrar/marcharse de esa pantalla.
4. Cerrar sesión, login con esas credenciales.
5. Comprobar que el usuario tiene `organization_id` = id de Acme y `role` super_admin.
6. Listado de organizaciones muestra “Acme”.

---

## Fase 3: Códigos de invitación y registro con código

**Objetivo:** No hay registro abierto. Solo registro con código de invitación. Los org admins pueden generar códigos.

### 3.1 Generación de códigos

- [x] Endpoint o acción “Generar código de invitación” para la org del usuario (org admin). Crear `InvitationCode` con `organization_id`, `created_by_user_id`, `code` único.
- [x] Vista o sección en admin de la org para listar códigos (opcional) y “Generar código”. Mostrar código recién creado para copiar (y quizá URL tipo `/register?code=XXX`).

### 3.2 Registro

- [x] Sustituir/adaptar flujo de registro actual: formulario con código + nombre + email + contraseña (+ confirmación).
- [x] Validación: código existe, no usado (`used_at` null), org existe. Email único global.
- [x] Al registrar: crear usuario con `organization_id` del código, marcar código como usado (`used_at`, `used_by_user_id`), login y redirección al dashboard.
- [x] Quitar registro “abierto” (sin código). Mantener enlace “Registrarse” en login/welcome, pero apuntando a registro con código. Texto tipo “Necesitas un código de invitación de tu organización”.

### 3.3 Auditoría

- [x] Log cuando se genera un código (quién, org, código).
- [x] Log cuando se usa un código en registro (org, usuario creado).

### 3.4 Cómo probar

1. Como org super admin de “Acme”, generar código de invitación.
2. Copiar código, cerrar sesión.
3. Ir a “Registrarse”, pegar código + nombre + email + contraseña. Enviar.
4. Comprobar que se crea usuario en Acme, login correcto y acceso al dashboard.
5. Intentar registrar de nuevo con el mismo código → error (código ya usado).
6. Intentar registrar con código inventado → error.

---

## Fase 4: Crear usuario manual (org admin)

**Objetivo:** El org admin puede crear usuarios manualmente desde el panel, sin código.

### 4.1 Backend

- [x] Ruta `GET /admin/users/create` y `POST /admin/users` para crear usuario. Solo org admin (middleware `org-admin` + `StoreUserRequest`). Controller delega en `UserManagementService`.
- [x] `UserManagementService::createUserManually()`: crear usuario con `organization_id` del admin, rol `user`, contraseña del request.

### 4.2 Frontend

- [x] En lista de usuarios del admin de org: botón “Crear usuario”.
- [x] Formulario: nombre, email, contraseña (+ confirmación). Sin código. Mensaje tipo “El usuario podrá iniciar sesión con este email y contraseña”.

### 4.3 Autorización y auditoría

- [x] Solo org admin: rutas create/store con middleware `org-admin`; `StoreUserRequest::authorize()` verifica `isOrgAdmin()`.
- [x] Log al crear usuario manualmente (quién creó, org, nuevo usuario).

### 4.4 Cómo probar

1. Login como org super admin.
2. Admin → Usuarios → Crear usuario. Rellenar nombre, email, contraseña.
3. Ver nuevo usuario en la lista, con misma org.
4. Cerrar sesión, login con ese usuario → acceso correcto.

---

## Fase 5: Scoping admin (plataforma vs organización)

**Objetivo:** Platform admin ve y gestiona organizaciones y cosas de plataforma. Org admin solo ve y gestiona su org (usuarios, vault, grupos, auditoría).

### 5.1 Middleware / políticas

- [x] Diferenciar “platform super admin” (`organization_id` null) vs “org admin” (tiene `organization_id`).
- [x] Rutas de “Organizaciones”: solo platform super admin.
- [x] Rutas de usuarios/vault/grupos/auditoría en admin: si es org admin, filtrar por `organization_id` del usuario. Si es platform admin, vista global; org admin solo su org.

### 5.2 Cambios en controladores y vistas

- [x] Admin usuarios: listar solo usuarios de la org del admin. Crear usuario manual en su org (ya implementado en Fase 4).
- [x] Admin vault, grupos, auditoría: mismos filtros por org.
- [x] Sidebar/menú: “Organizaciones” solo para platform admin. Resto de enlaces de admin según permisos.

### 5.3 Cómo probar

1. Platform super admin: ve “Organizaciones”, puede crear orgs. Ver que no ve datos de una org concreta como si fuera org admin (o que tiene vista global, según lo definido).
2. Org admin de Acme: no ve “Organizaciones”, solo usuarios/vault/grupos/auditoría de Acme.
3. Crear Org “Beta”, org admin de Beta. Ver que org admin de Acme no ve usuarios de Beta y al revés.

---

## Fase 6: Scoping app (vault, grupos, carpetas)

**Objetivo:** En la app (no solo admin), vault, grupos y carpetas se filtran por `organization_id` del usuario.

### 6.1 Servicios y consultas

- [x] `VaultService`: listItems, findItem y createItem con scope/org; grupos del usuario filtrados por org.
- [x] `FolderService`: listFolders, getRootFolders, getGroupRootFolders, getFoldersForSelect, createFolder, updateFolder, deleteFolder con scope/org.
- [x] Al crear grupos, vault items, carpetas: asignar `organization_id` desde el usuario.

### 6.2 Policies

- [x] `VaultItemPolicy`: view, update, delete, restore, forceDelete, getPermission comprueban org cuando el usuario tiene `organization_id`.
- [x] Grupos: `ensureGroupInOrg` en `GroupController` para todas las acciones que reciben `$group`.

### 6.3 Auditoría

- [x] `AuditService::log` rellena `organization_id` (desde meta o desde el usuario); listeners de vault pasan `organization_id` en meta.

### 6.4 Cómo probar

1. Usuario de Acme: ve solo sus items, grupos y carpetas; no ve nada de Beta.
2. Usuario de Beta: mismo comportamiento para Beta.
3. Crear item en Acme, comprobar que en Beta no aparece y que no se puede acceder por URL directa (403).

---

## Cierre de cada fase

- [x] Revisión de integración (rutas, middlewares, políticas, vistas, logs).
- [x] Actualizar `ROUTE-MAP.md` y documentación del módulo según corresponda.
- [x] Ejecutar `php artisan route:list` y pruebas manuales básicas.

**Notas post-cierre:** Seguridad y Configuración (sidebar + rutas) solo para platform super admin. Dashboard admin sin badge "Vista global" / "Vista de tu organización".

---

## Referencias

- `ORDEN-COMMITS.md` — Orden de commits del proyecto.
- `ROUTE-MAP.md` — Estado de implementación.
- `.cursor/rules/code-standards.md` — Sección 9 (revisión de integración) y auditoría.
