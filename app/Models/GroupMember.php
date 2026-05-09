<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo GroupMember
 * 
 * Representa la membresía de un usuario en un grupo.
 * 
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $role Rol del usuario: owner|admin|member|viewer
 * @property string $status Estado: invited|active|revoked
 * @property int|null $invited_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class GroupMember extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'status',
        'invited_by_user_id',
    ];

    /**
     * Casts de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'group_id' => 'integer',
        'user_id' => 'integer',
        'invited_by_user_id' => 'integer',
    ];

    /**
     * Relación: Un miembro pertenece a un grupo.
     *
     * @return BelongsTo<Group>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Relación: Un miembro es un usuario.
     *
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Usuario que invitó a este miembro.
     *
     * @return BelongsTo<User>
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /**
     * Verificar si el miembro tiene un rol específico.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Verificar si el miembro está activo.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar si el miembro puede editar (admin o owner).
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    /**
     * Verificar si el miembro puede administrar (solo owner).
     *
     * @return bool
     */
    public function canAdmin(): bool
    {
        return $this->role === 'owner';
    }
}
