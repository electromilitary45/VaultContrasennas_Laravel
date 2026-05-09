<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'organization_id',
        'is_active',
        'must_change_password',
        'totp_secret',
        'totp_enabled',
        'totp_backup_codes',
        'totp_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'totp_enabled' => 'boolean',
            'totp_backup_codes' => 'array',
            'totp_verified_at' => 'datetime',
        ];
    }

    /**
     * Relación: Un usuario pertenece a una organización. NULL = super admin de plataforma.
     *
     * @return BelongsTo<Organization, User>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Relación: Un usuario puede ser propietario de muchos grupos.
     *
     * @return HasMany<Group>
     */
    public function ownedGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'owner_user_id');
    }

    /**
     * Relación: Un usuario puede ser miembro de muchos grupos.
     *
     * @return HasMany<GroupMember>
     */
    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Obtener los grupos donde el usuario es miembro activo.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Group>
     */
    public function activeGroups()
    {
        return Group::whereHas('members', function ($query) {
            $query->where('user_id', $this->id)
                  ->where('status', 'active');
        })->get();
    }

    /**
     * Relación: Un usuario puede ser propietario de muchos vault items.
     *
     * @return HasMany<VaultItem>
     */
    public function vaultItems(): HasMany
    {
        return $this->hasMany(VaultItem::class, 'owner_user_id');
    }

    /**
     * Relación: Un usuario puede crear muchas versiones de vault items.
     *
     * @return HasMany<VaultItemVersion>
     */
    public function createdVaultItemVersions(): HasMany
    {
        return $this->hasMany(VaultItemVersion::class, 'created_by_user_id');
    }

    /**
     * Relación: Un usuario puede tener muchos compartires de items con él.
     *
     * @return HasMany<ItemShareUser>
     */
    public function sharedItems(): HasMany
    {
        return $this->hasMany(ItemShareUser::class, 'user_id');
    }

    /**
     * Relación: Un usuario puede compartir items con otros usuarios.
     *
     * @return HasMany<ItemShareUser>
     */
    public function sharedItemsByMe(): HasMany
    {
        return $this->hasMany(ItemShareUser::class, 'shared_by_user_id');
    }

    /**
     * Relación: Un usuario puede compartir items con grupos.
     *
     * @return HasMany<ItemShareGroup>
     */
    public function sharedItemsWithGroups(): HasMany
    {
        return $this->hasMany(ItemShareGroup::class, 'shared_by_user_id');
    }

    /**
     * Relación: Un usuario puede ser propietario de muchas carpetas.
     *
     * @return HasMany<Folder>
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class, 'owner_user_id');
    }

    /**
     * Relación: Un usuario puede tener muchos logs de auditoría.
     *
     * @return HasMany<AuditLog>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Verificar si el usuario tiene 2FA habilitado.
     *
     * @return bool
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->totp_enabled && !empty($this->totp_secret);
    }

    /**
     * Obtener la URL del avatar o generar una con iniciales.
     *
     * @return string
     */
    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // Retornar null para usar avatar con iniciales en la vista
        return '';
    }

    /**
     * Obtener las iniciales del usuario para avatar.
     *
     * @return string
     */
    public function getInitials(): string
    {
        $name = trim($this->name);
        $words = explode(' ', $name);
        
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        
        return strtoupper(substr($name, 0, 1));
    }

    /**
     * Verificar si el usuario es un usuario normal.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Verificar si el usuario es un administrador.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Verificar si el usuario es un super administrador.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Verificar si el usuario es super admin de plataforma (sin organización).
     * Solo estos usuarios pueden crear organizaciones y gestionar la plataforma.
     *
     * @return bool
     */
    public function isPlatformSuperAdmin(): bool
    {
        return $this->role === 'super_admin' && $this->organization_id === null;
    }

    /**
     * Verificar si el usuario es admin de una organización (tiene org y rol admin/super_admin).
     * Pueden generar códigos de invitación y gestionar usuarios de su org.
     *
     * @return bool
     */
    public function isOrgAdmin(): bool
    {
        return $this->organization_id !== null && $this->isAdmin();
    }

    /**
     * Verificar si el usuario puede gestionar otros admins.
     * Solo super_admin puede gestionar roles.
     *
     * @return bool
     */
    public function canManageAdmins(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Verificar si el usuario está activo.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true; // Por defecto true si no está definido
    }

    /**
     * Indica si el usuario debe cambiar la contraseña en el próximo login
     * (ej. super admin de org con contraseña temporal).
     */
    public function hasMustChangePassword(): bool
    {
        return (bool) ($this->must_change_password ?? false);
    }
}
