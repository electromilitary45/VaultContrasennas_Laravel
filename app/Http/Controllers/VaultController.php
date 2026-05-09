<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\VaultItemCreated;
use App\Events\VaultItemDeleted;
use App\Events\VaultItemShared;
use App\Events\VaultItemUnshared;
use App\Events\VaultItemUpdated;
use App\Events\VaultItemViewed;
use App\Http\Requests\ShareVaultItemRequest;
use App\Http\Requests\StoreVaultItemRequest;
use App\Http\Requests\UpdateShareRequest;
use App\Http\Requests\UpdateVaultItemRequest;
use App\Models\Group;
use App\Models\ItemShareGroup;
use App\Models\ItemShareUser;
use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Support\Facades\Log;
use App\Services\CryptoService;
use App\Services\EnvFileParser;
use App\Services\FolderService;
use App\Services\ShareService;
use App\Services\TotpService;
use App\Services\VaultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Contracts\Encryption\DecryptException;

class VaultController extends Controller
{
    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(
        private VaultService $vaultService,
        private TotpService $totpService,
        private CryptoService $cryptoService,
        private ShareService $shareService,
        private FolderService $folderService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = [
            'type' => $request->input('type'),
            'favorite' => $request->boolean('favorite'),
            'search' => $request->input('search'),
            'folder_id' => $request->input('folder_id'),
        ];

        $items = $this->vaultService->listItems(Auth::user(), $filters);

        // Si es una petición AJAX, devolver solo la sección de items
        // Agregar información de TOTP y permiso efectivo a cada item para la vista
        $items->getCollection()->transform(function ($item) {
            $item->has_totp = $this->vaultService->hasTotp($item);
            $item->user_permission = $item->getEffectivePermission(Auth::user());
            return $item;
        });

        if ($request->ajax()) {
            return view('vault.partials.items-list', [
                'items' => $items,
            ]);
        }

        // Obtener carpetas del usuario para el sidebar
        $folders = $this->folderService->getRootFolders(Auth::user());
        $allFolders = $this->folderService->getFoldersForSelect(Auth::user());
        
        // Contar items sin carpeta
        $unassignedCount = $this->vaultService->listItems(Auth::user(), ['folder_id' => 'null'])->total();

        return view('vault.index', [
            'items' => $items,
            'folders' => $folders,
            'allFolders' => $allFolders,
            'unassignedCount' => $unassignedCount,
            'activeFolderId' => $request->input('folder_id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', VaultItem::class);
        
        // Obtener carpetas del usuario para el selector
        $folders = $this->folderService->getFoldersForSelect(Auth::user());
        
        return view('vault.create', [
            'folders' => $folders,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVaultItemRequest $request): RedirectResponse
    {
        $this->authorize('create', VaultItem::class);
        
        $user = Auth::user();
        
        try {
            $item = $this->vaultService->createItem(
                $user,
                $request->validated()
            );

            // Registrar en logs de auditoría
            Log::info('Vault item created', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'item_id' => $item->id,
                'item_title' => $item->title,
                'item_type' => $item->type,
                'folder_id' => $item->folder_id,
                'timestamp' => now(),
            ]);

            return redirect()
                ->route('vault.show', $item)
                ->with('success', 'Item creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si es un error de validación, Laravel lo maneja automáticamente
            throw $e;
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error al crear item del vault: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VaultItem $vault): View
    {
        $this->authorize('view', $vault);
        
        try {
            $item = $this->vaultService->findItem($vault->id, Auth::user());
            
            // Obtener datos del secreto para TOTP
            $totpCode = null;
            $totpRemainingTime = null;
            $totpUri = null;
            
            if ($item->secret) {
                // Descifrar el secreto
                try {
                    $secretData = $this->cryptoService->decrypt($item->secret->ciphertext, $item->secret->crypto_version);
                    if (!is_array($secretData)) {
                        $secretData = [];
                    }
                } catch (DecryptException $e) {
                    // Si falla el descifrado, usar array vacío
                    $secretData = [];
                }
                
                if (isset($secretData['totp_secret']) && !empty($secretData['totp_secret'])) {
                    try {
                        $totpCode = $this->totpService->generateCode($secretData['totp_secret']);
                        $totpRemainingTime = $this->totpService->getRemainingTime();
                        
                        // Generar URI para QR (opcional)
                        $label = $item->title . ':' . ($secretData['username'] ?? '');
                        $issuer = config('app.name');
                        $totpUri = $this->totpService->generateUri($secretData['totp_secret'], $label, $issuer);
                    } catch (\Exception $e) {
                        // Si hay error generando TOTP, simplemente no mostrar
                    }
                }
            }

            // Descifrar datos del secreto para la vista (cualquier usuario con acceso: owner, admin, edit o view)
            $secretData = [];
            if ($item->secret) {
                try {
                    $decrypted = $this->cryptoService->decrypt($item->secret->ciphertext, $item->secret->crypto_version);
                    $secretData = is_array($decrypted) ? $decrypted : [];
                } catch (DecryptException $e) {
                    // Si falla el descifrado, usar array vacío
                    $secretData = [];
                }
            }

            // Disparar evento de visualización
            event(new VaultItemViewed($item));

            // Obtener permiso efectivo del usuario sobre el item
            $userPermission = $item->getEffectivePermission(Auth::user());

            // Para env_file: quien tenga acceso (owner, admin, edit, view) ve el contenido completo
            $envLines = [];
            if ($item->type === 'env_file' && isset($secretData['lines'])) {
                $envLines = $secretData['lines'];
            }

            // Obtener compartires actuales (solo si es propietario o admin)
            $userShares = collect();
            $groupShares = collect();
            $availableUsers = collect();
            $availableGroups = collect();

            if (in_array($userPermission, ['owner', 'admin'])) {
                $userShares = $this->shareService->getUserShares($item);
                $groupShares = $this->shareService->getGroupShares($item);
                
                $availableUsersQuery = User::where('id', '!=', Auth::id());
                if (Auth::user()->organization_id !== null) {
                    $availableUsersQuery->where('organization_id', Auth::user()->organization_id);
                }
                $availableUsers = $availableUsersQuery->orderBy('name')->get(['id', 'name', 'email']);

                $availableGroupsQuery = Group::where(function ($query) {
                    $query->where('owner_user_id', Auth::id())
                        ->orWhereHas('members', fn ($q) => $q->where('user_id', Auth::id())->where('status', 'active'));
                });
                if (Auth::user()->organization_id !== null) {
                    $availableGroupsQuery->where('organization_id', Auth::user()->organization_id);
                }
                $availableGroups = $availableGroupsQuery->orderBy('name')->get(['id', 'name', 'description']);
            }

            return view('vault.show', [
                'item' => $item,
                'secretData' => $secretData,
                'totpCode' => $totpCode,
                'totpRemainingTime' => $totpRemainingTime,
                'totpUri' => $totpUri,
                'envLines' => $envLines ?? [],
                'userShares' => $userShares,
                'groupShares' => $groupShares,
                'availableUsers' => $availableUsers,
                'availableGroups' => $availableGroups,
                'userPermission' => $userPermission, // 'owner', 'admin', 'edit', 'view'
            ]);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VaultItem $vault): View
    {
        $this->authorize('update', $vault);
        
        try {
            $item = $this->vaultService->findItem($vault->id, Auth::user());
            
            // Obtener permiso efectivo del usuario sobre el item
            $userPermission = $item->getEffectivePermission(Auth::user());
            
            // Obtener carpetas del usuario para el selector
            $folders = $this->folderService->getFoldersForSelect(Auth::user());
            
            // Descifrar datos del secreto para el formulario
            $secretData = [];
            if ($item->secret) {
                try {
                    $decrypted = $this->cryptoService->decrypt($item->secret->ciphertext, $item->secret->crypto_version);
                    $secretData = is_array($decrypted) ? $decrypted : [];
                } catch (DecryptException $e) {
                    // Si falla el descifrado, usar array vacío
                    $secretData = [];
                }
            }

            return view('vault.edit', [
                'item' => $item,
                'secretData' => $secretData,
                'userPermission' => $userPermission,
                'folders' => $folders,
            ]);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVaultItemRequest $request, VaultItem $vault): RedirectResponse
    {
        $this->authorize('update', $vault);
        
        try {
            // Capturar cambios antes de actualizar
            $originalData = [
                'title' => $vault->title,
                'type' => $vault->type,
                'favorite' => $vault->favorite,
                'folder_id' => $vault->folder_id,
                'status' => $vault->status,
            ];
            
            $item = $this->vaultService->updateItem(
                $vault,
                Auth::user(),
                $request->validated()
            );
            
            // Refrescar el modelo para obtener los nuevos valores
            $item->refresh();
            
            // Calcular cambios
            $changes = [];
            foreach ($originalData as $key => $originalValue) {
                $newValue = $item->$key;
                if ($originalValue != $newValue) {
                    $changes[$key] = [
                        'from' => $originalValue,
                        'to' => $newValue,
                    ];
                }
            }

            // Registrar en logs de auditoría
            Log::info('Vault item updated', [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email,
                'item_id' => $item->id,
                'item_title' => $item->title,
                'changes' => $changes,
                'timestamp' => now(),
            ]);

            // Disparar evento de actualización
            event(new VaultItemUpdated($item, $changes));

            return redirect()
                ->route('vault.show', $item)
                ->with('success', 'Item actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VaultItem $vault): RedirectResponse
    {
        $this->authorize('delete', $vault);
        
        $user = Auth::user();
        
        // Guardar información para logging antes de eliminar
        $itemId = $vault->id;
        $itemTitle = $vault->title;
        $itemType = $vault->type;
        
        try {
            // Disparar evento antes de eliminar (para tener acceso al modelo completo)
            event(new VaultItemDeleted($vault));
            
            $this->vaultService->deleteItem($vault, $user);

            // Registrar en logs de auditoría
            Log::info('Vault item deleted', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'item_id' => $itemId,
                'item_title' => $itemTitle,
                'item_type' => $itemType,
                'timestamp' => now(),
            ]);

        return redirect()
            ->route('vault.index')
            ->with('success', 'Item eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar el item: ' . $e->getMessage());
        }
    }

    /**
     * Generar código TOTP vía AJAX.
     */
    public function generateTotp(Request $request)
    {
        $request->validate([
            'secret' => ['required', 'string'],
        ]);

        try {
            $code = $this->totpService->generateCode($request->secret);
            $remainingTime = $this->totpService->getRemainingTime();

            return response()->json([
                'code' => $code,
                'remaining_time' => $remainingTime,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar código TOTP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar secreto TOTP aleatorio vía AJAX.
     */
    public function generateTotpSecret()
    {
        try {
            $secret = $this->totpService->generateSecret();

            return response()->json([
                'secret' => $secret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar secreto TOTP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compartir un item con un usuario o grupo.
     */
    public function share(ShareVaultItemRequest $request, VaultItem $vault): RedirectResponse
    {
        $this->authorize('update', $vault);

        try {
            $validated = $request->validated();
            $shareType = $validated['share_type'];
            $permission = $validated['permission'];

            $owner = Auth::user();
            
            if ($shareType === 'user') {
                $user = User::findOrFail($validated['user_id']);
                if ($owner->organization_id !== null && $user->organization_id !== $owner->organization_id) {
                    throw new \Exception('No puedes compartir con un usuario de otra organización.');
                }
                $this->shareService->shareWithUser($vault, $owner, $user, $permission);
                
                // Registrar en logs de auditoría
                Log::info('Vault item shared with user', [
                    'owner_id' => $owner->id,
                    'owner_email' => $owner->email,
                    'item_id' => $vault->id,
                    'item_title' => $vault->title,
                    'shared_with_user_id' => $user->id,
                    'shared_with_user_email' => $user->email,
                    'permission' => $permission,
                    'timestamp' => now(),
                ]);
                
                // Disparar evento de compartición
                event(new VaultItemShared($vault, 'user', $user->id, $permission));
                
                $message = "Item compartido con {$user->name} exitosamente.";
            } else {
                $group = Group::findOrFail($validated['group_id']);
                if ($owner->organization_id !== null && $group->organization_id !== $owner->organization_id) {
                    throw new \Exception('No puedes compartir con un grupo de otra organización.');
                }
                $this->shareService->shareWithGroup($vault, $owner, $group, $permission);
                
                // Registrar en logs de auditoría
                Log::info('Vault item shared with group', [
                    'owner_id' => $owner->id,
                    'owner_email' => $owner->email,
                    'item_id' => $vault->id,
                    'item_title' => $vault->title,
                    'shared_with_group_id' => $group->id,
                    'shared_with_group_name' => $group->name,
                    'permission' => $permission,
                    'timestamp' => now(),
                ]);
                
                // Disparar evento de compartición
                event(new VaultItemShared($vault, 'group', $group->id, $permission));
                
                $message = "Item compartido con el grupo {$group->name} exitosamente.";
            }

            return redirect()
                ->route('vault.show', $vault)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al compartir el item: ' . $e->getMessage());
        }
    }

    /**
     * Revocar acceso compartido de un usuario o grupo.
     */
    public function revokeShare(Request $request, VaultItem $vault): RedirectResponse
    {
        $this->authorize('update', $vault);

        $request->validate([
            'share_type' => ['required', 'string', 'in:user,group'],
            'share_id' => ['required', 'integer'],
        ]);

        try {
            $shareType = $request->input('share_type');
            $shareId = $request->input('share_id');
            $owner = Auth::user();

            if ($shareType === 'user') {
                $share = ItemShareUser::findOrFail($shareId);
                if ($share->vault_item_id !== $vault->id) {
                    throw new \Exception('El compartir no pertenece a este item.');
                }
                $user = $share->user;
                $permission = $share->permission;
                
                $this->shareService->revokeUserShare($vault, $owner, $user);
                
                // Registrar en logs de auditoría
                Log::info('Vault item share revoked from user', [
                    'owner_id' => $owner->id,
                    'owner_email' => $owner->email,
                    'item_id' => $vault->id,
                    'item_title' => $vault->title,
                    'revoked_from_user_id' => $user->id,
                    'revoked_from_user_email' => $user->email,
                    'permission' => $permission,
                    'timestamp' => now(),
                ]);
                
                // Disparar evento de revocación
                event(new VaultItemUnshared($vault, 'user', $user->id));
                
                $message = "Acceso de {$user->name} revocado exitosamente.";
            } else {
                $share = ItemShareGroup::findOrFail($shareId);
                if ($share->vault_item_id !== $vault->id) {
                    throw new \Exception('El compartir no pertenece a este item.');
                }
                $group = $share->group;
                $permission = $share->permission;
                
                $this->shareService->revokeGroupShare($vault, $owner, $group);
                
                // Registrar en logs de auditoría
                Log::info('Vault item share revoked from group', [
                    'owner_id' => $owner->id,
                    'owner_email' => $owner->email,
                    'item_id' => $vault->id,
                    'item_title' => $vault->title,
                    'revoked_from_group_id' => $group->id,
                    'revoked_from_group_name' => $group->name,
                    'permission' => $permission,
                    'timestamp' => now(),
                ]);
                
                // Disparar evento de revocación
                event(new VaultItemUnshared($vault, 'group', $group->id));
                
                $message = "Acceso del grupo {$group->name} revocado exitosamente.";
            }

            return redirect()
                ->route('vault.show', $vault)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al revocar el acceso: ' . $e->getMessage());
        }
    }

    /**
     * Listar todos los compartires de un item.
     */
    public function listShares(VaultItem $vault): View
    {
        $this->authorize('view', $vault);

        $userShares = $this->shareService->getUserShares($vault);
        $groupShares = $this->shareService->getGroupShares($vault);

        return view('vault.shares', [
            'item' => $vault,
            'userShares' => $userShares,
            'groupShares' => $groupShares,
        ]);
    }

    /**
     * Actualizar permiso de un compartir.
     */
    public function updateSharePermission(
        UpdateShareRequest $request,
        VaultItem $vault
    ): RedirectResponse {
        $this->authorize('update', $vault);

        $request->validate([
            'share_type' => ['required', 'string', 'in:user,group'],
            'share_id' => ['required', 'integer'],
        ]);

        try {
            $shareType = $request->input('share_type');
            $shareId = $request->input('share_id');
            $permission = $request->validated()['permission'];

            if ($shareType === 'user') {
                $share = ItemShareUser::findOrFail($shareId);
                if ($share->vault_item_id !== $vault->id) {
                    throw new \Exception('El compartir no pertenece a este item.');
                }
                $user = $share->user;
                $this->shareService->updateUserSharePermission($vault, Auth::user(), $user, $permission);
                $message = "Permiso de {$user->name} actualizado exitosamente.";
            } else {
                $share = ItemShareGroup::findOrFail($shareId);
                if ($share->vault_item_id !== $vault->id) {
                    throw new \Exception('El compartir no pertenece a este item.');
                }
                $group = $share->group;
                $this->shareService->updateGroupSharePermission($vault, Auth::user(), $group, $permission);
                $message = "Permiso del grupo {$group->name} actualizado exitosamente.";
            }

            return redirect()
                ->route('vault.show', $vault)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al actualizar el permiso: ' . $e->getMessage());
        }
    }
}
