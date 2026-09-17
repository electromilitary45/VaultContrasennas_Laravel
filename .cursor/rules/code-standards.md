# Reglas de Código - PassVault

## Principios Fundamentales

### 1. Clean Code

1. **Nombres descriptivos:**
   - Variables y métodos deben tener nombres que expliquen su propósito
   - Evitar abreviaciones innecesarias
   - Usar verbos para métodos: `getUserVaultItems()`, `encryptSecret()`, `validatePermission()`
   - Usar sustantivos para clases y variables: `VaultItem`, `CryptoService`, `userPermissions`

2. **Funciones pequeñas y enfocadas:**
   - Una función debe hacer una sola cosa
   - Máximo 20-30 líneas por función (idealmente menos)
   - Si una función hace múltiples cosas, dividirla

3. **Evitar duplicación (DRY - Don't Repeat Yourself):**
   - Extraer lógica común a métodos privados o traits
   - Reutilizar código existente antes de duplicar

4. **Manejo de errores:**
   - Usar excepciones apropiadas de Laravel
   - Crear excepciones personalizadas cuando sea necesario
   - No silenciar errores sin manejo adecuado

5. **Tipado estricto:**
   - Usar type hints en todos los métodos
   - Usar return types
   - Declarar `declare(strict_types=1);` en archivos PHP

---

### 2. Arquitectura MVC

1. **Controllers (Delgados):**
   - Solo validación de entrada y orquestación
   - Delegar lógica de negocio a Services
   - Máximo 5-7 líneas por método (idealmente)
   - Usar Form Requests para validación compleja

2. **Models (Entidades de datos):**
   - Solo relaciones, scopes, accessors/mutators
   - No lógica de negocio compleja
   - Usar Eloquent para consultas simples

3. **Services (Lógica de negocio):**
   - Toda la lógica de negocio va aquí
   - Un servicio por dominio (VaultService, ShareService, etc.)
   - Métodos públicos claros y bien definidos

4. **Repositories (Opcional):**
   - Solo para consultas complejas
   - Abstracción de acceso a datos cuando sea necesario

5. **Policies (Autorización):**
   - Toda la lógica de permisos aquí
   - Un Policy por modelo principal

---

### 3. Comentarios en el Código

1. **Cuándo comentar:**
   - ✅ Explicar "por qué" se hace algo, no "qué" se hace
   - ✅ Documentar decisiones de diseño complejas
   - ✅ Explicar algoritmos o lógica no obvia
   - ✅ Documentar parámetros y retornos en métodos públicos
   - ✅ Explicar workarounds o soluciones temporales

2. **Cuándo NO comentar:**
   - ❌ Código autoexplicativo (el código debe ser claro)
   - ❌ Comentarios obvios que repiten el código
   - ❌ Código comentado (eliminar, usar git)

3. **Formato de comentarios:**
   - Usar PHPDoc para clases y métodos públicos
   - Comentarios inline para explicaciones breves
   - Comentarios de bloque para explicaciones extensas

4. **Ejemplo de PHPDoc:**
   ```php
   /**
    * Cifra un secreto usando el método configurado.
    * 
    * @param array $secretData Datos del secreto en formato array
    * @param int $userId ID del usuario propietario
    * @return array Array con 'ciphertext', 'iv', 'salt' y 'crypto_version'
    * @throws EncryptionException Si falla el cifrado
    */
   public function encryptSecret(array $secretData, int $userId): array
   ```

5. **Comentarios en español:**
   - Todos los comentarios y documentación en español
   - Código en inglés (nombres de variables, métodos, clases)

---

### 4. Mejores Prácticas UI/UX

1. **Accesibilidad:**
   - Usar etiquetas semánticas de HTML
   - Agregar `aria-label` cuando sea necesario
   - Asegurar contraste adecuado de colores
   - Navegación por teclado funcional

2. **Feedback al usuario:**
   - Mostrar mensajes de éxito/error claros
   - Indicadores de carga para operaciones asíncronas
   - Confirmaciones para acciones destructivas
   - Validación en tiempo real cuando sea posible

3. **Consistencia:**
   - Mantener el mismo estilo en toda la aplicación
   - Usar componentes Bootstrap de forma consistente
   - Seguir el diseño minimalista tipo Apple

4. **Rendimiento:**
   - Lazy loading de imágenes
   - Paginación para listas largas
   - Optimizar consultas N+1
   - Usar caché cuando sea apropiado

5. **Responsive:**
   - Diseño mobile-first
   - Probar en diferentes tamaños de pantalla
   - Usar breakpoints de Bootstrap correctamente

6. **Seguridad en UI:**
   - No mostrar información sensible en URLs
   - Ocultar contraseñas por defecto
   - Protección CSRF en todos los formularios
   - Validación tanto en frontend como backend

---

### 5. Estándares de Código PHP/Laravel

1. **PSR-12:**
   - Seguir el estándar PSR-12 para estilo de código
   - Usar Laravel Pint para formateo automático

2. **Convenciones de Laravel:**
   - Nombres de modelos en singular: `VaultItem`, `Group`
   - Nombres de tablas en plural: `vault_items`, `groups`
   - Nombres de controladores en plural: `VaultItemsController`
   - Usar snake_case para nombres de columnas en BD

3. **Inyección de dependencias:**
   - Siempre inyectar dependencias en constructores
   - Usar el contenedor de servicios de Laravel
   - Evitar facades cuando sea posible (preferir inyección)

4. **Validación:**
   - Usar Form Requests para validación compleja
   - Validar en el backend siempre (nunca confiar solo en frontend)
   - Mensajes de error claros y en español

5. **Consultas a base de datos:**
   - Usar Eloquent cuando sea posible
   - Eager loading para evitar N+1
   - Índices apropiados en base de datos
   - No hacer consultas en bucles

---

### 6. Estructura de Archivos

1. **Organización:**
   - Un archivo por clase
   - Namespaces apropiados
   - Agrupar por funcionalidad, no por tipo

2. **Tamaño de archivos:**
   - Máximo 300-400 líneas por archivo
   - Si un archivo crece mucho, considerar dividirlo

3. **Imports:**
   - Organizar imports alfabéticamente
   - Agrupar por: Laravel, Vendor, App

---

### 7. Testing (Futuro)

1. **Cobertura:**
   - Tests unitarios para Services
   - Tests de feature para controladores
   - Tests de integración para flujos completos

2. **Nomenclatura:**
   - `test_should_do_something_when_condition()`
   - Usar assertions claras

---

### 8. Auditoría y Logging (Regla Obligatoria)

**A partir de ahora, TODO desarrollo nuevo debe incluir logs de auditoría para acciones importantes.**

1. **Acciones que requieren logs:**
   - ✅ Todas las acciones administrativas (cambio de rol, reset de contraseña, activación/desactivación)
   - ✅ Creación, actualización, eliminación de recursos críticos
   - ✅ Cambios de permisos o roles
   - ✅ Acciones de seguridad (login, logout, cambios de contraseña)
   - ✅ Compartición y revocación de acceso
   - ✅ Cualquier acción que afecte la seguridad o privacidad

2. **Información a registrar:**
   - ID y email del usuario que realiza la acción
   - ID del recurso afectado (si aplica)
   - Tipo de acción realizada
   - Timestamp de la acción
   - Contexto relevante (valores anteriores/nuevos cuando sea apropiado)
   - **NO registrar información sensible** (contraseñas, secretos, tokens)

3. **Formato de logs:**
   ```php
   use Illuminate\Support\Facades\Log;
   
   Log::info('Action description', [
       'user_id' => $user->id,
       'user_email' => $user->email,
       'resource_id' => $resource->id,
       'action' => 'action_name',
       'timestamp' => now(),
       // Contexto adicional relevante
   ]);
   ```

4. **Niveles de log:**
   - `Log::info()` - Acciones normales y esperadas
   - `Log::warning()` - Acciones que requieren atención pero no son errores
   - `Log::error()` - Errores y excepciones

5. **Ejemplo de implementación:**
   ```php
   public function changeRole(ChangeRoleRequest $request, User $user): RedirectResponse
   {
       $admin = $request->user();
       $oldRole = $user->role;
       
       $user->role = $request->validated()['role'];
       $user->save();
       
       // Log de auditoría
       Log::info('User role changed by admin', [
           'admin_id' => $admin->id,
           'admin_email' => $admin->email,
           'user_id' => $user->id,
           'user_email' => $user->email,
           'old_role' => $oldRole,
           'new_role' => $user->role,
           'timestamp' => now(),
       ]);
       
       return redirect()->route('admin.users.show', $user)
           ->with('success', "Rol cambiado exitosamente.");
   }
   ```

6. **Estado actual:**
   - ✅ Logs de auditoría implementados en todos los módulos (Vault, Grupos, Compartición, Carpetas, Autenticación, Administración)
   - Todos los métodos críticos incluyen logs con información relevante (usuario, recurso, acción, timestamp, IP cuando aplica)

---

### 9. Revisión de Integración (Regla Obligatoria)

**IMPORTANTE:** Después de completar cualquier desarrollo, SIEMPRE se debe realizar una revisión completa de integración para asegurar que todo esté correctamente conectado con el sistema.

#### Checklist de Revisión de Integración:

1. **Rutas y Middleware:**
   - [ ] Verificar que las rutas estén correctamente definidas en `routes/web.php` o `routes/api.php`
   - [ ] Confirmar que las rutas estén protegidas con los middlewares apropiados (auth, admin, etc.)
   - [ ] Verificar que los nombres de rutas sigan la convención del proyecto
   - [ ] Ejecutar `php artisan route:list` para verificar que las rutas se registren correctamente

2. **Controladores:**
   - [ ] Verificar que los controladores estén en el namespace correcto
   - [ ] Confirmar que los métodos tengan los tipos de retorno correctos
   - [ ] Verificar que se usen los Form Requests cuando corresponda
   - [ ] Confirmar que los controladores extiendan `Controller` base

3. **Modelos y Relaciones:**
   - [ ] Verificar que las relaciones Eloquent estén correctamente definidas
   - [ ] Confirmar que los métodos de relación usen los nombres correctos
   - [ ] Verificar que los `$fillable` y `$casts` estén completos
   - [ ] Probar que las relaciones funcionen (eager loading, lazy loading)

4. **Vistas:**
   - [ ] Verificar que las vistas usen el layout correcto (`<x-admin-layout>`, `<x-app-layout>`, etc.)
   - [ ] Confirmar que los componentes Blade estén correctamente referenciados
   - [ ] Verificar que las rutas en las vistas usen `route()` helper
   - [ ] Confirmar que los formularios incluyan `@csrf` y métodos HTTP correctos
   - [ ] Usar modales (`<x-confirm-modal>`, `showAlert`, `showConfirm`) en lugar de `alert()` o `confirm()`; ver `.cursor/rules/design-rules.md`

5. **Logs de Auditoría:**
   - [ ] Verificar que todas las acciones importantes tengan logs de auditoría
   - [ ] Confirmar que los logs incluyan información relevante (admin, usuario, recurso, timestamp)
   - [ ] Verificar que se use el nivel de log apropiado (`info`, `warning`, `error`)

6. **Validación y Autorización:**
   - [ ] Verificar que las validaciones estén implementadas (Form Requests o `$request->validate()`)
   - [ ] Confirmar que la autorización esté implementada (Policies, middleware, checks manuales)
   - [ ] Verificar que se manejen correctamente los errores 403 y 404

7. **Dependencias y Servicios:**
   - [ ] Verificar que los servicios inyectados existan y estén correctamente configurados
   - [ ] Confirmar que las dependencias estén en `composer.json` si son externas
   - [ ] Verificar que los helpers y traits estén correctamente importados

8. **Base de Datos:**
   - [ ] Verificar que las migraciones estén creadas y sean reversibles
   - [ ] Confirmar que los seeders funcionen correctamente si aplica
   - [ ] Verificar que los índices y foreign keys estén correctamente definidos

9. **Testing (si aplica):**
   - [ ] Verificar que los tests existentes sigan pasando
   - [ ] Confirmar que se hayan agregado tests para nueva funcionalidad si es crítica

10. **Documentación:**
    - [ ] Actualizar `ROUTE-MAP.md` con el estado del desarrollo
    - [ ] Actualizar documentación de módulo si existe
    - [ ] Actualizar `README.md` si es necesario

#### Ejemplo de Revisión:

```php
// ✅ CORRECTO: Revisión completa antes de marcar como completado
// 1. Rutas verificadas en web.php
// 2. Middleware 'admin' aplicado correctamente
// 3. Modelo y relaciones funcionando
// 4. Vista usando <x-admin-layout>
// 5. Logs de auditoría implementados
// 6. Validación con Form Request
// 7. Autorización verificada
```

**Esta revisión debe realizarse ANTES de marcar cualquier tarea como completada y ANTES de hacer commit.**

---

### 10. Prohibiciones

❌ NO usar código comentado (eliminar, usar git)
❌ NO crear métodos con más de 50 líneas sin dividir
❌ NO usar variables globales
❌ NO hacer consultas en bucles (N+1)
❌ NO validar solo en frontend
❌ NO exponer información sensible en logs o respuestas
❌ NO usar facades innecesariamente (preferir inyección)
❌ NO crear funciones sin propósito claro
❌ NO usar nombres genéricos: `$data`, `$result`, `process()`
❌ **NO olvidar agregar logs de auditoría en acciones importantes** (regla obligatoria)

---

### 11. Checklist Antes de Commit

- [ ] Código sigue PSR-12
- [ ] Funciones tienen type hints y return types
- [ ] Comentarios explican "por qué", no "qué"
- [ ] No hay código duplicado
- [ ] No hay consultas N+1
- [ ] Validación en backend
- [ ] Manejo de errores apropiado
- [ ] Nombres descriptivos
- [ ] UI/UX: feedback al usuario implementado
- [ ] UI/UX: accesibilidad básica verificada
- [ ] **Logs de auditoría agregados para acciones importantes** ⚠️ OBLIGATORIO

---

### 11. Cuando Consultar Este Archivo

- Antes de escribir código nuevo
- Al revisar código existente
- Antes de hacer commit
- Al refactorizar código
- Si tienes dudas sobre estructura o estilo
- **Antes de implementar cualquier acción administrativa o de seguridad** (verificar logs de auditoría)
- **Después de completar cualquier desarrollo** (realizar revisión de integración - sección 9)

---

**Última actualización:** 2026-01-23 (Agregada regla obligatoria de revisión de integración - sección 9)
