# Setup Inicial

Documentación relacionada con la configuración inicial del proyecto.

## Contenido

- Instalación de Laravel
- Configuración de base de datos
- Instalación de Bootstrap
- Configuración de Vite
- Estructura inicial del proyecto

## Archivos

- [`documentacionInicial.md`](documentacionInicial.md) - Documento de arranque completo del proyecto con arquitectura, esquema de datos y plan de implementación
- [`comandos-desarrollo.md`](comandos-desarrollo.md) - Guía de comandos para desarrollo: `composer dev` vs `npm run dev`, servicios, compilación

## Tras el setup

- Ejecutar `php artisan migrate` (incluye SuperAdminSeeder para el **platform super admin**).
- Para el modelo **SaaS multi-tenant** (organizaciones, códigos de invitación, scoping): ver [`documentacion/09-saas-multi-tenant/`](../09-saas-multi-tenant/).
