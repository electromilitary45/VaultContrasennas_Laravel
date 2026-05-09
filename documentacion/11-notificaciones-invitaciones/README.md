# Sistema de Notificaciones e Invitaciones - OCIANN Vault

Documentación del sistema de notificaciones en tiempo real (WebSockets) e invitaciones/solicitudes de acceso a grupos.

**Última actualización:** 2026-02-05

**Estado:** Fase 1 (Sistema Base) ✅ | Fase 2 (Invitaciones a Grupos) ✅ | Fase 3 (Solicitudes de Acceso) ✅ | Fase 4 (Compartición y Creación de Items) ✅ — **TODAS LAS FASES COMPLETADAS**

## 📋 Descripción

Sistema completo de notificaciones internas (no push del navegador) con persistencia en BD, WebSockets para tiempo real, bandeja de notificaciones en navbar y página dedicada. Integrado con invitaciones a grupos (aceptar/rechazar) y solicitudes de acceso a grupos.

## ✨ Funcionalidades

### Sistema de Notificaciones ✅ (Fase 1 Completada)
- **Persistencia en BD**: Todas las notificaciones se guardan en tabla `notifications` ✅
- **Tiempo real**: WebSockets (Laravel Reverb) para recibir notificaciones sin recargar ✅
- **Bandeja en navbar**: Dropdown solo con no leídas (últimas 10), contador, marcar como leída por AJAX (la notificación desaparece de la lista), acciones rápidas ✅
- **Página dedicada**: `/notifications` con lista completa agrupada por fecha ✅
- **Marcar como leída**: Individual o "todas como leídas" ✅
- **Indefinidamente**: Sin expiración automática ✅
- **Toast notifications**: Notificaciones temporales cuando llegan vía WebSocket ✅
- **Comando de prueba**: `php artisan notifications:test` para crear notificaciones de prueba ✅

### Invitaciones a Grupos ✅ (Fase 2 - Completada)
- **Flujo con aceptación**: Owner/admin invita → usuario recibe notificación → debe aceptar/rechazar ✅
- **Sin expiración**: Las invitaciones no expiran, se pueden reenviar ✅
- **Pestaña en `/groups`**: "Invitaciones Pendientes" para ver y gestionar ✅
- **Notificaciones**: Al invitar, al aceptar, al rechazar ✅
- **Acciones rápidas**: Botones aceptar/rechazar desde notificaciones (dropdown y página) ✅

### Solicitudes de Acceso a Grupos ✅ (Fase 3 - Completada)
- **Cualquier usuario puede solicitar**: Acceso a cualquier grupo de su organización ✅
- **Aprobación por owner/admin**: Solo ellos pueden aceptar/rechazar ✅
- **Pestaña en `/groups`**: "Grupos Disponibles" para solicitar, "Solicitudes" para aprobar ✅
- **Notificaciones**: Al crear solicitud (para owner/admin), al aceptar/rechazar (para solicitante) ✅

### Notificaciones de Compartición y Creación ✅ (Fase 4 - Completada)
- **Al compartir item** (usuario→usuario): Notificar al receptor ✅
- **Al compartir item** (usuario→grupo): Notificar a todos los miembros activos ✅
- **Al crear item en grupo**: Notificar a todos los miembros activos (excepto creador) ✅
- **Acciones rápidas**: Botones desde notificaciones para ir directamente al item/grupo ✅

## 🏗️ Arquitectura

### Modelos

#### `Notification`
- `user_id`: Usuario receptor
- `organization_id`: Organización (scoping)
- `type`: Tipo de notificación (enum)
- `title`, `message`: Contenido
- `data`: JSON con datos adicionales (group_id, vault_item_id, etc.)
- `read`, `read_at`: Estado de lectura
- `action_url`, `action_label`: Acción rápida opcional

#### `GroupAccessRequest` (nuevo)
- `group_id`, `user_id`, `organization_id`
- `status`: `pending`, `accepted`, `rejected`
- `message`: Mensaje opcional del solicitante
- `responded_by_user_id`, `responded_at`

### Servicios

#### `NotificationService`
- `create()`: Crear notificación y emitir evento broadcast
- `markAsRead()`: Marcar una como leída
- `markAllAsRead()`: Marcar todas como leídas
- `getUnreadCount()`: Contador de no leídas
- `getNotifications()`: Lista con filtros

#### `GroupAccessRequestService`
- `createRequest()`: Crear solicitud de acceso
- `acceptRequest()`: Aceptar (crea GroupMember, notificaciones)
- `rejectRequest()`: Rechazar (notificaciones)
- `getPendingRequestsForGroup()`: Solicitudes pendientes de un grupo
- `getPendingRequestsForUser()`: Solicitudes del usuario

### WebSockets

