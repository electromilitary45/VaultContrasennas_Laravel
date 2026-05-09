<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VaultItem;
use App\Services\CryptoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controlador para Reportes de Seguridad del Panel de Administración
 * 
 * Proporciona análisis de seguridad del sistema:
 * - Contraseñas débiles
 * - Contraseñas reutilizadas
 * - Usuarios inactivos
 * - Reporte general de seguridad
 */
class AdminSecurityController extends Controller
{
    public function __construct(
        private CryptoService $cryptoService
    ) {
    }

    /**
     * Dashboard principal de seguridad
     *
     * @return View
     */
    public function index(): View
    {
        return view('admin.security.index');
    }

    /**
     * Reporte de contraseñas débiles
     *
     * @param Request $request
     * @return View
     */
    public function weakPasswords(Request $request): View
    {
        $weakPasswords = $this->findWeakPasswords();

        // Log de auditoría
        Log::info('Security report: Weak passwords accessed', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'weak_passwords_count' => count($weakPasswords),
            'timestamp' => now(),
        ]);

        return view('admin.security.weak-passwords', [
            'weakPasswords' => $weakPasswords,
            'total' => count($weakPasswords),
        ]);
    }

    /**
     * Reporte de contraseñas reutilizadas
     *
     * @param Request $request
     * @return View
     */
    public function reusedPasswords(Request $request): View
    {
        $reusedPasswords = $this->findReusedPasswords();

        // Log de auditoría
        Log::info('Security report: Reused passwords accessed', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'reused_passwords_count' => count($reusedPasswords),
            'timestamp' => now(),
        ]);

        return view('admin.security.reused-passwords', [
            'reusedPasswords' => $reusedPasswords,
            'total' => count($reusedPasswords),
        ]);
    }

    /**
     * Reporte de usuarios inactivos
     *
     * @param Request $request
     * @return View
     */
    public function inactiveUsers(Request $request): View
    {
        $daysInactive = (int) $request->input('days', 90);
        $inactiveUsers = $this->findInactiveUsers($daysInactive);

        // Log de auditoría
        Log::info('Security report: Inactive users accessed', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'days_inactive' => $daysInactive,
            'inactive_users_count' => count($inactiveUsers),
            'timestamp' => now(),
        ]);

        return view('admin.security.inactive-users', [
            'inactiveUsers' => $inactiveUsers,
            'daysInactive' => $daysInactive,
            'total' => count($inactiveUsers),
        ]);
    }

    /**
     * Reporte de intentos de login fallidos
     *
     * @param Request $request
     * @return View
     */
    public function failedLogins(Request $request): View
    {
        $days = (int) $request->input('days', 7);
        $failedLogins = $this->findFailedLogins($days);

        // Log de auditoría
        Log::info('Security report: Failed logins accessed', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'days' => $days,
            'failed_logins_count' => count($failedLogins),
            'timestamp' => now(),
        ]);

        return view('admin.security.failed-logins', [
            'failedLogins' => $failedLogins,
            'days' => $days,
            'total' => count($failedLogins),
        ]);
    }

    /**
     * Reporte general de seguridad
     *
     * @return View
     */
    public function securityReport(): View
    {
        $weakPasswords = $this->findWeakPasswords();
        $reusedPasswords = $this->findReusedPasswords();
        $inactiveUsers30 = $this->findInactiveUsers(30);
        $inactiveUsers90 = $this->findInactiveUsers(90);
        $inactiveUsers180 = $this->findInactiveUsers(180);

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'users_with_2fa' => User::where('totp_enabled', true)->whereNotNull('totp_secret')->count(),
            'total_auth_items' => VaultItem::where('type', 'auth')->where('status', '!=', 'deleted')->count(),
            'weak_passwords_count' => count($weakPasswords),
            'reused_passwords_count' => count($reusedPasswords),
            'inactive_30_days' => count($inactiveUsers30),
            'inactive_90_days' => count($inactiveUsers90),
            'inactive_180_days' => count($inactiveUsers180),
        ];

        // Log de auditoría
        Log::info('Security report: General security report accessed', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'stats' => $stats,
            'timestamp' => now(),
        ]);

        return view('admin.security.report', [
            'stats' => $stats,
            'weakPasswords' => array_slice($weakPasswords, 0, 10), // Primeros 10 para preview
            'reusedPasswords' => array_slice($reusedPasswords, 0, 10), // Primeros 10 para preview
        ]);
    }

    /**
     * Encontrar contraseñas débiles
     *
     * @return array<int, array<string, mixed>>
     */
    private function findWeakPasswords(): array
    {
        $weakPasswords = [];
        $commonPasswords = $this->getCommonPasswords();

        // Obtener todos los items de tipo 'auth' con secretos
        $authItems = VaultItem::where('type', 'auth')
            ->where('status', '!=', 'deleted')
            ->whereHas('secret')
            ->with(['owner', 'secret'])
            ->get();

        foreach ($authItems as $item) {
            try {
                // Descifrar el secreto
                $secretData = $this->cryptoService->decrypt($item->secret->ciphertext);

                if (!isset($secretData['password']) || empty($secretData['password'])) {
                    continue;
                }

                $password = $secretData['password'];
                $issues = $this->analyzePasswordStrength($password, $commonPasswords);

                if (!empty($issues)) {
                    $weakPasswords[] = [
                        'item_id' => $item->id,
                        'item_title' => $item->title,
                        'owner_id' => $item->owner_user_id,
                        'owner_name' => $item->owner->name,
                        'owner_email' => $item->owner->email,
                        'password_length' => strlen($password),
                        'issues' => $issues,
                        'created_at' => $item->created_at,
                    ];
                }
            } catch (\Exception $e) {
                // Si falla el descifrado, continuar con el siguiente
                continue;
            }
        }

        // Ordenar por número de issues (más críticos primero)
        usort($weakPasswords, function ($a, $b) {
            return count($b['issues']) <=> count($a['issues']);
        });

        return $weakPasswords;
    }

    /**
     * Encontrar contraseñas reutilizadas
     *
     * @return array<int, array<string, mixed>>
     */
    private function findReusedPasswords(): array
    {
        $passwordMap = [];
        $reusedPasswords = [];

        // Obtener todos los items de tipo 'auth' con secretos
        $authItems = VaultItem::where('type', 'auth')
            ->where('status', '!=', 'deleted')
            ->whereHas('secret')
            ->with(['owner', 'secret'])
            ->get();

        foreach ($authItems as $item) {
            try {
                // Descifrar el secreto
                $secretData = $this->cryptoService->decrypt($item->secret->ciphertext);

                if (!isset($secretData['password']) || empty($secretData['password'])) {
                    continue;
                }

                $password = $secretData['password'];
                $passwordHash = hash('sha256', $password);

                // Agrupar por hash de contraseña
                if (!isset($passwordMap[$passwordHash])) {
                    $passwordMap[$passwordHash] = [];
                }

                $passwordMap[$passwordHash][] = [
                    'item_id' => $item->id,
                    'item_title' => $item->title,
                    'owner_id' => $item->owner_user_id,
                    'owner_name' => $item->owner->name,
                    'owner_email' => $item->owner->email,
                    'created_at' => $item->created_at,
                ];
            } catch (\Exception $e) {
                // Si falla el descifrado, continuar con el siguiente
                continue;
            }
        }

        // Filtrar solo las que aparecen más de una vez
        foreach ($passwordMap as $hash => $items) {
            if (count($items) > 1) {
                $reusedPasswords[] = [
                    'password_hash' => substr($hash, 0, 16) . '...', // Solo mostrar prefijo por seguridad
                    'reuse_count' => count($items),
                    'items' => $items,
                ];
            }
        }

        // Ordenar por número de reutilizaciones (más críticos primero)
        usort($reusedPasswords, function ($a, $b) {
            return $b['reuse_count'] <=> $a['reuse_count'];
        });

        return $reusedPasswords;
    }

    /**
     * Encontrar usuarios inactivos
     *
     * @param int $daysInactive
     * @return array<int, array<string, mixed>>
     */
    private function findInactiveUsers(int $daysInactive): array
    {
        $cutoffTimestamp = now()->subDays($daysInactive)->timestamp;

        // Obtener usuarios activos
        $users = User::where('is_active', true)
            ->withCount(['vaultItems' => function ($query) {
                $query->where('status', '!=', 'deleted');
            }])
            ->get();

        $inactiveUsers = [];

        foreach ($users as $user) {
            // Obtener la última actividad de la tabla sessions
            $lastActivity = DB::table('sessions')
                ->where('user_id', $user->id)
                ->max('last_activity');

            // Si no tiene sesiones o la última actividad es anterior al cutoff
            if (!$lastActivity || $lastActivity < $cutoffTimestamp) {
                $lastLoginAt = $lastActivity ? \Carbon\Carbon::createFromTimestamp($lastActivity) : null;
                $daysInactiveCount = $lastLoginAt ? now()->diffInDays($lastLoginAt) : null;

                $inactiveUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'last_login_at' => $lastLoginAt?->format('Y-m-d H:i:s'),
                    'days_inactive' => $daysInactiveCount,
                    'created_at' => $user->created_at,
                    'vault_items_count' => $user->vault_items_count,
                    'has_2fa' => $user->totp_enabled && $user->totp_secret,
                ];
            }
        }

        // Ordenar por días inactivos (más inactivos primero)
        usort($inactiveUsers, function ($a, $b) {
            $aDays = $a['days_inactive'] ?? 999999;
            $bDays = $b['days_inactive'] ?? 999999;
            return $bDays <=> $aDays;
        });

        return $inactiveUsers;
    }

    /**
     * Analizar fortaleza de una contraseña
     *
     * @param string $password
     * @param array<string> $commonPasswords
     * @return array<string>
     */
    private function analyzePasswordStrength(string $password, array $commonPasswords): array
    {
        $issues = [];

        // Longitud mínima
        if (strlen($password) < 8) {
            $issues[] = 'Muy corta (menos de 8 caracteres)';
        }

        // Longitud recomendada
        if (strlen($password) < 12) {
            $issues[] = 'Corta (menos de 12 caracteres recomendados)';
        }

        // Falta de mayúsculas
        if (!preg_match('/[A-Z]/', $password)) {
            $issues[] = 'Falta mayúscula';
        }

        // Falta de minúsculas
        if (!preg_match('/[a-z]/', $password)) {
            $issues[] = 'Falta minúscula';
        }

        // Falta de números
        if (!preg_match('/[0-9]/', $password)) {
            $issues[] = 'Falta número';
        }

        // Falta de caracteres especiales
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $issues[] = 'Falta carácter especial';
        }

        // Contraseña común
        if (in_array(strtolower($password), $commonPasswords)) {
            $issues[] = 'Contraseña común';
        }

        // Solo números o solo letras
        if (ctype_digit($password) || ctype_alpha($password)) {
            $issues[] = 'Solo números o solo letras';
        }

        return $issues;
    }

    /**
     * Encontrar intentos de login fallidos
     *
     * @param int $days
     * @return array<int, array<string, mixed>>
     */
    private function findFailedLogins(int $days): array
    {
        $cutoffDate = now()->subDays($days);
        $failedLogins = [];

        // Leer logs de Laravel (archivo storage/logs/laravel.log)
        $logPath = storage_path('logs/laravel.log');
        
        if (!file_exists($logPath)) {
            return [];
        }

        // Leer las últimas líneas del log (últimas 10000 líneas para no sobrecargar)
        $lines = file($logPath);
        if ($lines === false) {
            return [];
        }

        // Procesar líneas en reversa (más recientes primero)
        $recentLines = array_slice($lines, -10000);
        $recentLines = array_reverse($recentLines);

        foreach ($recentLines as $line) {
            // Buscar líneas que contengan "Failed login attempt"
            if (strpos($line, 'Failed login attempt') === false) {
                continue;
            }

            // Extraer información del log
            // Formato esperado: [timestamp] local.WARNING: Failed login attempt: ...
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*Failed login attempt: (.*)/', $line, $matches)) {
                $logDate = \Carbon\Carbon::parse($matches[1]);
                
                // Solo incluir logs dentro del rango de días
                if ($logDate->lt($cutoffDate)) {
                    continue;
                }

                // Extraer JSON del log si está presente
                $jsonMatch = [];
                if (preg_match('/\{.*\}/', $line, $jsonMatch)) {
                    $logData = json_decode($jsonMatch[0], true);
                    if ($logData) {
                        $failedLogins[] = [
                            'timestamp' => $logDate,
                            'email' => $logData['email'] ?? 'N/A',
                            'user_id' => $logData['user_id'] ?? null,
                            'ip_address' => $logData['ip_address'] ?? 'N/A',
                            'user_agent' => $logData['user_agent'] ?? 'N/A',
                            'reason' => $logData['reason'] ?? 'unknown',
                            'user_exists' => $logData['user_exists'] ?? null,
                        ];
                    }
                } else {
                    // Si no hay JSON, extraer información básica
                    $emailMatch = [];
                    if (preg_match('/email["\']?\s*[:=]\s*["\']?([^"\',\s]+)/', $line, $emailMatch)) {
                        $failedLogins[] = [
                            'timestamp' => $logDate,
                            'email' => $emailMatch[1],
                            'user_id' => null,
                            'ip_address' => 'N/A',
                            'user_agent' => 'N/A',
                            'reason' => 'unknown',
                            'user_exists' => null,
                        ];
                    }
                }
            }
        }

        // Ordenar por timestamp (más recientes primero)
        usort($failedLogins, function ($a, $b) {
            return $b['timestamp']->timestamp <=> $a['timestamp']->timestamp;
        });

        // Limitar a los últimos 500 intentos para no sobrecargar
        return array_slice($failedLogins, 0, 500);
    }

    /**
     * Obtener lista de contraseñas comunes
     *
     * @return array<string>
     */
    private function getCommonPasswords(): array
    {
        return [
            'password',
            '123456',
            '12345678',
            '123456789',
            '1234567890',
            'qwerty',
            'abc123',
            'password1',
            'admin',
            'letmein',
            'welcome',
            'monkey',
            '1234567',
            'dragon',
            'master',
            'sunshine',
            'princess',
            'football',
            'shadow',
            'superman',
        ];
    }
}
