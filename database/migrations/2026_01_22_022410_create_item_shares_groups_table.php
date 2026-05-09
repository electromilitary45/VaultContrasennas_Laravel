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
        Schema::create('item_shares_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_item_id')
                ->constrained('vault_items')
                ->onDelete('cascade')
                ->comment('Item del vault compartido');
            $table->foreignId('group_id')
                ->constrained('groups')
                ->onDelete('cascade')
                ->comment('Grupo con el que se comparte');
            $table->enum('permission', ['view', 'edit', 'admin'])
                ->default('view')
                ->comment('Permiso del grupo: view (solo ver), edit (editar), admin (administrar)');
            $table->foreignId('shared_by_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Usuario que realizó el compartir');
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['vault_item_id', 'group_id'], 'item_shares_groups_unique');
            
            // Índices para búsquedas rápidas
            $table->index('vault_item_id');
            $table->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_shares_groups');
    }
};
