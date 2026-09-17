# Organizaciones - PassVault

Documentación del módulo de **organizaciones** (tenants) en el modelo SaaS multi-tenant.

## Descripción

Las organizaciones son los tenants del sistema. Cada una tiene un **super admin de la organización** (org admin), usuarios, grupos, vault items y carpetas scoped por `organization_id`. Solo el **platform super admin** gestiona organizaciones.

## Quién gestiona organizaciones

- **Platform super admin** (`organization_id` null + `role` super_admin): crea, edita y lista organizaciones. Ve "Organizaciones" en el panel de administración. Puede **regenerar contraseña temporal** del super admin de una org.
- **Org admin**: no ve "Organizaciones"; solo su org (usuarios, vault, grupos, auditoría, códigos de invitación).

## CRUD de organizaciones

- **Listar:** `GET /admin/organizations` → `admin.organizations.index`
- **Crear (formulario):** `GET /admin/organizations/create` → `admin.organizations.create`
- **Crear (envío):** `POST /admin/organizations` → `admin.organizations.store`
- **Ver detalle:** `GET /admin/organizations/{organization}` → `admin.organizations.show`
- **Regenerar contraseña temporal:** `POST /admin/organizations/{organization}/regenerate-password` → `admin.organizations.regenerate-password`
- **Editar (formulario):** `GET /admin/organizations/{organization}/edit` → `admin.organizations.edit`
- **Actualizar:** `PUT /admin/organizations/{organization}` → `admin.organizations.update`

Rutas protegidas por middleware `platform-super-admin`.

## Flujo de creación

1. Platform super admin va a Organizaciones → Crear.
2. Formulario: nombre, logo (opcional). Envío vía `StoreOrganizationRequest`.
3. `OrganizationService::createOrganization()`:
   - Crea la organización (nombre, slug, logo).
   - Genera slug único desde el nombre (ej. "Acme Corp" → `acme-corp`).
   - Crea el **super admin de la org**: email `admin@{slug}.vault.local`, contraseña temporal aleatoria (`Str::random(12)`), `role` super_admin, `organization_id` = org, `email_verified_at` = now, **`must_change_password` = true**.
4. Se muestra la vista **"Organización creada"** (`admin.organizations.created`) con:
   - Email y **contraseña temporal** (solo en esa pantalla).
   - Aviso: guardar credenciales; usar la temporal solo para el primer acceso; se pedirá cambiarla de inmediato. Si se pierden, se puede **regenerar** desde el detalle de la organización.

## Primer login del super admin de la org

- El org admin inicia sesión con email y contraseña temporal.
- Tras login (o tras verificación 2FA si la tuviera), si `must_change_password` es true, se redirige a **`/password/change-required`**.
- Debe elegir una **nueva contraseña** (y confirmarla). No se pide la actual.
- Al guardar, `AuthService::forceChangePassword()` actualiza la contraseña y pone `must_change_password` = false. Redirección al dashboard.
- El middleware **`ensure-password-changed`** impide acceder al resto de la app (dashboard, vault, admin, etc.) hasta cambiar la contraseña. Las rutas de cambio obligatorio y logout quedan excluidas.

## Regenerar contraseña temporal

- En el **detalle de una organización** (`admin.organizations.show`), el platform super admin puede usar el botón **"Regenerar contraseña temporal"**.
- Se genera una nueva contraseña aleatoria, se actualiza el super admin de esa org y se pone de nuevo `must_change_password` = true.
- Se muestra una pantalla tipo "creada" con la nueva contraseña **una sola vez** y el aviso de guardarla. Se registra en logs de auditoría.

## Relación con otros módulos

- **Usuarios:** Cada usuario pertenece a una organización (`organization_id`). Los de la org ven solo usuarios de su org en admin. Ver [`08-administracion`](../08-administracion/).
- **Grupos, vault, carpetas:** Tienen `organization_id`. Todo se filtra por org. Ver [`03-vault-items`](../03-vault-items/), [`04-comparticion`](../04-comparticion/), [`05-grupos`](../05-grupos/).
- **Códigos de invitación:** Por organización. El org admin genera códigos; el registro con código asigna `organization_id` al nuevo usuario. Ver [`09-saas-multi-tenant`](../09-saas-multi-tenant/).
- **Auditoría:** Los logs pueden filtrarse por `organization_id`.

## Archivos principales

- `app/Models/Organization.php` — Modelo organización.
- `app/Services/OrganizationService.php` — Crear org, super admin, slug; regenerar contraseña temporal.
- `app/Http/Controllers/Admin/OrganizationController.php` — CRUD y regenerar contraseña.
- `app/Http/Requests/Admin/StoreOrganizationRequest.php`, `UpdateOrganizationRequest.php` — Validación.
- `resources/views/admin/organizations/` — Index, create, created, show, edit, password-regenerated.
- `app/Http/Middleware/EnsurePasswordChangedMiddleware.php` — Redirige a cambio obligatorio si `must_change_password`.
- Migraciones: `create_organizations_table`, `add_organization_id_to_*`, `add_slug_to_organizations_table`, `add_must_change_password_to_users_table`.

## Referencias

- [`09-saas-multi-tenant/`](../09-saas-multi-tenant/) — Decisiones MVP, plan por fases, códigos de invitación.
- [`08-administracion/`](../08-administracion/) — Panel admin, platform vs org admin, scoping.
