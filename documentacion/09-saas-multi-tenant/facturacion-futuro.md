# Facturación y planes — Futuro (fuera del MVP)

Notas y decisiones para una **fase posterior** al MVP multi-tenant. No forma parte del alcance actual.

**Última actualización:** 2026-01-24

---

## 1. Modelo de facturación

- **Facturación por organización:** cada empresa (tenant) es un cliente de facturación.
- **Responsable de facturación:** super admin de la organización.
- **Pasarela:** inicialmente **facturación manual**; más adelante **pasarela de pago propia**.

---

## 2. Planes (a definir)

- MVP: solo uso **gratuito**.
- En el futuro se podrán definir:
  - Planes por organización (ej. Free, Pro, Enterprise).
  - Límites asociados (usuarios, items, almacenamiento).
  - Precios y periodicidad (mensual, anual).

---

## 3. Qué dejar preparado (opcional)

Si se desea facilitar la futura implementación:

- **Modelo de datos:** campo `organization.billing_contact_user_id` o similar, y tablas para planes/suscripciones cuando se implementen.
- **Documentación:** describir en detalle el flujo de facturación deseado (quién contrata, cómo se renueva, qué pasa si no paga, etc.).
- **Límites:** estructura para aplicar límites por org según plan (aunque no se activen en MVP).

---

## 4. Estado

- **Pendiente:** diseño detallado de planes, precios y flujo de facturación.
- **Pendiente:** elección e integración de pasarela propia.

Este documento se ampliará cuando se priorice la fase de facturación.
