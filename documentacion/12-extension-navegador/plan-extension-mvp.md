# Plan: Extensión de navegador OCIANN Vault (Open Source)

Plan de producto y técnico para una extensión de navegador open source que se integre con OCIANN Vault, con sección Integraciones en la web, versionado y actualización automática.

---

## 1. Visión y enfoque open source

- **Objetivo:** Ofrecer una extensión de navegador **open source** que permita usar el vault desde el navegador (autocompletar, copiar credenciales, ver items) sin salir de la pestaña.
- **Licencia:** Alineada al proyecto (ej. MIT) para que la comunidad pueda auditar, mejorar y reutilizar.
- **Inspiración:** Tomar las mejores ideas de Bitwarden, 1Password, LastPass y similares, adaptadas a nuestro modelo (cuenta en nuestro servidor, sin E2EE obligatorio en MVP si el backend ya cifra).

---

## 2. Mejores ideas de otras aplicaciones (referencia)

### 2.1 Bitwarden

- **Popup compacto:** Búsqueda rápida, lista de items (solo títulos/usuarios), clic para revelar/copiar contraseña.
- **Auto-fill:** Detecta formularios de login (inputs tipo password + usuario/email) y ofrece rellenar con un item del vault.
- **Guardar nuevo login:** Tras enviar un formulario de login, ofrece “¿Guardar en el vault?” (consentimiento explícito).
- **Generador de contraseñas:** En popup y en formularios (campo contraseña).
- **Badge:** Número de items o estado (bloqueado / desbloqueado).
- **Bloqueo por inactividad:** Tras X minutos sin uso, pide PIN o contraseña maestra de nuevo.

### 2.2 1Password

- **Detección de campos:** Muy afinada (placeholder, name, id, autocomplete).
- **Categorías:** Ver items por tipo (Login, Tarjeta, etc.) en la extensión.
- **One-time passwords (TOTP):** Mostrar/copiar código TOTP desde el popup.

### 2.3 Otras ideas útiles

- **KeePass / navegador:** Conexión con base local; nosotros usamos servidor, pero la UX de “elegir qué credencial usar para este sitio” es referente.
- **Último uso:** “Último item usado” o “sugerencia para este dominio” para reducir clics.
- **Atajos de teclado:** Por ejemplo Ctrl+Shift+L para abrir popup o rellenar.

Resumen: para nuestro **MVP** priorizamos popup con búsqueda y copia, auto-fill en formularios de login, y opcionalmente “guardar este login” y generador de contraseñas. TOTP y bloqueo por tiempo pueden ser post-MVP.

---

## 3. Integración 100% con la aplicación main

La extensión **no duplica lógica ni datos**: consume exactamente lo que ya tiene la aplicación Laravel (mismo usuario, misma organización, mismos items y permisos). Todo lo que ve y hace la extensión debe ser coherente con lo que el usuario ve en la web.

### 3.1 Principios

- **Una sola fuente de verdad:** El backend Laravel (VaultService, FolderService, ShareService, VaultItemPolicy) es quien decide qué items ve el usuario y qué puede hacer con ellos. La extensión solo llama a la aplicación.
- **Mismo usuario y organización:** El usuario que usa la extensión es el mismo que en la web (`organization_id`, `is_active`, `must_change_password`). No hay "cuenta de extensión" separada.
- **Mismos datos visibles:** Lista de items = misma que en `/vault` (items propios + compartidos directos + compartidos por grupo), con el mismo scope por `organization_id`. Tipos de item: `auth`, `note`, `card`, `api_key`, `ssh_key` (auto-fill solo para `auth`).
- **Mismos permisos:** Quién puede ver/editar en la web puede ver/copiar en la extensión; quien no tiene acceso en la web no lo tiene en la extensión.
- **Sin lógica duplicada:** Descifrado de secretos sigue en el servidor (CryptoService). La extensión recibe ya el secreto descifrado para la sesión autorizada, igual que la vista web.

### 3.2 Autenticación: dos opciones compatibles con la app actual

| Opción | Cómo funciona | Ventaja |
|--------|----------------|---------|
| **A. Sesión (mismo dominio)** | La extensión tiene permiso de host sobre el dominio del vault (ej. `https://vault.ocianncloud.com`). El usuario inicia sesión (y 2FA, cambio de contraseña si aplica) en una pestaña del vault. La extensión hace `fetch(url, { credentials: 'include' })` a ese mismo dominio y reutiliza las cookies de sesión. | No requiere API ni tokens; mismos middleware `auth`, `ensure-password-changed` que la web. |
| **B. Token API (futuro)** | Se añade Laravel Sanctum (o similar). En la web, en Integraciones, el usuario genera un token "Para la extensión". La extensión guarda el token y lo envía en `Authorization: Bearer ...`. El backend identifica al usuario y usa los mismos servicios. | La extensión funciona aunque el usuario no tenga el vault abierto en una pestaña. |

