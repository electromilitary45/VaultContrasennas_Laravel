<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo GroupAccessRequest
 * 
 * Representa una solicitud de acceso de un usuario a un grupo.
 * 
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property int|null $organization_id
 * @property string $status Estado: pending|accepted|rejected
 * @property \Illuminate\Support\Carbon $requested_at
 * @property \Illuminate\Support\Carbon|null $responded_at
 * @property int|null $responded_by_user_id
 * @property string|null $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class GroupAccessRequest extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'user_id',
        'organization_id',
        'status',
        'requested_at',
        'responded_at',
        'responded_by_user_id',
        'message',
    ];

    /**
     * Casts de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'group_id' => 'integer',
        'user_id' => 'integer',
        'organization_id' => 'integer',
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
        'responded_by_user_id' => 'integer',
    ];

    /**
     * Relación con el grupo.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Relación con el usuario solicitante.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la organización.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Relación con el usuario que respondió la solicitud.
     */
    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by_user_id');
    }

    /**
     * Scope para solicitudes pendientes.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para solicitudes aceptadas.
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope para solicitudes rechazadas.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope para filtrar por organización.
     */
    public function scopeForOrganization(Builder $query, ?int $organizationId): Builder
    {
        if ($organizationId === null) {
            return $query->whereNull('organization_id');
        }
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Aceptar la solicitud.
     */
    public function accept(User $respondedBy): void
    {
        $this->update([
            'status' => 'accepted',
            'responded_at' => now(),
            'responded_by_user_id' => $respondedBy->id,
        ]);
    }

    /**
     * Rechazar la solicitud.
     */
    public function reject(User $respondedBy): void
    {
        $this->update([
            'status' => 'rejected',
            'responded_at' => now(),
            'responded_by_user_id' => $respondedBy->id,
        ]);
    }

    /**
     * Verificar si la solicitud está pendiente.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar si la solicitud fue aceptada.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Verificar si la solicitud fue rechazada.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
