<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\GroupAccessRequest;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestionar solicitudes de acceso a grupos.
 */
class GroupAccessRequestService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Crear una solicitud de acceso a un grupo.
     */
    public function createRequest(Group $group, User $user, ?string $message = null): GroupAccessRequest
    {
        // Verificar que no haya una solicitud pendiente ya
        $existingRequest = GroupAccessRequest::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            throw new \Exception('Ya existe una solicitud pendiente para este grupo.');
        }

        // Verificar que el usuario no sea ya miembro
        $isMember = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($isMember) {
            throw new \Exception('El usuario ya es miembro activo del grupo.');
        }

        // Crear la solicitud
        $request = GroupAccessRequest::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'status' => 'pending',
            'requested_at' => now(),
            'message' => $message,
        ]);

        // Obtener todos los owner/admin del grupo para notificarles
        $ownersAndAdmins = $group->members()
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'admin'])
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        // Crear notificaciones para cada owner/admin
        foreach ($ownersAndAdmins as $admin) {
            $this->notificationService->create([
                'type' => 'group_access_request',
                'title' => 'Solicitud de acceso a grupo',
                'message' => "{$user->name} ha solicitado acceso al grupo \"{$group->name}\"",
                'data' => [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'requested_user_id' => $user->id,
                    'requested_user_name' => $user->name,
                    'message' => $message,
                    'request_id' => $request->id,
                ],
                'action_url' => route('groups.index', ['tab' => 'requests']),
                'action_label' => 'Ver solicitudes',
            ], $admin, $admin->organization_id);
        }

        // Registrar en logs de auditoría
        Log::info('Group access request created', [
            'request_id' => $request->id,
            'group_id' => $group->id,
            'group_name' => $group->name,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'timestamp' => now(),
        ]);

        return $request;
    }

    /**
     * Aceptar una solicitud de acceso.
     */
    public function acceptRequest(GroupAccessRequest $request, User $respondedBy): void
    {
        if (!$request->isPending()) {
            throw new \Exception('La solicitud no está pendiente.');
        }

        $request->accept($respondedBy);

        // Crear el miembro del grupo con status 'active'
        GroupMember::create([
            'group_id' => $request->group_id,
            'user_id' => $request->user_id,
            'role' => 'member', // Por defecto, se puede cambiar después
            'status' => 'active',
            'invited_by_user_id' => $respondedBy->id,
        ]);

        // Crear notificación para el usuario solicitante
        $this->notificationService->create([
            'type' => 'group_access_request_accepted',
            'title' => 'Solicitud de acceso aceptada',
            'message' => "Tu solicitud de acceso al grupo \"{$request->group->name}\" ha sido aceptada",
            'data' => [
                'group_id' => $request->group_id,
                'group_name' => $request->group->name,
                'responded_by_user_id' => $respondedBy->id,
                'responded_by_user_name' => $respondedBy->name,
            ],
            'action_url' => route('groups.items.index', $request->group),
            'action_label' => 'Ver grupo',
        ], $request->user, $request->user->organization_id);

        // Registrar en logs de auditoría
        Log::info('Group access request accepted', [
            'request_id' => $request->id,
            'group_id' => $request->group_id,
            'user_id' => $request->user_id,
            'responded_by_user_id' => $respondedBy->id,
            'timestamp' => now(),
        ]);
    }

    /**
     * Rechazar una solicitud de acceso.
     */
    public function rejectRequest(GroupAccessRequest $request, User $respondedBy): void
    {
        if (!$request->isPending()) {
            throw new \Exception('La solicitud no está pendiente.');
        }

        $request->reject($respondedBy);

        // Crear notificación para el usuario solicitante
        $this->notificationService->create([
            'type' => 'group_access_request_rejected',
            'title' => 'Solicitud de acceso rechazada',
            'message' => "Tu solicitud de acceso al grupo \"{$request->group->name}\" ha sido rechazada",
            'data' => [
                'group_id' => $request->group_id,
                'group_name' => $request->group->name,
                'responded_by_user_id' => $respondedBy->id,
                'responded_by_user_name' => $respondedBy->name,
            ],
            'action_url' => route('groups.index', ['tab' => 'available']),
            'action_label' => 'Ver grupos',
        ], $request->user, $request->user->organization_id);

        // Registrar en logs de auditoría
        Log::info('Group access request rejected', [
            'request_id' => $request->id,
            'group_id' => $request->group_id,
            'user_id' => $request->user_id,
            'responded_by_user_id' => $respondedBy->id,
            'timestamp' => now(),
        ]);
    }

    /**
     * Obtener solicitudes pendientes de un grupo.
     */
    public function getPendingRequestsForGroup(Group $group): Collection
    {
        return GroupAccessRequest::where('group_id', $group->id)
            ->where('status', 'pending')
            ->with(['user', 'group'])
            ->latest('requested_at')
            ->get();
    }

    /**
     * Obtener solicitudes pendientes de un usuario.
     */
    public function getPendingRequestsForUser(User $user): Collection
    {
        return GroupAccessRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['group', 'group.owner'])
            ->latest('requested_at')
            ->get();
    }

    /**
     * Verificar si un usuario tiene una solicitud pendiente para un grupo.
     */
    public function hasPendingRequest(Group $group, User $user): bool
    {
        return GroupAccessRequest::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Obtener todas las solicitudes pendientes de los grupos que administra un usuario.
     */
    public function getPendingRequestsForUserGroups(User $user): Collection
    {
        // Obtener IDs de grupos donde el usuario es owner o admin
        $groupIds = GroupMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'admin'])
            ->pluck('group_id');

        return GroupAccessRequest::whereIn('group_id', $groupIds)
            ->where('status', 'pending')
            ->with(['user', 'group', 'group.owner'])
            ->latest('requested_at')
            ->get();
    }
}
