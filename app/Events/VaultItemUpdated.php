<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\VaultItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento: VaultItem actualizado
 */
class VaultItemUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public VaultItem $vaultItem,
        public array $changes
    ) {
        //
    }
}
