<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Organization
 *
 * Representa una organización (tenant) en el modelo SaaS multi-tenant.
 * Cada organización tiene su propio super admin y espacio de datos.
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Organization extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'logo',
    ];

    /**
     * Relación: Una organización tiene muchos usuarios.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    /**
     * Relación: Una organización tiene muchos grupos.
     *
     * @return HasMany<Group>
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'organization_id');
    }

    /**
     * Relación: Una organización tiene muchos vault items.
     *
     * @return HasMany<VaultItem>
     */
    public function vaultItems(): HasMany
    {
        return $this->hasMany(VaultItem::class, 'organization_id');
    }

    /**
     * Relación: Una organización tiene muchas carpetas.
     *
     * @return HasMany<Folder>
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class, 'organization_id');
    }

    /**
     * Relación: Una organización tiene muchos logs de auditoría.
     *
     * @return HasMany<AuditLog>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'organization_id');
    }

    /**
     * Relación: Una organización tiene muchos códigos de invitación.
     *
     * @return HasMany<OrganizationInvitationCode>
     */
    public function invitationCodes(): HasMany
    {
        return $this->hasMany(OrganizationInvitationCode::class, 'organization_id');
    }
}
