# Vault Items - PassVault

Documentación del sistema de gestión de items del vault (contraseñas y secretos).

## 📋 Descripción

El sistema de Vault Items permite gestionar diferentes tipos de secretos de forma segura, con cifrado, organización en carpetas y compartición con usuarios y grupos.

## ✨ Funcionalidades Implementadas

### Tipos de Items
- **Auth (Autenticación)**: Credenciales de login (usuario, contraseña, URIs, TOTP)
- **Card (Tarjeta)**: Información de tarjetas de crédito/débito
- **API Key**: Claves de API con host/URL
- **SSH Key**: Claves SSH públicas y privadas con passphrase
- **Note (Nota)**: Notas seguras con contenido libre
- **Env File (Archivo .env)**: Archivo .env completo; visualizador simple con botón Copiar. Ver [env-file-item.md](env-file-item.md).

### Operaciones CRUD
- **Crear**: Formularios dinámicos según tipo de item
- **Leer**: Vista detallada con controles de visibilidad
- **Actualizar**: Edición con validación de permisos
- **Eliminar**: Solo owner puede eliminar

### Características Especiales
- **Cifrado**: Todos los secretos se almacenan cifrados (AES-256-CBC)
- **TOTP/2FA**: Generación de secretos TOTP con QR codes
- **Favoritos**: Marcar items como favoritos
- **Carpetas**: Organización en carpetas jerárquicas
- **Filtros**: Búsqueda, filtrado por tipo, favoritos, carpetas (AJAX)

## 🏗️ Arquitectura

### Modelos

#### `VaultItem`
- `owner_user_id`: Usuario propietario
- `type`: Tipo de item (auth, card, api_key, ssh_key, note)
- `title`: Título del item
- `folder_id`: Carpeta (nullable)
- `favorite`: Favorito (boolean)
- `status`: Estado (active, deleted)
- Relaciones: `owner`, `secret`, `versions`, `folder`, `sharesWithUsers`, `sharesWithGroups`

#### `VaultItemSecret`
- `vault_item_id`: Item relacionado
- `ciphertext`: Datos cifrados (JSON)
- `hmac`: Hash para verificación de integridad
- Relación: `vaultItem`

### Servicios

#### `VaultService`
- `createItem()`: Crear item con cifrado de secretos
- `updateItem()`: Actualizar item (descifra, actualiza, cifra)
- `findItem()`: Buscar item con verificación de acceso
- `listItems()`: Listar items con filtros y comparticiones
- `deleteItem()`: Eliminar item (soft delete)
- `hasTotp()`: Verificar si item tiene TOTP

#### `CryptoService`
- `encrypt()`: Cifrar datos (AES-256-CBC + HMAC)
- `decrypt()`: Descifrar y verificar integridad
- `generateHmac()`: Generar HMAC para verificación

#### `TotpService`
- `generateSecret()`: Generar secreto TOTP aleatorio
- `generateQrCode()`: Generar QR code para escanear
- `verifyCode()`: Verificar código TOTP (pendiente)

### Políticas

#### `VaultItemPolicy`
- `viewAny()`: Ver lista de items
- `view()`: Ver item específico
- `create()`: Crear item
- `update()`: Actualizar item
- `delete()`: Eliminar item
- `getPermission()`: Obtener permiso efectivo

## 🔄 Flujo de Trabajo

### Crear Item
1. Usuario selecciona tipo de item
2. Formulario muestra campos específicos del tipo
3. Usuario completa información
4. `VaultService` cifra los secretos
5. Item se guarda con owner = usuario actual
6. Si se crea desde grupo, se comparte automáticamente

### Ver Item
1. `VaultItemPolicy` verifica acceso
2. `VaultService` descifra secretos
3. Vista muestra información según permisos
4. Controles de visibilidad para datos sensibles

### Editar Item
1. `VaultItemPolicy` verifica permiso de edición
2. `VaultService` descifra datos actuales
3. Usuario modifica información
4. `VaultService` cifra datos actualizados
5. Solo owner puede cambiar tipo y estado

### Filtrar Items
1. Usuario aplica filtros (tipo, favoritos, búsqueda, carpeta)
2. AJAX envía petición sin recargar
3. `VaultService` aplica filtros
4. Vista parcial se actualiza dinámicamente
5. URL se actualiza con History API

## 📍 Rutas

- `GET /vault` - Lista de items
- `GET /vault/create` - Crear item
- `POST /vault` - Guardar item
- `GET /vault/{vault}` - Ver item
- `GET /vault/{vault}/edit` - Editar item
- `PUT /vault/{vault}` - Actualizar item
- `DELETE /vault/{vault}` - Eliminar item
- `POST /vault/totp/generate-secret` - Generar secreto TOTP

## 🎨 UI/UX

### Lista de Items (`vault/index.blade.php`)
- Sidebar con carpetas
- Tabla de items con filtros
- Búsqueda en tiempo real (debounce)
- Filtros por tipo y favoritos
- Indicadores de items compartidos
- Paginación

### Crear/Editar (`vault/create.blade.php`, `vault/edit.blade.php`)
- Selector de tipo con campos dinámicos
- Selector de carpeta con botón para crear nueva
- Campos específicos por tipo
- Generador de contraseñas
- Generador de secretos TOTP con QR
- Validación en tiempo real

### Detalle (`vault/show.blade.php`)
- Información completa del item
- Controles de visibilidad para datos sensibles (mostrar/ocultar con icono ojo)
- **Botón copiar al portapapeles** en cada campo secreto: contraseña, CVV, API Key, passphrase SSH, clave privada; feedback visual (check verde) al copiar
- TOTP con código actualizable y botón copiar
- Sección de compartición (owner/admin)
- Botones de acción según permisos

## 🔒 Seguridad

- Todos los secretos se almacenan cifrados
- Verificación de integridad con HMAC
- Autorización granular con policies
- Solo owner puede eliminar
- Permisos verificados en cada operación
- No se loguean datos sensibles

## 📝 Notas de Implementación

- Los secretos se cifran antes de guardar
- Los secretos se descifran solo cuando se necesitan mostrar
- El cifrado usa la clave de Laravel (`APP_KEY`)
- Los items eliminados usan soft delete (status = 'deleted')
- Los filtros AJAX mejoran la experiencia sin recargar página

### Multi-tenant (SaaS)
- Los items tienen `organization_id`. `VaultService` (listItems, findItem, createItem) y `VaultItemPolicy` aplican scope por organización. Los usuarios solo ven y gestionan items de su org. Ver [`documentacion/09-saas-multi-tenant/`](../09-saas-multi-tenant/).

### Item tipo Archivo .env
- Tipo `env_file` para almacenar y ver archivos .env. Visualizador simple. Ver [env-file-item.md](env-file-item.md).
