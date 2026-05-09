<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controlador para gestión de vault items desde el panel de administración
 * 
 * Los administradores pueden ver metadatos de todos los items pero NO los secretos
 */
class AdminVaultController extends Controller
{
    /**
     * Listar todos los vault items (solo metadatos, sin secretos)
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = VaultItem::with(['owner', 'folder'])
            ->where('status', '!=', 'deleted');

        $baseQuery = VaultItem::where('status', '!=', 'deleted');

        if ($request->user()->isOrgAdmin()) {
            $orgId = $request->user()->organization_id;
            $query->where('organization_id', $orgId);
            $baseQuery->where('organization_id', $orgId);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_user_id', $request->input('owner_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                      $ownerQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $items = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'by_type' => [
                'auth' => (clone $baseQuery)->where('type', 'auth')->count(),
                'card' => (clone $baseQuery)->where('type', 'card')->count(),
                'note' => (clone $baseQuery)->where('type', 'note')->count(),
                'api_key' => (clone $baseQuery)->where('type', 'api_key')->count(),
                'ssh_key' => (clone $baseQuery)->where('type', 'ssh_key')->count(),
                'env_file' => (clone $baseQuery)->where('type', 'env_file')->count(),
            ],
            'favorites' => (clone $baseQuery)->where('favorite', true)->count(),
            'with_totp' => (clone $baseQuery)->where('type', 'auth')->whereHas('secret')->count(),
        ];

        $usersQuery = User::orderBy('name');
        if ($request->user()->isOrgAdmin()) {
            $usersQuery->where('organization_id', $request->user()->organization_id);
        }
        $users = $usersQuery->get(['id', 'name', 'email']);

        return view('admin.vault.index', [
            'items' => $items,
            'stats' => $stats,
            'users' => $users,
            'filters' => $request->only(['type', 'owner_id', 'search', 'sort_by', 'sort_dir']),
        ]);
    }

    /**
     * Ver detalle de un vault item (solo metadatos, sin secretos)
     *
     * @param VaultItem $vaultItem
     * @return View
     */
    public function show(VaultItem $vaultItem): View
    {
        $this->ensureVaultItemInOrg($vaultItem);

        $vaultItem->load(['owner', 'folder', 'secret']);

        // Estadísticas del item
        $itemStats = [
            'shares_users' => $vaultItem->sharesWithUsers()->count(),
            'shares_groups' => $vaultItem->sharesWithGroups()->count(),
            'versions' => $vaultItem->versions()->count(),
        ];

        // Comparticiones
        $userShares = $vaultItem->sharesWithUsers()->with('user')->get();
        $groupShares = $vaultItem->sharesWithGroups()->with('group')->get();

        return view('admin.vault.show', [
            'item' => $vaultItem,
            'stats' => $itemStats,
            'userShares' => $userShares,
            'groupShares' => $groupShares,
        ]);
    }

    /**
     * Eliminar vault item desde administración
     *
     * @param VaultItem $vaultItem
     * @return RedirectResponse
     */
    public function destroy(VaultItem $vaultItem): RedirectResponse
    {
        $this->ensureVaultItemInOrg($vaultItem);

        $admin = auth()->user();

        // Guardar información para logging
        $itemTitle = $vaultItem->title;
        $itemType = $vaultItem->type;
        $ownerId = $vaultItem->owner_user_id;
        $ownerEmail = $vaultItem->owner->email ?? 'N/A';

        // Marcar como eliminado (soft delete)
        $vaultItem->status = 'deleted';
        $vaultItem->save();

        // Registrar en logs de auditoría
        Log::info('Vault item deleted by admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'item_id' => $vaultItem->id,
            'item_title' => $itemTitle,
            'item_type' => $itemType,
            'owner_id' => $ownerId,
            'owner_email' => $ownerEmail,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.vault.index')
            ->with('success', 'Item del vault eliminado exitosamente.');
    }

    private function ensureVaultItemInOrg(VaultItem $vaultItem): void
    {
        $admin = request()->user();
        if (! $admin->isOrgAdmin()) {
            return;
        }
        if ($vaultItem->organization_id !== $admin->organization_id) {
            abort(403, 'No puedes gestionar items de otra organización.');
        }
    }
}
