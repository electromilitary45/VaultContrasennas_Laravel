<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\VaultItemViewed;
use App\Services\AuditService;

class LogVaultItemViewed
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
    public function handle(VaultItemViewed $event): void
    {
        $meta = ['title' => $event->vaultItem->title];
        if ($event->vaultItem->organization_id !== null) {
            $meta['organization_id'] = $event->vaultItem->organization_id;
        }
        $this->auditService->log(
            action: 'view',
            modelType: $event->vaultItem::class,
            modelId: $event->vaultItem->id,
            changes: null,
            request: request(),
            meta: $meta
        );
    }
}
