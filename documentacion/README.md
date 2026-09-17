# Documentación del Proyecto - PassVault

Esta carpeta contiene toda la documentación del proyecto organizada por módulos.

## Estructura de Carpetas

```
documentacion/
├── 01-setup-inicial/      # Documentación del setup inicial del proyecto
├── 02-autenticacion/      # Documentación del sistema de autenticación
├── 03-vault-items/        # Documentación de gestión de vault items
├── 04-comparticion/       # Documentación del sistema de compartición
├── 05-grupos/             # Documentación de gestión de grupos
├── 06-auditoria/          # Documentación del sistema de auditoría
├── 07-seguridad/          # Documentación de seguridad y cifrado
├── 08-administracion/     # Documentación del sistema de administración
├── 09-saas-multi-tenant/  # Modelo SaaS multi-tenant, decisiones MVP y facturación futura
├── 10-organizaciones/     # CRUD organizaciones, super admin de org, contraseña temporal, regenerar
├── 11-notificaciones-invitaciones/  # Sistema de notificaciones (WebSockets), invitaciones y solicitudes de acceso a grupos
└── 12-extension-navegador/  # Plan extensión Chrome (MVP, Integraciones, versionado, actualización automática)
```

## Estado y progreso

**Última sincronización documentación y progresos:** 2026-02-05 (dropdown notificaciones solo no leídas + AJAX; botón copiar en vault/show)

- **MVP:** Completado (auditoría, SaaS multi-tenant). Ver [`ROUTE-MAP.md`](../ROUTE-MAP.md) y [`ROADMAP.md`](../ROADMAP.md).
- **SaaS multi-tenant:** Fases 1–6 completadas; platform vs org admin, códigos de invitación, scoping por org. Ver [`09-saas-multi-tenant/`](09-saas-multi-tenant/).
- **Sistema de Notificaciones:** Todas las fases completadas ✅ (Fase 1: Sistema Base, Fase 2: Invitaciones a Grupos, Fase 3: Solicitudes de Acceso, Fase 4: Compartición y Creación de Items). Ver [`11-notificaciones-invitaciones/`](11-notificaciones-invitaciones/).
- **Extensión de navegador:** MVP implementado (popup, tema, Ajustes, actualización desde vault, auto-fill). Ver [`12-extension-navegador/`](12-extension-navegador/).
- **Item Archivo .env (planificado):** Nuevo tipo de item para compartir .env con visibilidad granular por línea o sección (click-and-drag mixto). Ver [`03-vault-items/env-file-item.md`](03-vault-items/env-file-item.md).

## Convenciones

1. **Nomenclatura de archivos:**
   - Usar nombres descriptivos en minúsculas con guiones
   - Ejemplo: `esquema-base-datos.md`, `flujo-autenticacion.md`

2. **Formato:**
   - Preferir Markdown (.md) para documentación
   - Incluir diagramas cuando sea necesario (usar Mermaid o imágenes)

3. **Organización:**
   - Cada módulo debe tener su propio README.md explicando su contenido
   - Documentar decisiones de diseño importantes
   - Incluir ejemplos de código cuando sea relevante

4. **Actualización:**
   - Mantener la documentación actualizada con el código
   - Documentar cambios importantes en cada commit

## Archivos Principales en la Raíz

- `documentacion/01-setup-inicial/documentacionInicial.md` - Documento de arranque completo
- `documentacion/01-setup-inicial/comandos-desarrollo.md` - Guía de comandos: `composer dev` vs `npm run dev`, servicios, compilación
- `documentacion/09-saas-multi-tenant/decisiones-mvp.md` - Decisiones SaaS multi-tenant (MVP)
- `documentacion/09-saas-multi-tenant/decisiones-previas-codigo.md` - Decisiones previas al código
- `documentacion/09-saas-multi-tenant/plan-implementacion-fases.md` - Plan por fases (Fases 1–6 completadas; cierre realizado)
- `documentacion/10-organizaciones/README.md` - Organizaciones: CRUD, flujo de creación, contraseña temporal, regenerar
- `documentacion/11-notificaciones-invitaciones/plan-implementacion.md` - Plan por fases: notificaciones, invitaciones y solicitudes de acceso
- `documentacion/11-notificaciones-invitaciones/README.md` - Sistema de notificaciones e invitaciones: descripción, tipos, flujos
- `documentacion/11-notificaciones-invitaciones/configuracion-reverb.md` - Configuración de Reverb para desarrollo
- `documentacion/11-notificaciones-invitaciones/produccion-reverb.md` - Configuración de Reverb en producción (servicios, proxy, seguridad)
- `documentacion/12-extension-navegador/README.md` - Extensión de navegador: índice del módulo
- `documentacion/12-extension-navegador/plan-extension-mvp.md` - Plan extensión Chrome: MVP, Integraciones, versionado, actualización automática
- `documentacion/03-vault-items/env-file-item.md` - Plan item tipo Archivo .env: visibilidad granular por línea/sección, click-and-drag mixto
- `ORDEN-COMMITS.md` - Plan de commits incrementales
- `ROUTE-MAP.md` - Mapa de ruta del proyecto (estado técnico y progreso)
- `ROADMAP.md` - Plan de producto y visión futura

## Reglas del Proyecto

Las reglas del proyecto se encuentran en `.cursor/rules/`:

- `design-rules.md` - Reglas de diseño UI/UX y Bootstrap (estilo minimalista tipo Apple)
- `code-standards.md` - Estándares de código, clean code, MVC y comentarios
- `documentation-rules.md` - Reglas de documentación y organización