- **Laravel Reverb**: Servidor WebSocket self-hosted
- **Laravel Broadcasting**: Eventos `NotificationCreated` con broadcast
- **Laravel Echo (frontend)**: Escucha canal privado del usuario
- **Canal**: `private-organization.{orgId}.user.{userId}` o `private-user.{userId}`

### Tipos de Notificaciones

1. `group_invitation`: Invitación a grupo
2. `group_invitation_accepted`: Invitación aceptada (para inviter)
3. `group_invitation_rejected`: Invitación rechazada (para inviter)
4. `group_access_request`: Solicitud de acceso (para owner/admin)
5. `group_access_request_accepted`: Solicitud aceptada (para solicitante)
6. `group_access_request_rejected`: Solicitud rechazada (para solicitante)
7. `item_shared_user`: Item compartido con usuario
8. `item_shared_group`: Item compartido con grupo (para miembros)
9. `item_created_in_group`: Item creado en grupo (para miembros)

## 🔄 Flujos

### Invitación a Grupo
1. Owner/admin invita usuario → `GroupMember` con `status = 'invited'`
2. Se crea notificación `group_invitation` para el usuario
3. Usuario ve notificación en dropdown/bandeja
4. Usuario acepta → `status = 'active'`, notificación `group_invitation_accepted` para inviter
5. Usuario rechaza → `status = 'revoked'`, notificación `group_invitation_rejected` para inviter

### Solicitud de Acceso
1. Usuario solicita acceso a grupo → `GroupAccessRequest` con `status = 'pending'`
2. Se crea notificación `group_access_request` para todos los owner/admin del grupo
3. Owner/admin ve notificación y solicitud en pestaña
4. Owner/admin acepta → `GroupMember` creado, notificación `group_access_request_accepted` para solicitante
5. Owner/admin rechaza → `status = 'rejected'`, notificación `group_access_request_rejected` para solicitante

### Compartición de Items
1. Usuario comparte item con otro usuario → notificación `item_shared_user`
2. Usuario comparte item con grupo → notificaciones `item_shared_group` para todos los miembros
3. Usuario crea item en grupo → notificaciones `item_created_in_group` para todos los miembros (excepto creador)

## 📍 Rutas

### Notificaciones
- `GET /notifications` → Bandeja completa
- `GET /notifications/unread-count` → Contador (AJAX)
- `POST /notifications/{notification}/read` → Marcar como leída
- `POST /notifications/read-all` → Marcar todas como leídas

### Invitaciones a Grupos
- `POST /groups/{group}/invitations/{invitation}/accept` → Aceptar invitación
- `POST /groups/{group}/invitations/{invitation}/reject` → Rechazar invitación
- `POST /groups/{group}/invitations/{invitation}/resend` → Reenviar invitación

### Solicitudes de Acceso
- `POST /groups/{group}/access-requests` → Crear solicitud
- `POST /groups/{group}/access-requests/{request}/accept` → Aceptar solicitud
- `POST /groups/{group}/access-requests/{request}/reject` → Rechazar solicitud
- `GET /groups/access-requests` → Mis solicitudes
- `GET /groups/{group}/access-requests/pending` → Solicitudes pendientes del grupo (owner/admin)

## 🎨 UI/UX

### Navbar: Dropdown de Notificaciones
- Icono con badge de contador (solo no leídas)
- **Dropdown muestra solo notificaciones no leídas** (últimas 10); las leídas no aparecen en el dropdown
- Al hacer clic en "Marcar como leída" o "Marcar todas como leídas": la petición se envía por **AJAX** y la notificación **desaparece de la lista** al instante (sin recargar); se actualiza el badge
- Cada item: icono, título, mensaje, tiempo, botón "Marcar como leída"
- Footer: "Ver todas las notificaciones", "Marcar todas como leídas" (este último solo si hay no leídas)
- Estado vacío: "No hay notificaciones no leídas" cuando no queda ninguna
- Auto-refresh del contador vía WebSocket

### Página `/notifications`
- Lista agrupada por fecha (hoy, ayer, esta semana, este mes, anterior)
- Filtros: todas / no leídas
- Acciones: marcar como leída, marcar todas
- Botones de acción rápida según `action_url`

### `/groups`: Pestañas
- **"Mis Grupos"**: Lista actual (grupos propios y donde es miembro)
- **"Invitaciones Pendientes"**: Invitaciones recibidas (status = 'invited')
- **"Grupos Disponibles"**: Todos los grupos de la org donde no es miembro (botón "Solicitar Acceso")
- **"Solicitudes Pendientes"**: Solo para owner/admin, solicitudes de sus grupos

### Toast/Banner
- Aparece cuando llega nueva notificación vía WebSocket
- Auto-oculta después de 5-8 segundos
- Click → acción rápida o ir a notificación

