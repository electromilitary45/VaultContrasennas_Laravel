# Roadmap - PassVault

Plan de producto y visión futura del proyecto. Funcionalidades planificadas, mejoras y objetivos a largo plazo.

**Última actualización:** 2026-02-05 (Documentación sincronizada con ROUTE-MAP y README)

> **Nota:** Para detalles técnicos de implementación, ver [`ROUTE-MAP.md`](ROUTE-MAP.md)

---

## 🎯 Visión del Producto

PassVault aspira a ser una solución completa y segura para la gestión de contraseñas y secretos, con énfasis en colaboración, seguridad y facilidad de uso.

---

## ✅ Estado Actual del MVP

**MVP Core: ~98% completado**

### Funcionalidades Core Implementadas
- ✅ Autenticación completa (Laravel Breeze)
- ✅ CRUD de Vault Items (5 tipos: Auth, Card, API Key, SSH Key, Note)
- ✅ Cifrado de secretos (AES-256-CBC)
- ✅ Compartición con usuarios y grupos
- ✅ Gestión de grupos con roles
- ✅ Carpetas jerárquicas (personales y de grupo)
- ✅ TOTP/2FA con QR codes
- ✅ Dashboard mejorado
- ✅ **Auditoría:** registro de eventos (create, update, delete, view, share), vista por usuario/org, exportación CSV, filtros

### SaaS Multi-Tenant (Completado)
- ✅ Organizaciones; platform super admin vs org admin
- ✅ Códigos de invitación y registro con código (sin registro abierto)
- ✅ Creación manual de usuarios por org admin
- ✅ Scoping admin y app por `organization_id` (vault, grupos, carpetas, auditoría)
- ✅ Dashboard admin con métricas por org / global; logs recientes
- ✅ Seguridad y Configuración solo para platform super admin

---

## 🚧 En Desarrollo

- _(Nada activo.)_ **Sistema de Notificaciones e Invitaciones:** Todas las fases (1–4) completadas ✅ (Sistema base, invitaciones a grupos, solicitudes de acceso, notificaciones de compartición y creación de items). Ver [`documentacion/11-notificaciones-invitaciones/plan-implementacion.md`](documentacion/11-notificaciones-invitaciones/plan-implementacion.md).

---

## 📋 Próximas Funcionalidades

### 🔴 Alta Prioridad (Q1-Q2 2026)

#### 1. Completar MVP
- [x] Sistema de auditoría (logs, filtros, export CSV, scope por org)
- [ ] Mejoras de seguridad (rate limiting, logging de intentos fallidos)
- [ ] Política de contraseñas fuerte

#### 2. Importación/Exportación
- [ ] Exportación de items (JSON/CSV)
- [ ] Importación desde Bitwarden/1Password
- [ ] Backup completo del vault
- [ ] Restauración desde backup

#### 3. Mejoras de Seguridad
- [ ] Rate limiting en endpoints sensibles
- [ ] Sesiones concurrentes
- [ ] Encriptación de backups
- [ ] Detección de contraseñas comprometidas

### 🟡 Media Prioridad (Q2-Q3 2026)

#### 4. Mejoras de UX
- [ ] Búsqueda global mejorada
- [ ] Tags/etiquetas para items
- [ ] Vistas personalizadas
- [ ] Atajos de teclado
- [x] **Notificaciones en tiempo real (Fase 1):** Sistema base completado ✅ (WebSockets con Reverb, bandeja en navbar, página dedicada, toast).
- [x] **Invitaciones a grupos (Fase 2):** Completado ✅ (aceptar/rechazar invitaciones, notificaciones, pestaña en `/groups`, acciones rápidas).
- [x] **Solicitudes de acceso a grupos (Fase 3):** Completado ✅ (solicitar acceso, aprobar/rechazar, pestañas "Grupos Disponibles" y "Solicitudes", notificaciones).
- [x] **Notificaciones de compartición y creación de items (Fase 4):** Completado ✅ (notificaciones al compartir items con usuarios/grupos, notificaciones al crear items en grupos, acciones rápidas). Ver [`documentacion/11-notificaciones-invitaciones/`](documentacion/11-notificaciones-invitaciones/).

#### 5. Funcionalidades Avanzadas
- [ ] **Item Archivo .env:** Nuevo tipo de item para compartir .env con visibilidad granular. Un item = archivo completo; el owner elige por línea o sección (click-and-drag mixto) qué ve cada usuario. Ver [`documentacion/03-vault-items/env-file-item.md`](documentacion/03-vault-items/env-file-item.md).
- [ ] Compartir carpetas completas
- [ ] Plantillas de items
- [ ] Generador de contraseñas mejorado
- [ ] Análisis de fortaleza de contraseñas
- [ ] Historial de versiones visual

