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
        Schema::create('vault_item_secrets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_item_id')
                ->constrained('vault_items')
                ->onDelete('cascade')
                ->comment('Item del vault al que pertenece el secreto');
            $table->longText('ciphertext')->comment('Texto cifrado del secreto (JSON cifrado)');
            $table->string('iv', 64)->comment('Vector de inicialización para el cifrado');
            $table->string('salt', 128)->nullable()->comment('Salt para el cifrado (opcional)');
            $table->string('crypto_version', 20)->default('v1')->comment('Versión del algoritmo de cifrado');
            $table->timestamps();

            // Índice único: un item solo puede tener un secreto activo
            $table->unique('vault_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_item_secrets');
    }
};
