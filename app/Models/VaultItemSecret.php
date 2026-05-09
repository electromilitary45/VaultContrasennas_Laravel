<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo VaultItemSecret
 * 
 * Representa el secreto cifrado de un item del vault.
 * 
 * @property int $id
 * @property int $vault_item_id
 * @property string $ciphertext
 * @property string $iv
 * @property string|null $salt
 * @property string $crypto_version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class VaultItemSecret extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vault_item_id',
        'ciphertext',
        'iv',
        'salt',
        'crypto_version',
    ];

    /**
     * Relación: Un secreto pertenece a un item del vault.
     *
     * @return BelongsTo<VaultItem>
     */
    public function vaultItem(): BelongsTo
    {
        return $this->belongsTo(VaultItem::class);
    }
}
