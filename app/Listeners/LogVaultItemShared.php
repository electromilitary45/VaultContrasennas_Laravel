<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\VaultItemShared;
use App\Services\AuditService;

class LogVaultItemShared
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private AuditService $auditService
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VaultItemShared $event): void
    {
        $meta = ['title' => $event->vaultItem->title];
        if ($event->vaultItem->organization_id !== null) {
            $meta['organization_id'] = $event->vaultItem->organization_id;
        }
        $this->auditService->log(
            action: 'share',
            modelType: $event->vaultItem::class,
            modelId: $event->vaultItem->id,
            changes: [
                'shared_with_type' => $event->sharedWithType,
                'shared_with_id' => $event->sharedWithId,
                'permission' => $event->permission,
            ],
            request: request(),
            meta: $meta
        );
    }
}
