<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\VaultItemUpdated;
use App\Services\AuditService;

class LogVaultItemUpdated
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
    public function handle(VaultItemUpdated $event): void
    {
        $meta = ['title' => $event->vaultItem->title];
        if ($event->vaultItem->organization_id !== null) {
            $meta['organization_id'] = $event->vaultItem->organization_id;
        }
        $this->auditService->log(
            action: 'update',
            modelType: $event->vaultItem::class,
            modelId: $event->vaultItem->id,
            changes: $event->changes,
            request: request(),
            meta: $meta
        );
    }
}
