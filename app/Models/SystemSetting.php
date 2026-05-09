<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Modelo SystemSetting
 * 
 * Representa una configuración del sistema almacenada en la base de datos.
 * 
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property string|null $description
 * @property bool $is_encrypted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SystemSetting extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_encrypted',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Obtener el valor de una configuración
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->is_encrypted 
            ? Crypt::decryptString($setting->value) 
            : $setting->value;

        return self::castValue($value, $setting->type);
    }

    /**
     * Establecer el valor de una configuración
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @param string|null $description
     * @param bool $encrypt
     * @return SystemSetting
     */
    public static function set(
        string $key,
        mixed $value,
        string $type = 'string',
        string $group = 'general',
        ?string $description = null,
        bool $encrypt = false
    ): SystemSetting {
        $stringValue = self::stringifyValue($value, $type);

        if ($encrypt) {
            $stringValue = Crypt::encryptString($stringValue);
        }

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_encrypted' => $encrypt,
            ]
        );
    }

    /**
     * Convertir valor a string según el tipo
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private static function stringifyValue(mixed $value, string $type): string
    {
        return match ($type) {
            'json', 'array' => json_encode($value, JSON_UNESCAPED_UNICODE),
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            default => (string) $value,
        };
    }

    /**
     * Convertir string a valor según el tipo
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'json', 'array' => json_decode($value, true),
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            default => $value,
        };
    }

    /**
     * Obtener todas las configuraciones de un grupo
     *
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Collection<int, SystemSetting>
     */
    public static function getByGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('group', $group)->get();
    }

    /**
     * Obtener el valor decifrado (accesor)
     *
     * @return mixed
     */
    public function getDecryptedValueAttribute(): mixed
    {
        $value = $this->is_encrypted 
            ? Crypt::decryptString($this->value) 
            : $this->value;

        return self::castValue($value, $this->type);
    }
}
