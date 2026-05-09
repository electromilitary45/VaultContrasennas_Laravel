# Sistema de Compartición - OCIANN Vault

Documentación del sistema de compartición de items del vault con usuarios y grupos.

## 📋 Descripción

El sistema de compartición permite compartir items del vault con otros usuarios o grupos, con control granular de permisos. Los items compartidos son visibles en el vault del usuario receptor y respetan los permisos asignados.

## ✨ Funcionalidades Implementadas

### Compartición con Usuarios
- Compartir items con usuarios individuales
- Permisos: `view` (solo lectura), `edit` (editar), `admin` (administrar)
- Actualización de permisos en tiempo real
- Revocación de acceso
- Vista de quién compartió el item

### Compartición con Grupos
- Compartir items con grupos completos
- Todos los miembros del grupo reciben acceso según el permiso asignado
- Permisos: `view`, `edit`, `admin`
- Actualización de permisos
- Revocación de acceso del grupo

### Notificaciones al Compartir ✅
- **Usuario→usuario**: Al compartir un item con un usuario, el receptor recibe una notificación en tiempo real (`item_shared_user`). Acción rápida: "Ver item".
- **Usuario→grupo**: Al compartir un item con un grupo, todos los miembros activos del grupo (excepto quien comparte) reciben notificación (`item_shared_group`). Acción rápida: "Ver item".
- **Creación en grupo**: Al crear un item directamente en un grupo (desde el vault del grupo), todos los miembros activos (excepto el creador) reciben notificación `item_created_in_group`. Acción rápida: "Ver grupo".
- Integrado con el [Sistema de Notificaciones](../11-notificaciones-invitaciones/README.md) (WebSockets, bandeja, dropdown).

### Gestión de Permisos
- **Owner**: Propietario del item, control total
- **Admin**: Puede compartir, revocar y cambiar permisos
- **Edit**: Puede editar el contenido del item
- **View**: Solo puede ver el item (solo lectura)

## 🏗️ Arquitectura

### Modelos

#### `ItemShareUser`
- `vault_item_id`: Item compartido
- `user_id`: Usuario receptor
- `permission`: Permiso (view, edit, admin)
- `shared_by_user_id`: Usuario que compartió

#### `ItemShareGroup`
- `vault_item_id`: Item compartido
- `group_id`: Grupo receptor
- `permission`: Permiso (view, edit, admin)
- `shared_by_user_id`: Usuario que compartió

### Servicios

#### `ShareService`
- `shareWithUser()`: Compartir con usuario
- `shareWithGroup()`: Compartir con grupo
- `revokeUserShare()`: Revocar compartición con usuario
- `revokeGroupShare()`: Revocar compartición con grupo
- `getUserShares()`: Obtener items compartidos con el usuario
- `getGroupShares()`: Obtener items compartidos con grupos
- `getAllUsersWithAccess()`: Obtener todos los usuarios con acceso
- `updateUserSharePermission()`: Actualizar permiso de usuario
- `updateGroupSharePermission()`: Actualizar permiso de grupo

### Políticas

#### `VaultItemPolicy`
- `view()`: Verificar acceso de lectura
- `update()`: Verificar acceso de edición
- `delete()`: Verificar acceso de eliminación
- `getPermission()`: Obtener permiso efectivo del usuario

## 🔄 Flujo de Compartición

1. **Usuario comparte item**:
   - Selecciona usuario/grupo
   - Asigna permiso (view/edit/admin)
   - `ShareService` crea registro en `item_shares_users` o `item_shares_groups`

2. **Usuario receptor accede**:
   - `VaultItemPolicy` verifica acceso
   - Si tiene acceso, puede ver/editar según permiso
   - Item aparece en su vault con indicador de "Compartido"

3. **Gestión de permisos**:
   - Owner/Admin puede actualizar permisos
   - Owner/Admin puede revocar acceso
   - Cambios se reflejan inmediatamente

## 📍 Rutas

- `POST /vault/{vault}/share` - Compartir item
- `DELETE /vault/{vault}/share` - Revocar compartición
- `GET /vault/{vault}/shares` - Listar comparticiones
- `PUT /vault/{vault}/share/permission` - Actualizar permiso

## 🎨 UI/UX

### Vista de Detalle (`vault/show.blade.php`)
- Sección "Compartición" visible solo para owner/admin
- Lista de usuarios con acceso
- Lista de grupos con acceso
- Botones para editar permisos y revocar
- Modal para compartir nuevo usuario/grupo
- Modal para editar permisos existentes

### Indicadores Visuales
- Badge "Compartido" en lista de items
- Diferencia entre compartición directa (usuario) y por grupo
- Muestra quién compartió el item

## 🔒 Seguridad

- Solo el owner puede compartir inicialmente
- Solo owner/admin pueden gestionar comparticiones
- Permisos verificados en cada acceso
- No se puede compartir con el mismo usuario dos veces (se actualiza permiso)

## 📝 Notas de Implementación

- Los items compartidos aparecen en el vault del receptor
- Los permisos se verifican en cada operación
- La compartición automática ocurre al crear items desde un grupo
- Las carpetas son personales; los items compartidos pueden organizarse en carpetas del receptor

### Multi-tenant (SaaS)
- Solo se puede compartir con usuarios y grupos de la **misma organización**. `VaultController` filtra `availableUsers` y `availableGroups` por `organization_id` y valida al compartir. Ver [`documentacion/09-saas-multi-tenant/`](../09-saas-multi-tenant/).

### Notificaciones
- Las notificaciones al compartir y al crear items en grupos se crean vía `ShareService` y `GroupController::itemsStore()`, usando `NotificationService`. Ver [`documentacion/11-notificaciones-invitaciones/`](../11-notificaciones-invitaciones/).
