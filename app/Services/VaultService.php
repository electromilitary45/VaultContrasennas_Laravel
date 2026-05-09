<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\VaultItem;
use App\Models\VaultItemSecret;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Servicio para gestión de Vault Items
 * 
 * Contiene la lógica de negocio para operaciones CRUD de items del vault.
 */
class VaultService
{
    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(
        private CryptoService $cryptoService,
        private EnvFileParser $envFileParser
    ) {}

    /**
     * Listar items del vault con filtros opcionales.
     * Incluye items propios y compartidos (directos y por grupos).
     *
     * @param User $user
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listItems(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Obtener IDs de items compartidos directamente con el usuario
        $sharedItemIds = \App\Models\ItemShareUser::where('user_id', $user->id)
            ->pluck('vault_item_id')
            ->toArray();

        $userGroupIdsQuery = \App\Models\GroupMember::where('user_id', $user->id)
            ->where('status', 'active');
        if ($user->organization_id !== null) {
            $userGroupIdsQuery->whereHas('group', fn ($q) => $q->where('organization_id', $user->organization_id));
        }
        $userGroupIds = $userGroupIdsQuery->pluck('group_id')->toArray();

        // Obtener IDs de items compartidos con esos grupos
        $groupSharedItemIds = [];
        if (!empty($userGroupIds)) {
            $groupSharedItemIds = \App\Models\ItemShareGroup::whereIn('group_id', $userGroupIds)
                ->pluck('vault_item_id')
                ->toArray();
        }

        // Combinar todos los IDs de items a los que el usuario tiene acceso
        $accessibleItemIds = array_unique(array_merge(
            [$user->id], // Items propios (se filtrará por owner_user_id)
            $sharedItemIds,
            $groupSharedItemIds
        ));

        $query = VaultItem::where(function ($q) use ($user, $sharedItemIds, $groupSharedItemIds) {
                $q->where('owner_user_id', $user->id);
                if (!empty($sharedItemIds)) {
                    $q->orWhereIn('id', $sharedItemIds);
                }
                if (!empty($groupSharedItemIds)) {
                    $q->orWhereIn('id', $groupSharedItemIds);
                }
            })
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

        // Filtro por carpeta
        // IMPORTANTE: Solo filtrar por carpetas del usuario, no por carpetas de items compartidos
        // Los items compartidos pueden estar en carpetas del usuario que los recibe, pero no en las del propietario
        if (isset($filters['folder_id'])) {
            if ($filters['folder_id'] === 'null' || $filters['folder_id'] === null) {
                // Items sin carpeta
                $query->whereNull('folder_id');
            } else {
                // Items en una carpeta específica del usuario
                $folderId = (int) $filters['folder_id'];
                // Verificar que la carpeta pertenece al usuario
                $folder = \App\Models\Folder::where('id', $folderId)
                    ->where('owner_user_id', $user->id)
                    ->first();
                
                if ($folder) {
                    $query->where('folder_id', $folderId);
                }
            }
        }

        $items = $query->paginate($perPage);

        // Obtener información de compartición directa (usuario)
        $userShares = \App\Models\ItemShareUser::where('user_id', $user->id)
            ->with('sharedBy')
            ->get()
            ->keyBy('vault_item_id');

        // Obtener información de compartición por grupos
        $groupShares = [];
        if (!empty($userGroupIds)) {
            $groupSharesData = \App\Models\ItemShareGroup::whereIn('group_id', $userGroupIds)
                ->with('group', 'sharedBy')
                ->get()
                ->groupBy('vault_item_id');
            
            // Convertir a formato más accesible
            foreach ($groupSharesData as $itemId => $shares) {
                $groupShares[$itemId] = $shares;
            }
        }

        // Agregar información sobre si el item es compartido o propio
        $items->getCollection()->transform(function ($item) use ($user, $userShares, $groupShares) {
            $item->is_shared = $item->owner_user_id !== $user->id;
            $item->is_owner = $item->owner_user_id === $user->id;
            
            // Información de compartición directa
            $item->shared_by_user = null;
            if (isset($userShares[$item->id])) {
                $share = $userShares[$item->id];
                $item->shared_by_user = $share->sharedBy;
            }
            
            // Información de compartición por grupo
            $item->shared_by_groups = [];
            if (isset($groupShares[$item->id])) {
                $item->shared_by_groups = $groupShares[$item->id]->map(function ($share) {
                    return [
                        'group' => $share->group,
                        'shared_by' => $share->sharedBy,
                    ];
                })->toArray();
            }
            
            return $item;
        });

        return $items;
    }

    /**
     * Crear un nuevo item del vault.
     *
     * @param User $user
     * @param array $data
     * @return VaultItem
     * @throws \Exception
     */
    public function createItem(User $user, array $data): VaultItem
    {
        try {
            DB::beginTransaction();

            // Crear el item del vault
            $item = VaultItem::create([
                'owner_user_id' => $user->id,
                'organization_id' => $user->organization_id,
                'type' => $data['type'],
                'title' => $data['title'],
                'folder_id' => $data['folder_id'] ?? null,
                'favorite' => $data['favorite'] ?? false,
                'status' => 'active',
            ]);

            // Construir datos del secreto según el tipo
            $secretData = $this->buildSecretData($data['type'], $data);

            // Si no hay datos del secreto, usar un array vacío
            if (empty($secretData)) {
                $secretData = [];
            }

            // Cifrar el secreto antes de guardarlo
            $encrypted = $this->cryptoService->encrypt($secretData);

            // Crear el secreto cifrado
            VaultItemSecret::create([
                'vault_item_id' => $item->id,
                'ciphertext' => $encrypted['ciphertext'],
                'iv' => $encrypted['iv'],
                'salt' => $encrypted['salt'],
                'crypto_version' => $encrypted['crypto_version'],
            ]);

            DB::commit();

            return $item->fresh(['secret']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar un item del vault.
     *
     * @param VaultItem $item
     * @param User $user
     * @param array $data
     * @return VaultItem
     * @throws \Exception
     */
    public function updateItem(VaultItem $item, User $user, array $data): VaultItem
    {
        // Obtener permiso efectivo del usuario
        $userPermission = $item->getEffectivePermission($user);
        $isOwner = $userPermission === 'owner';
        $canEdit = in_array($userPermission, ['owner', 'admin', 'edit']);

        // Verificar que el usuario tiene permisos de edición
        if (!$canEdit) {
            throw new \Exception('No tienes permiso para editar este item.');
        }

        try {
            DB::beginTransaction();

            // Preparar datos de actualización
            $updateData = [
                'title' => $data['title'] ?? $item->title,
                'favorite' => isset($data['favorite']) ? (bool) $data['favorite'] : $item->favorite,
            ];

            // Solo el propietario puede cambiar tipo, folder_id y status
            if ($isOwner) {
                $updateData['type'] = $data['type'] ?? $item->type;
                $updateData['folder_id'] = $data['folder_id'] ?? $item->folder_id;
                $updateData['status'] = $data['status'] ?? $item->status;
            }

            // Actualizar el item
            $item->update($updateData);

            // Actualizar el secreto
            $secret = $item->secret;
            if ($secret) {
                // Descifrar datos existentes
                try {
                    $existingData = $this->cryptoService->decrypt($secret->ciphertext, $secret->crypto_version);
                    if (!is_array($existingData)) {
                        $existingData = [];
                    }
                } catch (DecryptException $e) {
                    // Si falla el descifrado, empezar con datos vacíos
                    $existingData = [];
                }
                
                // Construir nuevos datos del secreto
                $newSecretData = $this->buildSecretData($item->type, $data, $existingData);
                
                // Cifrar los nuevos datos
                $encrypted = $this->cryptoService->encrypt($newSecretData);
                
                // Actualizar el secreto cifrado
                $secret->update([
                    'ciphertext' => $encrypted['ciphertext'],
                    'iv' => $encrypted['iv'],
                    'salt' => $encrypted['salt'],
                    'crypto_version' => $encrypted['crypto_version'],
                ]);
            }

            DB::commit();

            return $item->fresh(['secret']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Eliminar un item del vault (soft delete lógico).
     *
     * @param VaultItem $item
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function deleteItem(VaultItem $item, User $user): bool
    {
        // Verificar que el usuario es el propietario
        if ($item->owner_user_id !== $user->id) {
            throw new \Exception('No tienes permiso para eliminar este item.');
        }

        // Soft delete lógico
        return $item->update(['status' => 'deleted']);
    }

    /**
     * Encontrar un item por ID y verificar permisos.
     *
     * @param int $id
     * @param User $user
     * @return VaultItem
     * @throws \Exception
     */
    public function findItem(int $id, User $user): VaultItem
    {
        $item = VaultItem::with(['secret', 'owner'])
            ->where('id', $id)
            ->where('status', '!=', 'deleted')
            ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
            ->firstOrFail();

        // Verificar que el usuario tiene acceso (propietario, compartido directo o por grupo)
        if ($item->owner_user_id === $user->id) {
            // Es el propietario, tiene acceso
            return $item;
        }

        // Verificar acceso compartido directo
        $hasDirectShare = \App\Models\ItemShareUser::where('vault_item_id', $item->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($hasDirectShare) {
            return $item;
        }

        // Verificar acceso compartido a través de grupos
        $userGroupIds = \App\Models\GroupMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('group_id')
            ->toArray();

        if (!empty($userGroupIds)) {
            $hasGroupShare = \App\Models\ItemShareGroup::where('vault_item_id', $item->id)
                ->whereIn('group_id', $userGroupIds)
                ->exists();

            if ($hasGroupShare) {
                return $item;
            }
        }

        // Si no tiene acceso de ninguna forma, lanzar excepción
        throw new \Exception('No tienes permiso para ver este item.');
    }

    /**
     * Construir los datos del secreto según el tipo de item.
     *
     * @param string $type
     * @param array $data
     * @param array $existingData
     * @return array
     */
    private function buildSecretData(string $type, array $data, array $existingData = []): array
    {
        $secretData = $existingData;

        switch ($type) {
            case 'auth':
                if (isset($data['username'])) {
                    $secretData['username'] = $data['username'];
                }
                if (isset($data['password'])) {
                    $secretData['password'] = $data['password'];
                }
                if (isset($data['uri'])) {
                    // Filtrar URIs vacías o nulas
                    $secretData['uri'] = array_values(array_filter($data['uri'] ?? [], function($uri) {
                        return !empty(trim($uri ?? ''));
                    }));
                }
                if (isset($data['totp_secret'])) {
                    $secretData['totp_secret'] = $data['totp_secret'];
                }
                if (isset($data['notes'])) {
                    $secretData['notes'] = $data['notes'];
                }
                break;

            case 'card':
                if (isset($data['cardholder_name'])) {
                    $secretData['cardholder_name'] = $data['cardholder_name'];
                }
                if (isset($data['card_number'])) {
                    $secretData['card_number'] = $data['card_number'];
                }
                if (isset($data['brand'])) {
                    $secretData['brand'] = $data['brand'];
                }
                if (isset($data['exp_month'])) {
                    $secretData['exp_month'] = $data['exp_month'];
                }
                if (isset($data['exp_year'])) {
                    $secretData['exp_year'] = $data['exp_year'];
                }
                if (isset($data['security_code'])) {
                    $secretData['security_code'] = $data['security_code'];
                }
                if (isset($data['notes'])) {
                    $secretData['notes'] = $data['notes'];
                }
                break;

            case 'api_key':
                if (isset($data['api_key'])) {
                    $secretData['api_key'] = $data['api_key'];
                }
                if (isset($data['host'])) {
                    $secretData['host'] = $data['host'];
                }
                if (isset($data['notes'])) {
                    $secretData['notes'] = $data['notes'];
                }
                break;

            case 'ssh_key':
                if (isset($data['public_key'])) {
                    $secretData['public_key'] = $data['public_key'];
                }
                if (isset($data['private_key'])) {
                    $secretData['private_key'] = $data['private_key'];
                }
                if (isset($data['passphrase'])) {
                    $secretData['passphrase'] = $data['passphrase'];
                }
                if (isset($data['host'])) {
                    $secretData['host'] = $data['host'];
                }
                if (isset($data['notes'])) {
                    $secretData['notes'] = $data['notes'];
                }
                break;

            case 'note':
                if (isset($data['notes'])) {
                    $secretData['notes'] = $data['notes'];
                }
                break;

            case 'env_file':
                if (isset($data['env_content'])) {
                    $raw = $data['env_content'];
                    $secretData['raw'] = $raw;
                    $secretData['lines'] = $this->envFileParser->parse($raw);
                }
                if (!isset($secretData['visibility_rules'])) {
                    $secretData['visibility_rules'] = $existingData['visibility_rules'] ?? [];
                }
                if (isset($data['visibility_rules']) && is_array($data['visibility_rules'])) {
                    $secretData['visibility_rules'] = $data['visibility_rules'];
                }
                if (isset($data['notes'])) {
                    $secretData['notes'] = $data['notes'];
                }
                break;

            default:
                if (isset($data['notes'])) {
                    $secretData['notes'] = $data['notes'];
                }
                break;
        }

        return $secretData;
    }

    /**
     * Verificar si un item tiene TOTP sin descifrar completamente.
     * 
     * Por rendimiento, esta es una verificación aproximada.
     * Para verificación precisa, se debe descifrar el secreto.
     *
     * @param VaultItem $item
     * @return bool
     */
    public function hasTotp(VaultItem $item): bool
    {
        if (!$item->secret || empty($item->secret->ciphertext)) {
            return false;
        }

        // Si el item es de tipo 'auth', es probable que tenga TOTP si tiene secreto
        // Esta es una aproximación. Para verificación precisa, se debería descifrar.
        // Una mejora futura sería agregar un campo 'has_totp' en vault_items.
        return $item->type === 'auth';
    }
}
