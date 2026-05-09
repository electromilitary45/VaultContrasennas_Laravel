# Decisiones previas al código — SaaS Multi-Tenant

Decisiones que **hay que cerrar antes de implementar**. Revisar y confirmar cada una.

**Última actualización:** 2026-01-24  
**Referencia:** [`decisiones-mvp.md`](decisiones-mvp.md), [`plan-implementacion-fases.md`](plan-implementacion-fases.md)

---

## 1. Platform super admin y `organization_id`

- **Decisión:** Usuario con `organization_id` **NULL** = super admin de **plataforma** (no pertenece a ninguna org).
- El SuperAdminSeeder crea el primer usuario con `role = super_admin` y `organization_id = null`.
- Solo estos usuarios pueden crear organizaciones y gestionar la plataforma.

---

## 2. Tabla de códigos de invitación

- **Nombre:** `organization_invitation_codes` (o `invitation_codes`).
- **Campos propuestos:**
  - `id`
  - `organization_id` (FK)
  - `code` (string, único, índice) — por ejemplo 12–16 caracteres alfanuméricos URL-safe.
  - `created_by_user_id` (FK, nullable) — org admin que generó el código.
  - `used_at` (timestamp, nullable) — si es NULL, el código no se ha usado.
  - `used_by_user_id` (FK, nullable) — usuario que se registró con el código.
  - `timestamps`
- **Uso único:** Al registrarse con un código, se actualiza `used_at` y `used_by_user_id`. No se puede reutilizar.

**Pendiente de decidir:**

- **Expiración:** ¿Los códigos expiran? (Ej.: 7 días, 30 días.) Para MVP se puede dejar sin expiración y añadir `expires_at` más adelante.

---

## 3. Formato del código

- **Propuesta:** `Str::random(12)` o similar (alfanumérico, mayúsculas + minúsculas). Sin guiones para evitar confusiones al copiar/pegar.
- **Alternativa:** Código más largo (ej. 16 caracteres) si se prefiere más entropía.

---

## 4. Flujo de registro con código

- **Decisión:** No hay registro público abierto. Solo registro **con código de invitación**.
- **Flujo propuesto (una sola pantalla):**
  1. Usuario va a `/register`.
  2. Formulario: **código de invitación** + nombre + email + contraseña + confirmación.
  3. Al enviar: se valida el código (válido, no usado, pertenece a una org existente). Si falla, se muestra error.
  4. Si el código es válido: se crea el usuario con `organization_id` de la org del código, se marca el código como usado, login y redirección al dashboard.
- **Alternativa:** Dos pasos (primero introducir y validar código, luego mostrar formulario nombre/email/password). Para MVP se recomienda una sola pantalla por simplicidad.

**Pendiente de decidir:**

- ¿Mostrar el **nombre de la organización** tras validar el código (ej. en un paso previo) para que el usuario sepa a qué org se une? Puede hacerse por AJAX al salir del campo “código” o en un segundo paso.

---

## 5. Unicidad del email

- **Decisión:** El email sigue siendo **único a nivel global** en `users`.
- Una misma persona en dos organizaciones = dos cuentas = dos emails distintos (ej. `juan@empresa-a.com`, `juan@empresa-b.com`).

---

## 6. Quién genera códigos de invitación

- **Decisión:** Los **admins de la organización** (super_admin y admin de esa org) pueden generar códigos.
- Los códigos son siempre para **su** organización (la del usuario autenticado).

---

## 7. Creación manual de usuarios

- **Decisión:** El admin de la org puede **crear usuarios manualmente** (formulario en el panel de admin).
- Formulario: nombre, email, contraseña (temporal). El usuario se crea en la **misma organización** que el admin. No se usa código de invitación.
- Envío de credenciales por email **queda pendiente** para más adelante; por ahora el admin las comunica por el canal que corresponda.

---

## 8. Tabla `organizations`

- **Campos mínimos MVP:**
  - `id`
  - `name`
  - `logo` (nullable, string — ruta o URL)
  - `timestamps`
- Opcional para después: `slug`, `billing_contact_user_id`, etc.

---

## 9. `organization_id` en otras tablas

- **`users`:** Sí, `organization_id` (nullable para platform super admins).
- **`groups`:** Sí. Se establece según la org del `owner_user_id` al crear. Facilita listar “todos los grupos de la org”.
- **`vault_items`:** Sí. Misma lógica que grupos.
- **`folders`:** Sí. Misma lógica.
- **`audit_logs`:** Opcional pero recomendable. Añadir `organization_id` (nullable) para filtrar logs por org sin pasar por `user_id`.

Comparticiones (`item_shares_*`) se consideran implícitamente por org vía usuario/grupo; no es obligatorio añadir `organization_id` en MVP.

---

## 10. Enlaces “Registrarse” (welcome, login)

- **Decisión:** Se mantiene el enlace a “Registrarse”, pero la ruta `/register` pasa a ser **solo registro con código**.
- En la vista de registro se explica que se necesita un **código de invitación** de la organización.
- No se muestra formulario de “registro abierto” sin código.

---

## 11. Resumen de pendientes de cerrar

| # | Tema | Opciones | Recomendación MVP |
|---|------|----------|-------------------|
| 1 | Expiración de códigos | Sin expiración / 7 días / 30 días | Sin expiración |
| 2 | Registro: una vs dos pantallas | Una (código + datos) / Dos (validar código → formulario) | Una pantalla |
| 3 | Mostrar nombre de org al validar código | Sí / No | Opcional (mejora UX) |

Una vez confirmadas estas decisiones, se puede seguir el [`plan-implementacion-fases.md`](plan-implementacion-fases.md) sin bloqueos.