#### 6. API REST
- [ ] Endpoints de API
- [ ] Autenticación por tokens
- [ ] Documentación con Swagger/OpenAPI
- [ ] Rate limiting para API
- [ ] Webhooks

### 🟢 Baja Prioridad / Futuro (Q3-Q4 2026+)

#### 7. Funcionalidades Premium
- [ ] Compartir por enlaces públicos (con expiración)
- [ ] Compartir con usuarios externos (sin cuenta)
- [ ] Comparación de versiones
- [ ] Restauración de versiones anteriores

#### 8. Integraciones
- [x] **Extensión de navegador (Chrome):** MVP implementado (popup con lista, búsqueda, copiar usuario/contraseña, tema claro/oscuro/sistema, Ajustes, comprobación de actualización; content script auto-fill; API de extensión; página Integraciones con versión y descarga ZIP). Ver [`documentacion/12-extension-navegador/`](documentacion/12-extension-navegador/README.md) y [`plan-extension-mvp.md`](documentacion/12-extension-navegador/plan-extension-mvp.md).
- [ ] Extensión Firefox (post-MVP Chrome)
- [ ] Aplicación móvil (iOS/Android)
- [ ] Integración con servicios externos
- [ ] Sincronización en la nube

#### 9. Colaboración Avanzada
- [ ] Comentarios en items
- [x] **Notificaciones de cambios (Fase 4):** Completado ✅ (notificaciones al compartir items con usuarios/grupos, al crear items en grupos, acciones rápidas). Ver [`documentacion/11-notificaciones-invitaciones/`](documentacion/11-notificaciones-invitaciones/).
- [ ] Workflows de aprobación
- [ ] Delegación temporal de permisos

---

## 🎯 Hitos y Objetivos

### Hito 1: MVP Completo ✅ (Q1 2026)
- [x] Autenticación
- [x] CRUD de items
- [x] Compartición
- [x] Grupos
- [x] Auditoría básica
- [x] SaaS multi-tenant (orgs, códigos invitación, scoping)

### Hito 2: Seguridad Avanzada (Q2 2026)
- [x] Auditoría (logs, filtros, export, org)
- [ ] Rate limiting
- [ ] Mejoras de seguridad (logging fallidos, etc.)
- [ ] Backup/restore

### Hito 3: Integraciones (Q3 2026)
- [ ] API REST
- [ ] Importación/Exportación
- [ ] Extensiones de navegador

### Hito 4: Producto Premium (Q4 2026+)
- [ ] Aplicación móvil
- [ ] Funcionalidades premium
- [ ] Colaboración avanzada

---

## 📊 Métricas de Progreso

### Por Área Funcional

| Área | Estado | Progreso |
|------|--------|----------|
| Autenticación | ✅ Completado | 100% |
| Gestión de Items | ✅ Completado | 100% |
| Compartición | ✅ Completado | 100% |
| Grupos | ✅ Completado | 100% |
| Carpetas | ✅ Completado | 100% |
| TOTP/2FA | ✅ Completado | 100% |
| Dashboard | ✅ Completado | 100% |
| Auditoría | ✅ Completado | 100% |
| SaaS Multi-Tenant | ✅ Completado | 100% |
| Notificaciones (Fase 1) | ✅ Completado | 100% |
| Invitaciones a Grupos (Fase 2) | ✅ Completado | 100% |
| Solicitudes de Acceso (Fase 3) | ✅ Completado | 100% |
| Notificaciones de Compartición (Fase 4) | ✅ Completado | 100% |
| Importación/Exportación | ⏳ Pendiente | 0% |
| API REST | ⏳ Pendiente | 0% |
| Extensiones (Chrome) | ✅ MVP implementado | ~90% |
| App Móvil | ⏳ Pendiente | 0% |

---

## 🎨 Principios de Diseño

- **Seguridad por diseño**: La seguridad es prioritaria en cada decisión
- **Simplicidad**: Interfaz intuitiva y fácil de usar
- **Colaboración**: Herramientas para trabajar en equipo
- **Open Source**: Código abierto y comunidad

---

## 📝 Notas

- Este roadmap es dinámico y puede cambiar según feedback de usuarios
- Las funcionalidades se priorizan según necesidad y recursos
- El MVP incluye auditoría y SaaS multi-tenant (orgs, códigos invitación, scoping). Próximos focos: seguridad avanzada, import/export.
- Para detalles técnicos de implementación, consultar [`ROUTE-MAP.md`](ROUTE-MAP.md)

---

## 🎨 Leyenda

- ✅ Completado
- 🚧 En Desarrollo
- ⏳ Pendiente
- 🔴 Alta Prioridad
- 🟡 Media Prioridad
- 🟢 Baja Prioridad
