<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Usuario propietario de la carpeta');
            $table->string('name')->comment('Nombre de la carpeta');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('folders')
                ->onDelete('cascade')
                ->comment('Carpeta padre (nullable para carpetas raíz)');
            $table->timestamps();

            // Índice compuesto para búsquedas rápidas por usuario y nombre
            $table->index(['owner_user_id', 'name'], 'folders_owner_name_index');
            
            // Índice para búsquedas por padre
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
