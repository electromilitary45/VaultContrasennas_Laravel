<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\VaultItemCreated;
use App\Services\AuditService;
use Illuminate\Http\Request;

class LogVaultItemCreated
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
    public function handle(VaultItemCreated $event): void
    {
        $meta = ['folder_id' => $event->vaultItem->folder_id];
        if ($event->vaultItem->organization_id !== null) {
            $meta['organization_id'] = $event->vaultItem->organization_id;
        }
        $this->auditService->log(
            action: 'create',
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
