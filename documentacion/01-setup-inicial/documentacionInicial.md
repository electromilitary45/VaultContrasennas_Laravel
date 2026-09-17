# PassVault (Open Source Password Vault) — Documento de Arranque

## 1. Objetivo

1. Construir una aplicación open source tipo “vault de contraseñas” con:
   1) Ítems de autenticación (Auth Items) y otros tipos de secretos.
   2) Organización por grupos/colecciones.
   3) Compartición de ítems con usuarios y/o grupos, con permisos.
   4) Historial/versionado de cambios.
   5) Auditoría (quién accedió/modificó/compartió).
   6) Interfaz web con Bootstrap.

2. Restricción tecnológica:
   1) Laravel (MVC mejorado) + MySQL + migraciones.
   2) Bootstrap para UI.
   3) Enfoque de seguridad por diseño.

---

## 2. Alcance (MVP)

1. Autenticación y sesión:
   1) Registro / login / logout.
   2) 2FA TOTP (opcional MVP, recomendado v1).
   3) Recuperación de cuenta (sin exponer secretos).

2. Dominio “Vault”:
   1) Crear/editar/eliminar Auth Items.
   2) Ver detalle (con controles anti shoulder-surfing básicos).
   3) Versionado de ítems.
   4) Compartir:
      1) Compartir ítem a usuario (permiso: ver / editar / administrar).
      2) Compartir ítem a grupo (permiso: ver / editar / administrar).

3. Grupos:
   1) Crear grupo.
   2) Invitar miembros.
   3) Roles por grupo: owner/admin/member/viewer.

4. Auditoría:
   1) Registro de eventos clave: create/update/delete/share/unshare/view.
   2) Export simple (CSV) para admin del usuario/grupo.

---

## 3. No-Ambito (por ahora)

1. Cliente móvil nativo.
2. Extensión de navegador.
3. Importación/exportación compatible con 1Password/Bitwarden (se deja como “fase 2”).
4. Compartición por enlaces públicos (alto riesgo; solo si se diseña bien).

---

## 4. Modelo de Seguridad (decisión de arquitectura)

> Nota: Un vault “serio” idealmente usa cifrado end-to-end (E2EE) donde el servidor nunca ve el secreto en texto claro.
> Para un MVP web, hay dos rutas. Elegir una y sostenerla.

### 4.1 Ruta A (Recomendada para producto “premium real”): E2EE en el cliente
1. El navegador cifra/descifra.
2. El servidor almacena:
   1) ciphertext + iv + salt + metadata.
3. Ventajas:
   1) El servidor no puede leer secretos.
4. Complejidad:
   1) Manejo de claves, rotación, compartir (reencriptado por destinatario).

### 4.2 Ruta B (MVP rápido): Cifrado en servidor (menos robusto)
1. Laravel cifra antes de persistir y descifra al mostrar.
2. Se usa una llave maestra del servidor (APP_KEY o un KMS externo).
3. Riesgo:
   1) Un compromiso del servidor expone secretos.
4. Mitigación:
   1) Separar llaves por tenant/usuario con envelope encryption.

**Decisión inicial propuesta:**
1. Implementar Ruta B en MVP (por velocidad),
2. Diseñar esquema para migrar a Ruta A sin reescritura total (guardar campos: ciphertext/iv/salt/version).

---

## 5. Arquitectura (Laravel MVC mejorado)

1. Capas:
   1) Controllers: delgados (validación + orquestación).
   2) Services: lógica de negocio (VaultService, ShareService, CryptoService).
   3) Repositories (opcional): consultas complejas.
   4) Policies/Gates: autorización.
   5) Jobs/Queues: tareas pesadas (rotaciones, export, notificaciones).
   6) Events/Listeners: auditoría y notificaciones desacopladas.

2. Estructura sugerida:
   1) `app/Domain/Vault/*` (entidades, DTOs, acciones)
   2) `app/Services/*`
   3) `app/Policies/*`
   4) `app/Http/Controllers/*`
   5) `app/Http/Requests/*` (FormRequest)
   6) `app/Models/*`

---

## 6. Esquema de Datos (MySQL) — Propuesta

### 6.1 Tablas principales

1. `users`
   1) Campos típicos Laravel (name, email, password, etc.)
   2) Recomendado:
      1) `email_verified_at`
      2) `last_login_at` (auditoría)

