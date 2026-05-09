<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controlador para gestión de grupos desde el panel de administración
 */
class AdminGroupController extends Controller
{
    /**
     * Listar todos los grupos
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Group::with(['owner'])->withCount('activeMembers');
        $baseQuery = Group::query();

        if ($request->user()->isOrgAdmin()) {
            $orgId = $request->user()->organization_id;
            $query->where('organization_id', $orgId);
            $baseQuery->where('organization_id', $orgId);
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_user_id', $request->input('owner_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                      $ownerQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $groups = $query->paginate(20)->withQueryString();

        $groupIdsInScope = (clone $baseQuery)->pluck('id');
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'total_members' => \App\Models\GroupMember::where('status', 'active')
                ->whereIn('group_id', $groupIdsInScope)->count(),
            'groups_with_items' => (clone $baseQuery)->whereHas('sharedItems', function ($q) {
                $q->whereHas('vaultItem', function ($itemQuery) {
                    $itemQuery->where('status', '!=', 'deleted');
                });
            })->count(),
        ];

        $usersQuery = User::orderBy('name');
        if ($request->user()->isOrgAdmin()) {
            $usersQuery->where('organization_id', $request->user()->organization_id);
        }
        $users = $usersQuery->get(['id', 'name', 'email']);

        return view('admin.groups.index', [
            'groups' => $groups,
            'stats' => $stats,
            'users' => $users,
            'filters' => $request->only(['owner_id', 'search', 'sort_by', 'sort_dir']),
        ]);
    }

    /**
     * Ver detalle de un grupo
     *
     * @param Group $group
     * @return View
     */
    public function show(Group $group): View
    {
        $this->ensureGroupInOrg($group);

        $group->load(['owner', 'members.user', 'sharedItems']);

        // Estadísticas del grupo
        $groupStats = [
            'total_members' => $group->activeMembers()->count(),
            'total_items' => $group->sharedItems()->whereHas('vaultItem', function ($q) {
                $q->where('status', '!=', 'deleted');
            })->count(),
            'by_role' => [
                'owner' => 1, // El owner siempre es 1
                'admin' => $group->members()->where('role', 'admin')->where('status', 'active')->count(),
                'member' => $group->members()->where('role', 'member')->where('status', 'active')->count(),
                'viewer' => $group->members()->where('role', 'viewer')->where('status', 'active')->count(),
            ],
        ];

        // Miembros activos
        $members = $group->members()
            ->where('status', 'active')
            ->with(['user', 'invitedBy'])
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'member', 'viewer')")
            ->orderBy('created_at')
            ->get();

        return view('admin.groups.show', [
            'group' => $group,
            'stats' => $groupStats,
            'members' => $members,
        ]);
    }

    /**
     * Eliminar grupo desde administración
     *
     * @param Group $group
     * @return RedirectResponse
     */
    public function destroy(Group $group): RedirectResponse
    {
        $this->ensureGroupInOrg($group);

        $admin = auth()->user();

        // Guardar información para logging ANTES de eliminar
        $groupId = $group->id;
        $groupName = $group->name;
        $ownerId = $group->owner_user_id;
        $ownerEmail = $group->owner->email ?? 'N/A';
        $memberCount = $group->activeMembers()->count();
        $itemsCount = $group->sharedItems()->whereHas('vaultItem', function ($q) {
            $q->where('status', '!=', 'deleted');
        })->count();

        // Eliminar grupo (esto eliminará en cascada los miembros y comparticiones)
        $group->delete();

        // Registrar en logs de auditoría
        Log::info('Group deleted by admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'group_id' => $groupId,
            'group_name' => $groupName,
            'owner_id' => $ownerId,
            'owner_email' => $ownerEmail,
            'members_count' => $memberCount,
            'items_count' => $itemsCount,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.groups.index')
            ->with('success', 'Grupo eliminado exitosamente.');
    }

    private function ensureGroupInOrg(Group $group): void
    {
        $admin = request()->user();
        if (! $admin->isOrgAdmin()) {
            return;
        }
        if ($group->organization_id !== $admin->organization_id) {
            abort(403, 'No puedes gestionar grupos de otra organización.');
        }
    }
}
