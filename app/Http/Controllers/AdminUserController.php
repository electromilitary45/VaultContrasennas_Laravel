<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ChangeRoleRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Group;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Controlador para gestión de usuarios en el panel de administración
 *
 * Solo accesible para usuarios con rol admin o super_admin
 */
class AdminUserController extends Controller
{
    public function __construct(
        private UserManagementService $userManagementService
    ) {}

    /**
     * Mostrar lista de usuarios con filtros
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = User::query();
        $scopeQuery = User::query();

        if ($request->user()->isOrgAdmin()) {
            $orgId = $request->user()->organization_id;
            $query->where('organization_id', $orgId);
            $scopeQuery->where('organization_id', $orgId);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => (clone $scopeQuery)->count(),
            'active' => (clone $scopeQuery)->where('is_active', true)->count(),
            'inactive' => (clone $scopeQuery)->where('is_active', false)->count(),
            'by_role' => [
                'user' => (clone $scopeQuery)->where('role', 'user')->count(),
                'admin' => (clone $scopeQuery)->where('role', 'admin')->count(),
                'super_admin' => (clone $scopeQuery)->where('role', 'super_admin')->count(),
            ],
            'verified' => (clone $scopeQuery)->whereNotNull('email_verified_at')->count(),
            'with_2fa' => (clone $scopeQuery)->where('totp_enabled', true)->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['role', 'search', 'status', 'sort_by', 'sort_dir']),
        ]);
    }

    /**
     * Mostrar formulario para crear usuario manualmente (solo org admin).
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Crear usuario manualmente en la org del admin (solo org admin).
     *
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->userManagementService->createUserManually(
            $request->user()->organization,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Usuario «{$user->name}» creado. Podrá iniciar sesión con su email y contraseña.");
    }

    /**
     * Mostrar detalle de un usuario
     *
     * @param User $user
     * @return View
     */
    public function show(User $user): View
    {
        $this->ensureUserInOrg($user);

        // Estadísticas del usuario
        $userStats = [
            'vault_items' => $user->vaultItems()->where('status', '!=', 'deleted')->count(),
            'groups_owned' => $user->ownedGroups()->count(),
            'groups_member' => $user->groupMemberships()->where('status', 'active')->count(),
            'shared_items' => $user->sharedItems()->count(),
            'audit_logs' => $user->auditLogs()->count(),
        ];

        // Items recientes
        $recentItems = $user->vaultItems()
            ->where('status', '!=', 'deleted')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Grupos del usuario con conteo de miembros
        $groups = Group::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'active');
        })->withCount('activeMembers')->get();

        // Logs recientes
        $recentLogs = $user->auditLogs()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.users.show', [
            'user' => $user,
            'stats' => $userStats,
            'recentItems' => $recentItems,
            'groups' => $groups,
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * Mostrar formulario de edición de usuario
     *
     * @param User $user
     * @return View
     */
    public function edit(User $user): View
    {
        $this->ensureUserInOrg($user);

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Actualizar información de usuario
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureUserInOrg($user);

        $validated = $request->validated();
        $admin = $request->user();
        
        // Guardar valores anteriores para logging
        $oldName = $user->name;
        $oldEmail = $user->email;
        $oldEmailVerified = $user->email_verified_at !== null;

        // Actualizar información básica
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Verificación de email (solo si el admin lo marca)
        $emailVerificationChanged = false;
        if ($request->has('email_verified')) {
            if ($request->boolean('email_verified') && !$user->email_verified_at) {
                $user->email_verified_at = now();
                $emailVerificationChanged = true;
            } elseif (!$request->boolean('email_verified') && $user->email_verified_at) {
                $user->email_verified_at = null;
                $emailVerificationChanged = true;
            }
        }

        $user->save();

        // Registrar en logs de auditoría
        Log::info('User updated by admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'user_id' => $user->id,
            'old_name' => $oldName,
            'new_name' => $user->name,
            'old_email' => $oldEmail,
            'new_email' => $user->email,
            'email_verification_changed' => $emailVerificationChanged,
            'email_verified' => $user->email_verified_at !== null,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Cambiar rol de usuario
     * 
     * Solo super admin puede cambiar roles
     *
     * @param ChangeRoleRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function changeRole(ChangeRoleRequest $request, User $user): RedirectResponse
    {
        $this->ensureUserInOrg($user);

        $admin = $request->user();

        // No permitir cambiar el rol del último super admin
        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() === 1) {
            // Registrar intento fallido en logs
            Log::warning('Attempt to change role of last super admin blocked', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now(),
            ]);
            
            return back()->withErrors(['role' => 'No se puede cambiar el rol del último super administrador.']);
        }

        // No permitir que un admin cambie su propio rol
        if ($user->id === $request->user()->id) {
            // Registrar intento fallido en logs
            Log::warning('Attempt to self-change role blocked', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'user_id' => $user->id,
                'timestamp' => now(),
            ]);
            
            return back()->withErrors(['role' => 'No puedes cambiar tu propio rol.']);
        }

        $oldRole = $user->role;
        $user->role = $request->validated()['role'];
        $user->save();

        // Registrar en logs de auditoría
        Log::info('User role changed by admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'old_role' => $oldRole,
            'new_role' => $user->role,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "Rol del usuario cambiado de '{$oldRole}' a '{$user->role}' exitosamente.");
    }

    /**
     * Resetear contraseña de usuario
     * 
     * Nota: Esta acción debe ser ética y transparente. Se registra en logs de auditoría.
     * TODO: Implementar notificación por email cuando se configure el sistema de correo.
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->ensureUserInOrg($user);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'notify_user' => ['sometimes', 'boolean'],
        ]);

        $admin = $request->user();
        
        // Cambiar contraseña
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Registrar en logs de auditoría
        Log::info('Password reset by admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'notified' => $request->boolean('notify_user', false),
            'timestamp' => now(),
        ]);

        // TODO: Implementar notificación por email cuando se configure el sistema de correo
        // Por ahora solo se registra en logs

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Contraseña del usuario reseteada exitosamente. La acción ha sido registrada en los logs de auditoría.');
    }

    /**
     * Activar usuario
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function activate(User $user): RedirectResponse
    {
        $this->ensureUserInOrg($user);

        $admin = auth()->user();

        $user->is_active = true;
        $user->save();

        // Registrar en logs de auditoría
        Log::info('User activated by admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Usuario activado exitosamente.');
    }

    /**
     * Desactivar usuario
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function deactivate(User $user): RedirectResponse
    {
        $this->ensureUserInOrg($user);

        $admin = auth()->user();

        // No permitir desactivar al último super admin
        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->where('is_active', true)->count() === 1) {
            // Registrar intento fallido en logs
            Log::warning('Attempt to deactivate last super admin blocked', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now(),
            ]);
            
            return back()->withErrors(['error' => 'No se puede desactivar al último super administrador activo.']);
        }

        // No permitir desactivarse a sí mismo
        if ($user->id === auth()->id()) {
            // Registrar intento fallido en logs
            Log::warning('Attempt to self-deactivate blocked', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'user_id' => $user->id,
                'timestamp' => now(),
            ]);
            
            return back()->withErrors(['error' => 'No puedes desactivar tu propia cuenta.']);
        }

        $user->is_active = false;
        $user->save();

        // Registrar en logs de auditoría
        Log::info('User deactivated by admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Usuario desactivado exitosamente. No podrá iniciar sesión hasta que sea reactivado.');
    }

    /**
     * Eliminar usuario (soft delete)
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->ensureUserInOrg($user);

        $admin = auth()->user();

        // No permitir eliminar al último super admin
        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() === 1) {
            // Registrar intento fallido en logs
            Log::warning('Attempt to delete last super admin blocked', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now(),
            ]);
            
            return back()->withErrors(['error' => 'No se puede eliminar al último super administrador.']);
        }

        // No permitir eliminarse a sí mismo
        if ($user->id === auth()->id()) {
            // Registrar intento fallido en logs
            Log::warning('Attempt to self-delete blocked', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'user_id' => $user->id,
                'timestamp' => now(),
            ]);
            
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta.']);
        }

        // Guardar información antes de modificar para logging
        $deletedEmail = $user->email;
        $deletedName = $user->name;
        $deletedRole = $user->role;

        // Soft delete: marcar como inactivo en lugar de eliminar físicamente
        $user->is_active = false;
        $user->email = 'deleted_' . $user->id . '_' . time() . '@deleted.local';
        $user->save();

        // Registrar en logs de auditoría
        Log::info('User deleted by admin (soft delete)', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'user_id' => $user->id,
            'deleted_email' => $deletedEmail,
            'deleted_name' => $deletedName,
            'deleted_role' => $deletedRole,
            'new_email' => $user->email,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Comprobar que el admin puede gestionar al usuario (org admin solo usuarios de su org).
     */
    private function ensureUserInOrg(User $user): void
    {
        $admin = request()->user();
        if (! $admin->isOrgAdmin()) {
            return;
        }
        if ($user->organization_id !== $admin->organization_id) {
            abort(403, 'No puedes gestionar usuarios de otra organización.');
        }
    }
}
