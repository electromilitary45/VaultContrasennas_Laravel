<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\VaultItemDeleted;
use App\Services\AuditService;

class LogVaultItemDeleted
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
    public function handle(VaultItemDeleted $event): void
    {
        $meta = ['status' => $event->vaultItem->status];
        if ($event->vaultItem->organization_id !== null) {
            $meta['organization_id'] = $event->vaultItem->organization_id;
        }
        $this->auditService->log(
            action: 'delete',
            modelType: $event->vaultItem::class,
            modelId: $event->vaultItem->id,
            changes: [
                'title' => $event->vaultItem->title,
                'type' => $event->vaultItem->type,
            ],
            request: request(),
            meta: $meta
        );
    }
}
