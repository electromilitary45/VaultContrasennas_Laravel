# Decisiones SaaS Multi-Tenant — MVP

Documento de decisiones para convertir OCIANN Vault en una aplicación multi-tenant donde cada **organización/empresa** tiene su propio super admin y su propio espacio de datos, todo bajo una única plataforma.

**Última actualización:** 2026-01-24  
**Estado:** Definición de producto — previo a implementación

---

## 1. Alcance del MVP

- **Incluido:** Multi-tenant + super admin por organización. Sin facturación.
- **Excluido:** Facturación, planes de pago, límites por org, custom domains, invitación por email (queda pendiente).
- **Sin fecha límite** definida para el MVP.

---

## 2. Organizaciones y usuarios

### 2.1 Un usuario, una organización

- **Un usuario pertenece solo a una organización.**
- Si una persona trabaja en Empresa X y Empresa Y, debe tener **dos cuentas separadas** (un usuario por org).
- No hay “cambio de organización” en sesión: el usuario siempre está en su única org.

### 2.2 Identificación de tenant

- **Mismo dominio** para todas las organizaciones (sin subdominios ni rutas por org).
- **Identificación por `organization_id`:** cada usuario tiene `organization_id`; toda la lógica y las consultas se filtran por organización.
- No se usa el dominio del correo (@empresa.com) para determinar la org; la relación explícita usuario → organización es la fuente de verdad.

---

## 3. Creación de organizaciones y onboarding

### 3.1 Quién crea organizaciones

- **Solo el admin de plataforma** (super admin global) puede crear organizaciones/empresas.
- No hay self-signup de organizaciones en el MVP.

### 3.2 Super admin de la organización

- Cuando se crea una organización, **se crea automáticamente** el usuario super admin de esa org.
- Ese usuario es el primero y tiene rol super admin dentro de la organización.
- **Credenciales iniciales:** se generan en el momento de crear la org y **deben enviarse** a la organización (mecanismo por definir: email, entrega manual, etc.).
- **Después del primer envío, las credenciales no se vuelven a mostrar** (por seguridad). El super admin de la org deberá usar “olvidé mi contraseña” o flujo similar si las pierde.
- Pendiente: diseño concreto del envío (email, etc.).

### 3.3 Alta de usuarios dentro de una organización

- **El admin de la organización** (super admin u otros roles con permiso) **crea los usuarios nuevos** de su org.
- **Dos vías:**
  1. **Código de invitación:** El admin genera un código vinculado a la org. El usuario va a `/register`, introduce el código (más nombre, email, contraseña). Se valida que el código sea válido, no usado y corresponda a esa org; se crea el usuario con `organization_id` en oculto/sesión y se marca el código como usado.
  2. **Creación manual:** El admin crea el usuario desde el panel (formulario nombre, email, contraseña). El usuario pertenece a la misma org. Sin código. Envío de credenciales por email queda para más adelante.
- Invitación por email y flujo de activación de cuenta **quedan pendientes** para después del MVP.

---

## 4. Roles y permisos

### 4.1 Nivel plataforma

- **Solo super admin de plataforma** por el momento.
- Creación de organizaciones, gestión de orgs a nivel global, etc.

### 4.2 Nivel organización

- **Misma estructura de roles actual:** super admin, admin, usuarios normales, etc., pero **restringidos a la organización**.
- El super admin de la org puede:
  - Gestionar usuarios de su org.
  - Ver y gestionar auditoría de su org.
  - Gestionar grupos, vault (metadatos), etc. **solo de su org.**
- No ve ni puede actuar sobre datos de otras organizaciones.

---

## 5. Datos y aislamiento

### 5.1 Base de datos

- **Una sola base de datos** para todos los tenants.
- **Aislamiento por `organization_id`:** todas las tablas relevantes tienen `organization_id` (o equivalente vía relación) y **toda consulta se filtra por organización**.
- No se usa DB por tenant ni schema por tenant en el MVP.

### 5.2 Límites

- **Sin límites por organización** en el MVP (usuarios, items, almacenamiento, etc.).
- Se puede dejar documentación o extensión futura para límites por plan.

### 5.3 Datos existentes

- **No hay datos de producción** relevantes; solo datos de prueba.
- Se puede diseñar multi-tenant desde cero sin migración de datos existentes.

---

## 6. Branding (por organización)

- **Logo y nombre** de la organización: cada org puede tener su propio logo y nombre.
- **Fuera del MVP:** más branding (colores, etc.) o custom domains.

---

## 7. Fuera del MVP — Para más adelante

Documentar como **no parte del MVP**, pero como líneas claras para futuras fases:

### 7.1 Facturación y planes

- **Por ahora:** solo free; sin facturación en el MVP.
- **Futuro:**
  - Facturación **por organización** (cada org = cliente de facturación).
  - **Responsable de facturación:** super admin de la org (por el momento).
  - **Facturación manual** al inicio; luego implementar **pasarela propia**.
- Dejar **documentación y/o diseño** preparado para planes, límites y facturación (ver `facturacion-futuro.md`).

### 7.2 Otros

- Invitación por email y activación de cuentas.
- Límites por org (usuarios, items, etc.) asociados a planes.
- Custom domains por organización.

---

## 8. Resumen de decisiones clave

| Tema | Decisión |
|------|----------|
| Usuario ↔ organización | 1 usuario = 1 organización |
| Identificación tenant | `organization_id` en usuario; mismo dominio para todos |
| Quién crea orgs | Solo admin de plataforma |
| Super admin org | Se crea automáticamente al crear la org; credenciales se envían una vez y luego no se muestran |
| Alta de usuarios en org | Código de invitación + registro **o** creación manual por admin |
| Roles plataforma | Solo super admin |
| Roles org | Igual que hoy, acotados a la org |
| Base de datos | Una sola DB; filtrado por `organization_id` |
| Límites | Ninguno en MVP |
| Branding | Logo + nombre por org |
| Facturación | Fuera del MVP; documentar para más adelante |

---

## 9. Referencias

- `documentacion/01-setup-inicial/documentacionInicial.md` — Arquitectura y modelo de seguridad (tenant/usuario en cifrado).
- `documentacion/08-administracion/README.md` — Panel de administración actual.
- `documentacion/09-saas-multi-tenant/decisiones-previas-codigo.md` — Decisiones que hay que cerrar antes de codear.
- `documentacion/09-saas-multi-tenant/plan-implementacion-fases.md` — Plan por fases con hitos probables.
- `ROADMAP.md` — Plan de producto y visión futura.
