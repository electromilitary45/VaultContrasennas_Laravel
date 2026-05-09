<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para gestión de Carpetas
 * 
 * Contiene la lógica de negocio para operaciones CRUD de carpetas.
 */
class FolderService
{
    /**
     * Listar todas las carpetas de un usuario en estructura jerárquica.
     *
     * @param User $user
     * @return Collection<Folder>
     */
    public function listFolders(User $user): Collection
    {
        return Folder::where('owner_user_id', $user->id)
            ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
            ->with(['children', 'vaultItems'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener carpetas raíz (sin padre) de un usuario.
     *
     * @param User $user
     * @return Collection<Folder>
     */
    public function getRootFolders(User $user): Collection
    {
        return Folder::where('owner_user_id', $user->id)
            ->whereNull('group_id')
            ->whereNull('parent_id')
            ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
            ->withCount('vaultItems')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener carpetas raíz de un grupo.
     *
     * @param \App\Models\Group $group
     * @return Collection<Folder>
     */
    public function getGroupRootFolders(\App\Models\Group $group): Collection
    {
        return Folder::where('group_id', $group->id)
            ->whereNull('parent_id')
            ->when($group->organization_id !== null, fn ($q) => $q->where('organization_id', $group->organization_id))
            ->with(['children', 'vaultItems'])
            ->withCount('vaultItems')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener todas las carpetas de un usuario en formato plano para dropdowns.
     * Incluye la ruta completa para mostrar jerarquía.
     *
     * @param User $user
     * @param int|null $excludeId ID de carpeta a excluir (para evitar bucles al editar)
     * @param int|null $groupId Si se proporciona, solo devuelve carpetas de ese grupo
     * @return Collection<Folder>
     */
    public function getFoldersForSelect(User $user, ?int $excludeId = null, ?int $groupId = null): Collection
    {
        $query = Folder::where('owner_user_id', $user->id)
            ->when($user->organization_id !== null, fn ($q) => $q->where('organization_id', $user->organization_id))
            ->with('parent');

        if ($groupId) {
            $query->where('group_id', $groupId);
        } else {
            $query->whereNull('group_id');
        }

        $folders = $query->orderBy('name')->get();

        if ($excludeId) {
            $folders = $folders->reject(function ($folder) use ($excludeId) {
                // Excluir la carpeta y todas sus hijas
                return $folder->id === $excludeId || $this->isDescendantOf($folder, $excludeId, $folders);
            });
        }

        return $folders;
    }

    /**
     * Verificar si una carpeta es descendiente de otra.
     *
     * @param Folder $folder
     * @param int $parentId
     * @param Collection $allFolders
     * @return bool
     */
    private function isDescendantOf(Folder $folder, int $parentId, Collection $allFolders): bool
    {
        $current = $folder;
        while ($current->parent_id !== null) {
            if ($current->parent_id === $parentId) {
                return true;
            }
            $current = $allFolders->firstWhere('id', $current->parent_id);
            if (!$current) {
                break;
            }
        }
        return false;
    }

    /**
     * Crear una nueva carpeta.
     *
     * @param User $user
     * @param array $data
     * @return Folder
     * @throws \Exception
     */
    public function createFolder(User $user, array $data, ?\App\Models\Group $group = null): Folder
    {
        // Validar que no exista otra carpeta con el mismo nombre en el mismo nivel
        $query = Folder::where('owner_user_id', $user->id)
            ->where('name', $data['name'])
            ->where('parent_id', $data['parent_id'] ?? null);

        if ($group) {
            $query->where('group_id', $group->id);
        } else {
            $query->whereNull('group_id');
        }

        $existingFolder = $query->first();

        // Si es carpeta de grupo, validar que el usuario es miembro del grupo
        if ($group && !$group->hasActiveMember($user)) {
            throw new \Exception('No tienes permiso para crear carpetas en este grupo.');
        }

        if ($existingFolder) {
            throw new \Exception('Ya existe una carpeta con ese nombre en este nivel.');
        }

        // Validar profundidad máxima (5 niveles)
        if (isset($data['parent_id']) && $data['parent_id']) {
            $depth = $this->getFolderDepth($data['parent_id']);
            if ($depth >= 5) {
                throw new \Exception('No se puede crear una carpeta a más de 5 niveles de profundidad.');
            }
        }

        return Folder::create([
            'owner_user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'group_id' => $group?->id,
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);
    }

    /**
     * Actualizar una carpeta.
     *
     * @param Folder $folder
     * @param User $user
     * @param array $data
     * @return Folder
     * @throws \Exception
     */
    public function updateFolder(Folder $folder, User $user, array $data): Folder
    {
        if ($user->organization_id !== null && $folder->organization_id !== $user->organization_id) {
            throw new \Exception('No tienes permiso para editar esta carpeta.');
        }

        if ($folder->isPersonalFolder()) {
            if ($folder->owner_user_id !== $user->id) {
                throw new \Exception('No tienes permiso para editar esta carpeta.');
            }
        } else {
            if (!$folder->group->hasActiveMember($user)) {
                throw new \Exception('No tienes permiso para editar esta carpeta.');
            }
        }

        // Validar que no exista otra carpeta con el mismo nombre en el mismo nivel
        if (isset($data['name'])) {
            $parentId = $data['parent_id'] ?? $folder->parent_id;
            $query = Folder::where('owner_user_id', $user->id)
                ->where('name', $data['name'])
                ->where('parent_id', $parentId)
                ->where('id', '!=', $folder->id);

            // Si es carpeta de grupo, filtrar por group_id
            if ($folder->isGroupFolder()) {
                $query->where('group_id', $folder->group_id);
            } else {
                $query->whereNull('group_id');
            }

            $existingFolder = $query->first();

            if ($existingFolder) {
                throw new \Exception('Ya existe una carpeta con ese nombre en este nivel.');
            }
        }

        // Validar que no se mueva la carpeta a ser hija de sí misma
        if (isset($data['parent_id']) && $data['parent_id']) {
            if ($data['parent_id'] === $folder->id) {
                throw new \Exception('Una carpeta no puede ser padre de sí misma.');
            }

            $allFoldersQuery = Folder::where('owner_user_id', $user->id);
            if ($user->organization_id !== null) {
                $allFoldersQuery->where('organization_id', $user->organization_id);
            }
            $allFolders = $allFoldersQuery->get();
            if ($this->isDescendantOf($folder, $data['parent_id'], $allFolders)) {
                throw new \Exception('No se puede mover una carpeta dentro de sus propias subcarpetas.');
            }

            // Validar profundidad máxima
            $depth = $this->getFolderDepth($data['parent_id']);
            if ($depth >= 5) {
                throw new \Exception('No se puede mover la carpeta a más de 5 niveles de profundidad.');
            }
        }

        $folder->update($data);

        return $folder->fresh();
    }

    /**
     * Eliminar una carpeta.
     * Los items dentro de la carpeta NO se eliminan, solo se les quita la referencia a la carpeta.
     *
     * @param Folder $folder
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function deleteFolder(Folder $folder, User $user): bool
    {
        if ($user->organization_id !== null && $folder->organization_id !== $user->organization_id) {
            throw new \Exception('No tienes permiso para eliminar esta carpeta.');
        }
        if ($folder->owner_user_id !== $user->id) {
            throw new \Exception('No tienes permiso para eliminar esta carpeta.');
        }

        try {
            DB::beginTransaction();

            // Mover items a "sin carpeta" (null)
            $folder->vaultItems()->update(['folder_id' => null]);

            // Mover subcarpetas a raíz (parent_id = null)
            $folder->children()->update(['parent_id' => null]);

            // Eliminar la carpeta
            $folder->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener la profundidad de una carpeta (cuántos niveles tiene arriba).
     *
     * @param int $folderId
     * @return int
     */
    private function getFolderDepth(int $folderId): int
    {
        $depth = 0;
        $folder = Folder::find($folderId);

        while ($folder && $folder->parent_id !== null) {
            $depth++;
            $folder = Folder::find($folder->parent_id);
        }

        return $depth;
    }

    /**
     * Obtener el conteo de items en una carpeta (incluyendo subcarpetas).
     *
     * @param Folder $folder
     * @return int
     */
    public function getItemCount(Folder $folder): int
    {
        $count = $folder->vaultItems()->count();

        foreach ($folder->children as $child) {
            $count += $this->getItemCount($child);
        }

        return $count;
    }
}