**Recomendación:** Implementar primero **Opción A** (endpoints JSON que requieran la misma sesión web). Así la extensión queda 100% integrada sin tocar el modelo de auth actual. Opción B se puede añadir después.

### 3.3 Qué debe exponer la aplicación main para la extensión

La aplicación actual no tiene `routes/api.php` ni Sanctum. Para integración 100% hace falta:

1. **Endpoints JSON** (protegidos por `auth` y `ensure-password-changed`), por ejemplo:
   - `GET /api/extension/vault/items` — Lista de items (metadatos: id, title, type, username si es auth, favorite, folder_id). Misma lógica que `VaultService::listItems()` y scope por `organization_id`. Parámetros: `search`, `type`, `favorite`, `folder_id`.
   - `GET /api/extension/vault/items/{id}` — Detalle de un item con secreto descifrado (solo si el usuario tiene permiso). Misma lógica que `VaultController::show` + policy. Para tipo `auth`: `username`, `password`; para otros tipos los campos que correspondan.
   - Opcional: `GET /api/extension/folders` — Carpetas del usuario. Misma lógica que `FolderService::getFoldersForSelect()`.

2. **CORS y credenciales:** Si la extensión hace fetch desde popup/background al dominio del vault, el origen es `chrome-extension://<id>`. El backend debe permitir en CORS ese origen y `Access-Control-Allow-Credentials: true`. En Laravel: configurar `config/cors.php` para el origen de la extensión.

3. **Reutilizar servicios y policies:** Los endpoints deben usar `VaultService`, `FolderService` y `VaultItemPolicy::view`; no duplicar lógica.

### 3.4 Reglas de negocio que la extensión hereda (sin reimplementar)

- Usuario inactivo (`is_active = false`): la sesión web ya lo impide; la extensión no podrá obtener datos.
- Cambio de contraseña obligatorio (`must_change_password`): middleware `ensure-password-changed`; la extensión usa la misma sesión, queda bloqueada hasta que el usuario cambie la contraseña en la web.
- Organización: solo se ven items de la organización del usuario (`organization_id`). Platform super admin según lógica actual.
- Permisos por item: owner, compartido usuario, compartido grupo (view/edit/admin). La extensión solo necesita lectura; ya definida en `VaultItemPolicy`.
- 2FA: el usuario inicia sesión (y 2FA) en la web; la extensión usa esa misma sesión.

### 3.5 Resumen

- **Backend:** Añadir rutas JSON (bajo `auth` + `ensure-password-changed`) que devuelvan lista de items y detalle con secreto, reutilizando `VaultService` y policies. Configurar CORS para `chrome-extension://...` si la extensión llama desde popup/background.
- **Extensión:** Solo consume esos endpoints con la sesión (cookie) o con token futuro. No implementa usuarios, organizaciones ni permisos propios; todo viene de la aplicación main.

---

## 4. Qué podemos hacer (lista de capacidades)

| Capacidad | Descripción | MVP |
|-----------|-------------|-----|
| Popup con lista/búsqueda | Ver items del vault (título, usuario, tipo); búsqueda por texto | ✅ |
| Copiar usuario / contraseña | Clic en item → copiar al portapapeles (y opcional “pegar en campo activo”) | ✅ |
| Auto-fill en formularios | Detectar páginas de login y ofrecer rellenar con un item elegido | ✅ |
| Guardar nuevo login | Tras enviar un formulario, oferta “Guardar en OCIANN Vault” | ✅ Opcional |
| Generador de contraseñas | En popup y/o en campos password (insertar generada) | ✅ Opcional |
| Badge (icono) | Número de items o candado (bloqueado) | ✅ |
| Autenticación en extensión | Login con usuario/contraseña (y 2FA si aplica) o reutilizar sesión web | ✅ |
| TOTP en popup | Ver/copiar código TOTP del item | ⏳ Post-MVP |
| Bloqueo por inactividad | Tras X min, pedir PIN/maestra de nuevo | ⏳ Post-MVP |
| Integraciones en la web | Página/sección “Integraciones” con enlace a descarga y versión | ✅ |
| Versionado | Timestamp en `version.txt`; API de versión y descarga; comprobación en popup | ✅ |
| Actualización automática | Chrome Web Store o update_url self-hosted | ✅ |

---

## 5. MVP de la extensión (alcance mínimo)

Objetivo: extensión usable y publicable, con integración básica al vault y a la web.

### 5.1 Funcionalidades MVP

