<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\VaultItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento: VaultItem visualizado
 */
class VaultItemViewed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public VaultItem $vaultItem
    ) {
        //
    }
}
