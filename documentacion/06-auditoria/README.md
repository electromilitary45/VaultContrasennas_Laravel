# Auditoría - PassVault

Documentación del sistema de auditoría y registro de eventos.

## Estado

**Implementado.** Logs de acciones (create, update, delete, view, share), filtros por usuario/org, exportación CSV. Scope por `organization_id` en multi-tenant.

## Contenido

### Eventos y listeners
- Listeners para vault items: `LogVaultItemCreated`, `LogVaultItemUpdated`, `LogVaultItemDeleted`, `LogVaultItemViewed`, `LogVaultItemShared`, `LogVaultItemUnshared`
- Otros eventos (organizaciones, usuarios, códigos de invitación, etc.) registrados vía `AuditService::log()`

### Registro de logs
- **`AuditService`**: `log($action, $modelType, $modelId, $changes, $request, $meta)`. Extrae `organization_id` de `$meta` o del usuario autenticado.
- **Tabla `audit_logs`**: `user_id`, `organization_id`, `action`, `model_type`, `model_id`, `changes`, `ip_address`, `user_agent`, `meta`, `created_at`

### Consulta de auditoría
- **`AuditController`**: `index()` (lista con filtros), `export()` (CSV)
- **Filtros**: usuario, acción, tipo de modelo, rango de fechas, **organización** (org admin solo ve logs de su org; platform admin ve todos)
- **`AuditService::getLogs()`**, **`filterLogs()`**, **`getStats()`**: todos soportan `organization_id` en filtros

### Export de logs
- Exportación a CSV con los mismos filtros (usuario, acción, tipo, fechas, org)

## Archivos principales

- `app/Services/AuditService.php` — Servicio de auditoría
- `app/Models/AuditLog.php` — Modelo con scopes `forOrganization`, `forUser`, `forAction`, `forModelType`, `dateRange`
- `app/Http/Controllers/AuditController.php` — Panel de auditoría (admin) y export
- `app/Listeners/LogVaultItem*.php` — Listeners de eventos de vault items

## Referencias

- [`documentacion/08-administracion/`](../08-administracion/) — Panel de administración (auditoría integrada)
- [`documentacion/09-saas-multi-tenant/`](../09-saas-multi-tenant/) — Scoping por organización
