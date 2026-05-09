<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Códigos de invitación para registro. Un código = una org; uso único.
     */
    public function up(): void
    {
        Schema::create('organization_invitation_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete()
                ->comment('Organización a la que pertenece el código');
            $table->string('code', 32)->unique()->comment('Código alfanumérico único');
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que generó el código');
            $table->timestamp('used_at')->nullable()->comment('Cuándo se usó; NULL = no usado');
            $table->foreignId('used_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario que se registró con el código');
            $table->timestamps();

            $table->index(['organization_id', 'used_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_invitation_codes');
    }
};