2. `groups`
   1) `id`
   2) `owner_user_id` (FK users)
   3) `name`
   4) `description` (nullable)
   5) timestamps

3. `group_members`
   1) `id`
   2) `group_id` (FK)
   3) `user_id` (FK)
   4) `role` enum: `owner|admin|member|viewer`
   5) `status` enum: `invited|active|revoked`
   6) `invited_by_user_id` (FK users, nullable)
   7) timestamps
   8) Índices:
      1) unique (`group_id`, `user_id`)

4. `vault_items`
   1) `id`
   2) `owner_user_id` (FK users) — propietario original
   3) `type` enum: `auth|note|card|api_key|ssh_key` (iniciar con `auth`)
   4) `title`
   5) `folder_id` (nullable)
   6) `favorite` tinyint default 0
   7) `status` enum: `active|archived|deleted` (soft-delete lógico)
   8) timestamps
   9) Índices:
      1) (`owner_user_id`, `type`)
      2) (`owner_user_id`, `status`)

5. `vault_item_secrets`
   1) `id`
   2) `vault_item_id` (FK)
   3) `ciphertext` longtext
   4) `iv` varchar(64)
   5) `salt` varchar(128) nullable
   6) `crypto_version` varchar(20) default 'v1'
   7) timestamps
   8) Nota:
      1) En Ruta B, aquí se cifra el JSON del secreto.
      2) En Ruta A, aquí llega ya cifrado desde cliente.

6. `vault_item_versions`
   1) `id`
   2) `vault_item_id` (FK)
   3) `version_number` int
   4) `ciphertext` longtext
   5) `iv` varchar(64)
   6) `salt` varchar(128) nullable
   7) `crypto_version` varchar(20)
   8) `created_by_user_id` (FK users)
   9) timestamps
   10) Índices:
      1) (`vault_item_id`, `version_number`) unique

7. Compartición (ACL):
   1) `item_shares_users`
      1) `id`
      2) `vault_item_id` (FK)
      3) `user_id` (FK)
      4) `permission` enum: `view|edit|admin`
      5) `shared_by_user_id` (FK)
      6) timestamps
      7) unique (`vault_item_id`, `user_id`)
   2) `item_shares_groups`
      1) `id`
      2) `vault_item_id` (FK)
      3) `group_id` (FK)
      4) `permission` enum: `view|edit|admin`
      5) `shared_by_user_id` (FK)
      6) timestamps
      7) unique (`vault_item_id`, `group_id`)

8. `folders`
   1) `id`
   2) `owner_user_id` (FK)
   3) `name`
   4) `parent_id` (nullable, self FK)
   5) timestamps
   6) Índices:
      1) (`owner_user_id`, `name`)

9. `audit_logs`
   1) `id`
   2) `actor_user_id` (FK users, nullable si sistema)
   3) `event` varchar(50) (create_item, view_secret, share_item, etc.)
   4) `entity_type` varchar(50) (vault_item, group, etc.)
   5) `entity_id` bigint
   6) `ip` varchar(64) nullable
   7) `user_agent` text nullable
   8) `meta` json nullable
   9) timestamps
   10) Índices:
      1) (`actor_user_id`, `event`)
      2) (`entity_type`, `entity_id`)

---

## 7. Formato del Secreto (Auth Item)

1. Guardar un JSON cifrado (dentro de `ciphertext`), por ejemplo:
   1) `username`
   2) `password`
   3) `url`
   4) `totp_secret` (opcional)
   5) `notes` (opcional)
   6) `tags` (opcional)

2. Reglas:
   1) Nunca guardar password en texto claro.
   2) No loguear payloads de secretos.
   3) Sanitizar inputs en UI, pero sin “romper” caracteres especiales.

---

## 8. Autorización (Policies)

1. Principios:
   1) El acceso se determina por:
      1) Propiedad (owner),
      2) Share directo a usuario,
      3) Share a grupo + rol del usuario dentro del grupo.

2. Niveles:
   1) `view`: ver metadata + (si aplica) descifrar secreto.
   2) `edit`: actualizar item.
   3) `admin`: compartir/revocar, ver auditoría del item, borrar/archivar.

---

## 9. UI (Bootstrap) — Pantallas mínimas

1. Auth:
   1) Login
   2) Register
   3) Forgot password

2. Vault:
   1) Lista de ítems (buscar, filtrar por tipo, folder, favoritos)
   2) Crear/editar Auth Item
   3) Detalle del ítem (mostrar/ocultar password)
   4) Historial de versiones
   5) Compartir (usuarios/grupos + permisos)

