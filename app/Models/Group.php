<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Group
 * 
 * Representa un grupo de usuarios que pueden compartir vault items.
 * 
 * @property int $id
 * @property int $owner_user_id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Group extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_user_id',
        'organization_id',
        'name',
        'description',
    ];

    /**
     * Relación: Un grupo pertenece a un usuario propietario.
     *
     * @return BelongsTo<User>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Relación: Un grupo pertenece a una organización.
     *
     * @return BelongsTo<Organization>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Relación: Un grupo tiene muchos miembros.
     *
     * @return HasMany<GroupMember>
     */
    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Obtener los miembros activos del grupo.
     *
     * @return HasMany<GroupMember>
     */
    public function activeMembers(): HasMany
    {
        return $this->members()->where('status', 'active');
    }

    /**
     * Verificar si un usuario es miembro activo del grupo.
     *
     * @param User|int $user
     * @return bool
     */
    public function hasActiveMember(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        return $this->activeMembers()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Obtener el rol de un usuario en el grupo.
     *
     * @param User|int $user
     * @return string|null
     */
    public function getUserRole(User|int $user): ?string
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        $member = $this->members()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        return $member?->role;
    }

    /**
     * Relación: Un grupo tiene muchos compartires de items.
     *
     * @return HasMany<ItemShareGroup>
     */
    public function sharedItems(): HasMany
    {
        return $this->hasMany(ItemShareGroup::class, 'group_id');
    }

    /**
     * Relación: Un grupo tiene muchas carpetas.
     *
     * @return HasMany<Folder>
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class, 'group_id');
    }
}
