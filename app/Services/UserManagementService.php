<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Servicio para operaciones de gestión de usuarios (admin).
 *
 * Creación manual de usuarios por org admin, etc.
 */
class UserManagementService
{
    /**
     * Crea un usuario manualmente en la organización (org admin).
     *
     * @param  array{name: string, email: string, password: string}  $data
     * @return User
     */
    public function createUserManually(Organization $organization, array $data, User $createdBy): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'organization_id' => $organization->id,
        ]);

        Log::info('Usuario creado manualmente por org admin', [
            'created_by_user_id' => $createdBy->id,
            'created_by_email' => $createdBy->email,
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'new_user_id' => $user->id,
            'new_user_email' => $user->email,
            'timestamp' => now(),
        ]);

        return $user;
    }

    /**
     * Reglas de validación para creación manual de usuario.
     *
     * @return array<string, array<int, mixed>>
     */
    public function createUserManuallyRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }
}
