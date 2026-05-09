<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Auth\Access\Response;

/**
 * Policy para VaultItem
 * 
 * Controla el acceso a los items del vault basándose en:
 * - Propietario del item
 * - Compartición directa con usuario
 * - Compartición a través de grupos
 */
class VaultItemPolicy
{
    /**
     * Determine whether the user can view any models.
     * 
     * Todos los usuarios autenticados pueden ver la lista de sus items.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * 
     * El usuario puede ver el item si:
     * - Es el propietario
     * - Tiene acceso compartido directo (view, edit, admin)
     * - Pertenece a un grupo con acceso compartido (view, edit, admin)
     * - Es admin/super_admin (solo metadatos, NO secretos descifrados)
     */
    public function view(User $user, VaultItem $vaultItem): bool
    {
        if ($user->organization_id !== null && $vaultItem->organization_id !== $user->organization_id) {
            return false;
        }
        if ($user->isPlatformSuperAdmin()) {
            return true;
        }

        if ($vaultItem->owner_user_id === $user->id) {
            return true;
        }

        // Verificar acceso compartido directo usando consulta directa
        $directShare = \App\Models\ItemShareUser::where('vault_item_id', $vaultItem->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($directShare) {
            return true; // Cualquier permiso (view, edit, admin) permite ver
        }

        // Verificar acceso compartido a través de grupos
        $userGroupIds = \App\Models\GroupMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('group_id')
            ->toArray();

        if (!empty($userGroupIds)) {
            $groupShare = \App\Models\ItemShareGroup::where('vault_item_id', $vaultItem->id)
                ->whereIn('group_id', $userGroupIds)
                ->exists();

            if ($groupShare) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * 
     * Todos los usuarios autenticados pueden crear items.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * 
     * El usuario puede actualizar el item si:
     * - Es el propietario
     * - Tiene acceso compartido con permiso 'edit' o 'admin'
     * - Pertenece a un grupo con permiso 'edit' o 'admin'
     */
    public function update(User $user, VaultItem $vaultItem): bool
    {
        if ($user->organization_id !== null && $vaultItem->organization_id !== $user->organization_id) {
            return false;
        }
        if ($user->isPlatformSuperAdmin()) {
            return true;
        }
        if ($vaultItem->owner_user_id === $user->id) {
            return true;
        }

        // Verificar acceso compartido directo con permiso de edición usando consulta directa
        $directShare = \App\Models\ItemShareUser::where('vault_item_id', $vaultItem->id)
            ->where('user_id', $user->id)
            ->whereIn('permission', ['edit', 'admin'])
            ->exists();

        if ($directShare) {
            return true;
        }

        // Verificar acceso compartido a través de grupos con permiso de edición
        $userGroupIds = \App\Models\GroupMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('group_id')
            ->toArray();

        if (!empty($userGroupIds)) {
            $groupShare = \App\Models\ItemShareGroup::where('vault_item_id', $vaultItem->id)
                ->whereIn('group_id', $userGroupIds)
                ->whereIn('permission', ['edit', 'admin'])
                ->exists();

            if ($groupShare) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Pueden eliminar:
     * - El propietario
     * - Admins (con confirmación, para moderación)
     * 
     * Los usuarios con acceso compartido NO pueden eliminar.
     */
    public function delete(User $user, VaultItem $vaultItem): bool
    {
        if ($user->organization_id !== null && $vaultItem->organization_id !== $user->organization_id) {
            return false;
        }
        if ($user->isPlatformSuperAdmin()) {
            return true;
        }
        return $vaultItem->owner_user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     * 
     * Solo el propietario puede restaurar items archivados.
     */
    public function restore(User $user, VaultItem $vaultItem): bool
    {
        if ($user->organization_id !== null && $vaultItem->organization_id !== $user->organization_id) {
            return false;
        }
        return $vaultItem->owner_user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * 
     * Solo el propietario puede eliminar permanentemente.
     */
    public function forceDelete(User $user, VaultItem $vaultItem): bool
    {
        if ($user->organization_id !== null && $vaultItem->organization_id !== $user->organization_id) {
            return false;
        }
        return $vaultItem->owner_user_id === $user->id;
    }

    /**
     * Obtener el permiso efectivo del usuario sobre el item.
     * 
     * Retorna: 'owner', 'admin', 'edit', 'view', o null si no tiene acceso.
     * 
     * @param User $user
     * @param VaultItem $vaultItem
     * @return string|null
     */
    public function getPermission(User $user, VaultItem $vaultItem): ?string
    {
        if ($user->organization_id !== null && $vaultItem->organization_id !== $user->organization_id) {
            return null;
        }
        if ($vaultItem->owner_user_id === $user->id) {
            return 'owner';
        }

        // Verificar acceso compartido directo usando consulta directa
        $directShare = \App\Models\ItemShareUser::where('vault_item_id', $vaultItem->id)
            ->where('user_id', $user->id)
            ->first();

        if ($directShare) {
            return $directShare->permission;
        }

        // Verificar acceso compartido a través de grupos
        $userGroupIds = \App\Models\GroupMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('group_id')
            ->toArray();

        if (!empty($userGroupIds)) {
            $groupShare = \App\Models\ItemShareGroup::where('vault_item_id', $vaultItem->id)
                ->whereIn('group_id', $userGroupIds)
                ->orderByRaw("FIELD(permission, 'admin', 'edit', 'view')")
                ->first();

            if ($groupShare) {
                return $groupShare->permission;
            }
        }

        return null;
    }
}
