<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo VaultItemVersion
 * 
 * Representa una versión histórica de un secreto del vault.
 * 
 * @property int $id
 * @property int $vault_item_id
 * @property int $version_number
 * @property string $ciphertext
 * @property string $iv
 * @property string|null $salt
 * @property string $crypto_version
 * @property int $created_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class VaultItemVersion extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vault_item_id',
        'version_number',
        'ciphertext',
        'iv',
        'salt',
        'crypto_version',
        'created_by_user_id',
    ];

    /**
     * Relación: Una versión pertenece a un item del vault.
     *
     * @return BelongsTo<VaultItem>
     */
    public function vaultItem(): BelongsTo
    {
        return $this->belongsTo(VaultItem::class);
    }

    /**
     * Relación: Una versión fue creada por un usuario.
     *
     * @return BelongsTo<User>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
