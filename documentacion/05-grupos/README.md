# Sistema de Grupos - PassVault

Documentación del sistema de grupos para colaboración y compartición de items del vault.

## 📋 Descripción

Los grupos permiten colaborar con equipos, compartir items del vault y gestionar accesos de forma centralizada. Cada grupo tiene miembros con roles específicos y un vault compartido donde todos pueden ver y gestionar items.

## ✨ Funcionalidades Implementadas

### Gestión de Grupos
- Crear, editar y eliminar grupos
- Descripción del grupo
- Propietario del grupo (owner)

### Gestión de Miembros
- **Invitaciones con aceptación**: Owner/admin invita → el usuario entra con `status = 'invited'` → debe **aceptar** o **rechazar** la invitación. Sin aceptación no es miembro activo. Se pueden reenviar invitaciones; no expiran.
- Roles: `owner`, `admin`, `member`, `viewer`
- Cambiar roles de miembros
- Remover miembros del grupo
- Ver quién invitó a cada miembro

### Solicitudes de Acceso ✅
- **Cualquier usuario** de la organización puede solicitar acceso a un grupo del que no es miembro.
- **Owner/admin** del grupo reciben la solicitud y pueden **aceptar** o **rechazar**.
- Pestaña "Grupos Disponibles" en `/groups`: lista de grupos donde no eres miembro, con botón "Solicitar Acceso".
- Pestaña "Solicitudes": solo para owner/admin, lista de solicitudes pendientes de sus grupos.
- Notificaciones en tiempo real al solicitar, aceptar y rechazar. Ver [Sistema de Notificaciones](../11-notificaciones-invitaciones/README.md).

### Vault de Grupo
- Vista dedicada para items compartidos con el grupo
- Crear items directamente desde el grupo
- Items creados desde grupo se comparten automáticamente con todos los miembros
- Carpetas del grupo (compartidas entre todos los miembros)
- Filtros y búsqueda integrados

### Carpetas de Grupo
- Carpetas compartidas entre todos los miembros del grupo
- Estructura jerárquica (hasta 5 niveles)
- Solo visibles dentro del grupo
- Todos los miembros ven las mismas carpetas

## 🏗️ Arquitectura

### Modelos

#### `Group`
- `owner_user_id`: Usuario propietario
- `name`: Nombre del grupo
- `description`: Descripción opcional
- Relaciones: `owner`, `members`, `activeMembers`, `sharedItems`, `folders`

#### `GroupMember`
- `group_id`: Grupo
- `user_id`: Usuario miembro
- `role`: Rol (owner, admin, member, viewer)
- `status`: Estado (`active`, `invited`, `revoked`). Con invitación: primero `invited`, luego `active` al aceptar o `revoked` al rechazar.
- `invited_by_user_id`: Usuario que invitó
- Relaciones: `group`, `user`, `invitedBy`

#### `GroupAccessRequest` (solicitudes de acceso)
- `group_id`, `user_id`, `organization_id`
- `status`: `pending`, `accepted`, `rejected`
- `message`: Mensaje opcional del solicitante
- `responded_by_user_id`, `responded_at`
- Servicio: `GroupAccessRequestService`; controlador: `GroupAccessRequestController`

### Servicios

#### `GroupController`
- `index()`: Lista de grupos (redirige a vault si hay grupo seleccionado)
- `create()`: Formulario de creación
- `store()`: Crear grupo
- `show()`: Redirige al vault del grupo
- `edit()`: Formulario de edición
- `update()`: Actualizar grupo
- `destroy()`: Eliminar grupo
- `admin()`: Vista de administración del grupo
- `itemsIndex()`: Vault del grupo
- `itemsCreate()`: Crear item desde grupo
- `itemsStore()`: Guardar item y compartir automáticamente
- `inviteMember()`: Invitar usuario
- `removeMember()`: Remover miembro
- `updateMemberRole()`: Cambiar rol
- `storeFolder()`: Crear carpeta del grupo
- `updateFolder()`: Actualizar carpeta del grupo
- `destroyFolder()`: Eliminar carpeta del grupo

### Roles y Permisos

#### Roles del Grupo
- **Owner**: Propietario del grupo, control total
- **Admin**: Puede invitar miembros, gestionar roles, crear items
- **Member**: Puede crear items y usar items compartidos
- **Viewer**: Solo puede ver items compartidos

#### Permisos en Items del Grupo
- Items creados desde grupo: Owner = creador, compartido con grupo (permiso "edit")
- Items compartidos manualmente: Permiso según asignación
- Todos los miembros pueden ver items compartidos con el grupo

## 🔄 Flujo de Trabajo

### Crear y Gestionar Grupo
1. Usuario crea grupo → Se convierte en owner
2. Owner/admin **invita** miembros con roles → se crea `GroupMember` con `status = 'invited'` y notificación al invitado
3. Invitado **acepta** o **rechaza**: aceptar → `status = 'active'`; rechazar → `status = 'revoked'`. Notificaciones al inviter.
4. Alternativa: usuario **solicita acceso** a un grupo → `GroupAccessRequest`; owner/admin **aceptan** o **rechazan** → notificaciones al solicitante
5. Owner/admin gestionan miembros, roles, invitaciones y solicitudes