3. Grupos:
   1) Lista de grupos
   2) Detalle del grupo (miembros, roles)
   3) Invitar usuario (por email)

4. Auditoría:
   1) Tabla de logs por usuario/grupo

---

## 10. Rutas (Web)

1. `GET /vault`
2. `GET /vault/items/create`
3. `POST /vault/items`
4. `GET /vault/items/{id}`
5. `GET /vault/items/{id}/edit`
6. `PUT /vault/items/{id}`
7. `POST /vault/items/{id}/share/user`
8. `POST /vault/items/{id}/share/group`
9. `DELETE /vault/items/{id}/share/user/{userId}`
10. `DELETE /vault/items/{id}/share/group/{groupId}`

11. `GET /groups`
12. `POST /groups`
13. `GET /groups/{id}`
14. `POST /groups/{id}/invite`
15. `PUT /groups/{id}/members/{memberId}` (rol/estado)

---

## 11. Plan de Implementación (primer sprint)

1. Inicialización:
   1) Crear proyecto Laravel.
   2) Config MySQL y `.env`.
   3) Instalar Bootstrap (Vite o build actual del stack).
   4) Autenticación (starter kit) y layouts base.

2. Migraciones:
   1) Crear tablas: groups, group_members, vault_items, vault_item_secrets, item_shares_users, item_shares_groups, audit_logs, folders, vault_item_versions.
   2) Añadir índices/uniques.

3. Dominio Vault:
   1) CRUD de Auth Item (metadata + secret cifrado).
   2) Policy de `view/edit/admin`.

4. Compartición:
   1) Compartir a usuario.
   2) Compartir a grupo.
   3) Revocar.

5. Auditoría:
   1) Event + listener para registrar logs en create/update/view/share.

---

## 12. Convenciones de Migraciones (recomendación)

1. Siempre crear modelo + migración juntos:
   1) `php artisan make:model VaultItem -m`
2. Nombrar migraciones con intención:
   1) `create_vault_items_table`
   2) `create_vault_item_secrets_table`
3. No alterar tablas “a mano” fuera de migraciones.
4. Si se adopta un generador tipo Blueprint, documentar el YAML como fuente de verdad.

---

## 13. Checklist de Seguridad (MVP)

1. No exponer secretos en logs (deshabilitar dumps, revisar logs).
2. Rate limiting en login y endpoints sensibles.
3. CSRF activo en formularios.
4. Headers básicos (CSP en fase 2, mínimo X-Frame-Options, etc.).
5. Auditoría de “view_secret”.
6. Rotación futura de claves (diseñar `crypto_version` desde el inicio).

---

## 14. Licencia y Contribución

1. Licencia propuesta:
   1) AGPL-3.0 si se quiere obligar a publicar cambios cuando se ofrece como servicio.
   2) MIT si se prefiere adopción máxima.

2. CONTRIBUTING:
   1) Estándar de PRs.
   2) Reglas de seguridad: no subir secretos, no subir dumps de producción.

---

## 15. Definición de “Premium parity” (lista inicial de features)

1. Compartición granular (usuarios/grupos).
2. Versionado y recuperación.
3. Auditoría.
4. 2FA.
5. Carpetas y etiquetas.
6. Export/backup cifrado.
7. Generador de contraseñas.
8. Autocompletado (fase 2: browser extension).
9. Políticas de seguridad (longitud mínima, etc.).

--- 

## 16. Tareas para Cursor (prompts internos)

1. “Generar migraciones y modelos para el esquema propuesto, con FKs, índices y uniques.”
2. “Crear Policies y Gate central para resolver permisos por owner/share a user/share a group.”
3. “Implementar VaultService con cifrado JSON hacia vault_item_secrets.”
4. “Crear UI Bootstrap: lista, create/edit, detalle, share modal, group management.”
5. “Implementar auditoría con events/listeners.”

---

## 17. Proceso de Instalación Inicial (Setup Base)

### 17.1 Instalación de Laravel - COMPLETADO ✅

**Versión instalada:** Laravel 12.48.1 (PHP 8.2+ requerido)

**Estado:**
- ✅ Proyecto Laravel creado con `composer create-project`
- ✅ Estructura base de Laravel instalada
- ✅ Archivos de documentación preservados (README.md)
- ✅ Documentación organizada en carpeta `documentacion/`

