# Plan de Implementación: Sistema de Notificaciones e Invitaciones

**Última actualización:** 2026-01-25

## 📋 Resumen Ejecutivo

Implementación de un sistema completo de notificaciones en tiempo real (WebSockets) e invitaciones/solicitudes de acceso a grupos, integrado con el sistema de compartición de items.

### Objetivos

1. **Sistema de notificaciones interno** (no push del navegador)
   - Persistencia en BD
   - WebSockets para tiempo real
   - Bandeja en navbar (dropdown) + página dedicada
   - Marcar individual y "todas como leídas"
   - Indefinidamente (sin expiración)

2. **Invitaciones a grupos** (reemplaza agregar directo)
   - Solo owner/admin pueden invitar
   - Usuario debe aceptar/rechazar
   - Sin expiración, se puede reenviar
   - Pestaña en `/groups` para invitaciones pendientes
   - Notificar al invitar y al aceptar/rechazar

3. **Solicitudes de acceso a grupos**
   - Cualquier usuario de la organización puede solicitar acceso
   - Solo owner/admin aprueban
   - Todos los grupos visibles para solicitar
   - Sin límites ni cooldown
   - Pestaña en `/groups` para grupos disponibles y solicitudes pendientes
   - Notificar al crear solicitud y al aceptar/rechazar

4. **Notificaciones de compartición y creación**
   - Al compartir item (usuario→usuario, usuario→grupo, grupo→usuario): siempre notificar
   - Al crear item en grupo: notificar a todos los miembros
   - Acciones rápidas desde notificaciones

### Tecnología WebSockets

**Recomendación: Laravel Reverb** (Laravel 12+)
- Self-hosted, sin dependencias externas
- Integrado con Laravel Broadcasting
- Soporte para WebSockets nativo
- Alternativa: Laravel Echo Server (si Reverb no está disponible)

**Stack:**
- Backend: Laravel Broadcasting + Reverb
- Frontend: Laravel Echo (JS) + Bootstrap modals para toasts

---

## 🎯 Fase 1: Sistema Base de Notificaciones

**Objetivo:** Infraestructura de notificaciones con BD, WebSockets, UI básica (dropdown + bandeja).

### 1.1. Base de Datos y Modelos ✅

- [x] **Migración `create_notifications_table`**
  - `id`, `user_id` (FK users), `organization_id` (FK organizations, nullable para platform admin)
  - `type` (enum: `group_invitation`, `group_access_request`, `group_invitation_accepted`, `group_invitation_rejected`, `group_access_request_accepted`, `group_access_request_rejected`, `item_shared_user`, `item_shared_group`, `item_created_in_group`)
  - `title` (string), `message` (text), `data` (JSON: `group_id`, `vault_item_id`, `user_id`, etc.)
  - `read_at` (timestamp, nullable), `read` (boolean, default false)
  - `action_url` (string, nullable: ruta para acción rápida)
  - `action_label` (string, nullable: "Ver grupo", "Ver item", etc.)
  - `created_at`, `updated_at`
  - Índices: `user_id`, `organization_id`, `read`, `created_at`, `type`

- [x] **Modelo `Notification`**
  - Relaciones: `user` (BelongsTo), `organization` (BelongsTo, nullable)
  - Scopes: `unread()`, `read()`, `forOrganization($orgId)`
  - Métodos: `markAsRead()`, `markAsUnread()`, `isRead()`, `isUnread()`
  - Casts: `data` (array), `read` (boolean), `read_at` (datetime)

### 1.2. Servicio de Notificaciones ✅

- [x] **`NotificationService`**
  - `create(array $data, User $user, ?int $organizationId = null): Notification` (emite evento `NotificationCreated`)
  - `markAsRead(Notification $notification): void`
  - `markAllAsRead(User $user, ?int $organizationId = null): int` (retorna cantidad)
  - `getUnreadCount(User $user, ?int $organizationId = null): int`
  - `getNotifications(User $user, ?int $organizationId = null, int $limit = 50, bool $unreadOnly = false): Collection`
  - `deleteOldRead(int $days = 90): int` (opcional, limpieza)

### 1.3. WebSockets (Laravel Broadcasting + Reverb) ✅

- [x] **Instalar Laravel Reverb**
  - `composer require laravel/reverb` (v1.7.0)
  - Configuración en `config/broadcasting.php` y `config/reverb.php`
  - Variables `.env` configuradas (REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET, etc.)
  - Agregado a `composer dev` (inicio automático)

