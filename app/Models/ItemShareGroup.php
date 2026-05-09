<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo ItemShareGroup
 * 
 * Representa el compartir de un vault item con un grupo.
 * 
 * @property int $id
 * @property int $vault_item_id
 * @property int $group_id
 * @property string $permission
 * @property int $shared_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ItemShareGroup extends Model
{
    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'item_shares_groups';

    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vault_item_id',
        'group_id',
        'permission',
        'shared_by_user_id',
    ];

    /**
     * Relación: Un compartir pertenece a un vault item.
     *
     * @return BelongsTo<VaultItem>
     */
    public function vaultItem(): BelongsTo
    {
        return $this->belongsTo(VaultItem::class, 'vault_item_id');
    }

    /**
     * Relación: Un compartir pertenece a un grupo.
     *
     * @return BelongsTo<Group>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Relación: Un compartir fue realizado por un usuario.
     *
     * @return BelongsTo<User>
     */
    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    /**
     * Verificar si el permiso es de solo lectura.
     *
     * @return bool
     */
    public function isViewOnly(): bool
    {
        return $this->permission === 'view';
    }

    /**
     * Verificar si el permiso permite editar.
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        return in_array($this->permission, ['edit', 'admin']);
    }

    /**
     * Verificar si el permiso es de administrador.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->permission === 'admin';
    }
}
