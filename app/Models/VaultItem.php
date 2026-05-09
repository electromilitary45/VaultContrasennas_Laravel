<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo VaultItem
 * 
 * Representa un item del vault (contraseña, nota, tarjeta, etc.).
 * 
 * @property int $id
 * @property int $owner_user_id
 * @property string $type
 * @property string $title
 * @property int|null $folder_id
 * @property bool $favorite
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class VaultItem extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_user_id',
        'organization_id',
        'type',
        'title',
        'folder_id',
        'favorite',
        'status',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'favorite' => 'boolean',
    ];

    /**
     * Relación: Un item pertenece a un usuario propietario.
     *
     * @return BelongsTo<User>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Relación: Un item pertenece a una organización.
     *
     * @return BelongsTo<Organization>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Relación: Un item tiene un secreto (uno a uno).
     *
     * @return HasOne<VaultItemSecret>
     */
    public function secret(): HasOne
    {
        return $this->hasOne(VaultItemSecret::class);
    }

    /**
     * Relación: Un item tiene muchas versiones.
     *
     * @return HasMany<VaultItemVersion>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(VaultItemVersion::class);
    }

    /**
     * Relación: Un item pertenece a una carpeta (opcional).
     *
     * @return BelongsTo<Folder>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    /**
     * Relación: Un item tiene muchos compartires con usuarios.
     *
     * @return HasMany<ItemShareUser>
     */
    public function sharesWithUsers(): HasMany
    {
        return $this->hasMany(ItemShareUser::class, 'vault_item_id');
    }

    /**
     * Relación: Un item tiene muchos compartires con grupos.
     *
     * @return HasMany<ItemShareGroup>
     */
    public function sharesWithGroups(): HasMany
    {
        return $this->hasMany(ItemShareGroup::class, 'vault_item_id');
    }

    /**
     * Verificar si el item está activo.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar si el item está archivado.
     *
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Verificar si el item está eliminado.
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->status === 'deleted';
    }

    /**
     * Verificar si un usuario es el propietario del item.
     *
     * @param User|int $user
     * @return bool
     */
    public function isOwner(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $this->owner_user_id === $userId;
    }

    /**
     * Verificar si un usuario tiene acceso compartido directo.
     *
     * @param User|int $user
     * @return bool
     */
    public function hasDirectShare(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $this->sharesWithUsers()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Obtener el permiso compartido directo de un usuario.
     *
     * @param User|int $user
     * @return string|null 'view', 'edit', 'admin', o null si no tiene acceso
     */
    public function getDirectSharePermission(User|int $user): ?string
    {
        $userId = $user instanceof User ? $user->id : $user;
        $share = $this->sharesWithUsers()
            ->where('user_id', $userId)
            ->first();

        return $share?->permission;
    }

    /**
     * Verificar si un usuario tiene acceso compartido a través de grupos.
     *
     * @param User|int $user
     * @return bool
     */
    public function hasGroupShare(User|int $user): bool
    {
        $userModel = $user instanceof User ? $user : User::find($user);
        if (!$userModel) {
            return false;
        }
        
        $userGroupIds = $userModel->groupMemberships()
            ->where('status', 'active')
            ->pluck('group_id')
            ->toArray();

        if (empty($userGroupIds)) {
            return false;
        }

        return $this->sharesWithGroups()
            ->whereIn('group_id', $userGroupIds)
            ->exists();
    }

    /**
     * Obtener el permiso compartido más alto a través de grupos para un usuario.
     * 
     * Prioridad: admin > edit > view
     *
     * @param User|int $user
     * @return string|null 'view', 'edit', 'admin', o null si no tiene acceso
     */
    public function getGroupSharePermission(User|int $user): ?string
    {
        $userModel = $user instanceof User ? $user : User::find($user);
        if (!$userModel) {
            return null;
        }
        
        $userGroupIds = $userModel->groupMemberships()
            ->where('status', 'active')
            ->pluck('group_id')
            ->toArray();

        if (empty($userGroupIds)) {
            return null;
        }

        // Obtener el permiso más alto (admin > edit > view)
        $share = $this->sharesWithGroups()
            ->whereIn('group_id', $userGroupIds)
            ->orderByRaw("FIELD(permission, 'admin', 'edit', 'view')")
            ->first();

        return $share?->permission;
    }

    /**
     * Verificar si un usuario tiene acceso al item (propietario, compartido directo o por grupo).
     *
     * @param User|int $user
     * @return bool
     */
    public function hasAccess(User|int $user): bool
    {
        return $this->isOwner($user) 
            || $this->hasDirectShare($user) 
            || $this->hasGroupShare($user);
    }

    /**
     * Obtener el permiso efectivo más alto de un usuario sobre el item.
     * 
     * Prioridad: owner > admin > edit > view
     *
     * @param User|int $user
     * @return string|null 'owner', 'admin', 'edit', 'view', o null si no tiene acceso
     */
    public function getEffectivePermission(User|int $user): ?string
    {
        if ($this->isOwner($user)) {
            return 'owner';
        }

        // Obtener permisos de compartir directo y por grupo
        $directPermission = $this->getDirectSharePermission($user);
        $groupPermission = $this->getGroupSharePermission($user);

        // Prioridad: admin > edit > view
        $permissions = array_filter([$directPermission, $groupPermission]);
        if (empty($permissions)) {
            return null;
        }

        // Retornar el permiso más alto
        if (in_array('admin', $permissions)) {
            return 'admin';
        }
        if (in_array('edit', $permissions)) {
            return 'edit';
        }
        return 'view';
    }
}