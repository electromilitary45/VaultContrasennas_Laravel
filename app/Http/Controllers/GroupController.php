<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\StoreVaultItemRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\FolderService;
use App\Services\GroupAccessRequestService;
use App\Services\NotificationService;
use App\Services\ShareService;
use App\Services\VaultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GroupController extends Controller
{
    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(
        private VaultService $vaultService,
        private ShareService $shareService,
        private FolderService $folderService,
        private NotificationService $notificationService,
        private GroupAccessRequestService $accessRequestService
    ) {}

    /**
     * Display a listing of the resource.
     * Si hay un grupo seleccionado, redirige al vault del grupo.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        // Si hay un grupo seleccionado, redirigir al vault (items)
        if ($request->has('group') && $request->input('group')) {
            $groupQuery = Group::where('id', $request->input('group'));
            if ($user->organization_id !== null) {
                $groupQuery->where('organization_id', $user->organization_id);
            }
            $group = $groupQuery->first();
            if ($group) {
                $isOwner = $group->owner_user_id === $user->id;
                $isMember = $group->hasActiveMember($user);
                if ($isOwner || $isMember) {
                    return redirect()->route('groups.items.index', $group);
                }
            }
        }

        $ownedQuery = Group::where('owner_user_id', $user->id);
        if ($user->organization_id !== null) {
            $ownedQuery->where('organization_id', $user->organization_id);
        }
        $ownedGroups = $ownedQuery->withCount('activeMembers')->with('owner')->latest()->get();

        $memberQuery = Group::whereHas('members', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->where('owner_user_id', '!=', $user->id);
        if ($user->organization_id !== null) {
            $memberQuery->where('organization_id', $user->organization_id);
        }
        $memberGroups = $memberQuery->withCount('activeMembers')->with('owner')->latest()->get();

        // Obtener invitaciones pendientes
        $pendingInvitationsQuery = GroupMember::where('user_id', $user->id)
            ->where('status', 'invited')
            ->with(['group.owner', 'invitedBy']);
        if ($user->organization_id !== null) {
            $pendingInvitationsQuery->whereHas('group', fn ($q) => $q->where('organization_id', $user->organization_id));
        }
        $pendingInvitations = $pendingInvitationsQuery->latest()->get();

        // Obtener grupos disponibles (donde el usuario NO es miembro)
        $allGroupsQuery = Group::query();
        if ($user->organization_id !== null) {
            $allGroupsQuery->where('organization_id', $user->organization_id);
        }
        $allGroups = $allGroupsQuery->withCount('activeMembers')->with('owner')->latest()->get();

        // Filtrar grupos donde el usuario no es miembro ni owner
        $availableGroups = $allGroups->filter(function ($group) use ($user) {
            if ($group->owner_user_id === $user->id) {
                return false;
            }
            $isMember = $group->members()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
            return !$isMember;
        })->values();

        // Obtener solicitudes pendientes de los grupos que administra el usuario
        $pendingAccessRequests = $this->accessRequestService->getPendingRequestsForUserGroups($user);

        // Tab activo (por defecto 'groups' o el que viene en request)
        $activeTab = $request->input('tab', 'groups');

        return view('groups.index', [
            'ownedGroups' => $ownedGroups,
            'memberGroups' => $memberGroups,
            'pendingInvitations' => $pendingInvitations,
            'availableGroups' => $availableGroups,
            'pendingAccessRequests' => $pendingAccessRequests,
            'activeTab' => $activeTab,
            'accessRequestService' => $this->accessRequestService,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $user = Auth::user();
        
        try {
            $group = Group::create([
                'owner_user_id' => $user->id,
                'organization_id' => $user->organization_id,
                'name' => $request->validated()['name'],
                'description' => $request->validated()['description'] ?? null,
            ]);

            // El propietario se agrega automáticamente como miembro con rol 'owner'
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'status' => 'active',
                'invited_by_user_id' => $user->id,
            ]);

            // Registrar en logs de auditoría
            Log::info('Group created', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'timestamp' => now(),
            ]);

            return redirect()
                ->route('groups.items.index', $group)
                ->with('success', 'Grupo creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el grupo: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Redirige al vault del grupo (items).
     */
    public function show(Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $isOwner = $group->owner_user_id === $user->id;
        $isMember = $group->hasActiveMember($user);

        if (!$isOwner && !$isMember) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        // Redirigir al vault del grupo
        return redirect()->route('groups.items.index', $group);
    }

    /**
     * Mostrar administración del grupo.
     */
    public function admin(Group $group): View
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $isOwner = $group->owner_user_id === $user->id;
        $isMember = $group->hasActiveMember($user);

        if (!$isOwner && !$isMember) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        // Cargar miembros con relaciones
        $members = $group->members()
            ->with(['user', 'invitedBy'])
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'member', 'viewer')")
            ->orderBy('created_at')
            ->get();

        $memberUserIds = $members->pluck('user_id')->toArray();
        $availableUsersQuery = User::whereNotIn('id', $memberUserIds)->where('id', '!=', $user->id);
        if ($user->organization_id !== null) {
            $availableUsersQuery->where('organization_id', $user->organization_id);
        }
        $availableUsers = $availableUsersQuery->orderBy('name')->get(['id', 'name', 'email']);

        return view('groups.admin', [
            'group' => $group,
            'members' => $members,
            'availableUsers' => $availableUsers,
            'isOwner' => $isOwner,
            'userRole' => $group->getUserRole($user),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group): View
    {
        $this->ensureGroupInOrg($group);
        if ($group->owner_user_id !== Auth::id()) {
            abort(403, 'Solo el propietario puede editar el grupo.');
        }

        return view('groups.edit', [
            'group' => $group,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        if ($group->owner_user_id !== $user->id) {
            abort(403, 'Solo el propietario puede editar el grupo.');
        }

        // Capturar datos originales antes de actualizar
        $originalName = $group->name;
        $originalDescription = $group->description;

        try {
            $group->update($request->validated());

            // Calcular cambios
            $changes = [];
            if ($originalName !== $group->name) {
                $changes['name'] = ['from' => $originalName, 'to' => $group->name];
            }
            if ($originalDescription !== $group->description) {
                $changes['description'] = ['from' => $originalDescription, 'to' => $group->description];
            }

            // Registrar en logs de auditoría
            Log::info('Group updated', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'changes' => $changes,
                'timestamp' => now(),
            ]);

            return redirect()
                ->route('groups.admin', $group)
                ->with('success', 'Grupo actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el grupo: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        if ($group->owner_user_id !== $user->id) {
            abort(403, 'Solo el propietario puede eliminar el grupo.');
        }

        // Guardar información para logging antes de eliminar
        $groupId = $group->id;
        $groupName = $group->name;
        $memberCount = $group->activeMembers()->count();

        try {
            $group->delete();

            // Registrar en logs de auditoría
            Log::info('Group deleted', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'group_id' => $groupId,
                'group_name' => $groupName,
                'members_count' => $memberCount,
                'timestamp' => now(),
            ]);

            return redirect()
                ->route('groups.index')
                ->with('success', 'Grupo eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar el grupo: ' . $e->getMessage());
        }
    }

    /**
     * Invitar un usuario al grupo.
     */
    public function inviteMember(Request $request, Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $userRole = $group->getUserRole($user);

        if (!in_array($userRole, ['owner', 'admin'])) {
            abort(403, 'No tienes permisos para invitar miembros al grupo.');
        }

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ]);

        try {
            $userId = $request->input('user_id');
            $role = $request->input('role');

            // Verificar que el usuario no sea el propietario
            if ($group->owner_user_id == $userId) {
                return redirect()
                    ->back()
                    ->with('error', 'El propietario ya es miembro del grupo.');
            }

            // Obtener información del usuario invitado
            $invitedUser = User::findOrFail($userId);

            // Verificar que no sea miembro activo ya
            $existingMember = GroupMember::where('group_id', $group->id)
                ->where('user_id', $userId)
                ->first();

            if ($existingMember) {
                if ($existingMember->status === 'active') {
                    return redirect()
                        ->back()
                        ->with('error', 'El usuario ya es miembro activo del grupo.');
                } elseif ($existingMember->status === 'invited') {
                    // Ya hay una invitación pendiente, actualizar rol y reenviar notificación
                    $existingMember->update([
                        'role' => $role,
                        'invited_by_user_id' => $user->id,
                    ]);
                } else {
                    // Reactivar invitación (status = 'revoked')
                    $existingMember->update([
                        'status' => 'invited',
                        'role' => $role,
                        'invited_by_user_id' => $user->id,
                    ]);
                }
            } else {
                // Crear nueva invitación (status = 'invited', no 'active')
                $existingMember = GroupMember::create([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                    'role' => $role,
                    'status' => 'invited',
                    'invited_by_user_id' => $user->id,
                ]);
            }

            // Crear notificación para el usuario invitado
            $this->notificationService->create([
                'type' => 'group_invitation',
                'title' => 'Invitación a grupo',
                'message' => "{$user->name} te ha invitado al grupo \"{$group->name}\"",
                'data' => [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'inviter_id' => $user->id,
                    'inviter_name' => $user->name,
                    'role' => $role,
                ],
                'action_url' => route('groups.index', ['tab' => 'invitations']),
                'action_label' => 'Ver invitaciones',
            ], $invitedUser, $invitedUser->organization_id);

            // Registrar en logs de auditoría
            Log::info('Group member invited', [
                'inviter_id' => $user->id,
                'inviter_email' => $user->email,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'invited_user_id' => $invitedUser->id,
                'invited_user_email' => $invitedUser->email,
                'role' => $role,
                'is_reactivation' => $existingMember && $existingMember->status !== 'invited',
                'timestamp' => now(),
            ]);

            return redirect()
                ->route('groups.admin', $group)
                ->with('success', 'Invitación enviada. El usuario recibirá una notificación y deberá aceptar la invitación.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al invitar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Aceptar una invitación a un grupo.
     */
    public function acceptInvitation(Request $request, Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();

        // Buscar la invitación pendiente
        $invitation = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', 'invited')
            ->first();

        if (!$invitation) {
            return redirect()
                ->route('groups.index', ['tab' => 'invitations'])
                ->with('error', 'No se encontró una invitación pendiente para este grupo.');
        }

        // Cambiar status a 'active'
        $invitation->update([
            'status' => 'active',
        ]);

        // Obtener información del inviter
        $inviter = $invitation->invitedBy;

        // Crear notificación para el inviter
        if ($inviter) {
            $this->notificationService->create([
                'type' => 'group_invitation_accepted',
                'title' => 'Invitación aceptada',
                'message' => "{$user->name} ha aceptado tu invitación al grupo \"{$group->name}\"",
                'data' => [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'accepted_user_id' => $user->id,
                    'accepted_user_name' => $user->name,
                ],
                'action_url' => route('groups.admin', $group),
                'action_label' => 'Ver grupo',
            ], $inviter, $inviter->organization_id);
        }

        // Registrar en logs de auditoría
        Log::info('Group invitation accepted', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'group_id' => $group->id,
            'group_name' => $group->name,
            'inviter_id' => $inviter?->id,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('groups.items.index', $group)
            ->with('success', 'Invitación aceptada. Ahora eres miembro del grupo.');
    }

    /**
     * Rechazar una invitación a un grupo.
     */
    public function rejectInvitation(Request $request, Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();

        // Buscar la invitación pendiente
        $invitation = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('status', 'invited')
            ->first();

        if (!$invitation) {
            return redirect()
                ->route('groups.index', ['tab' => 'invitations'])
                ->with('error', 'No se encontró una invitación pendiente para este grupo.');
        }

        // Cambiar status a 'revoked' (o eliminar, pero mejor mantener para historial)
        $invitation->update([
            'status' => 'revoked',
        ]);

        // Obtener información del inviter
        $inviter = $invitation->invitedBy;

        // Crear notificación para el inviter
        if ($inviter) {
            $this->notificationService->create([
                'type' => 'group_invitation_rejected',
                'title' => 'Invitación rechazada',
                'message' => "{$user->name} ha rechazado tu invitación al grupo \"{$group->name}\"",
                'data' => [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'rejected_user_id' => $user->id,
                    'rejected_user_name' => $user->name,
                ],
                'action_url' => route('groups.admin', $group),
                'action_label' => 'Ver grupo',
            ], $inviter, $inviter->organization_id);
        }

        // Registrar en logs de auditoría
        Log::info('Group invitation rejected', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'group_id' => $group->id,
            'group_name' => $group->name,
            'inviter_id' => $inviter?->id,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('groups.index', ['tab' => 'invitations'])
            ->with('success', 'Invitación rechazada.');
    }

    /**
     * Reenviar una invitación a un grupo (solo owner/admin).
     */
    public function resendInvitation(Request $request, Group $group, User $user): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $currentUser = Auth::user();
        $userRole = $group->getUserRole($currentUser);

        if (!in_array($userRole, ['owner', 'admin'])) {
            abort(403, 'No tienes permisos para reenviar invitaciones.');
        }

        // Verificar que el usuario no sea el propietario
        if ($group->owner_user_id == $user->id) {
            return redirect()
                ->back()
                ->with('error', 'El propietario ya es miembro del grupo.');
        }

        // Buscar invitación existente
        $invitation = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if ($invitation && $invitation->status === 'active') {
            return redirect()
                ->back()
                ->with('error', 'El usuario ya es miembro activo del grupo.');
        }

        // Si no existe o está revocada, crear/actualizar invitación
        if (!$invitation) {
            $invitation = GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'role' => 'member', // Por defecto, se puede cambiar después
                'status' => 'invited',
                'invited_by_user_id' => $currentUser->id,
            ]);
        } else {
            // Actualizar invitación existente
            $invitation->update([
                'status' => 'invited',
                'invited_by_user_id' => $currentUser->id,
            ]);
        }

        // Crear notificación para el usuario invitado
        $this->notificationService->create([
            'type' => 'group_invitation',
            'title' => 'Invitación a grupo',
            'message' => "{$currentUser->name} te ha invitado al grupo \"{$group->name}\"",
            'data' => [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'inviter_id' => $currentUser->id,
                'inviter_name' => $currentUser->name,
                'role' => $invitation->role,
            ],
            'action_url' => route('groups.index', ['tab' => 'invitations']),
            'action_label' => 'Ver invitaciones',
        ], $user, $user->organization_id);

        // Registrar en logs de auditoría
        Log::info('Group invitation resent', [
            'inviter_id' => $currentUser->id,
            'inviter_email' => $currentUser->email,
            'group_id' => $group->id,
            'group_name' => $group->name,
            'invited_user_id' => $user->id,
            'invited_user_email' => $user->email,
            'timestamp' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Invitación reenviada exitosamente.');
    }

    /**
     * Remover un miembro del grupo.
     */
    public function removeMember(Request $request, Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $userRole = $group->getUserRole($user);

        if (!in_array($userRole, ['owner', 'admin'])) {
            abort(403, 'No tienes permisos para remover miembros del grupo.');
        }

        $request->validate([
            'member_id' => ['required', 'integer', 'exists:group_members,id'],
        ]);

        try {
            $member = GroupMember::findOrFail($request->input('member_id'));

            // Verificar que el miembro pertenece al grupo
            if ($member->group_id !== $group->id) {
                return redirect()
                    ->back()
                    ->with('error', 'El miembro no pertenece a este grupo.');
            }

            // No permitir remover al propietario
            if ($member->role === 'owner') {
                return redirect()
                    ->back()
                    ->with('error', 'No se puede remover al propietario del grupo.');
            }

            // Si es admin removiendo, no puede remover a otro admin (solo owner puede)
            if ($userRole === 'admin' && $member->role === 'admin') {
                return redirect()
                    ->back()
                    ->with('error', 'Solo el propietario puede remover administradores.');
            }

            // Guardar información para logging antes de actualizar
            $removedUserId = $member->user_id;
            $removedUser = $member->user;
            $memberRole = $member->role;

            // Cambiar estado a 'revoked' en lugar de eliminar
            $member->update(['status' => 'revoked']);

            // Registrar en logs de auditoría
            Log::info('Group member removed', [
                'remover_id' => $user->id,
                'remover_email' => $user->email,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'removed_user_id' => $removedUserId,
                'removed_user_email' => $removedUser->email ?? 'N/A',
                'removed_user_role' => $memberRole,
                'timestamp' => now(),
            ]);

            return redirect()
                ->route('groups.admin', $group)
                ->with('success', 'Miembro removido del grupo exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al remover miembro: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar el rol de un miembro.
     */
    public function updateMemberRole(Request $request, Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        if ($group->owner_user_id !== Auth::id()) {
            abort(403, 'Solo el propietario puede actualizar roles.');
        }

        $request->validate([
            'member_id' => ['required', 'integer', 'exists:group_members,id'],
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ]);

        try {
            $member = GroupMember::findOrFail($request->input('member_id'));

            // Verificar que el miembro pertenece al grupo
            if ($member->group_id !== $group->id) {
                return redirect()
                    ->back()
                    ->with('error', 'El miembro no pertenece a este grupo.');
            }

            // No permitir cambiar el rol del propietario
            if ($member->role === 'owner') {
                return redirect()
                    ->back()
                    ->with('error', 'No se puede cambiar el rol del propietario.');
            }

            $oldRole = $member->role;
            $newRole = $request->input('role');
            $memberUser = $member->user;
            
            $member->update(['role' => $newRole]);

            // Registrar en logs de auditoría
            Log::info('Group member role updated', [
                'updater_id' => Auth::id(),
                'updater_email' => Auth::user()->email,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'member_user_id' => $memberUser->id,
                'member_user_email' => $memberUser->email,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'timestamp' => now(),
            ]);

            return redirect()
                ->route('groups.admin', $group)
                ->with('success', 'Rol del miembro actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al actualizar rol: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar items compartidos con el grupo.
     */
    public function itemsIndex(Request $request, Group $group): View
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $isOwner = $group->owner_user_id === $user->id;
        $isMember = $group->hasActiveMember($user);

        if (!$isOwner && !$isMember) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        // Obtener items compartidos con el grupo
        $sharedItemIds = \App\Models\ItemShareGroup::where('group_id', $group->id)
            ->pluck('vault_item_id')
            ->toArray();

        $filters = [
            'type' => $request->input('type'),
            'favorite' => $request->boolean('favorite'),
            'search' => $request->input('search'),
            'folder_id' => $request->input('folder_id'),
        ];

        // Si no hay items compartidos, devolver lista vacía
        if (empty($sharedItemIds)) {
            $items = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                15,
                1
            );
        } else {
            $query = \App\Models\VaultItem::whereIn('id', $sharedItemIds)
                ->where('status', '!=', 'deleted')
                ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
                ->with('secret', 'owner', 'folder')
                ->latest();

            // Aplicar filtros
            if (isset($filters['type']) && !empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['favorite']) && $filters['favorite']) {
                $query->where('favorite', true);
            }

            if (isset($filters['search']) && !empty($filters['search'])) {
                $query->where('title', 'like', '%' . $filters['search'] . '%');
            }

            // Filtro por carpeta del grupo
            if (isset($filters['folder_id']) && $filters['folder_id']) {
                if ($filters['folder_id'] === 'null' || $filters['folder_id'] === null) {
                    $query->whereNull('folder_id');
                } else {
                    $folderId = (int) $filters['folder_id'];
                    $folder = \App\Models\Folder::where('id', $folderId)
                        ->where('group_id', $group->id)
                        ->first();
                    
                    if ($folder) {
                        $query->where('folder_id', $folderId);
                    }
                }
            }

            $items = $query->paginate(15);
        }

        // Agregar información adicional
        $items->getCollection()->transform(function ($item) use ($user) {
            $item->has_totp = $this->vaultService->hasTotp($item);
            $item->user_permission = $item->getEffectivePermission($user);
            $item->is_shared = $item->owner_user_id !== $user->id;
            return $item;
        });

        $folders = $this->folderService->getGroupRootFolders($group);
        $folders->load('children');
        $allFoldersQuery = \App\Models\Folder::where('group_id', $group->id)->with('parent');
        if ($user->organization_id !== null) {
            $allFoldersQuery->where('organization_id', $user->organization_id);
        }
        $allFolders = $allFoldersQuery->orderBy('name')->get();

        // Contar items sin carpeta (solo items compartidos con el grupo)
        $unassignedCount = 0;
        if (!empty($sharedItemIds)) {
            $unassignedCount = \App\Models\VaultItem::whereIn('id', $sharedItemIds)
                ->whereNull('folder_id')
                ->where('status', '!=', 'deleted')
                ->count();
        }

        // Obtener rol del usuario en el grupo
        $userRole = $group->getUserRole($user);

        if ($request->ajax()) {
            return view('groups.items.partials.items-list', [
                'items' => $items,
                'group' => $group,
            ]);
        }

        return view('groups.items.vault', [
            'group' => $group,
            'items' => $items,
            'folders' => $folders,
            'allFolders' => $allFolders,
            'unassignedCount' => $unassignedCount,
            'userRole' => $userRole,
            'activeFolderId' => $request->input('folder_id'),
        ]);
    }

    /**
     * Mostrar formulario para crear item desde el grupo.
     */
    public function itemsCreate(Group $group): View
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $isOwner = $group->owner_user_id === $user->id;
        $isMember = $group->hasActiveMember($user);
        $userRole = $group->getUserRole($user);

        if (!$isOwner && !$isMember) {
            abort(403, 'No tienes acceso a este grupo.');
        }
        if ($userRole === 'viewer') {
            abort(403, 'No tienes permisos para crear items en este grupo.');
        }

        $folders = $this->folderService->getGroupRootFolders($group);
        $folders->load('children');
        $allFoldersQuery = \App\Models\Folder::where('group_id', $group->id)->with('parent');
        if ($user->organization_id !== null) {
            $allFoldersQuery->where('organization_id', $user->organization_id);
        }
        $allFolders = $allFoldersQuery->orderBy('name')->get();

        return view('groups.items.create', [
            'group' => $group,
            'folders' => $allFolders,
            'userRole' => $userRole,
        ]);
    }

    /**
     * Crear item desde el grupo y compartir automáticamente.
     */
    public function itemsStore(StoreVaultItemRequest $request, Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $isOwner = $group->owner_user_id === $user->id;
        $isMember = $group->hasActiveMember($user);
        $userRole = $group->getUserRole($user);

        if (!$isOwner && !$isMember) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        // Solo owner, admin y member pueden crear (no viewer)
        if ($userRole === 'viewer') {
            abort(403, 'No tienes permisos para crear items en este grupo.');
        }

        try {
            // Validar que la carpeta pertenece al grupo si se especifica
            if ($request->filled('folder_id')) {
                $folder = \App\Models\Folder::where('id', $request->input('folder_id'))
                    ->where('group_id', $group->id)
                    ->first();
                
                if (!$folder) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'La carpeta seleccionada no pertenece a este grupo.');
                }
            }

            // Crear el item (owner = usuario que crea)
            $item = $this->vaultService->createItem($user, $request->validated());

            // Compartir automáticamente con el grupo con permiso "edit"
            $this->shareService->shareWithGroup($item, $user, $group, 'edit');

            // Obtener todos los miembros activos del grupo (excepto el creador)
            $groupMembers = $group->members()
                ->where('status', 'active')
                ->where('user_id', '!=', $user->id)
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            // Crear notificaciones para todos los miembros activos del grupo
            foreach ($groupMembers as $member) {
                $this->notificationService->create([
                    'type' => 'item_created_in_group',
                    'title' => 'Nuevo item en grupo',
                    'message' => "{$user->name} ha creado el item \"{$item->title}\" en el grupo \"{$group->name}\"",
                    'data' => [
                        'vault_item_id' => $item->id,
                        'vault_item_title' => $item->title,
                        'vault_item_type' => $item->type,
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'created_by_user_id' => $user->id,
                        'created_by_user_name' => $user->name,
                    ],
                    'action_url' => route('groups.items.index', $group),
                    'action_label' => 'Ver grupo',
                ], $member, $member->organization_id);
            }

            return redirect()
                ->route('groups.items.index', $group)
                ->with('success', 'Item creado y compartido con el grupo exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el item: ' . $e->getMessage());
        }
    }

    /**
     * Crear carpeta del grupo.
     */
    public function storeFolder(Request $request, Group $group): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $isOwner = $group->owner_user_id === $user->id;
        $isMember = $group->hasActiveMember($user);
        $userRole = $group->getUserRole($user);

        if (!$isOwner && !$isMember) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        // Solo owner, admin y member pueden crear carpetas (no viewer)
        if ($userRole === 'viewer') {
            abort(403, 'No tienes permisos para crear carpetas en este grupo.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:1'],
            'parent_id' => ['nullable', 'integer', 'exists:folders,id'],
        ]);

        try {
            $folder = $this->folderService->createFolder($user, $request->only(['name', 'parent_id']), $group);

            // Registrar en logs de auditoría
            Log::info('Group folder created', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'folder_id' => $folder->id,
                'folder_name' => $folder->name,
                'parent_id' => $folder->parent_id,
                'timestamp' => now(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Carpeta creada exitosamente.',
                    'folder' => [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'path' => $folder->getFullPath(),
                    ],
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Carpeta creada exitosamente.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Error al crear la carpeta: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar carpeta del grupo.
     */
    public function updateFolder(Request $request, Group $group, \App\Models\Folder $folder): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $isOwner = $group->owner_user_id === $user->id;
        $isMember = $group->hasActiveMember($user);
        $userRole = $group->getUserRole($user);

        if (!$isOwner && !$isMember) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        // Solo owner, admin y member pueden editar carpetas (no viewer)
        if ($userRole === 'viewer') {
            abort(403, 'No tienes permisos para editar carpetas en este grupo.');
        }

        // Verificar que la carpeta pertenece al grupo
        if ($folder->group_id !== $group->id) {
            abort(403, 'La carpeta no pertenece a este grupo.');
        }

        // Capturar datos originales antes de actualizar
        $originalName = $folder->name;
        $originalParentId = $folder->parent_id;

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:1'],
            'parent_id' => ['nullable', 'integer', 'exists:folders,id'],
        ]);

        try {
            $updatedFolder = $this->folderService->updateFolder($folder, $user, $request->only(['name', 'parent_id']));

            // Calcular cambios
            $changes = [];
            if ($originalName !== $updatedFolder->name) {
                $changes['name'] = ['from' => $originalName, 'to' => $updatedFolder->name];
            }
            if ($originalParentId !== $updatedFolder->parent_id) {
                $changes['parent_id'] = ['from' => $originalParentId, 'to' => $updatedFolder->parent_id];
            }

            // Registrar en logs de auditoría
            Log::info('Group folder updated', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'folder_id' => $updatedFolder->id,
                'folder_name' => $updatedFolder->name,
                'changes' => $changes,
                'timestamp' => now(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Carpeta actualizada exitosamente.',
                    'folder' => [
                        'id' => $updatedFolder->id,
                        'name' => $updatedFolder->name,
                        'path' => $updatedFolder->getFullPath(),
                    ],
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Carpeta actualizada exitosamente.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Error al actualizar la carpeta: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar carpeta del grupo.
     */
    public function destroyFolder(Request $request, Group $group, \App\Models\Folder $folder): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $this->ensureGroupInOrg($group);
        $user = Auth::user();
        $isOwner = $group->owner_user_id === $user->id;
        $isMember = $group->hasActiveMember($user);
        $userRole = $group->getUserRole($user);

        if (!$isOwner && !$isMember) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        // Solo owner, admin y member pueden eliminar carpetas (no viewer)
        if ($userRole === 'viewer') {
            abort(403, 'No tienes permisos para eliminar carpetas en este grupo.');
        }

        // Verificar que la carpeta pertenece al grupo
        if ($folder->group_id !== $group->id) {
            abort(403, 'La carpeta no pertenece a este grupo.');
        }

        // Guardar información para logging antes de eliminar
        $folderId = $folder->id;
        $folderName = $folder->name;
        $parentId = $folder->parent_id;

        try {
            $this->folderService->deleteFolder($folder, $user);

            // Registrar en logs de auditoría
            Log::info('Group folder deleted', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'folder_id' => $folderId,
                'folder_name' => $folderName,
                'parent_id' => $parentId,
                'timestamp' => now(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Carpeta eliminada exitosamente.',
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Carpeta eliminada exitosamente.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Error al eliminar la carpeta: ' . $e->getMessage());
        }
    }

    private function ensureGroupInOrg(Group $group): void
    {
        $user = Auth::user();
        if ($user->organization_id === null) {
            return;
        }
        if ($group->organization_id !== $user->organization_id) {
            abort(403, 'No tienes acceso a este grupo.');
        }
    }
}