**Archivos importantes creados:**
- `composer.json` - Gestión de dependencias PHP
- `package.json` - Gestión de dependencias Node.js (incluye Vite + Tailwind CSS por defecto)
- `.env.example` - Plantilla de configuración de entorno
- `vite.config.js` - Configuración de Vite para assets

**Nota importante:** Laravel 12 viene con **Tailwind CSS** por defecto, no Bootstrap. Se requiere instalar Bootstrap manualmente.

---

### 17.2 Próximos Pasos (Plan de Implementación Incremental)

**Para hacer commits valiosos, seguir este orden:**

#### Commit 1: Instalación base de Laravel ✅
- Instalación completa del framework
- Estructura de directorios estándar

#### Commit 2: Configuración inicial del entorno
- Configurar `.env` con conexión MySQL
- Generar `APP_KEY`
- Configurar base de datos

#### Commit 3: Instalación y configuración de Bootstrap
- Desinstalar/desactivar Tailwind CSS (opcional)
- Instalar Bootstrap 5.x
- Configurar Vite para usar Bootstrap
- Configurar `resources/css/app.css` y `resources/js/app.js`

#### Commit 4: Sistema de autenticación
- Instalar Laravel Breeze (o Jetstream) con Bootstrap
- Configurar rutas de autenticación
- Crear layouts base con Bootstrap

#### Commit 5: Estructura de directorios del dominio
- Crear `app/Domain/Vault/`
- Crear `app/Services/`
- Crear `app/Repositories/` (opcional)
- Configurar autoloading si es necesario

#### Commit 6: Migraciones base
- Crear migraciones para: `groups`, `group_members`, `vault_items`, `vault_item_secrets`, `vault_item_versions`, `item_shares_users`, `item_shares_groups`, `folders`, `audit_logs`
- Ejecutar migraciones

#### Commit 7: Modelos Eloquent
- Crear modelos: `Group`, `GroupMember`, `VaultItem`, `VaultItemSecret`, `VaultItemVersion`, `ItemShareUser`, `ItemShareGroup`, `Folder`, `AuditLog`
- Definir relaciones entre modelos

---

### 17.3 Comandos Importantes para el Setup

```bash
# Generar APP_KEY
php artisan key:generate

# Crear .env desde .env.example (si no existe)
cp .env.example .env

# Instalar dependencias Node.js
npm install

# Compilar assets en desarrollo
# Opción recomendada: Inicia todo (Laravel + Queue + Reverb + Vite)
composer dev

# O solo frontend si Laravel ya está corriendo:
npm run dev

# Compilar assets para producción
npm run build

# Ejecutar servidor de desarrollo
php artisan serve
# O en un puerto específico (ejemplo: 8001)
php artisan serve --port=8001

# Ejecutar migraciones
php artisan migrate

# Crear migración
php artisan make:migration create_nombre_tabla_table

# Crear modelo con migración
php artisan make:model NombreModelo -m

# Crear controlador
php artisan make:controller NombreController

# Instalar Laravel Breeze (autenticación)
composer require laravel/breeze --dev
php artisan breeze:install blade --dark
```

---

### 17.4 Configuración de .env (Variables Importantes)

**Base de datos:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vault_contrasenna
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

**Aplicación:**
```env
APP_NAME="PassVault"
APP_ENV=local
APP_KEY=  # Se genera con php artisan key:generate
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8001
```

**Nota de seguridad:** En producción, `APP_DEBUG=false` y `APP_ENV=production`.

---

### 17.5 Estructura de Directorios Recomendada

```
app/
├── Domain/
│   └── Vault/
│       ├── Models/          # Entidades del dominio
│       ├── DTOs/            # Data Transfer Objects
│       └── Actions/         # Acciones del dominio
├── Services/
│   ├── VaultService.php
│   ├── ShareService.php
│   ├── CryptoService.php
│   └── AuditService.php
├── Repositories/            # Opcional, para consultas complejas
├── Policies/
│   └── VaultItemPolicy.php
├── Http/
│   ├── Controllers/
│   │   ├── VaultController.php
│   │   ├── GroupController.php
│   │   └── AuditController.php
│   └── Requests/
│       ├── StoreVaultItemRequest.php
│       └── UpdateVaultItemRequest.php
└── Events/
    ├── VaultItemCreated.php
    └── VaultItemViewed.php
```

---

Fin del documento.
