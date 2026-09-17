# Reglas de Documentación - PassVault

## Estructura de Documentación

Toda la documentación del proyecto debe organizarse en la carpeta `documentacion/` con subcarpetas por módulos.

### Estructura de Carpetas

```
documentacion/
├── 01-setup-inicial/      # Setup inicial del proyecto
├── 02-autenticacion/      # Sistema de autenticación
├── 03-vault-items/        # Gestión de vault items
├── 04-comparticion/       # Sistema de compartición
├── 05-grupos/             # Gestión de grupos
├── 06-auditoria/          # Sistema de auditoría
├── 07-seguridad/          # Seguridad y cifrado
├── 08-administracion/     # Panel de administración
├── 09-saas-multi-tenant/  # Modelo SaaS multi-tenant, decisiones MVP
├── 10-organizaciones/     # CRUD organizaciones, contraseña temporal, regenerar
├── 11-notificaciones-invitaciones/  # Notificaciones WebSockets, invitaciones, solicitudes de acceso
└── 12-extension-navegador/  # Plan extensión Chrome (MVP, Integraciones, versionado, auto-actualización)
```

### Convenciones

1. **Nomenclatura de archivos:**
   - Usar nombres descriptivos en minúsculas con guiones
   - Ejemplos: `esquema-base-datos.md`, `flujo-autenticacion.md`, `decisiones-diseño.md`
   - Preferir formato Markdown (.md)

2. **Organización por módulos:**
   - Cada módulo tiene su propia subcarpeta numerada
   - Cada subcarpeta debe tener un `README.md` explicando su contenido
   - Agrupar documentación relacionada en la misma carpeta

3. **Tipos de documentación:**
   - **Decisiones de diseño:** Documentar decisiones arquitectónicas importantes
   - **Esquemas:** Diagramas de base de datos, flujos, arquitectura
   - **APIs:** Documentación de endpoints y servicios
   - **Guías:** Cómo hacer algo específico
   - **Referencias:** Información de consulta rápida

4. **Formato:**
   - Usar Markdown para toda la documentación
   - Incluir diagramas cuando sea necesario (Mermaid, imágenes)
   - Usar código con sintaxis highlighting
   - Mantener estructura clara con encabezados

5. **Actualización:**
   - Documentar cambios importantes en cada commit
   - Mantener la documentación sincronizada con el código
   - Actualizar cuando se modifique funcionalidad relacionada

### Archivos Principales en la Raíz

- `documentacion/01-setup-inicial/documentacionInicial.md` - Documento de arranque completo del proyecto
- `ORDEN-COMMITS.md` - Plan de commits incrementales
- `ROUTE-MAP.md` - Mapa de ruta del proyecto (estado, completado, pendiente)
- `README.md` - Documentación principal del proyecto

### Reglas Relacionadas

- `.cursor/rules/design-rules.md` - Reglas de diseño UI/UX y Bootstrap
- `.cursor/rules/code-standards.md` - Estándares de código, clean code, MVC y comentarios

### Cuándo Documentar

- ✅ Al crear un nuevo módulo o funcionalidad
- ✅ Al tomar decisiones arquitectónicas importantes
- ✅ Al implementar flujos complejos
- ✅ Al definir esquemas de base de datos
- ✅ Al crear APIs o servicios
- ✅ Al establecer convenciones del proyecto
- ✅ **Después de cada commit importante:** Actualizar `ROUTE-MAP.md` marcando lo completado

### Ejemplos de Documentación por Módulo

**01-setup-inicial/**
- `instalacion-laravel.md`
- `configuracion-bootstrap.md`
- `estructura-proyecto.md`

**02-autenticacion/**
- `flujo-autenticacion.md`
- `configuracion-breeze.md`
- `middleware-autenticacion.md`

**03-vault-items/**
- `esquema-vault-items.md`
- `flujo-crud.md`
- `cifrado-secretos.md`

**04-comparticion/**
- `sistema-comparticion.md`
- `permisos-acl.md`
- `flujo-compartir.md`

**05-grupos/**
- `esquema-grupos.md`
- `roles-permisos.md`
- `invitaciones.md`

**06-auditoria/**
- `sistema-auditoria.md`
- `eventos-listeners.md`
- `export-logs.md`

**07-seguridad/**
- `modelo-cifrado.md`
- `mejores-practicas.md`
- `checklist-seguridad.md`

**08-administracion/**
- Panel de administración (usuarios, vault, grupos, seguridad, configuración)

**09-saas-multi-tenant/**
- `decisiones-mvp.md`
- `plan-implementacion-fases.md`
- `decisiones-previas-codigo.md`
- `facturacion-futuro.md`

**10-organizaciones/**
- CRUD organizaciones, flujo de creación, contraseña temporal, regenerar

**11-notificaciones-invitaciones/**
- `plan-implementacion.md`
- `README.md`
- `configuracion-reverb.md`
- `produccion-reverb.md`

**12-extension-navegador/**
- `README.md`
- `plan-extension-mvp.md` — Plan extensión Chrome: MVP, Integraciones, versionado, actualización automática

### Prohibiciones

❌ NO crear documentación fuera de la carpeta `documentacion/`
❌ NO mezclar documentación de diferentes módulos en un solo archivo
❌ NO usar formatos propietarios (preferir Markdown)
❌ NO dejar documentación desactualizada
❌ NO olvidar actualizar `ROUTE-MAP.md` después de completar funcionalidades

### ROUTE-MAP.md

El archivo `ROUTE-MAP.md` en la raíz del proyecto es el mapa de ruta principal que muestra:
- Estado general del proyecto
- Lo que está completado (✅)
- Lo que está en progreso (🚧)
- Lo que está pendiente (⏳)
- Métricas de progreso por módulo
- Próximos pasos

**Importante:** Actualizar este archivo después de cada commit importante para mantener el estado del proyecto actualizado.

---

**Última actualización:** 2026-02-05 — Estructura ampliada con módulos 08–11 (administración, SaaS, organizaciones, notificaciones).
