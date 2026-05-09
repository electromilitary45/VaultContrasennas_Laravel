<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\VaultItemUnshared;
use App\Services\AuditService;

class LogVaultItemUnshared
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
    public function handle(VaultItemUnshared $event): void
    {
        $meta = ['title' => $event->vaultItem->title];
        if ($event->vaultItem->organization_id !== null) {
            $meta['organization_id'] = $event->vaultItem->organization_id;
        }
        $this->auditService->log(
            action: 'unshare',
            modelType: $event->vaultItem::class,
            modelId: $event->vaultItem->id,
            changes: [
                'shared_with_type' => $event->sharedWithType,
                'shared_with_id' => $event->sharedWithId,
            ],
            request: request(),
            meta: $meta
        );
    }
}