- [x] **Configurar Broadcasting**
  - `config/broadcasting.php`: driver `reverb` (default)
  - Evento `NotificationCreated` (implements `ShouldBroadcast`, canal privado `App.Models.User.{userId}`)
  - `routes/channels.php` configurado para canales privados

- [x] **Frontend: Laravel Echo**
  - `npm install laravel-echo pusher-js` (instalado)
  - Configurado en `resources/js/app.js` (escucha canal privado, dispara evento `notification-received`)
  - Variables globales en layouts (`window.userId`, `window.reverbAppKey`, etc.)

### 1.4. UI: Dropdown en Navbar ✅

- [x] **Componente dropdown de notificaciones** (`<x-notifications-dropdown>`)
  - Icono con badge de contador (no leídas)
  - Dropdown solo con **no leídas** (últimas 10); marcar como leída por AJAX hace que la notificación desaparezca del dropdown
  - Cada item: icono según tipo, título, mensaje, tiempo relativo, botón "Marcar como leída"
  - Footer: "Ver todas las notificaciones", "Marcar todas como leídas"
  - Integrado en `layouts/navigation.blade.php`

- [x] **Vista `notifications/index.blade.php`**
  - Lista completa agrupada por fecha (hoy, ayer, esta semana, este mes, anterior)
  - Filtros: todas / no leídas
  - Acciones: marcar como leída, marcar todas como leídas
  - Acciones rápidas: botones según `action_url` y `action_label`

### 1.5. Rutas y Controlador ✅

- [x] **Rutas** (en `routes/web.php`, grupo `auth`)
  - `GET /notifications` → `NotificationController@index`
  - `GET /notifications/unread-count` → `NotificationController@unreadCount` (AJAX)
  - `POST /notifications/{notification}/read` → `NotificationController@markAsRead`
  - `POST /notifications/read-all` → `NotificationController@markAllAsRead`

- [x] **`NotificationController`**
  - `index()`: Lista agrupada por fecha (scoped por organization_id)
  - `unreadCount()`: JSON con contador (AJAX)
  - `markAsRead(Notification $notification)`: Marcar una (verifica ownership)
  - `markAllAsRead()`: Marcar todas (scoped por organization_id)

### 1.6. Toast/Banner Temporal ✅

- [x] **Componente toast para nuevas notificaciones**
  - JavaScript `notifications.js` escucha evento `notification-received`
  - Muestra toast dinámico (Bootstrap toast) con icono según tipo
  - Auto-ocultar después de 6 segundos
  - Botón de acción rápida si `action_url` está disponible
  - Actualiza contador automáticamente

**Pausa para pruebas Fase 1:**
- Verificar que las notificaciones se crean en BD
- Verificar que WebSocket funciona (nueva notificación aparece en dropdown sin recargar)
- Verificar contador en navbar
- Verificar bandeja completa
- Verificar marcar como leída (individual y todas)

**Comando de prueba:** ✅
```bash
# Crear notificación de prueba (interactivo)
php artisan notifications:test

# Crear notificación específica
php artisan notifications:test --type=item_shared_user --user=admin@example.com
```

**Estado Fase 1:** ✅ **COMPLETADA** - Sistema base de notificaciones funcionando. Listo para pruebas y continuar con Fase 2.

---

## 🎯 Fase 2: Invitaciones a Grupos (Aceptar/Rechazar) ✅

**Objetivo:** Cambiar flujo de invitación: en lugar de agregar directo, crear invitación pendiente que el usuario debe aceptar.

**Estado:** ✅ **COMPLETADA**

### 2.1. Cambios en Modelo y Migración ✅

- [x] **Actualizar `GroupMember`**
  - El `status` ya existe (`invited`, `active`, `revoked`)
  - Cambiar lógica: al invitar, crear con `status = 'invited'` (no `active`)
  - Solo cuando el usuario acepta, cambiar a `status = 'active'`

- [x] **Verificar migración `group_members`**
  - Asegurar que `status` tiene `'invited'` como opción válida (ya existe)

### 2.2. Cambios en `GroupController` ✅

