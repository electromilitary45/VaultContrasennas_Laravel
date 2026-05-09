<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\SuperAdminSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin', 'super_admin'])
                ->default('user')
                ->after('email')
                ->comment('Rol del usuario: user (normal), admin (administrador), super_admin (super administrador)');
            
            // Índice para búsquedas por rol
            $table->index('role');
        });

        // Ejecutar el seeder para crear el super administrador
        Artisan::call('db:seed', [
            '--class' => SuperAdminSeeder::class,
            '--force' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