### Trabajar con Items del Grupo
1. **Entrar al grupo** → Redirige automáticamente al vault del grupo
2. **Ver items** → Lista de items compartidos con el grupo
3. **Crear item** → Se crea con owner = usuario, se comparte automáticamente con grupo
4. **Organizar en carpetas** → Usar carpetas del grupo para organizar

### Carpetas del Grupo
1. Cualquier miembro (excepto viewer) puede crear carpetas
2. Las carpetas son compartidas entre todos los miembros
3. Todos ven la misma estructura de carpetas
4. Los items pueden organizarse en estas carpetas

## 📍 Rutas

### Grupos
- `GET /groups` - Lista de grupos
- `GET /groups/create` - Crear grupo
- `POST /groups` - Guardar grupo
- `GET /groups/{group}` - Redirige a vault del grupo
- `GET /groups/{group}/edit` - Editar grupo
- `PUT /groups/{group}` - Actualizar grupo
- `DELETE /groups/{group}` - Eliminar grupo
- `GET /groups/{group}/admin` - Administración del grupo

### Items del Grupo
- `GET /groups/{group}/items` - Vault del grupo
- `GET /groups/{group}/items/create` - Crear item desde grupo
- `POST /groups/{group}/items` - Guardar item y compartir

### Carpetas del Grupo
- `POST /groups/{group}/folders` - Crear carpeta
- `PUT /groups/{group}/folders/{folder}` - Actualizar carpeta
- `DELETE /groups/{group}/folders/{folder}` - Eliminar carpeta

### Miembros
- `POST /groups/{group}/invite` - Invitar miembro (crea con `status = 'invited'`, notificación)
- `POST /groups/{group}/invitations/{invitation}/accept` - Aceptar invitación
- `POST /groups/{group}/invitations/{invitation}/reject` - Rechazar invitación
- `POST /groups/{group}/invitations/{invitation}/resend` - Reenviar invitación
- `DELETE /groups/{group}/member` - Remover miembro
- `PUT /groups/{group}/member/role` - Cambiar rol

### Solicitudes de Acceso
- `POST /groups/{group}/access-requests` - Crear solicitud de acceso
- `POST /groups/{group}/access-requests/{request}/accept` - Aceptar solicitud (owner/admin)
- `POST /groups/{group}/access-requests/{request}/reject` - Rechazar solicitud (owner/admin)
- `GET /groups/access-requests` - Mis solicitudes
- `GET /groups/{group}/access-requests/pending` - Solicitudes pendientes del grupo (owner/admin)

## 🎨 UI/UX

### Lista de Grupos (`groups/index.blade.php`)
- **Pestañas**: "Mis Grupos" (propios y donde eres miembro), "Invitaciones Pendientes" (invitaciones recibidas, aceptar/rechazar), "Grupos Disponibles" (grupos de la org donde no eres miembro, solicitar acceso), "Solicitudes" (solo owner/admin: solicitudes pendientes de sus grupos, aceptar/rechazar).
- Tabla con grupos; botones: "Items" (va al vault), "Admin" (administración), "Editar", "Eliminar"
- Acceso directo al vault desde la lista

### Vault del Grupo (`groups/items/vault.blade.php`)
- Sidebar con carpetas del grupo
- Lista de items compartidos con el grupo
- Filtros y búsqueda
- Botón "Administrar Grupo" para owner/admin

### Administración (`groups/admin.blade.php`)
- Información del grupo
- Lista de miembros con roles
- Invitar nuevos miembros
- Cambiar roles
- Remover miembros
- Eliminar grupo (solo owner)

## 🔒 Seguridad

- Solo el owner puede eliminar el grupo
- Solo owner/admin pueden invitar miembros
- Solo owner puede cambiar roles de admin
- Solo owner/admin pueden gestionar carpetas del grupo
- Los items compartidos respetan los permisos del grupo

## 📝 Notas de Implementación

- Al crear un item desde el grupo, el owner es el usuario que lo crea (no el grupo)
- El item se comparte automáticamente con el grupo con permiso "edit"
- Las carpetas del grupo son compartidas; todos los miembros las ven
- Los items compartidos pueden organizarse en carpetas personales del receptor
- La entrada a un grupo redirige automáticamente al vault (no a administración)

### Multi-tenant (SaaS)
- Grupos y carpetas de grupo tienen `organization_id`. `GroupController` aplica `ensureGroupInOrg` y filtra listados por org; la invitación de miembros solo ofrece usuarios de la misma org. Ver [`documentacion/09-saas-multi-tenant/`](../09-saas-multi-tenant/).

### Notificaciones e invitaciones
- Invitaciones, solicitudes de acceso y notificaciones (al invitar, aceptar, rechazar, compartir, crear items en grupo) integrados con el [Sistema de Notificaciones](../11-notificaciones-invitaciones/README.md).
