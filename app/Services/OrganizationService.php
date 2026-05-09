<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Servicio para gestión de organizaciones (tenants) en el modelo SaaS multi-tenant.
 *
 * Creación de organizaciones y del super admin de la organización.
 */
class OrganizationService
{
    /**
     * Crea una organización y su super admin. Retorna credenciales para mostrar una sola vez.
     * El email del super admin es admin@{slug}.vault.local (slug derivado del nombre).
     *
     * @param array{name: string, logo?: string|null} $data
     * @param User $createdBy Usuario que crea (platform super admin)
     * @return array{organization: Organization, admin: User, email: string, password: string}
     */
    public function createOrganization(array $data, User $createdBy): array
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $slug = $this->generateUniqueSlug($data['name']);

            $org = Organization::create([
                'name' => $data['name'],
                'slug' => $slug,
                'logo' => $data['logo'] ?? null,
            ]);

            $email = 'admin@' . $slug . '.vault.local';
            $password = Str::random(12);

            $admin = User::create([
                'name' => 'Super Admin – ' . $org->name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'organization_id' => $org->id,
                'email_verified_at' => now(),
                'must_change_password' => true,
            ]);

            return [
                'organization' => $org,
                'admin' => $admin,
                'email' => $email,
                'password' => $password,
            ];
        });
    }

    /**
     * Regenera la contraseña temporal del super admin de la organización.
     * Actualiza el usuario, pone must_change_password = true y retorna las nuevas credenciales.
     *
     * @return array{organization: Organization, admin: User, email: string, password: string}
     * @throws \RuntimeException Si no existe super admin para la org
     */
    public function regenerateTemporaryPassword(Organization $organization, User $createdBy): array
    {
        $admin = User::where('organization_id', $organization->id)
            ->where('role', 'super_admin')
            ->first();

        if (! $admin) {
            throw new \RuntimeException('No existe super admin para la organización.');
        }

        $password = Str::random(12);
        $admin->update([
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]);

        return [
            'organization' => $organization,
            'admin' => $admin,
            'email' => $admin->email,
            'password' => $password,
        ];
    }

    /**
     * Genera un slug único a partir del nombre (ej. "Acme Corp" → "acme-corp").
     * Si existe, añade -2, -3, etc.
     */
    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'org';
        }

        $slug = $base;
        $n = 1;
        while (Organization::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$n);
        }

        return $slug;
    }
}
