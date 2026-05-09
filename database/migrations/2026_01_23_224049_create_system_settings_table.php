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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Clave única de la configuración');
            $table->text('value')->nullable()->comment('Valor de la configuración (JSON o texto)');
            $table->string('type')->default('string')->comment('Tipo de dato: string, integer, boolean, json, array');
            $table->string('group')->default('general')->comment('Grupo de configuración: general, email, security, maintenance');
            $table->text('description')->nullable()->comment('Descripción de la configuración');
            $table->boolean('is_encrypted')->default(false)->comment('Indica si el valor está cifrado');
            $table->timestamps();

            // Índices
            $table->index('group');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