1. **Autenticación**
   - Inicio de sesión desde la extensión (usuario + contraseña; 2FA si el backend lo soporta vía API).
   - Alternativa: “Abrir vault en pestaña” para iniciar sesión en la web y que la extensión reutilice sesión (mismo dominio/cookies) si técnicamente viable y seguro.

2. **Popup**
   - Lista de items del vault (solo metadatos: título, nombre de usuario, tipo).
   - Búsqueda por título/usuario.
   - Al hacer clic en un item: opciones “Copiar usuario” y “Copiar contraseña” (y opcional “Rellenar en esta pestaña” si hay pestaña activa con formulario).

3. **Auto-fill**
   - Content script que detecte formularios con al menos un campo tipo `password` y uno de usuario/email.
   - Mostrar icono o botón “Rellenar con OCIANN Vault”: abre selector de item y rellena los campos.

4. **Badge**
   - Mostrar en el icono de la extensión un número (cantidad de items) o un estado (ej. “—” si no hay sesión).

5. **Guardar nuevo login (opcional en MVP)**
   - Tras enviar un formulario de login, si la página coincide con reglas básicas, mostrar mensaje “¿Guardar este inicio de sesión en OCIANN Vault?” y abrir la web del vault en “crear item” con datos pre-rellenados (vía query params o API).

6. **Generador de contraseñas (opcional en MVP)**
   - En el popup, botón “Generar contraseña” que genere una segura y la copie (y opcionalmente la inserte en el campo activo si es un input password).

### 5.2 Dependencias del MVP

- **API REST del vault:** Endpoints mínimos: login (y 2FA si hay), listado de items (metadatos, sin secretos en listado), obtener un item (con secreto descifrado para la sesión autorizada). Si la API aún no existe, se puede acotar un “API mínima para extensión” y documentarla en este módulo.
- **CORS y cookies:** Si la extensión llama a la misma instancia del vault (mismo dominio), usar sesión cookie; si es distinto dominio, tokens (Bearer) y CORS configurado en Laravel.

### 5.3 No incluido en MVP

- TOTP en popup.
- Bloqueo por inactividad (PIN/tiempo).
- Soporte Firefox (se puede planificar en paralelo o justo después del MVP Chrome).
- Compartir/colaboración desde la extensión (solo lectura/copia y rellenar).

---

## 6. Sección “Integraciones” en la aplicación web

Objetivo: que el usuario vea en un solo lugar la opción de descargar la extensión, la versión recomendada y que perciba confianza (versionado y actualizaciones).

### 6.1 Dónde ubicarla

- **Opción A (recomendada):** Nueva sección **Integraciones** en el menú principal (junto a Vault, Grupos, etc.), visible para usuarios autenticados.
- **Opción B:** Dentro de **Configuración / Perfil** como subsección “Integraciones y extensiones”.
- **Opción C:** Ambas: enlace destacado en Dashboard (“Usa la extensión de navegador”) y página dedicada bajo “Integraciones”.

Recomendación: **Opción A** — ruta `/integrations` con su entrada en la navegación, y en esa página la “Extensión de navegador” como primera (o única al inicio) integración.

### 6.2 Contenido de la página Integraciones

- **Listado de integraciones:** Tarjetas o bloques por integración.
- **Extensión de navegador:**
  - Título: “Extensión para Chrome” (y luego “para Firefox” cuando exista).
  - Descripción corta: autocompletar contraseñas, copiar desde el popup, etc.
  - **Versión mostrada:** “Versión actual: 1.0.0” (leída desde backend o fichero estático, ver sección 6).
  - **Botón principal:** “Descargar / Añadir a Chrome” que enlace a:
    - Chrome Web Store (si está publicada), o
    - Página de “Instalación manual” (instrucciones para desarrolladores / .crx o “Cargar descomprimida”).
  - Enlace secundario: “Cómo usar la extensión”, “Política de privacidad” o “Código fuente (GitHub)” si aplica.

### 6.3 Datos que debe mostrar

- Nombre de la integración.
- Versión actual (para que el usuario sepa si tiene la última).
- Enlace de descarga/instalación.
- Opcional: changelog mínimo o “Novedades de la última versión”.

---

## 7. Versionado

### 7.1 Esquema (implementado)

- **Timestamp:** La fuente de verdad es `browser-extension/version.txt`: un único valor, el **Unix timestamp** (segundos desde 1970). No se usa SemVer en la extensión para simplificar: cada release es un timestamp nuevo.
- **Sincronización:** Desde la raíz del proyecto, `php artisan extension:sync-version` escribe el timestamp actual en `version.txt` y actualiza `public/extension-assets/browser-extension.json`. La página Integraciones y la API de extensión leen esa versión.

### 7.2 Cómo expone la web la versión actual

