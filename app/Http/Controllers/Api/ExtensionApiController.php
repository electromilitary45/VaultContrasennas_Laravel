<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemShareGroup;
use App\Models\ItemShareUser;
use App\Models\VaultItem;
use App\Services\CryptoService;
use App\Services\EnvFileParser;
use App\Services\VaultService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API para la extensión de navegador (Chrome).
 * Devuelve JSON; reutiliza VaultService y policies.
 * Requiere sesión web (auth + ensure-password-changed).
 */
class ExtensionApiController extends Controller
{
    public function __construct(
        private VaultService $vaultService,
        private CryptoService $cryptoService
    ) {}

    /**
     * Lista de items del vault (solo metadatos para el popup).
     * GET /api/extension/vault/items?search=&type=&favorite=&folder_id=
     */
    public function items(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'favorite' => $request->boolean('favorite'),
            'folder_id' => $request->input('folder_id'),
        ];

        $items = $this->vaultService->listItems($user, $filters, perPage: 50);
        $collection = $items->getCollection();
        $itemIds = $collection->pluck('id')->toArray();

        $sharedWithUserIds = [];
        if (! empty($itemIds)) {
            $sharedWithUserIds = ItemShareUser::where('user_id', $user->id)
                ->whereIn('vault_item_id', $itemIds)
                ->pluck('vault_item_id')
                ->toArray();
        }
        $userGroupIds = \App\Models\GroupMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('group_id')
            ->toArray();
        $sharedWithGroupIds = [];
        if (! empty($itemIds) && ! empty($userGroupIds)) {
            $sharedWithGroupIds = ItemShareGroup::whereIn('group_id', $userGroupIds)
                ->whereIn('vault_item_id', $itemIds)
                ->pluck('vault_item_id')
                ->toArray();
        }

        $data = $collection->map(function (VaultItem $item) use ($user, $sharedWithUserIds, $sharedWithGroupIds) {
            $username = null;
            if ($item->secret && $item->type === 'auth') {
                try {
                    $decrypted = $this->cryptoService->decrypt(
                        $item->secret->ciphertext,
                        $item->secret->crypto_version ?? 1
                    );
                    $username = is_array($decrypted) ? ($decrypted['username'] ?? null) : null;
                } catch (DecryptException $e) {
                    // No exponer error; solo no incluir username
                }
            }

            $shareContext = 'own';
            if ($item->owner_user_id !== $user->id) {
                $shareContext = in_array($item->id, $sharedWithUserIds, true) ? 'shared_user' : 'shared_group';
            }

            return [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'username' => $username,
                'favorite' => $item->favorite,
                'folder_id' => $item->folder_id,
                'share_context' => $shareContext,
            ];
        });

        return response()->json([
            'items' => $data,
            'total' => $items->total(),
        ]);
    }

    /**
     * Detalle de un item con secreto descifrado (para copiar / autofill).
     * GET /api/extension/vault/items/{id}
     */
    public function show(Request $request, VaultItem $item): JsonResponse
    {
        $this->authorize('view', $item);

        $secretData = [];
        if ($item->secret) {
            try {
                $decrypted = $this->cryptoService->decrypt(
                    $item->secret->ciphertext,
                    $item->secret->crypto_version ?? 1
                );
                $secretData = is_array($decrypted) ? $decrypted : [];
            } catch (DecryptException $e) {
                return response()->json(['error' => 'No se pudo descifrar el item.'], 500);
            }
        }

        // Para env_file: filtrar por visibilidad y exponer solo contenido visible
        if ($item->type === 'env_file') {
            $lines = $secretData['lines'] ?? [];
            $visibilityRules = $secretData['visibility_rules'] ?? [];
            $user = $request->user();
            $isOwner = $item->owner_user_id === $user->id;

            if ($isOwner) {
                $raw = $secretData['raw'] ?? '';
                $secretData = ['env_content' => $raw];
            } else {
                $userIds = [$user->id];
                $groupIds = \App\Models\GroupMember::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->pluck('group_id')
                    ->toArray();
                $visibleLineNumbers = app(EnvFileParser::class)->getVisibleLineNumbers(
                    $lines,
                    $visibilityRules,
                    $userIds,
                    $groupIds
                );
                $visibleSet = array_flip($visibleLineNumbers);
                $filteredLines = array_values(array_filter($lines, fn ($l) => isset($visibleSet[$l['line_number']])));
                $raw = app(EnvFileParser::class)->buildRawFromLines($lines, $visibleLineNumbers);
                $secretData = ['env_content' => $raw];
            }
        }

        // Exponer solo los campos necesarios para la extensión (autofill tipo auth, copiar)
        $payload = [
            'id' => $item->id,
            'title' => $item->title,
            'type' => $item->type,
            'secret' => $secretData,
        ];

        return response()->json($payload);
    }
}
