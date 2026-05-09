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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Usuario receptor de la notificación');
            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->onDelete('cascade')
                ->comment('Organización (scoping). Nullable para platform super admin');
            $table->enum('type', [
                'group_invitation',
                'group_invitation_accepted',
                'group_invitation_rejected',
                'group_access_request',
                'group_access_request_accepted',
                'group_access_request_rejected',
                'item_shared_user',
                'item_shared_group',
                'item_created_in_group',
            ])
                ->comment('Tipo de notificación');
            $table->string('title')
                ->comment('Título de la notificación');
            $table->text('message')
                ->comment('Mensaje de la notificación');
            $table->json('data')
                ->nullable()
                ->comment('Datos adicionales (group_id, vault_item_id, user_id, etc.)');
            $table->boolean('read')
                ->default(false)
                ->comment('Indica si la notificación ha sido leída');
            $table->timestamp('read_at')
                ->nullable()
                ->comment('Fecha y hora en que se marcó como leída');
            $table->string('action_url')
                ->nullable()
                ->comment('URL para acción rápida (ej: route de grupo, item, etc.)');
            $table->string('action_label')
                ->nullable()
                ->comment('Etiqueta del botón de acción rápida (ej: "Ver grupo", "Ver item")');
            $table->timestamps();

            // Índices
            $table->index('user_id');
            $table->index('organization_id');
            $table->index('read');
            $table->index('created_at');
            $table->index('type');
            $table->index(['user_id', 'read']);
            $table->index(['user_id', 'organization_id', 'read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