- **API:** `GET /api/extension/version` devuelve `version` (timestamp) y `download_url` (URL del ZIP en el vault). La página Integraciones y el popup (Ajustes → Actualizar extensión) usan esta API.
- **Fichero estático:** `public/extension-assets/browser-extension.json` se actualiza con `extension:sync-version` y puede usarse para mostrar la versión en la web sin llamar a la API.

---

## 8. Actualización automática de la extensión

### 8.1 Publicación en Chrome Web Store (recomendado)

- Subes cada nueva versión (zip con `manifest.json` actualizado) a la Chrome Web Store.
- Chrome actualiza la extensión automáticamente a todos los usuarios cuando hay nueva versión publicada.
- No hace falta `update_url` en el manifest; el store se encarga.

Ventajas: actualización automática sin infra propia, confianza del usuario (instalación desde la tienda). Desventaja: revisión y políticas de Google.

### 8.2 Distribución self-hosted (alternativa)

- Si no publicas en la Store (por ejemplo solo para uso interno o beta):
  - En `manifest.json` (Manifest V2) puedes usar `update_url` apuntando a un XML de actualización en tu servidor.
  - Chrome consulta ese XML y, si hay una versión mayor, descarga el .crx desde la URL indicada.
- Requiere servir un XML con formato específico de Chrome y el .crx firmado.

Para un producto open source público, lo más simple es **publicar en Chrome Web Store** y usar su actualización automática; la sección Integraciones solo necesita el enlace a la Store y mostrar la versión (sincronizada con lo que publiques).

### 7.3 Sincronizar versión en web y actualización en el popup

- Al publicar una nueva versión (ZIP desde Integraciones o Chrome Web Store), ejecutar `php artisan extension:sync-version` para que la web y la API muestren la nueva versión. En el popup, "Actualizar extensión" compara la versión instalada (lectura de `version.txt` incluido en el ZIP) con la API y ofrece descargar el ZIP si hay actualización.

---

## 9. Resumen de tareas (orden sugerido)

1. **Backend / Web (aplicación main)**
   - [ ] Crear ruta y vista **Integraciones** (`/integrations`).
   - [ ] Añadir entrada “Integraciones” en la navegación (usuarios autenticados).
   - [ ] Definir origen de la versión (JSON estático o endpoint) y mostrarla en la tarjeta “Extensión de navegador”.
   - [ ] Añadir enlace a Chrome Web Store (placeholder hasta tener la extensión publicada) o a “Instalación manual”.

2. **API (si aún no existe)**
   - [ ] Definir “API mínima para extensión”: login, 2FA (si hay), listado de items (metadatos), obtener un item (con secreto para sesión autorizada).
   - [ ] Documentar en este módulo o en `documentacion/03-vault-items/` o en una futura `documentacion/13-api/`.

3. **Extensión Chrome**
   - [x] Crear proyecto en `browser-extension/` con Manifest V3, popup, content script.
   - [x] Autenticación: reutilizar sesión web (usuario inicia sesión en pestaña del vault; extensión hace fetch con `credentials: 'include'` a los endpoints `/api/extension/...`). Sin duplicar login ni 2FA en la extensión.
   - [x] Popup: lista + búsqueda, copiar usuario/contraseña; badges tipo y contexto (Compartido/En grupo); tema claro/oscuro/sistema (guardado en `chrome.storage.local`); vistas principal, login requerido y Ajustes; estado de carga (spinner); UI con Bootstrap 5 y bloque `<style>` mínimo para transiciones y hover.
   - [x] Content script: detección de formularios de login y auto-fill.
   - [x] Badge con número de items (o vacío si no hay sesión).
   - [ ] Opcional MVP: “Guardar este login”, generador de contraseñas.
   - [x] Versionado por timestamp en `version.txt`; API `/api/extension/version`; en popup, Ajustes → "Actualizar extensión" (comprueba versión y ofrece descargar ZIP).

4. **Publicación y actualización**
   - [x] Página Integraciones con versión actual (timestamp), descarga ZIP y pasos de instalación; comando `extension:sync-version` para publicar nueva versión.
   - [ ] Publicar en Chrome Web Store (actualizaciones automáticas); o documentar update_url para self-hosted.
   - [ ] Documentar en este módulo el proceso de release (tag, build, subida a Store, ejecutar `extension:sync-version`).

---

## 10. Referencias

- [Chrome Extension docs](https://developer.chrome.com/docs/extensions/)
- [Manifest V3](https://developer.chrome.com/docs/extensions/mv3/intro/)
- [Chrome Web Store publishing](https://developer.chrome.com/docs/webstore/publish/)
- [Extension update manifest (update_url)](https://developer.chrome.com/docs/extensions/mv3/hosted/)

---

**Última actualización:** 2026-02-05