- [x] **Modificar `inviteMember()`**
  - En lugar de crear con `status = 'active'`, crear con `status = 'invited'`
  - Crear notificación tipo `group_invitation` para el usuario invitado (vía `NotificationService`)
  - Emitir evento `NotificationCreated` (broadcast automático desde `NotificationService`)

- [x] **Nuevos métodos**
  - `acceptInvitation(Group $group)`: Cambiar `status` a `active`, crear notificación `group_invitation_accepted` para el inviter
  - `rejectInvitation(Group $group)`: Cambiar `status` a `revoked`, crear notificación `group_invitation_rejected` para el inviter
  - `resendInvitation(Group $group, User $user)`: Reenviar (actualizar invitación existente o crear nueva, crear notificación)

### 2.3. Rutas ✅

- [x] **Nuevas rutas** (en `routes/web.php`, grupo `auth`)
  - `POST /groups/{group}/invitations/accept` → `acceptInvitation`
  - `POST /groups/{group}/invitations/reject` → `rejectInvitation`
  - `POST /groups/{group}/invitations/{user}/resend` → `resendInvitation` (solo owner/admin)

### 2.4. UI: Pestaña "Invitaciones" en `/groups` ✅

- [x] **Actualizar `groups/index.blade.php`**
  - Agregar tabs: "Mis Grupos" (actual), "Invitaciones Pendientes", "Grupos Disponibles" (Fase 3 - placeholder)
  - Tab "Invitaciones Pendientes":
    - Lista de `GroupMember` con `status = 'invited'` y `user_id = auth()->id()`
    - Mostrar: nombre del grupo, descripción, quién invitó, fecha, rol
    - Botones: "Aceptar" (modal confirm), "Rechazar" (modal confirm)
    - Badge con contador de invitaciones pendientes en el tab

- [x] **Modales de confirmación**
  - Usar `<x-confirm-modal>` para aceptar/rechazar

### 2.5. Eventos y Notificaciones ✅

- [x] **Notificación al invitar** (en `GroupController::inviteMember()`)
  - `NotificationService::create()` con tipo `group_invitation`
  - Data: `group_id`, `group_name`, `inviter_id`, `inviter_name`, `role`
  - Action URL: `route('groups.index', ['tab' => 'invitations'])`

- [x] **Notificación al aceptar** (en `GroupController::acceptInvitation()`)
  - `NotificationService::create()` con tipo `group_invitation_accepted` para el inviter
  - Data: `group_id`, `group_name`, `accepted_user_id`, `accepted_user_name`
  - Action URL: `route('groups.admin', $group)`

- [x] **Notificación al rechazar** (en `GroupController::rejectInvitation()`)
  - `NotificationService::create()` con tipo `group_invitation_rejected` para el inviter
  - Data: `group_id`, `group_name`, `rejected_user_id`, `rejected_user_name`
  - Action URL: `route('groups.admin', $group)`

**Pausa para pruebas Fase 2:**
- Invitar usuario a grupo → verificar que se crea con `status = 'invited'`
- Verificar notificación llega al usuario invitado
- Aceptar invitación → verificar `status = 'active'` y notificación al inviter
- Rechazar invitación → verificar `status = 'revoked'` y notificación al inviter
- Verificar pestaña "Invitaciones Pendientes" muestra correctamente
- Verificar acciones rápidas desde notificaciones (aceptar/rechazar desde dropdown y página)

**Estado Fase 2:** ✅ **COMPLETADA** - Sistema de invitaciones a grupos funcionando. Listo para pruebas y continuar con Fase 3.

---

## 🎯 Fase 3: Solicitudes de Acceso a Grupos ✅

**Objetivo:** Permitir que usuarios soliciten acceso a grupos y que owner/admin aprueben/rechacen.

**Estado:** ✅ **COMPLETADA**

### 3.1. Base de Datos ✅

- [x] **Migración `create_group_access_requests_table`**
  - `id`, `group_id` (FK groups), `user_id` (FK users), `organization_id` (FK organizations)
  - `status` (enum: `pending`, `accepted`, `rejected`)
  - `requested_at` (timestamp), `responded_at` (timestamp, nullable)
  - `responded_by_user_id` (FK users, nullable)
  - `message` (text, nullable: mensaje opcional del solicitante)
  - `created_at`, `updated_at`
  - Índices: `group_id`, `user_id`, `status`, `organization_id`
  - Unique: `['group_id', 'user_id']` (un usuario solo una solicitud pendiente por grupo)

