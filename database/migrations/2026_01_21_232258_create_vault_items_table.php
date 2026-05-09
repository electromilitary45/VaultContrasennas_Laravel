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
        Schema::create('vault_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Usuario propietario original del item');
            $table->enum('type', ['auth', 'note', 'card', 'api_key', 'ssh_key'])
                ->default('auth')
                ->comment('Tipo de item del vault');
            $table->string('title')->comment('Título del item');
            $table->foreignId('folder_id')
                ->nullable()
                ->comment('Carpeta a la que pertenece (nullable, FK se agregará después)');
            $table->boolean('favorite')->default(false)->comment('Marcado como favorito');
            $table->enum('status', ['active', 'archived', 'deleted'])
                ->default('active')
                ->comment('Estado del item (soft-delete lógico)');
            $table->timestamps();

            // Índices
            $table->index(['owner_user_id', 'type']);
            $table->index(['owner_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_items');
    }
};
