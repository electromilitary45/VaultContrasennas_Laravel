<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Modelo OrganizationInvitationCode
 *
 * Códigos de invitación para registro. Un código pertenece a una organización,
 * es de un solo uso y vincula al usuario recién registrado con esa org.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $code
 * @property int|null $created_by_user_id
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property int|null $used_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class OrganizationInvitationCode extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_id',
        'code',
        'created_by_user_id',
        'used_at',
        'used_by_user_id',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    /**
     * Relación: El código pertenece a una organización.
     *
     * @return BelongsTo<Organization>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Relación: Usuario que generó el código.
     *
     * @return BelongsTo<User, OrganizationInvitationCode>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Relación: Usuario que se registró con el código.
     *
     * @return BelongsTo<User, OrganizationInvitationCode>
     */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    /**
     * Scope: solo códigos no usados.
     *
     * @param  Builder<OrganizationInvitationCode>  $query
     * @return Builder<OrganizationInvitationCode>
     */
    public function scopeUnused(Builder $query): Builder
    {
        return $query->whereNull('used_at');
    }

    /**
     * Genera un código único para la organización.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = Str::random(12);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Indica si el código ya fue usado.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
