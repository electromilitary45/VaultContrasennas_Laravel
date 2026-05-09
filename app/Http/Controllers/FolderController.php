<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FolderController extends Controller
{
    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(
        private FolderService $folderService
    ) {}

    /**
     * Obtener todas las carpetas del usuario (para AJAX).
     */
    public function index(): JsonResponse
    {
        $folders = $this->folderService->getRootFolders(Auth::user());
        
        return response()->json([
            'folders' => $folders->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'path' => $folder->getFullPath(),
                    'item_count' => $folder->vault_items_count,
                    'children' => $this->formatChildren($folder),
                ];
            }),
        ]);
    }

    /**
     * Formatear carpetas hijas recursivamente.
     */
    private function formatChildren(Folder $folder): array
    {
        return $folder->children->map(function ($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'path' => $child->getFullPath(),
                'item_count' => $child->vaultItems->count(),
                'children' => $this->formatChildren($child),
            ];
        })->toArray();
    }

    /**
     * Obtener carpetas para dropdown (formato plano con ruta completa).
     */
    public function forSelect(): JsonResponse
    {
        $folders = $this->folderService->getFoldersForSelect(Auth::user());
        
        return response()->json([
            'folders' => $folders->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'path' => $folder->getFullPath(),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFolderRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        
        try {
            $folder = $this->folderService->createFolder(
                $user,
                $request->validated()
            );

            // Registrar en logs de auditoría
            Log::info('Folder created', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'folder_id' => $folder->id,
                'folder_name' => $folder->name,
                'parent_id' => $folder->parent_id,
                'group_id' => $folder->group_id,
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
     * Update the specified resource in storage.
     */
    public function update(UpdateFolderRequest $request, Folder $folder): JsonResponse|RedirectResponse
    {
        try {
            $updatedFolder = $this->folderService->updateFolder(
                $folder,
                Auth::user(),
                $request->validated()
            );

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
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Folder $folder): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        
        // Guardar información para logging antes de eliminar
        $folderId = $folder->id;
        $folderName = $folder->name;
        $parentId = $folder->parent_id;
        $groupId = $folder->group_id;
        
        try {
            $this->folderService->deleteFolder($folder, $user);

            // Registrar en logs de auditoría
            Log::info('Folder deleted', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'folder_id' => $folderId,
                'folder_name' => $folderName,
                'parent_id' => $parentId,
                'group_id' => $groupId,
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
}
