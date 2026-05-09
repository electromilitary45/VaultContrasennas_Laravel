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
        Schema::table('vault_items', function (Blueprint $table) {
            // Agregar foreign key constraint para folder_id
            // Nota: La columna folder_id ya existe en la tabla, solo agregamos la constraint
            $table->foreign('folder_id')
                ->references('id')
                ->on('folders')
                ->onDelete('set null')
                ->comment('Carpeta a la que pertenece el item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vault_items', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
        });
    }
};
