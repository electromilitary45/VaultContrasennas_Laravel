<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Folder
 * 
 * Representa una carpeta para organizar vault items.
 * 
 * @property int $id
 * @property int $owner_user_id
 * @property string $name
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Folder extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_user_id',
        'organization_id',
        'group_id',
        'name',
        'parent_id',
    ];

    /**
     * Relación: Una carpeta pertenece a un usuario propietario.
     *
     * @return BelongsTo<User>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Relación: Una carpeta pertenece a una organización.
     *
     * @return BelongsTo<Organization>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Relación: Una carpeta puede tener una carpeta padre.
     *
     * @return BelongsTo<Folder>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Relación: Una carpeta puede tener muchas carpetas hijas.
     *
     * @return HasMany<Folder>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Relación: Una carpeta pertenece a un grupo (opcional).
     *
     * @return BelongsTo<Group>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Relación: Una carpeta tiene muchos vault items.
     *
     * @return HasMany<VaultItem>
     */
    public function vaultItems(): HasMany
    {
        return $this->hasMany(VaultItem::class, 'folder_id');
    }

    /**
     * Verificar si la carpeta es de un grupo.
     *
     * @return bool
     */
    public function isGroupFolder(): bool
    {
        return $this->group_id !== null;
    }

    /**
     * Verificar si la carpeta es personal.
     *
     * @return bool
     */
    public function isPersonalFolder(): bool
    {
        return $this->group_id === null;
    }

    /**
     * Verificar si la carpeta es una carpeta raíz (sin padre).
     *
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Obtener la ruta completa de la carpeta (ej: "Carpeta1 / Carpeta2 / Carpeta3").
     *
     * @return string
     */
    public function getFullPath(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent !== null) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' / ', $path);
    }
}
