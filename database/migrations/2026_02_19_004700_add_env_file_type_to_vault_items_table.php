<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade 'env_file' al ENUM type de vault_items.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE vault_items MODIFY COLUMN type ENUM('auth', 'note', 'card', 'api_key', 'ssh_key', 'env_file') DEFAULT 'auth' COMMENT 'Tipo de item del vault'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE vault_items MODIFY COLUMN type ENUM('auth', 'note', 'card', 'api_key', 'ssh_key') DEFAULT 'auth' COMMENT 'Tipo de item del vault'");
    }
};
