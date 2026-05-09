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
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                ->constrained('groups')
                ->onDelete('cascade')
                ->comment('Grupo al que pertenece el miembro');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Usuario miembro del grupo');
            $table->enum('role', ['owner', 'admin', 'member', 'viewer'])
                ->default('member')
                ->comment('Rol del usuario en el grupo');
            $table->enum('status', ['invited', 'active', 'revoked'])
                ->default('invited')
                ->comment('Estado de la membresía');
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Usuario que invitó a este miembro');
            $table->timestamps();

            // Índices
            $table->unique(['group_id', 'user_id'], 'group_members_unique');
            $table->index('group_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
