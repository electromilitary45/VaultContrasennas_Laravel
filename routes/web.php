<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'ensure-password-changed'])->name('dashboard');

// Versión de la extensión (público, CORS para extensión) — actualización desde tu página sin Chrome Web Store
Route::get('/api/extension/version', [\App\Http\Controllers\IntegrationsController::class, 'extensionVersion'])
    ->middleware('extension.cors')->name('api.extension.version');

Route::middleware(['auth', 'ensure-password-changed'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas de 2FA
    Route::get('/profile/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'index'])->name('profile.two-factor');
    Route::post('/profile/two-factor/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('profile.two-factor.enable');
    Route::post('/profile/two-factor/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('profile.two-factor.disable');
    Route::post('/profile/two-factor/regenerate-backup-codes', [\App\Http\Controllers\TwoFactorController::class, 'regenerateBackupCodes'])->name('profile.two-factor.regenerate-backup-codes');
    
    // Rutas de Vault
    Route::resource('vault', \App\Http\Controllers\VaultController::class);
    Route::post('/vault/totp/generate', [\App\Http\Controllers\VaultController::class, 'generateTotp'])->name('vault.totp.generate');
    Route::post('/vault/totp/generate-secret', [\App\Http\Controllers\VaultController::class, 'generateTotpSecret'])->name('vault.totp.generate-secret');
    
    // Rutas de compartición
    Route::post('/vault/{vault}/share', [\App\Http\Controllers\VaultController::class, 'share'])->name('vault.share');
    Route::delete('/vault/{vault}/share', [\App\Http\Controllers\VaultController::class, 'revokeShare'])->name('vault.share.revoke');
    Route::get('/vault/{vault}/shares', [\App\Http\Controllers\VaultController::class, 'listShares'])->name('vault.shares');
    Route::put('/vault/{vault}/share/permission', [\App\Http\Controllers\VaultController::class, 'updateSharePermission'])->name('vault.share.update-permission');

    // Rutas de Grupos
    Route::resource('groups', \App\Http\Controllers\GroupController::class);
    Route::get('/groups/{group}/admin', [\App\Http\Controllers\GroupController::class, 'admin'])->name('groups.admin');
    Route::post('/groups/{group}/invite', [\App\Http\Controllers\GroupController::class, 'inviteMember'])->name('groups.invite');
    Route::post('/groups/{group}/invitations/accept', [\App\Http\Controllers\GroupController::class, 'acceptInvitation'])->name('groups.invitations.accept');
    Route::post('/groups/{group}/invitations/reject', [\App\Http\Controllers\GroupController::class, 'rejectInvitation'])->name('groups.invitations.reject');
    Route::post('/groups/{group}/invitations/{user}/resend', [\App\Http\Controllers\GroupController::class, 'resendInvitation'])->name('groups.invitations.resend');
    Route::post('/groups/{group}/access-requests', [\App\Http\Controllers\GroupAccessRequestController::class, 'store'])->name('groups.access-requests.store');
    Route::post('/groups/{group}/access-requests/{accessRequest}/accept', [\App\Http\Controllers\GroupAccessRequestController::class, 'accept'])->name('groups.access-requests.accept');
    Route::post('/groups/{group}/access-requests/{accessRequest}/reject', [\App\Http\Controllers\GroupAccessRequestController::class, 'reject'])->name('groups.access-requests.reject');
    Route::get('/groups/access-requests', [\App\Http\Controllers\GroupAccessRequestController::class, 'index'])->name('groups.access-requests.index');
    Route::get('/groups/{group}/access-requests/pending', [\App\Http\Controllers\GroupAccessRequestController::class, 'pendingForGroup'])->name('groups.access-requests.pending');
    Route::delete('/groups/{group}/member', [\App\Http\Controllers\GroupController::class, 'removeMember'])->name('groups.remove-member');
    Route::put('/groups/{group}/member/role', [\App\Http\Controllers\GroupController::class, 'updateMemberRole'])->name('groups.update-member-role');
    
    // Rutas de Items del Grupo
    Route::get('/groups/{group}/items', [\App\Http\Controllers\GroupController::class, 'itemsIndex'])->name('groups.items.index');
    Route::get('/groups/{group}/items/create', [\App\Http\Controllers\GroupController::class, 'itemsCreate'])->name('groups.items.create');
    Route::post('/groups/{group}/items', [\App\Http\Controllers\GroupController::class, 'itemsStore'])->name('groups.items.store');
    
    // Rutas de Carpetas del Grupo
    Route::post('/groups/{group}/folders', [\App\Http\Controllers\GroupController::class, 'storeFolder'])->name('groups.folders.store');
    Route::put('/groups/{group}/folders/{folder}', [\App\Http\Controllers\GroupController::class, 'updateFolder'])->name('groups.folders.update');
    Route::delete('/groups/{group}/folders/{folder}', [\App\Http\Controllers\GroupController::class, 'destroyFolder'])->name('groups.folders.destroy');
    
    // Rutas de Carpetas (integradas en Vault)
    Route::prefix('folders')->group(function () {
        Route::get('/', [\App\Http\Controllers\FolderController::class, 'index'])->name('folders.index');
        Route::get('/for-select', [\App\Http\Controllers\FolderController::class, 'forSelect'])->name('folders.for-select');
        Route::post('/', [\App\Http\Controllers\FolderController::class, 'store'])->name('folders.store');
        Route::put('/{folder}', [\App\Http\Controllers\FolderController::class, 'update'])->name('folders.update');
        Route::delete('/{folder}', [\App\Http\Controllers\FolderController::class, 'destroy'])->name('folders.destroy');
    });
    
    // Rutas de Auditoría
    Route::prefix('audit')->group(function () {
        Route::get('/', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
        Route::get('/export', [\App\Http\Controllers\AuditController::class, 'export'])->name('audit.export');
    });
    
    // Integraciones (extensión de navegador, etc.)
    Route::get('/integrations', [\App\Http\Controllers\IntegrationsController::class, 'index'])->name('integrations.index');
    Route::get('/integrations/extension/download', [\App\Http\Controllers\IntegrationsController::class, 'downloadExtension'])->name('integrations.extension.download');

    // Rutas de Notificaciones
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    // API para extensión de navegador (Chrome) — sesión web, mismo usuario/org
    Route::prefix('api/extension')->middleware('extension.cors')->name('api.extension.')->group(function () {
        Route::get('/vault/items', [\App\Http\Controllers\Api\ExtensionApiController::class, 'items'])->name('vault.items');
        Route::get('/vault/items/{item}', [\App\Http\Controllers\Api\ExtensionApiController::class, 'show'])->name('vault.items.show');
    });
    
    // Rutas de Administración (solo para admins)
    Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Gestión de usuarios
        Route::get('/users', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('users.index');
        Route::middleware('org-admin')->group(function () {
            Route::get('/users/create', [\App\Http\Controllers\AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [\App\Http\Controllers\AdminUserController::class, 'store'])->name('users.store');
        });
        Route::get('/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/change-role', [\App\Http\Controllers\AdminUserController::class, 'changeRole'])->name('users.change-role');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/activate', [\App\Http\Controllers\AdminUserController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/deactivate', [\App\Http\Controllers\AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'destroy'])->name('users.destroy');
        
        // Gestión de contenido
        Route::get('/vault', [\App\Http\Controllers\Admin\AdminVaultController::class, 'index'])->name('vault.index');
        Route::get('/vault/{vaultItem}', [\App\Http\Controllers\Admin\AdminVaultController::class, 'show'])->name('vault.show');
        Route::delete('/vault/{vaultItem}', [\App\Http\Controllers\Admin\AdminVaultController::class, 'destroy'])->name('vault.destroy');
        
        Route::get('/groups', [\App\Http\Controllers\Admin\AdminGroupController::class, 'index'])->name('groups.index');
        Route::get('/groups/{group}', [\App\Http\Controllers\Admin\AdminGroupController::class, 'show'])->name('groups.show');
        Route::delete('/groups/{group}', [\App\Http\Controllers\Admin\AdminGroupController::class, 'destroy'])->name('groups.destroy');
        
        Route::get('/audit', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/export', [\App\Http\Controllers\AuditController::class, 'export'])->name('audit.export');
        
        // Reportes de seguridad (solo platform super admin)
        Route::middleware('platform-super-admin')->group(function () {
            Route::get('/security', [\App\Http\Controllers\Admin\AdminSecurityController::class, 'index'])->name('security.index');
            Route::get('/security/weak-passwords', [\App\Http\Controllers\Admin\AdminSecurityController::class, 'weakPasswords'])->name('security.weak-passwords');
            Route::get('/security/reused-passwords', [\App\Http\Controllers\Admin\AdminSecurityController::class, 'reusedPasswords'])->name('security.reused-passwords');
            Route::get('/security/inactive-users', [\App\Http\Controllers\Admin\AdminSecurityController::class, 'inactiveUsers'])->name('security.inactive-users');
            Route::get('/security/failed-logins', [\App\Http\Controllers\Admin\AdminSecurityController::class, 'failedLogins'])->name('security.failed-logins');
            Route::get('/security/report', [\App\Http\Controllers\Admin\AdminSecurityController::class, 'securityReport'])->name('security.report');
        });

        // Extensión de navegador y configuración (solo platform super admin)
        Route::middleware('platform-super-admin')->group(function () {
            Route::post('/extension/publish-version', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'publishExtensionVersion'])->name('extension.publish-version');
            Route::get('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings/general', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.update');
            Route::put('/settings/email', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateEmail'])->name('settings.update-email');
            Route::put('/settings/security', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateSecurity'])->name('settings.update-security');
            Route::put('/settings/maintenance', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateMaintenance'])->name('settings.update-maintenance');
        });

        // Códigos de invitación (solo org admins)
        Route::middleware('org-admin')->prefix('invitation-codes')->name('invitation-codes.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InvitationCodeController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\InvitationCodeController::class, 'store'])->name('store');
        });

        // Organizaciones (solo platform super admin)
        Route::middleware('platform-super-admin')->prefix('organizations')->name('organizations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\OrganizationController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\OrganizationController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\OrganizationController::class, 'store'])->name('store');
            Route::post('/{organization}/regenerate-password', [\App\Http\Controllers\Admin\OrganizationController::class, 'regeneratePassword'])->name('regenerate-password');
            Route::get('/{organization}', [\App\Http\Controllers\Admin\OrganizationController::class, 'show'])->name('show');
            Route::get('/{organization}/edit', [\App\Http\Controllers\Admin\OrganizationController::class, 'edit'])->name('edit');
            Route::put('/{organization}', [\App\Http\Controllers\Admin\OrganizationController::class, 'update'])->name('update');
        });
    });
});

/**
 * Rutas de Autenticación (Laravel Breeze)
 * 
 * Este archivo contiene todas las rutas relacionadas con autenticación:
 * - Login / Logout
 * - Registro de usuarios
 * - Recuperación de contraseña
 * - Verificación de email
 * - Confirmación de contraseña
 * 
 * Se mantiene separado del archivo principal de rutas (web.php) para:
 * 1. Organización: Mantener las rutas de autenticación agrupadas
 * 2. Mantenibilidad: Facilita la gestión y modificación de rutas de auth
 * 3. Convención: Laravel Breeze genera este archivo por defecto
 * 4. Separación de responsabilidades: Auth vs. Rutas de aplicación
 * 
 * Las rutas aquí definidas están protegidas por middleware de autenticación
 * cuando corresponde (login, registro son públicas; logout requiere auth).
 */
require __DIR__.'/auth.php';
