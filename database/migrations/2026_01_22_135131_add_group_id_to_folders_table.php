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
        Schema::table('folders', function (Blueprint $table) {
            // Verificar si la columna ya existe antes de agregarla
            if (!Schema::hasColumn('folders', 'group_id')) {
                $table->foreignId('group_id')
                    ->nullable()
                    ->after('owner_user_id')
                    ->constrained('groups')
                    ->onDelete('cascade')
                    ->comment('Grupo al que pertenece la carpeta (null para carpetas personales)');
                
                // Índice para búsquedas por grupo
                $table->index('group_id');
                
                // Índice adicional para búsquedas por owner, group y name
                $table->index(['owner_user_id', 'group_id', 'name'], 'folders_owner_group_name_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropIndex(['group_id']);
            $table->dropIndex('folders_owner_group_name_index');
            $table->dropColumn('group_id');
        });
    }
};
