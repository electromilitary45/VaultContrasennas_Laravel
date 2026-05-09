# OCIANN Vault - Extensión para Chrome

Extensión de navegador que se integra 100% con la aplicación OCIANN Vault: misma sesión, mismos datos y permisos.

## Requisitos

- Tener el vault abierto en una pestaña y **haber iniciado sesión**. La extensión usa la misma sesión (cookies) para llamar a la API.
- Para desarrollo: servidor Laravel corriendo (ej. `http://localhost:8000`).

## Instalación (desarrollo)

1. En Chrome, ve a `chrome://extensions`.
2. Activa **Modo desarrollador**.
3. Pulsa **Cargar descomprimida** y selecciona la carpeta `browser-extension` de este proyecto.
4. Abre el vault en una pestaña (ej. `http://localhost:8000`), inicia sesión (y 2FA si aplica).
5. Haz clic en el icono de la extensión en la barra de Chrome para abrir el popup.

## Configuración

- **Por defecto** la extensión apunta al dominio de producción (`https://vault.ocianncloud.com`) o a la URL inyectada al descargar desde tu instancia.
- **Opciones de desarrollador:** en Ajustes (enlace "Ajustes" en la vista principal o en "Iniciar sesión"), activa "Opciones de desarrollador" para mostrar el campo "URL del vault" y poder usar localhost u otra URL (ej. `http://localhost:8001`). La opción y la URL se guardan en `chrome.storage.local`.

## Funcionalidades

- **Popup:** Lista de items del vault (solo no eliminados), búsqueda por título/usuario. Botones "Usuario" y "Contraseña" para copiar al portapapeles (items tipo `auth`). Badges de tipo de item y de contexto (Compartido / En grupo).
- **Tema:** Selector en el header (Claro / Oscuro / Sistema). La preferencia se guarda en `chrome.storage.local` y se aplica con `data-bs-theme` en el popup.
- **Vistas:** Lista principal, pantalla "Iniciar sesión" si no hay sesión, y panel **Ajustes** (tema, actualizar extensión, opciones de desarrollador).
- **Badge:** En el icono de la extensión se muestra el número de items del vault (o se oculta si no hay sesión). Se actualiza al abrir el popup.
- **Actualización:** En Ajustes, "Actualizar extensión" comprueba la versión del servidor (`GET /api/extension/version`). Si hay versión nueva, ofrece descargar el ZIP desde la página del vault.
- **Autofill:** En páginas con formulario de login (campo password + usuario/email), aparece el botón "Rellenar con OCIANN Vault". Al hacer clic se elige un item y se rellenan los campos.

## API consumida

- `GET /api/extension/vault/items` — Lista de items (metadatos; incluye `share_context`: own, shared_user, shared_group).
- `GET /api/extension/vault/items/{id}` — Detalle con secreto descifrado (para copiar/rellenar).
- `GET /api/extension/version` — Versión actual recomendada y URL de descarga del ZIP (para actualización desde la app).

Todas requieren sesión web (cookies) salvo `version`, que es pública. CORS está configurado en el backend para el origen `chrome-extension://...`.

## Estructura

- `manifest.json` — Manifest V3.
- `popup/` — Popup con **Bootstrap 5** y un bloque `<style>` mínimo en `popup.html` para detalles visuales (transiciones en cards, hover, búsqueda tipo pill, tema). Incluye `bootstrap.min.css` y `bootstrap.bundle.min.js` copiados desde el `node_modules` del proyecto raíz. Estados: carga (spinner), lista de items, estado vacío, login requerido, ajustes.
- `content/content.js` — Content script para detectar formularios de login e inyectar el botón de rellenar.

Si al clonar el repo no tienes `popup/bootstrap.min.css` ni `popup/bootstrap.bundle.min.js`, cópialos desde la raíz del proyecto:

```bash
cp node_modules/bootstrap/dist/css/bootstrap.min.css browser-extension/popup/
cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js browser-extension/popup/
```

## Versión por timestamp

La **única fuente de verdad** es `browser-extension/version.txt`: un archivo con un único valor, el **Unix timestamp** (segundos desde 1970). No se usan números de versión tipo 1.0.0.

- La app Laravel y la API de actualización leen la versión desde `version.txt`.
- La extensión lee su versión desde ese mismo archivo (incluido en el ZIP).
- Para “publicar” una nueva versión, ejecuta desde la raíz del proyecto:

```bash
php artisan extension:sync-version
```

Eso escribe el **timestamp actual** en `browser-extension/version.txt` y actualiza `public/extension-assets/browser-extension.json`. En la web y en el popup la versión se muestra como fecha legible.
