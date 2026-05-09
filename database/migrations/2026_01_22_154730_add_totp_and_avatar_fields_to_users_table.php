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
        Schema::table('users', function (Blueprint $table) {
            // Campos TOTP/2FA
            $table->text('totp_secret')->nullable()->after('password');
            $table->boolean('totp_enabled')->default(false)->after('totp_secret');
            $table->json('totp_backup_codes')->nullable()->after('totp_enabled');
            $table->timestamp('totp_verified_at')->nullable()->after('totp_backup_codes');
            
            // Campo Avatar
            $table->string('avatar')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'totp_secret',
                'totp_enabled',
                'totp_backup_codes',
                'totp_verified_at',
                'avatar',
            ]);
        });
    }
};
