# Services

Esta carpeta contiene los servicios de lógica de negocio de la aplicación.

## Estructura

Los servicios contienen la lógica de negocio que no pertenece a los modelos ni a los controladores.

## Servicios existentes:

- ✅ `AuthService.php` - Lógica de autenticación (registro, login, recuperación de contraseña, verificación de email)
- ✅ `VaultService.php` - Operaciones CRUD de vault items (crear, leer, actualizar, eliminar)

## Servicios que se crearán:
- `ShareService.php` - Lógica de compartir items con usuarios y grupos
- `CryptoService.php` - Cifrado y descifrado de secretos
- `AuditService.php` - Registro de eventos de auditoría

## Uso en Controllers

```php
// En un Controller
use App\Services\VaultService;

class VaultController extends Controller
{
    public function __construct(
        private VaultService $vaultService
    ) {}

    public function store(StoreVaultItemRequest $request)
    {
        $item = $this->vaultService->createItem(
            $request->validated(),
            auth()->user()
        );

        return redirect()->route('vault.show', $item);
    }
}
```

## Convenciones

- Un servicio por dominio de negocio
- Métodos públicos claros y bien definidos
- Inyección de dependencias en el constructor
- Tipado estricto en todos los métodos
