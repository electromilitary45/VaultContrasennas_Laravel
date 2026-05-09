<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Modelo de log de auditoría
 * 
 * Registra todas las acciones importantes realizadas en el sistema
 */
class AuditLog extends Model
{
    /**
     * Campos que pueden ser asignados masivamente
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'organization_id',
        'action',
        'model_type',
        'model_id',
        'changes',
        'ip_address',
        'user_agent',
        'meta',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'meta' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relación con el usuario que realizó la acción
     *
     * @return BelongsTo<User, AuditLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la organización del evento
     *
     * @return BelongsTo<Organization, AuditLog>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Relación polimórfica con el modelo relacionado
     *
     * @return MorphTo<Model, AuditLog>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope para filtrar por acción
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditLog> $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para filtrar por tipo de modelo
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditLog> $query
     * @param string $modelType
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeForModelType($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope para filtrar por usuario
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditLog> $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por rango de fechas
     *
     * @param \Illuminate\Database\Eloquent\Builder<AuditLog> $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por organización.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AuditLog>  $query
     * @param  int  $organizationId
     * @return \Illuminate\Database\Eloquent\Builder<AuditLog>
     */
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