- [x] **Modelo `GroupAccessRequest`**
  - Relaciones: `group` (BelongsTo), `user` (BelongsTo), `organization` (BelongsTo), `respondedBy` (BelongsTo, nullable)
  - Scopes: `pending()`, `accepted()`, `rejected()`, `forOrganization($orgId)`
  - Métodos: `accept(User $respondedBy): void`, `reject(User $respondedBy): void`

### 3.2. Servicio ✅

- [x] **`GroupAccessRequestService`**
  - `createRequest(Group $group, User $user, ?string $message = null): GroupAccessRequest` ✅
  - `acceptRequest(GroupAccessRequest $request, User $respondedBy): void` (cambia status, crea GroupMember con `status = 'active'`, crea notificaciones) ✅
  - `rejectRequest(GroupAccessRequest $request, User $respondedBy): void` ✅
  - `getPendingRequestsForGroup(Group $group): Collection` ✅
  - `getPendingRequestsForUser(User $user): Collection` ✅
  - `hasPendingRequest(Group $group, User $user): bool` ✅
  - `getPendingRequestsForUserGroups(User $user): Collection` ✅ (adicional: solicitudes de grupos que administra)

### 3.3. Controlador y Rutas ✅

- [x] **`GroupAccessRequestController`**
  - `store(Request, Group $group)`: Crear solicitud ✅
  - `accept(GroupAccessRequest $request)`: Aceptar (solo owner/admin del grupo) ✅
  - `reject(GroupAccessRequest $request)`: Rechazar (solo owner/admin del grupo) ✅
  - `index()`: Lista de solicitudes pendientes del usuario (para ver estado) ✅
  - `pendingForGroup(Group $group)`: Lista de solicitudes pendientes de un grupo (para owner/admin) ✅

- [x] **Rutas** (en `routes/web.php`, grupo `auth`)
  - `POST /groups/{group}/access-requests` → `store` ✅
  - `POST /groups/{group}/access-requests/{accessRequest}/accept` → `accept` ✅
  - `POST /groups/{group}/access-requests/{accessRequest}/reject` → `reject` ✅
  - `GET /groups/access-requests` → `index` (mis solicitudes) ✅
  - `GET /groups/{group}/access-requests/pending` → `pendingForGroup` (solo owner/admin) ✅

### 3.4. UI: Pestaña "Grupos Disponibles" en `/groups`

- [ ] **Tab "Grupos Disponibles"**
  - Lista de todos los grupos de la organización donde el usuario **no** es miembro
  - Mostrar: nombre, descripción, owner, cantidad de miembros
  - Botón "Solicitar Acceso" (si no hay solicitud pendiente)
  - Si hay solicitud pendiente: badge "Pendiente" o "Rechazada" (con opción de volver a solicitar si fue rechazada)

- [ ] **Tab "Solicitudes Pendientes" (para owner/admin)**
  - Solo visible si el usuario es owner/admin de algún grupo
  - Lista de solicitudes pendientes de los grupos que administra
  - Mostrar: grupo, usuario solicitante, fecha, mensaje (si hay)
  - Botones: "Aceptar" (modal), "Rechazar" (modal)
  - Agrupado por grupo o lista única

### 3.5. Eventos y Notificaciones ✅

- [x] **Notificación al solicitar** (en `GroupAccessRequestService::createRequest()`)
  - `NotificationService::create()` con tipo `group_access_request` para todos los owner/admin del grupo ✅
  - Data: `group_id`, `group_name`, `requested_user_id`, `requested_user_name`, `message`, `request_id` ✅
  - Action URL: `route('groups.index', ['tab' => 'requests'])` ✅

- [x] **Notificación al aceptar** (en `GroupAccessRequestService::acceptRequest()`)
  - `NotificationService::create()` con tipo `group_access_request_accepted` para el solicitante ✅
  - Data: `group_id`, `group_name`, `responded_by_user_id`, `responded_by_user_name`

- [x] **Notificación al rechazar** (en `GroupAccessRequestService::rejectRequest()`)
  - `NotificationService::create()` con tipo `group_access_request_rejected` para el solicitante ✅
  - Data: `group_id`, `group_name`, `responded_by_user_id`, `responded_by_user_name` ✅
  - Action URL: `route('groups.index', ['tab' => 'available'])` ✅

