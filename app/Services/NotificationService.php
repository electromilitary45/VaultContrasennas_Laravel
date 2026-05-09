<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de Notificaciones
 * 
 * Encapsula la lógica de negocio para crear, gestionar y consultar notificaciones.
 */
class NotificationService
{
    /**
     * Crear una nueva notificación.
     *
     * @param array<string, mixed> $data Datos de la notificación (type, title, message, data, action_url, action_label)
     * @param User $user Usuario receptor
     * @param int|null $organizationId ID de la organización (nullable para platform super admin)
     * @return Notification
     */
    public function create(array $data, User $user, ?int $organizationId = null): Notification
    {
        // Si no se proporciona organization_id, usar el del usuario
        if ($organizationId === null) {
            $organizationId = $user->organization_id;
        }

        $notification = Notification::create([
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => $data['data'] ?? null,
            'read' => false,
            'read_at' => null,
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? null,
        ]);

        // Emitir evento para broadcasting
        event(new NotificationCreated($notification));

        return $notification;
    }

    /**
     * Marcar una notificación como leída.
     *
     * @param Notification $notification
     * @return void
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas.
     *
     * @param User $user
     * @param int|null $organizationId ID de la organización (nullable para platform super admin)
     * @return int Cantidad de notificaciones marcadas como leídas
     */
    public function markAllAsRead(User $user, ?int $organizationId = null): int
    {
        $query = Notification::where('user_id', $user->id)
            ->where('read', false);

        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        } else {
            // Si es null, solo marcar las que también tienen organization_id null
            $query->whereNull('organization_id');
        }

        return $query->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Obtener el contador de notificaciones no leídas.
     *
     * @param User $user
     * @param int|null $organizationId ID de la organización (nullable para platform super admin)
     * @return int
     */
    public function getUnreadCount(User $user, ?int $organizationId = null): int
    {
        $query = Notification::where('user_id', $user->id)
            ->where('read', false);

        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        } else {
            // Si es null, solo contar las que también tienen organization_id null
            $query->whereNull('organization_id');
        }

        return $query->count();
    }

    /**
     * Obtener notificaciones de un usuario.
     *
     * @param User $user
     * @param int|null $organizationId ID de la organización (nullable para platform super admin)
     * @param int $limit Límite de resultados
     * @param bool $unreadOnly Solo no leídas
     * @return Collection<int, Notification>
     */
    public function getNotifications(
        User $user,
        ?int $organizationId = null,
        int $limit = 50,
        bool $unreadOnly = false
    ): Collection {
        $query = Notification::where('user_id', $user->id);

        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        } else {
            // Si es null, solo obtener las que también tienen organization_id null
            $query->whereNull('organization_id');
        }

        if ($unreadOnly) {
            $query->where('read', false);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Eliminar notificaciones leídas antiguas (opcional, para limpieza).
     *
     * @param int $days Días de antigüedad (por defecto 90)
     * @return int Cantidad de notificaciones eliminadas
     */
    public function deleteOldRead(int $days = 90): int
    {
        $cutoffDate = now()->subDays($days);

        return Notification::where('read', true)
            ->where('read_at', '<=', $cutoffDate)
            ->delete();
    }
}