## 🔒 Seguridad y Scoping

- **Por organización**: Todas las notificaciones tienen `organization_id`
- **Solo propias**: Usuario solo ve sus notificaciones
- **Platform super admin**: Solo notificaciones globales (si las hay)
- **Canales privados**: WebSocket usa canales privados por usuario/organización

## 📝 Notas de Implementación

### Fase 1 (Completada) ✅
- Sistema base de notificaciones funcionando con WebSockets (Laravel Reverb)
- Notificaciones scoped por `organization_id` (platform super admin tiene `organization_id = null`)
- Contador y dropdown se actualizan en tiempo real vía WebSocket
- Toast notifications aparecen automáticamente cuando llegan nuevas notificaciones
- Comando de prueba disponible: `php artisan notifications:test`

### Fases 2-4 (Completadas) ✅
- Las invitaciones reemplazan el agregar directo: siempre se crea con `status = 'invited'`, el usuario debe aceptar/rechazar
- Las solicitudes de acceso permiten que usuarios soliciten acceso sin ser invitados; owners/admins aprueban/rechazan
- Las notificaciones de compartición con grupo notifican a **todos** los miembros activos (excepto quien comparte)
- Las notificaciones de creación en grupo notifican a **todos** excepto el creador
- WebSockets requieren servidor Reverb corriendo (`php artisan reverb:start` o `composer dev`)

## 📁 Archivos Principales

### Fase 1: Sistema Base de Notificaciones ✅

**Base de Datos:**
- `database/migrations/2026_01_25_162430_create_notifications_table.php` - Migración de tabla notifications

**Modelos:**
- `app/Models/Notification.php` - Modelo de notificaciones

**Servicios:**
- `app/Services/NotificationService.php` - Lógica de negocio de notificaciones

**Eventos:**
- `app/Events/NotificationCreated.php` - Evento broadcast para nuevas notificaciones

**Controladores:**
- `app/Http/Controllers/NotificationController.php` - Controlador de notificaciones

**Rutas:**
- `routes/web.php` - Rutas de notificaciones (grupo `auth`)

**Vistas:**
- `resources/views/components/notifications-dropdown.blade.php` - Dropdown de notificaciones en navbar
- `resources/views/notifications/index.blade.php` - Vista completa de notificaciones

**JavaScript:**
- `resources/js/notifications.js` - Lógica frontend (contador, toast, eventos WebSocket, marcar como leída por AJAX y quitar notificación del dropdown)
- `resources/js/app.js` - Configuración de Laravel Echo

**Layouts:**
- `resources/views/layouts/app.blade.php` - Variables globales para Echo
- `resources/views/layouts/admin-layout.blade.php` - Variables globales para Echo
- `resources/views/layouts/navigation.blade.php` - Incluye `<x-notifications-dropdown>`

**Comandos:**
- `app/Console/Commands/TestNotificationCommand.php` - Comando de prueba (`php artisan notifications:test`)

**Configuración:**
- `config/broadcasting.php` - Configuración de broadcasting (driver: reverb)
- `routes/channels.php` - Canales privados de broadcasting

### Fases 2-4: Completadas ✅

- **Fase 2:** `GroupController` (inviteMember, acceptInvitation, rejectInvitation, resendInvitation), rutas de invitaciones, pestaña "Invitaciones Pendientes" en `groups/index.blade.php`
- **Fase 3:** `GroupAccessRequest`, `GroupAccessRequestService`, `GroupAccessRequestController`, migración `group_access_requests`, rutas de access-requests, pestañas "Grupos Disponibles" y "Solicitudes" en `groups/index.blade.php`
- **Fase 4:** `ShareService` (shareWithUser, shareWithGroup) y `GroupController::itemsStore()` integrados con `NotificationService`; tipos `item_shared_user`, `item_shared_group`, `item_created_in_group`

Ver [`plan-implementacion.md`](plan-implementacion.md) para detalles completos.

## 🔗 Referencias

- [`plan-implementacion.md`](plan-implementacion.md) - Plan detallado por fases (Fases 1-4 ✅ completadas)
- [`configuracion-reverb.md`](configuracion-reverb.md) - Configuración de Reverb (desarrollo)
- [`produccion-reverb.md`](produccion-reverb.md) - Configuración de Reverb en producción (servicios, proxy, seguridad)
- [`verificacion-websockets.md`](verificacion-websockets.md) - Verificación e instalación de WebSockets
- [`../05-grupos/`](../05-grupos/) - Sistema de grupos
- [`../04-comparticion/`](../04-comparticion/) - Sistema de compartición
- [`../09-saas-multi-tenant/`](../09-saas-multi-tenant/) - Scoping por organización
