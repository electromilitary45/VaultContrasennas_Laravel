<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo Notification
 * 
 * Representa una notificación para un usuario.
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $organization_id
 * @property string $type Tipo de notificación
 * @property string $title Título
 * @property string $message Mensaje
 * @property array|null $data Datos adicionales (JSON)
 * @property bool $read Indica si está leída
 * @property \Illuminate\Support\Carbon|null $read_at Fecha de lectura
 * @property string|null $action_url URL para acción rápida
 * @property string|null $action_label Etiqueta del botón de acción
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Notification extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'organization_id',
        'type',
        'title',
        'message',
        'data',
        'read',
        'read_at',
        'action_url',
        'action_label',
    ];

    /**
     * Casts de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'organization_id' => 'integer',
        'data' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relación: Una notificación pertenece a un usuario.
     *
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Una notificación pertenece a una organización (opcional).
     *
     * @return BelongsTo<Organization>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope: Notificaciones no leídas.
     *
     * @param Builder<Notification> $query
     * @return Builder<Notification>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('read', false);
    }

    /**
     * Scope: Notificaciones leídas.
     *
     * @param Builder<Notification> $query
     * @return Builder<Notification>
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('read', true);
    }

    /**
     * Scope: Notificaciones de una organización específica.
     *
     * @param Builder<Notification> $query
     * @param int|null $organizationId
     * @return Builder<Notification>
     */
    public function scopeForOrganization(Builder $query, ?int $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Marcar notificación como leída.
     *
     * @return void
     */
    public function markAsRead(): void
    {
        $this->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Marcar notificación como no leída.
     *
     * @return void
     */
    public function markAsUnread(): void
    {
        $this->update([
            'read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Verificar si la notificación está leída.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read === true;
    }

    /**
     * Verificar si la notificación no está leída.
     *
     * @return bool
     */
    public function isUnread(): bool
    {
        return $this->read === false;
    }
}
