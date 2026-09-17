# Extensión de navegador - PassVault

Documentación del plan y desarrollo de la extensión de navegador (Chrome y futuramente Firefox) para PassVault: MVP, inspiración en Bitwarden y otras apps, sección Integraciones en la web, versionado y actualización automática.

## Contenido del módulo

- **[plan-extension-mvp.md](plan-extension-mvp.md)** — Plan completo: conversación, mejores ideas de Bitwarden/1Password, qué podemos hacer, MVP, sección Integraciones, versionado y actualización automática.

## Estado

- **Estado actual:** MVP de extensión implementado — API, popup con tema y ajustes, actualización desde el vault, content script (autofill).
- **Repositorio extensión:** Carpeta `browser-extension/` en el mismo repo (monorepo).
- **Integración 100% con la app main:** La extensión no duplica lógica: consume los mismos datos y permisos que la web (mismo usuario, organización, VaultService, policies). Ver plan, sección 3.
- **Backend:** Endpoints JSON: `GET /api/extension/vault/items`, `GET /api/extension/vault/items/{id}` (controlador `ExtensionApiController`), `GET /api/extension/version` (versión y URL de descarga). Protegidos por `auth` + `ensure-password-changed` (salvo `version`, público). CORS para `chrome-extension://` (middleware `ExtensionCorsMiddleware`).
- **Extensión:** Manifest V3; popup con lista, búsqueda, copiar usuario/contraseña, tema (claro/oscuro/sistema), vistas (principal, login requerido, Ajustes), estado de carga, comprobación de actualización desde el vault; **badge** en el icono (número de items, o vacío sin sesión); content script (detección de formularios de login y botón "Rellenar con PassVault").
- **Web:** Página **Integraciones** (`/integrations`) con tarjeta de la extensión Chrome, versión actual (timestamp en `version.txt`), descarga ZIP y pasos de instalación.

## Relación con el proyecto

- La **aplicación web** expone la sección **Integraciones** donde el usuario ve la opción de descargar la extensión, la versión actual recomendada y el enlace a la tienda (Chrome Web Store) o a instalación manual (ZIP).
- **Versionado:** Se usa **timestamp** (Unix) en `browser-extension/version.txt` como única fuente de verdad. Comando `php artisan extension:sync-version` actualiza `version.txt` y `public/extension-assets/browser-extension.json`. La API `/api/extension/version` devuelve esa versión y la URL de descarga del ZIP.
- **Actualización:** Desde el popup (Ajustes → "Actualizar extensión") se consulta la API de versión; si hay versión nueva se ofrece descargar el ZIP desde el vault. En Chrome Web Store las actualizaciones son automáticas cuando se publica una nueva versión.
