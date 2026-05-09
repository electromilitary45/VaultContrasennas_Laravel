<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\VaultItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento: Compartición de VaultItem revocada
 */
class VaultItemUnshared
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public VaultItem $vaultItem,
        public string $sharedWithType, // 'user' o 'group'
        public int $sharedWithId
    ) {
        //
    }
}
