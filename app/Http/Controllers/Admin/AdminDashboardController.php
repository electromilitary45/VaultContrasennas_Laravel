<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\ItemShareGroup;
use App\Models\ItemShareUser;
use App\Models\OrganizationInvitationCode;
use App\Models\User;
use App\Models\VaultItem;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controlador para el Dashboard de Actividad del Sistema
 *
 * Muestra métricas, gráficos y eventos recientes. Scope por organización
 * para org admin; global para platform super admin.
 */
class AdminDashboardController extends Controller
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Mostrar dashboard principal de administración
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $orgId = $user->isOrgAdmin() ? $user->organization_id : null;

        $metrics = $this->buildMetrics($orgId);
        $activityLast30Days = $this->getActivityLast30Days($orgId);
        $recentEvents = $this->getRecentEvents($orgId);
        $dailyActivity = $this->getDailyActivity(30, $orgId);
        $recentLogs = $this->getRecentLogs($orgId, 15);

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'activityLast30Days' => $activityLast30Days,
            'recentEvents' => $recentEvents,
            'dailyActivity' => $dailyActivity,
            'recentLogs' => $recentLogs,
            'orgId' => $orgId,
        ]);
    }

    /**
     * Publica una nueva versión de la extensión (timestamp en version.txt y JSON público).
     * Solo platform super admin.
     */
    public function publishExtensionVersion(): RedirectResponse
    {
        Artisan::call('extension:sync-version');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Nueva versión de la extensión publicada. Los usuarios verán la actualización al comprobar desde el popup.');
    }

    /**
     * Construir métricas principales (scope por org o global)
     *
     * @param int|null $orgId
     * @return array<string, mixed>
     */
    private function buildMetrics(?int $orgId): array
    {
        $userScope = fn ($q) => $orgId === null ? $q : $q->where('organization_id', $orgId);
        $vaultScope = fn ($q) => $orgId === null ? $q : $q->where('organization_id', $orgId);
        $groupScope = fn ($q) => $orgId === null ? $q : $q->where('organization_id', $orgId);

        $userQuery = User::query()->when($orgId !== null, $userScope);
        $vaultBase = VaultItem::where('status', '!=', 'deleted')->when($orgId !== null, $vaultScope);
        $groupBase = Group::query()->when($orgId !== null, $groupScope);

        $usersActive = (clone $userQuery)->where('is_active', true);
        $usersInactive = (clone $userQuery)->where('is_active', false);
        $usersVerified = (clone $userQuery)->whereNotNull('email_verified_at');
        $usersWith2fa = (clone $userQuery)->where('totp_enabled', true)->whereNotNull('totp_secret');
        $usersAdmins = (clone $userQuery)->whereIn('role', ['admin', 'super_admin']);
        $usersSuper = (clone $userQuery)->where('role', 'super_admin');

        $membersQuery = GroupMember::where('status', 'active')
            ->when($orgId !== null, fn ($q) => $q->whereHas('group', fn ($g) => $g->where('organization_id', $orgId)));

        $userSharesQuery = ItemShareUser::query()->when(
            $orgId !== null,
            fn ($q) => $q->whereHas('vaultItem', fn ($v) => $v->where('organization_id', $orgId))
        );
        $groupSharesQuery = ItemShareGroup::query()->when(
            $orgId !== null,
            fn ($q) => $q->whereHas('vaultItem', fn ($v) => $v->where('organization_id', $orgId))
        );

        $groupsWithItemsQuery = (clone $groupBase)->whereHas('sharedItems', function ($q) {
            $q->whereHas('vaultItem', fn ($itemQuery) => $itemQuery->where('status', '!=', 'deleted'));
        });

        $invitationCodes = [];
        if ($orgId !== null) {
            $invitationCodes = [
                'total' => OrganizationInvitationCode::where('organization_id', $orgId)->count(),
                'unused' => OrganizationInvitationCode::where('organization_id', $orgId)->unused()->count(),
            ];
        }

        return [
            'users' => [
                'total' => $userQuery->count(),
                'active' => $usersActive->count(),
                'inactive' => $usersInactive->count(),
                'verified' => $usersVerified->count(),
                'with_2fa' => $usersWith2fa->count(),
                'admins' => $usersAdmins->count(),
                'super' => $usersSuper->count(),
            ],
            'vault' => [
                'total' => (clone $vaultBase)->count(),
                'by_type' => [
                    'auth' => (clone $vaultBase)->where('type', 'auth')->count(),
                    'card' => (clone $vaultBase)->where('type', 'card')->count(),
                    'note' => (clone $vaultBase)->where('type', 'note')->count(),
                    'api_key' => (clone $vaultBase)->where('type', 'api_key')->count(),
                    'ssh_key' => (clone $vaultBase)->where('type', 'ssh_key')->count(),
                    'env_file' => (clone $vaultBase)->where('type', 'env_file')->count(),
                ],
                'favorites' => (clone $vaultBase)->where('favorite', true)->count(),
                'with_totp' => (clone $vaultBase)->where('type', 'auth')->whereHas('secret')->count(),
            ],
            'groups' => [
                'total' => $groupBase->count(),
                'total_members' => $membersQuery->count(),
                'groups_with_items' => $groupsWithItemsQuery->count(),
            ],
            'sharing' => [
                'user_shares' => $userSharesQuery->count(),
                'group_shares' => $groupSharesQuery->count(),
            ],
            'invitation_codes' => $invitationCodes,
        ];
    }

    /**
     * Actividad últimos 30 días (scope por org o global)
     *
     * @return array<string, int>
     */
    private function getActivityLast30Days(?int $orgId): array
    {
        $start = now()->subDays(30);

        $vaultBase = VaultItem::where('status', '!=', 'deleted')
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $itemsCreated = (clone $vaultBase)->where('created_at', '>=', $start)->count();
        $itemsUpdated = (clone $vaultBase)
            ->where('updated_at', '>=', $start)
            ->where('created_at', '<', $start)
            ->count();

        $groupBase = Group::query()->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $groupsCreated = (clone $groupBase)->where('created_at', '>=', $start)->count();

        $userBase = User::query()->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $usersRegistered = (clone $userBase)->where('created_at', '>=', $start)->count();

        $membersQuery = GroupMember::where('created_at', '>=', $start)->where('status', 'active');
        if ($orgId !== null) {
            $membersQuery->whereHas('group', fn ($q) => $q->where('organization_id', $orgId));
        }
        $membersInvited = $membersQuery->count();

        $userShares = ItemShareUser::where('created_at', '>=', $start)
            ->when($orgId !== null, fn ($q) => $q->whereHas('vaultItem', fn ($v) => $v->where('organization_id', $orgId)));
        $groupShares = ItemShareGroup::where('created_at', '>=', $start)
            ->when($orgId !== null, fn ($q) => $q->whereHas('vaultItem', fn ($v) => $v->where('organization_id', $orgId)));
        $sharesCreated = $userShares->count() + $groupShares->count();

        return [
            'items_created' => $itemsCreated,
            'items_updated' => $itemsUpdated,
            'groups_created' => $groupsCreated,
            'users_registered' => $usersRegistered,
            'members_invited' => $membersInvited,
            'shares_created' => $sharesCreated,
        ];
    }

    /**
     * Eventos recientes (últimos 7 días), scope por org o global
     *
     * @return \Illuminate\Support\Collection
     */
    private function getRecentEvents(?int $orgId): \Illuminate\Support\Collection
    {
        $start = now()->subDays(7);

        $itemsQuery = VaultItem::where('created_at', '>=', $start)
            ->where('status', '!=', 'deleted')
            ->with('owner')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $recentItems = $itemsQuery->get()->map(fn ($item) => [
            'type' => 'item_created',
            'description' => "Item '{$item->title}' creado por {$item->owner->name}",
            'timestamp' => $item->created_at,
            'user' => $item->owner,
        ]);

        $groupsQuery = Group::where('created_at', '>=', $start)
            ->with('owner')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $recentGroups = $groupsQuery->get()->map(fn ($group) => [
            'type' => 'group_created',
            'description' => "Grupo '{$group->name}' creado por {$group->owner->name}",
            'timestamp' => $group->created_at,
            'user' => $group->owner,
        ]);

        $usersQuery = User::where('created_at', '>=', $start)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $recentUsers = $usersQuery->get()->map(fn ($user) => [
            'type' => 'user_registered',
            'description' => "Usuario {$user->name} ({$user->email}) se registró",
            'timestamp' => $user->created_at,
            'user' => $user,
        ]);

        return collect()->merge($recentItems)->merge($recentGroups)->merge($recentUsers)
            ->sortByDesc('timestamp')
            ->take(20)
            ->values();
    }

    /**
     * Actividad diaria para gráfico (últimos N días), scope por org o global
     *
     * @return array<int, array<string, mixed>>
     */
    private function getDailyActivity(int $days, ?int $orgId): array
    {
        $start = now()->subDays($days);
        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $dates[$d] = ['date' => $d, 'items' => 0, 'groups' => 0, 'users' => 0, 'shares' => 0];
        }

        $itemsQuery = VaultItem::where('created_at', '>=', $start)->where('status', '!=', 'deleted')
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $itemsByDay = $itemsQuery->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')->pluck('count', 'date')->toArray();

        $groupsQuery = Group::where('created_at', '>=', $start)
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $groupsByDay = $groupsQuery->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')->pluck('count', 'date')->toArray();

        $usersQuery = User::where('created_at', '>=', $start)
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
        $usersByDay = $usersQuery->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')->pluck('count', 'date')->toArray();

        $userSharesQ = DB::table('item_shares_users')
            ->join('vault_items', 'item_shares_users.vault_item_id', '=', 'vault_items.id')
            ->where('item_shares_users.created_at', '>=', $start)
            ->select(DB::raw('DATE(item_shares_users.created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date');
        if ($orgId !== null) {
            $userSharesQ->where('vault_items.organization_id', $orgId);
        }
        $sharesByDay = $userSharesQ->pluck('count', 'date')->toArray();

        $groupSharesQ = DB::table('item_shares_groups')
            ->join('vault_items', 'item_shares_groups.vault_item_id', '=', 'vault_items.id')
            ->where('item_shares_groups.created_at', '>=', $start)
            ->select(DB::raw('DATE(item_shares_groups.created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date');
        if ($orgId !== null) {
            $groupSharesQ->where('vault_items.organization_id', $orgId);
        }
        $groupSharesByDay = $groupSharesQ->pluck('count', 'date')->toArray();
        foreach ($groupSharesByDay as $date => $count) {
            $sharesByDay[$date] = ($sharesByDay[$date] ?? 0) + $count;
        }

        foreach ($dates as $date => &$data) {
            $data['items'] = $itemsByDay[$date] ?? 0;
            $data['groups'] = $groupsByDay[$date] ?? 0;
            $data['users'] = $usersByDay[$date] ?? 0;
            $data['shares'] = $sharesByDay[$date] ?? 0;
        }

        return array_values($dates);
    }

    /**
     * Últimos N logs de auditoría (scope por org o global)
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\AuditLog>
     */
    private function getRecentLogs(?int $orgId, int $limit): \Illuminate\Support\Collection
    {
        $filters = [];
        if ($orgId !== null) {
            $filters['organization_id'] = $orgId;
        }
        $paginator = $this->auditService->getLogs($filters, $limit);
        return $paginator->getCollection();
    }
}
