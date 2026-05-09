# Policies

Esta carpeta contiene las políticas de autorización de Laravel.

## Estructura

Las políticas determinan qué usuarios pueden realizar qué acciones en los recursos.

## Ejemplos de políticas que se crearán:

- `VaultItemPolicy.php` - Autorización para vault items (view, create, update, delete, share)
- `GroupPolicy.php` - Autorización para grupos (view, create, update, delete, invite)

## Uso en Controllers

```php
// En un Controller
use App\Policies\VaultItemPolicy;

class VaultController extends Controller
{
    public function show(VaultItem $item)
    {
        $this->authorize('view', $item);
        
        return view('vault.show', compact('item'));
    }
}
```

## Uso en Blade

```blade
@can('update', $item)
    <a href="{{ route('vault.edit', $item) }}">Editar</a>
@endcan
```

## Convenciones

- Una política por modelo principal
- Métodos que retornan `bool`
- Usar `$this->authorize()` en controllers
- Usar `@can` y `@cannot` en vistas
