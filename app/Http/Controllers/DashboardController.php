<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\ItemShareGroup;
use App\Models\ItemShareUser;
use App\Models\VaultItem;
use App\Services\FolderService;
use App\Services\VaultService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(
        private VaultService $vaultService,
        private FolderService $folderService
    ) {}

    /**
     * Mostrar el dashboard principal.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Estadísticas principales
        $stats = $this->getStats($user);

        // Items recientes (últimos 7)
        $recentItems = $this->getRecentItems($user, 7);

        // Items favoritos (top 5)
        $favoriteItems = $this->getFavoriteItems($user, 5);

        // Grupos del usuario
        $userGroups = $this->getUserGroups($user);

        // Items compartidos recientemente
        $recentShares = $this->getRecentShares($user);

        return view('dashboard', [
            'stats' => $stats,
            'recentItems' => $recentItems,
            'favoriteItems' => $favoriteItems,
            'userGroups' => $userGroups,
            'recentShares' => $recentShares,
        ]);
    }

    /**
     * Obtener estadísticas del usuario.
     */
    private function getStats($user): array
    {
        // Items totales (propios + compartidos)
        $allItems = $this->vaultService->listItems($user, [], 1000);
        $totalItems = $allItems->total();

        $ownItems = VaultItem::where('owner_user_id', $user->id)
            ->where('status', '!=', 'deleted')
            ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
            ->count();

        $favoriteItems = VaultItem::where(function ($q) use ($user) {
                $q->where('owner_user_id', $user->id);
                $sharedItemIds = ItemShareUser::where('user_id', $user->id)->pluck('vault_item_id')->toArray();
                if (!empty($sharedItemIds)) {
                    $q->orWhereIn('id', $sharedItemIds);
                }
            })
            ->where('favorite', true)
            ->where('status', '!=', 'deleted')
            ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
            ->count();

        $sharedItemIds = ItemShareUser::where('user_id', $user->id)->pluck('vault_item_id')->toArray();
        $sharedWithMe = !empty($sharedItemIds)
            ? VaultItem::whereIn('id', $sharedItemIds)
                ->where('status', '!=', 'deleted')
                ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
                ->count()
            : 0;

        $userGroupIdsQuery = GroupMember::where('user_id', $user->id)->where('status', 'active');
        if ($user->organization_id !== null) {
            $userGroupIdsQuery->whereHas('group', fn ($q) => $q->where('organization_id', $user->organization_id));
        }
        $userGroupIds = $userGroupIdsQuery->pluck('group_id')->toArray();
        $groupSharedItemIds = !empty($userGroupIds)
            ? ItemShareGroup::whereIn('group_id', $userGroupIds)->pluck('vault_item_id')->toArray()
            : [];
        $groupSharedWithMe = !empty($groupSharedItemIds)
            ? VaultItem::whereIn('id', $groupSharedItemIds)
                ->where('status', '!=', 'deleted')
                ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
                ->count()
            : 0;
        $totalSharedWithMe = $sharedWithMe + $groupSharedWithMe;

        $sharedByMeItemIds = ItemShareUser::where('shared_by_user_id', $user->id)->pluck('vault_item_id')->toArray();
        $sharedByMe = !empty($sharedByMeItemIds)
            ? VaultItem::whereIn('id', $sharedByMeItemIds)
                ->where('status', '!=', 'deleted')
                ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
                ->count()
            : 0;

        $groupSharedByMeItemIds = ItemShareGroup::where('shared_by_user_id', $user->id)->pluck('vault_item_id')->toArray();
        $groupSharedByMe = !empty($groupSharedByMeItemIds)
            ? VaultItem::whereIn('id', $groupSharedByMeItemIds)
                ->where('status', '!=', 'deleted')
                ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
                ->count()
            : 0;
        $totalSharedByMe = $sharedByMe + $groupSharedByMe;

        $totalFolders = Folder::where('owner_user_id', $user->id)
            ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
            ->count();

        $ownedGroups = Group::where('owner_user_id', $user->id)
            ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
            ->count();

        $memberGroups = GroupMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('group', function ($q) use ($user) {
                $q->where('owner_user_id', '!=', $user->id);
                if ($user->organization_id !== null) {
                    $q->where('organization_id', $user->organization_id);
                }
            })
            ->count();

        // Items con TOTP
        $itemsWithTotp = 0;
        $allItemsCollection = $allItems->getCollection();
        foreach ($allItemsCollection as $item) {
            if ($this->vaultService->hasTotp($item)) {
                $itemsWithTotp++;
            }
        }

        return [
            'total_items' => $totalItems,
            'own_items' => $ownItems,
            'favorite_items' => $favoriteItems,
            'shared_with_me' => $totalSharedWithMe,
            'shared_by_me' => $totalSharedByMe,
            'total_folders' => $totalFolders,
            'owned_groups' => $ownedGroups,
            'member_groups' => $memberGroups,
            'items_with_totp' => $itemsWithTotp,
        ];
    }

    /**
     * Obtener items recientes.
     */
    private function getRecentItems($user, int $limit = 7)
    {
        $items = $this->vaultService->listItems($user, [], $limit);
        
        // Asegurar que las relaciones estén cargadas
        $items->loadMissing(['folder', 'owner']);
        
        return $items->getCollection()->map(function ($item) {
            $item->has_totp = $this->vaultService->hasTotp($item);
            $item->user_permission = $item->getEffectivePermission(Auth::user());
            return $item;
        });
    }

    /**
     * Obtener items favoritos.
     */
    private function getFavoriteItems($user, int $limit = 5)
    {
        $items = $this->vaultService->listItems($user, ['favorite' => true], $limit);
        
        // Asegurar que las relaciones estén cargadas
        $items->loadMissing(['folder', 'owner']);
        
        return $items->getCollection()->map(function ($item) {
            $item->has_totp = $this->vaultService->hasTotp($item);
            $item->user_permission = $item->getEffectivePermission(Auth::user());
            return $item;
        });
    }

    /**
     * Obtener grupos del usuario.
     */
    private function getUserGroups($user)
    {
        $ownedQuery = Group::where('owner_user_id', $user->id);
        if ($user->organization_id !== null) {
            $ownedQuery->where('organization_id', $user->organization_id);
        }
        $ownedGroups = $ownedQuery->withCount('activeMembers')->orderBy('created_at', 'desc')->limit(3)->get();

        $memberQuery = Group::whereHas('members', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->where('owner_user_id', '!=', $user->id);
        if ($user->organization_id !== null) {
            $memberQuery->where('organization_id', $user->organization_id);
        }
        $memberGroups = $memberQuery->withCount('activeMembers')->orderBy('created_at', 'desc')->limit(3)->get();

        return [
            'owned' => $ownedGroups,
            'member' => $memberGroups,
        ];
    }

    /**
     * Obtener compartires recientes.
     */
    private function getRecentShares($user)
    {
        $sharedWithMeQuery = ItemShareUser::where('user_id', $user->id)
            ->with(['vaultItem.owner', 'sharedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5);
        if ($user->organization_id !== null) {
            $sharedWithMeQuery->whereHas('vaultItem', fn ($q) => $q->where('organization_id', $user->organization_id));
        }
        $sharedWithMe = $sharedWithMeQuery->get();

        $sharedByMeQuery = ItemShareUser::where('shared_by_user_id', $user->id)
            ->with(['vaultItem', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5);
        if ($user->organization_id !== null) {
            $sharedByMeQuery->whereHas('vaultItem', fn ($q) => $q->where('organization_id', $user->organization_id));
        }
        $sharedByMe = $sharedByMeQuery->get();

        return [
            'with_me' => $sharedWithMe,
            'by_me' => $sharedByMe,
        ];
    }
}
