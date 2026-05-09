<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder para crear el primer super administrador
 * 
 * Ejecutar con: php artisan db:seed --class=SuperAdminSeeder
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un super admin
        $existingSuperAdmin = User::where('role', 'super_admin')->first();
        
        if ($existingSuperAdmin) {
            $this->command->warn('Ya existe un super administrador en el sistema.');
            return;
        }

        // Crear super admin de plataforma (organization_id se deja NULL; se añade en migración posterior)
        User::create([
            'name' => 'Super Administrador',
            'email' => 'admin@vault.local',
            'password' => Hash::make('admin123'), // Cambiar en producción
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super administrador creado exitosamente.');
        $this->command->warn('IMPORTANTE: Cambiar la contraseña del super admin después del primer login.');
        $this->command->line('Email: admin@vault.local');
        $this->command->line('Password: admin123');
    }
}