**Pausa para pruebas Fase 3:**
- Solicitar acceso a grupo → verificar notificación a owner/admin
- Aceptar solicitud → verificar GroupMember creado y notificación al solicitante
- Rechazar solicitud → verificar notificación al solicitante
- Verificar pestaña "Grupos Disponibles" muestra correctamente
- Verificar pestaña "Solicitudes" muestra correctamente para owners/admins

**Estado Fase 3:** ✅ **COMPLETADA** - Sistema de solicitudes de acceso a grupos funcionando. Listo para pruebas y continuar con Fase 4.

---

## 🎯 Fase 4: Notificaciones de Compartición y Creación de Items ✅

**Objetivo:** Notificar cuando se comparte un item o se crea un item en un grupo.

**Estado:** ✅ **COMPLETADA**

### 4.1. Notificaciones al Compartir Items ✅

- [x] **Modificar `ShareService`**
  - `shareWithUser()`: Después de crear `ItemShareUser`, crear notificación `item_shared_user` ✅
  - `shareWithGroup()`: Después de crear `ItemShareGroup`, crear notificaciones `item_shared_group` para **todos los miembros activos del grupo** (excepto el que comparte) ✅
  - Data: `vault_item_id`, `vault_item_title`, `vault_item_type`, `shared_by_user_id`, `shared_by_user_name`, `permission` ✅
  - Action URL: `route('vault.show', $item)` ✅
  - Action Label: `'Ver item'` ✅

- [x] **Notificación grupo→usuario**
  - Si un grupo comparte un item con un usuario (futuro, si se implementa), notificar al usuario (ya implementado en `shareWithGroup`) ✅

### 4.2. Notificaciones al Crear Item en Grupo ✅

- [x] **Modificar `GroupController::itemsStore()`**
  - Después de crear el item y compartirlo con el grupo, crear notificaciones `item_created_in_group` para **todos los miembros activos del grupo** (excepto el creador) ✅
  - Data: `vault_item_id`, `vault_item_title`, `vault_item_type`, `group_id`, `group_name`, `created_by_user_id`, `created_by_user_name` ✅
  - Action URL: `route('groups.items.index', $group)` ✅
  - Action Label: `'Ver grupo'` ✅

### 4.3. Acciones Rápidas desde Notificaciones ✅

- [x] **Configurar `action_url` y `action_label` en notificaciones**
  - `item_shared_user`, `item_shared_group`: `action_url = route('vault.show', $item)`, `action_label = 'Ver item'` ✅
  - `item_created_in_group`: `action_url = route('groups.items.index', $group)`, `action_label = 'Ver grupo'` ✅
  - `group_invitation`: `action_url = route('groups.index', ['tab' => 'invitations'])`, `action_label = 'Ver invitaciones'` ✅ (ya implementado en Fase 2)
  - `group_access_request`: `action_url = route('groups.index', ['tab' => 'requests'])`, `action_label = 'Ver solicitudes'` ✅ (ya implementado en Fase 3)

- [x] **UI: Botones de acción rápida**
  - En dropdown y bandeja: botón según `action_label` que lleva a `action_url` ✅ (ya implementado en Fase 1)

**Pausa para pruebas Fase 4:**
- Compartir item con usuario → verificar notificación
- Compartir item con grupo → verificar notificaciones a todos los miembros
- Crear item en grupo → verificar notificaciones a todos los miembros (excepto creador)
- Verificar acciones rápidas funcionan

**Estado Fase 4:** ✅ **COMPLETADA** - Sistema de notificaciones de compartición y creación de items funcionando. Todas las fases completadas.

---

## 📝 Documentación a Actualizar

- [x] **`documentacion/11-notificaciones-invitaciones/README.md`** (nuevo módulo)
  - Descripción del sistema ✅
  - Tipos de notificaciones ✅
  - Flujos de invitación y solicitudes ✅
  - WebSockets y tiempo real ✅
  - Archivos principales ✅

- [x] **`documentacion/05-grupos/README.md`**
  - Actualizar: flujo de invitación ahora requiere aceptación ✅
  - Agregar: sistema de solicitudes de acceso ✅
  - Actualizar rutas ✅

- [x] **`documentacion/04-comparticion/README.md`**
  - Agregar: notificaciones al compartir ✅
  - Notificaciones al crear items en grupos ✅

- [x] **`ROUTE-MAP.md`**
  - Sección "31. Sistema de Notificaciones e Invitaciones" con fases 1–4 completadas ✅

