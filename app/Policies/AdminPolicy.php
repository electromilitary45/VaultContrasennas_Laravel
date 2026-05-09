<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Policy para acciones de administración
 * 
 * Controla qué acciones pueden realizar los admins y super_admins
 */
class AdminPolicy
{
    /**
     * Determine whether the user can access the admin panel.
     */
    public function accessAdminPanel(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage users.
     * 
     * Admin y super_admin pueden gestionar usuarios.
     */
    public function manageUsers(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can change user roles.
     * 
     * Solo super_admin puede cambiar roles.
     */
    public function changeUserRole(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can manage other admins.
     * 
     * Solo super_admin puede gestionar otros admins.
     */
    public function manageAdmins(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view all vault items (metadatos).
     * 
     * Admin y super_admin pueden ver metadatos de todos los items.
     */
    public function viewAllVaultItems(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete any vault item.
     * 
     * Admin y super_admin pueden eliminar items (con confirmación).
     */
    public function deleteAnyVaultItem(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage system settings.
     * 
     * Solo super_admin puede gestionar configuración del sistema.
     */
    public function manageSystemSettings(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view all audit logs.
     * 
     * Admin y super_admin pueden ver todos los logs.
     */
    public function viewAllAuditLogs(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export audit logs.
     * 
     * Admin y super_admin pueden exportar logs.
     */
    public function exportAuditLogs(User $user): bool
    {
        return $user->isAdmin();
    }
}
