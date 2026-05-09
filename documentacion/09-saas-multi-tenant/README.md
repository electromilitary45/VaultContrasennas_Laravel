# SaaS Multi-Tenant

Documentación del modelo **multi-tenant** para OCIANN Vault: cada organización/empresa tiene su propio super admin y su espacio de datos, bajo una única plataforma.

**Estado:** MVP multi-tenant completado (Fases 1–6). Dashboard admin con métricas por org; Seguridad y Configuración solo para platform super admin.

## Contenido

- **Decisiones de producto y arquitectura** para el MVP multi-tenant.
- **Decisiones previas al código** que hay que cerrar antes de implementar.
- **Plan por fases** con hitos probables (visual/funcional).
- **Notas para el futuro** sobre facturación y planes (fuera del MVP).

## Archivos

| Archivo | Descripción |
|---------|-------------|
| [`decisiones-mvp.md`](decisiones-mvp.md) | Decisiones de alcance, orgs, usuarios, roles, datos y branding para el MVP |
| [`decisiones-previas-codigo.md`](decisiones-previas-codigo.md) | Decisiones que hay que cerrar **antes de codear** (códigos, registro, etc.) |
| [`plan-implementacion-fases.md`](plan-implementacion-fases.md) | Plan por fases con hitos probables y checklist de revisión |
| [`facturacion-futuro.md`](facturacion-futuro.md) | Facturación por organización, planes y pasarela — para fases posteriores |

## Resumen rápido

- **Usuario → 1 organización.** Mismo dominio; identificación por `organization_id`.
- **Solo admin de plataforma** crea organizaciones. Al crear una org, **se crea automáticamente** el super admin de esa org; credenciales se muestran una vez.
- **Alta de usuarios:** **código de invitación** (registro con código) **o** **creación manual** por admin de la org. Sin registro abierto.
- **Una sola base de datos;** todo filtrado por `organization_id`. Sin límites en el MVP.
- **MVP:** multi-tenant + super admin por org, sin facturación. Branding: logo + nombre por org.

## Implementación

1. [`plan-implementacion-fases.md`](plan-implementacion-fases.md): Fases 1–6 completadas y cierre realizado.
2. Para ampliaciones o cambios, revisar [`decisiones-mvp.md`](decisiones-mvp.md) y [`decisiones-previas-codigo.md`](decisiones-previas-codigo.md).

## Relación con otros módulos

- **08-administracion:** el panel se divide en “admin plataforma” vs “admin organización”.
- **02-autenticacion:** login, registro con código y contexto de org (`User.organization_id`).
- **07-seguridad:** aislamiento por org; considerar separación de claves por tenant (ver documento inicial).
