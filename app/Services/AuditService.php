<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de auditoría
 * 
 * Encapsula la lógica de negocio para el registro y consulta de logs de auditoría
 */
class AuditService
{
    /**
     * Registrar un evento de auditoría
     *
     * @param string $action Acción realizada (create, update, delete, view, share, unshare)
     * @param string $modelType Tipo de modelo (App\Models\VaultItem, etc.)
     * @param int|null $modelId ID del modelo relacionado
     * @param array<string, mixed>|null $changes Cambios realizados (antes/después)
     * @param Request|null $request Request para obtener IP y User-Agent
     * @param array<string, mixed>|null $meta Información adicional
     * @return AuditLog
     */
    public function log(
        string $action,
        string $modelType,
        ?int $modelId = null,
        ?array $changes = null,
        ?Request $request = null,
        ?array $meta = null
    ): AuditLog {
        $userId = auth()->id();
        $organizationId = null;
        if (is_array($meta) && array_key_exists('organization_id', $meta)) {
            $organizationId = $meta['organization_id'];
            unset($meta['organization_id']);
            $meta = $meta === [] ? null : $meta;
        }
        if ($organizationId === null && $userId) {
            $organizationId = auth()->user()?->organization_id;
        }

        return AuditLog::create([
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'changes' => $changes,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'meta' => $meta,
        ]);
    }

    /**
     * Obtener logs con filtros opcionales
     *
     * @param array<string, mixed> $filters Filtros (user_id, action, model_type, start_date, end_date)
     * @param int $perPage Items por página
     * @return LengthAwarePaginator<AuditLog>
     */
    public function getLogs(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filtrar por usuario
        if (isset($filters['user_id'])) {
            $query->forUser((int) $filters['user_id']);
        }

        // Filtrar por acción
        if (isset($filters['action'])) {
            $query->forAction($filters['action']);
        }

        // Filtrar por tipo de modelo
        if (isset($filters['model_type'])) {
            $query->forModelType($filters['model_type']);
        }

        // Filtrar por rango de fechas
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        } elseif (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        } elseif (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['organization_id'])) {
            $query->forOrganization((int) $filters['organization_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Obtener logs sin paginación (para exportación)
     *
     * @param array<string, mixed> $filters Filtros
     * @return Collection<int, AuditLog>
     */
    public function filterLogs(array $filters = []): Collection
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        if (isset($filters['user_id'])) {
            $query->forUser((int) $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->forAction($filters['action']);
        }

        if (isset($filters['model_type'])) {
            $query->forModelType($filters['model_type']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        } elseif (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        } elseif (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['organization_id'])) {
            $query->forOrganization((int) $filters['organization_id']);
        }

        return $query->get();
    }

    /**
     * Exportar logs a CSV
     *
     * @param array<string, mixed> $filters Filtros
     * @return string Contenido CSV
     */
    public function exportCsv(array $filters = []): string
    {
        $logs = $this->filterLogs($filters);

        $csv = fopen('php://temp', 'r+');
        
        // Encabezados
        fputcsv($csv, [
            'ID',
            'Fecha',
            'Usuario',
            'Acción',
            'Tipo de Modelo',
            'ID del Modelo',
            'IP',
            'User Agent',
        ]);

        // Datos
        foreach ($logs as $log) {
            fputcsv($csv, [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user?->email ?? 'Sistema',
                $log->action,
                $log->model_type,
                $log->model_id ?? '',
                $log->ip_address ?? '',
                $log->user_agent ?? '',
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return $content;
    }

    /**
     * Obtener estadísticas de auditoría
     *
     * @param array<string, mixed> $filters Filtros opcionales
     * @return array<string, mixed>
     */
    public function getStats(array $filters = []): array
    {
        $query = AuditLog::query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['organization_id'])) {
            $query->forOrganization((int) $filters['organization_id']);
        }

        return [
            'total' => $query->count(),
            'by_action' => $query->clone()
                ->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
            'by_model_type' => $query->clone()
                ->select('model_type', DB::raw('count(*) as count'))
                ->groupBy('model_type')
                ->pluck('count', 'model_type')
                ->toArray(),
        ];
    }
}
