<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controlador para Configuración del Sistema
 * 
 * Solo accesible por super administradores.
 * Permite configurar el sistema, email/SMTP, políticas de seguridad y modo de mantenimiento.
 */
class AdminSettingsController extends Controller
{
    /**
     * Mostrar página principal de configuración
     *
     * @return View
     */
    public function index(): View
    {
        // Agrupar configuraciones por grupo
        $settings = SystemSetting::all()->groupBy('group');
        
        // Configuraciones por defecto si no existen
        $defaultSettings = $this->getDefaultSettings();
        
        return view('admin.settings.index', [
            'settings' => $settings,
            'defaultSettings' => $defaultSettings,
        ]);
    }

    /**
     * Actualizar configuraciones generales
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $admin = auth()->user();
        
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_url' => 'nullable|url|max:255',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                SystemSetting::set($key, $value, 'string', 'general');
            }
        }

        Log::info('System settings updated', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'settings' => array_keys($validated),
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Configuración general actualizada exitosamente.');
    }

    /**
     * Actualizar configuración de email/SMTP
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $admin = auth()->user();
        
        $validated = $request->validate([
            'mail_mailer' => 'nullable|string|in:smtp,sendmail,mailgun,ses',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                // Cifrar contraseña si existe
                $encrypt = $key === 'mail_password';
                SystemSetting::set($key, $value, 'string', 'email', null, $encrypt);
            }
        }

        Log::info('Email settings updated', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'settings' => array_keys($validated),
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Configuración de email actualizada exitosamente.');
    }

    /**
     * Actualizar políticas de seguridad
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateSecurity(Request $request): RedirectResponse
    {
        $admin = auth()->user();
        
        $validated = $request->validate([
            'password_min_length' => 'nullable|integer|min:8|max:128',
            'password_require_uppercase' => 'nullable|boolean',
            'password_require_lowercase' => 'nullable|boolean',
            'password_require_numbers' => 'nullable|boolean',
            'password_require_symbols' => 'nullable|boolean',
            'session_lifetime' => 'nullable|integer|min:1|max:525600', // minutos
            'max_login_attempts' => 'nullable|integer|min:1|max:20',
            'lockout_duration' => 'nullable|integer|min:1|max:1440', // minutos
            'require_2fa_for_admins' => 'nullable|boolean',
        ]);

        // Manejar checkboxes: si no están presentes, son false
        $booleanFields = [
            'password_require_uppercase',
            'password_require_lowercase',
            'password_require_numbers',
            'password_require_symbols',
            'require_2fa_for_admins',
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->has($field) ? (bool) $request->input($field) : false;
        }

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $type = is_bool($value) ? 'boolean' : 'integer';
                SystemSetting::set($key, $value, $type, 'security');
            }
        }

        Log::info('Security settings updated', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'settings' => array_keys($validated),
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Políticas de seguridad actualizadas exitosamente.');
    }

    /**
     * Actualizar modo de mantenimiento
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateMaintenance(Request $request): RedirectResponse
    {
        $admin = auth()->user();
        
        $validated = $request->validate([
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:500',
            'maintenance_allowed_ips' => 'nullable|string|max:1000',
        ]);

        // Manejar checkbox: si no está presente, es false
        $maintenanceMode = $request->has('maintenance_mode') ? (bool) $request->input('maintenance_mode') : false;
        SystemSetting::set('maintenance_mode', $maintenanceMode, 'boolean', 'maintenance');
        
        if (isset($validated['maintenance_message'])) {
            SystemSetting::set('maintenance_message', $validated['maintenance_message'], 'string', 'maintenance');
        }
        
        if (isset($validated['maintenance_allowed_ips'])) {
            SystemSetting::set('maintenance_allowed_ips', $validated['maintenance_allowed_ips'], 'string', 'maintenance');
        }

        Log::info('Maintenance mode updated', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'maintenance_mode' => $validated['maintenance_mode'],
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Modo de mantenimiento actualizado exitosamente.');
    }

    /**
     * Obtener configuraciones por defecto
     *
     * @return array<string, array<string, mixed>>
     */
    private function getDefaultSettings(): array
    {
        return [
            'general' => [
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
            'email' => [
                'mail_mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_username' => config('mail.mailers.smtp.username'),
                'mail_encryption' => config('mail.mailers.smtp.encryption'),
                'mail_from_address' => config('mail.from.address'),
                'mail_from_name' => config('mail.from.name'),
            ],
            'security' => [
                'password_min_length' => 12,
                'password_require_uppercase' => true,
                'password_require_lowercase' => true,
                'password_require_numbers' => true,
                'password_require_symbols' => true,
                'session_lifetime' => config('session.lifetime', 120),
                'max_login_attempts' => 5,
                'lockout_duration' => 15,
                'require_2fa_for_admins' => false,
            ],
            'maintenance' => [
                'maintenance_mode' => false,
                'maintenance_message' => 'El sistema está en mantenimiento. Por favor, intente más tarde.',
                'maintenance_allowed_ips' => '',
            ],
        ];
    }
}