- [x] **`ROADMAP.md`**
  - "En Desarrollo" actualizado: Sistema de Notificaciones (Fases 1–4) completado ✅

- [x] **`documentacion/README.md`**
  - Módulo 11 en estructura; estado "Todas las fases completadas" ✅

---

## 🔧 Decisiones Técnicas

### WebSockets: Laravel Reverb vs Echo Server

**Recomendación: Laravel Reverb** (si está disponible en Laravel 12)
- Ventajas: Integrado, self-hosted, sin dependencias externas
- Configuración: `.env` + `php artisan reverb:start`
- Frontend: Laravel Echo con driver Reverb

**Alternativa: Laravel Echo Server**
- Si Reverb no está disponible o hay problemas
- Requiere Node.js
- Configuración similar

### Estructura de Notificaciones

```php
// Ejemplo de creación
NotificationService::create([
    'type' => 'group_invitation',
    'title' => 'Invitación a grupo',
    'message' => "{$inviter->name} te ha invitado al grupo '{$group->name}'",
    'data' => [
        'group_id' => $group->id,
        'group_name' => $group->name,
        'inviter_id' => $inviter->id,
        'inviter_name' => $inviter->name,
        'role' => $role,
    ],
    'action_url' => route('groups.index', ['tab' => 'invitations']),
    'action_label' => 'Ver invitaciones',
], $invitedUser, $invitedUser->organization_id);
```

### Scoping por Organización

- Todas las notificaciones tienen `organization_id`
- Platform super admin (`organization_id = null`) solo ve notificaciones globales del sistema (si las hay)
- Usuarios normales solo ven notificaciones de su organización
- `NotificationService` filtra automáticamente por `organization_id` del usuario autenticado

---

## ✅ Checklist de Implementación

### Fase 1: Sistema Base ✅
- [x] Migración `notifications`
- [x] Modelo `Notification`
- [x] `NotificationService`
- [x] Laravel Reverb configurado
- [x] Evento `NotificationCreated` con broadcast
- [x] Frontend Echo configurado
- [x] Dropdown en navbar
- [x] Página `/notifications`
- [x] Rutas y `NotificationController`
- [x] Toast para nuevas notificaciones

### Fase 2: Invitaciones a Grupos ✅
- [x] Modificar `GroupController::inviteMember()` (status = 'invited')
- [x] Nuevos métodos: `acceptInvitation`, `rejectInvitation`, `resendInvitation`
- [x] Rutas de aceptar/rechazar
- [x] Pestaña "Invitaciones Pendientes" en `/groups`
- [x] Eventos y notificaciones (invitar, aceptar, rechazar)

### Fase 3: Solicitudes de Acceso ✅
- [x] Migración `group_access_requests`
- [x] Modelo `GroupAccessRequest`
- [x] `GroupAccessRequestService`
- [x] `GroupAccessRequestController`
- [x] Rutas
- [x] Pestaña "Grupos Disponibles" en `/groups`
- [x] Pestaña "Solicitudes Pendientes" (owner/admin)
- [x] Eventos y notificaciones

### Fase 4: Notificaciones de Items ✅
- [x] Modificar `ShareService::shareWithUser()` (notificar)
- [x] Modificar `ShareService::shareWithGroup()` (notificar a todos)
- [x] Modificar `GroupController::itemsStore()` (notificar creación)
- [x] Configurar `action_url` y `action_label` en todas las notificaciones
- [x] Botones de acción rápida en UI

### Documentación ✅
- [x] Crear `documentacion/11-notificaciones-invitaciones/README.md`
- [x] Actualizar `documentacion/05-grupos/README.md`
- [x] Actualizar `documentacion/04-comparticion/README.md`
- [x] Actualizar `ROUTE-MAP.md`
- [x] Actualizar `ROADMAP.md`
- [x] Actualizar `documentacion/README.md`

---

## 🚀 Orden de Implementación Recomendado

1. **Fase 1 completa** (sistema base) → Pausa para pruebas
2. **Fase 2 completa** (invitaciones) → Pausa para pruebas
3. **Fase 3 completa** (solicitudes) → Pausa para pruebas
4. **Fase 4 completa** (notificaciones items) → Pausa para pruebas
5. **Documentación completa**

Cada fase es independiente y testeable visualmente antes de continuar.
