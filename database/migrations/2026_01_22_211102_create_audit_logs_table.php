<?php

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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action', 50); // create, update, delete, view, share, unshare, etc.
            $table->string('model_type', 100); // App\Models\VaultItem, App\Models\Group, etc.
            $table->unsignedBigInteger('model_id')->nullable(); // ID del modelo relacionado
            $table->json('changes')->nullable(); // Cambios realizados (antes/después)
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('meta')->nullable(); // Información adicional
            $table->timestamps();
            
            // Índices para mejorar consultas
            $table->index(['user_id', 'action']);
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
