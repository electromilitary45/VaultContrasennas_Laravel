<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\ItemShareGroup;
use App\Models\ItemShareUser;
use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para gestión de compartición de Vault Items
 * 
 * Contiene la lógica de negocio para compartir items con usuarios y grupos.
 */
class ShareService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }
    /**
     * Compartir un item con un usuario específico.
     *
     * @param VaultItem $item
     * @param User $sharedBy Usuario que realiza el compartir
     * @param User $user Usuario con quien se comparte
     * @param string $permission Permiso: 'view', 'edit', 'admin'
     * @return ItemShareUser
     * @throws \Exception
     */
    public function shareWithUser(
        VaultItem $item,
        User $sharedBy,
        User $user,
        string $permission = 'view'
    ): ItemShareUser {
        // Validar que el usuario que comparte tiene permisos
        if ($item->owner_user_id !== $sharedBy->id) {
            throw new \Exception('Solo el propietario puede compartir el item.');
        }

        // Validar que no se comparte consigo mismo
        if ($item->owner_user_id === $user->id) {
            throw new \Exception('No puedes compartir un item contigo mismo.');
        }

        // Validar permiso
        if (!in_array($permission, ['view', 'edit', 'admin'])) {
            throw new \Exception('Permiso inválido. Debe ser: view, edit o admin.');
        }

        // Verificar si ya existe un compartir
        $existingShare = ItemShareUser::where('vault_item_id', $item->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingShare) {
            // Actualizar permiso existente
            $existingShare->update([
                'permission' => $permission,
                'shared_by_user_id' => $sharedBy->id,
            ]);


            return $existingShare;
        }

        // Crear nuevo compartir
        $share = ItemShareUser::create([
            'vault_item_id' => $item->id,
            'user_id' => $user->id,
            'permission' => $permission,
            'shared_by_user_id' => $sharedBy->id,
        ]);

        // Crear notificación para el usuario con quien se comparte
        $this->notificationService->create([
            'type' => 'item_shared_user',
            'title' => 'Item compartido contigo',
            'message' => "{$sharedBy->name} ha compartido el item \"{$item->title}\" contigo",
            'data' => [
                'vault_item_id' => $item->id,
                'vault_item_title' => $item->title,
                'vault_item_type' => $item->type,
                'shared_by_user_id' => $sharedBy->id,
                'shared_by_user_name' => $sharedBy->name,
                'permission' => $permission,
            ],
            'action_url' => route('vault.show', $item),
            'action_label' => 'Ver item',
        ], $user, $user->organization_id);

        return $share;
    }

    /**
     * Compartir un item con un grupo.
     *
     * @param VaultItem $item
     * @param User $sharedBy Usuario que realiza el compartir
     * @param Group $group Grupo con el que se comparte
     * @param string $permission Permiso: 'view', 'edit', 'admin'
     * @return ItemShareGroup
     * @throws \Exception
     */
    public function shareWithGroup(
        VaultItem $item,
        User $sharedBy,
        Group $group,
        string $permission = 'view'
    ): ItemShareGroup {
        // Validar que el usuario que comparte tiene permisos
        if ($item->owner_user_id !== $sharedBy->id) {
            throw new \Exception('Solo el propietario puede compartir el item.');
        }

        // Validar permiso
        if (!in_array($permission, ['view', 'edit', 'admin'])) {
            throw new \Exception('Permiso inválido. Debe ser: view, edit o admin.');
        }

        // Verificar si ya existe un compartir
        $existingShare = ItemShareGroup::where('vault_item_id', $item->id)
            ->where('group_id', $group->id)
            ->first();

        if ($existingShare) {
            // Actualizar permiso existente
            $existingShare->update([
                'permission' => $permission,
                'shared_by_user_id' => $sharedBy->id,
            ]);


            return $existingShare;
        }

        // Crear nuevo compartir
        $share = ItemShareGroup::create([
            'vault_item_id' => $item->id,
            'group_id' => $group->id,
            'permission' => $permission,
            'shared_by_user_id' => $sharedBy->id,
        ]);

        // Obtener todos los miembros activos del grupo (excepto el que comparte)
        $groupMembers = $group->members()
            ->where('status', 'active')
            ->where('user_id', '!=', $sharedBy->id)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        // Crear notificaciones para todos los miembros activos del grupo
        foreach ($groupMembers as $member) {
            $this->notificationService->create([
                'type' => 'item_shared_group',
                'title' => 'Item compartido en grupo',
                'message' => "{$sharedBy->name} ha compartido el item \"{$item->title}\" en el grupo \"{$group->name}\"",
                'data' => [
                    'vault_item_id' => $item->id,
                    'vault_item_title' => $item->title,
                    'vault_item_type' => $item->type,
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'shared_by_user_id' => $sharedBy->id,
                    'shared_by_user_name' => $sharedBy->name,
                    'permission' => $permission,
                ],
                'action_url' => route('vault.show', $item),
                'action_label' => 'Ver item',
            ], $member, $member->organization_id);
        }

        return $share;
    }

    /**
     * Revocar acceso compartido de un usuario.
     *
     * @param VaultItem $item
     * @param User $revokedBy Usuario que revoca el acceso
     * @param User $user Usuario al que se revoca el acceso
     * @return bool
     * @throws \Exception
     */
    public function revokeUserShare(
        VaultItem $item,
        User $revokedBy,
        User $user
    ): bool {
        // Validar que el usuario que revoca tiene permisos
        if ($item->owner_user_id !== $revokedBy->id) {
            throw new \Exception('Solo el propietario puede revocar el acceso.');
        }

        $deleted = ItemShareUser::where('vault_item_id', $item->id)
            ->where('user_id', $user->id)
            ->delete();

        // Acceso revocado

        return $deleted > 0;
    }

    /**
     * Revocar acceso compartido de un grupo.
     *
     * @param VaultItem $item
     * @param User $revokedBy Usuario que revoca el acceso
     * @param Group $group Grupo al que se revoca el acceso
     * @return bool
     * @throws \Exception
     */
    public function revokeGroupShare(
        VaultItem $item,
        User $revokedBy,
        Group $group
    ): bool {
        // Validar que el usuario que revoca tiene permisos
        if ($item->owner_user_id !== $revokedBy->id) {
            throw new \Exception('Solo el propietario puede revocar el acceso.');
        }

        $deleted = ItemShareGroup::where('vault_item_id', $item->id)
            ->where('group_id', $group->id)
            ->delete();

        // Acceso revocado

        return $deleted > 0;
    }

    /**
     * Obtener todos los compartires de usuarios para un item.
     *
     * @param VaultItem $item
     * @return Collection<ItemShareUser>
     */
    public function getUserShares(VaultItem $item): Collection
    {
        return ItemShareUser::where('vault_item_id', $item->id)
            ->with(['user', 'sharedBy'])
            ->get();
    }

    /**
     * Obtener todos los compartires de grupos para un item.
     *
     * @param VaultItem $item
     * @return Collection<ItemShareGroup>
     */
    public function getGroupShares(VaultItem $item): Collection
    {
        return ItemShareGroup::where('vault_item_id', $item->id)
            ->with(['group', 'sharedBy'])
            ->get();
    }

    /**
     * Obtener todos los usuarios que tienen acceso a un item (incluyendo propietario).
     *
     * @param VaultItem $item
     * @return Collection<User>
     */
    public function getAllUsersWithAccess(VaultItem $item): Collection
    {
        $users = collect();

        // Agregar propietario
        $users->push($item->owner);

        // Agregar usuarios con share directo
        $sharedUsers = ItemShareUser::where('vault_item_id', $item->id)
            ->with('user')
            ->get()
            ->pluck('user');

        $users = $users->merge($sharedUsers);

        // Agregar usuarios de grupos con share
        $sharedGroups = ItemShareGroup::where('vault_item_id', $item->id)
            ->with('group.members.user')
            ->get();

        foreach ($sharedGroups as $shareGroup) {
            $groupMembers = $shareGroup->group->activeMembers()
                ->with('user')
                ->get()
                ->pluck('user');

            $users = $users->merge($groupMembers);
        }

        // Eliminar duplicados y retornar
        return $users->unique('id')->values();
    }

    /**
     * Actualizar permiso de un compartir con usuario.
     *
     * @param VaultItem $item
     * @param User $updatedBy Usuario que actualiza
     * @param User $user Usuario cuyo permiso se actualiza
     * @param string $permission Nuevo permiso: 'view', 'edit', 'admin'
     * @return ItemShareUser
     * @throws \Exception
     */
    public function updateUserSharePermission(
        VaultItem $item,
        User $updatedBy,
        User $user,
        string $permission
    ): ItemShareUser {
        // Validar que el usuario que actualiza tiene permisos
        if ($item->owner_user_id !== $updatedBy->id) {
            throw new \Exception('Solo el propietario puede actualizar permisos.');
        }

        // Validar permiso
        if (!in_array($permission, ['view', 'edit', 'admin'])) {
            throw new \Exception('Permiso inválido. Debe ser: view, edit o admin.');
        }

        $share = ItemShareUser::where('vault_item_id', $item->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $share->update([
            'permission' => $permission,
            'shared_by_user_id' => $updatedBy->id,
        ]);


        return $share;
    }

    /**
     * Actualizar permiso de un compartir con grupo.
     *
     * @param VaultItem $item
     * @param User $updatedBy Usuario que actualiza
     * @param Group $group Grupo cuyo permiso se actualiza
     * @param string $permission Nuevo permiso: 'view', 'edit', 'admin'
     * @return ItemShareGroup
     * @throws \Exception
     */
    public function updateGroupSharePermission(
        VaultItem $item,
        User $updatedBy,
        Group $group,
        string $permission
    ): ItemShareGroup {
        // Validar que el usuario que actualiza tiene permisos
        if ($item->owner_user_id !== $updatedBy->id) {
            throw new \Exception('Solo el propietario puede actualizar permisos.');
        }

        // Validar permiso
        if (!in_array($permission, ['view', 'edit', 'admin'])) {
            throw new \Exception('Permiso inválido. Debe ser: view, edit o admin.');
        }

        $share = ItemShareGroup::where('vault_item_id', $item->id)
            ->where('group_id', $group->id)
            ->firstOrFail();

        $share->update([
            'permission' => $permission,
            'shared_by_user_id' => $updatedBy->id,
        ]);


        return $share;
    }
}
