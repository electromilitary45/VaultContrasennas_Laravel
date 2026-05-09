<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controlador de auditoría
 * 
 * Maneja la visualización y exportación de logs de auditoría
 */
class AuditController extends Controller
{
    /**
     * Constructor con inyección de dependencias
     */
    public function __construct(
        private AuditService $auditService
    ) {
        // El middleware 'auth' y 'admin' se aplica en las rutas
    }

    /**
     * Mostrar lista de logs de auditoría
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['user_id', 'action', 'model_type', 'start_date', 'end_date']);

        if ($request->user()->isOrgAdmin()) {
            $filters['organization_id'] = $request->user()->organization_id;
        }

        $logs = $this->auditService->getLogs($filters, 25);

        $stats = $this->auditService->getStats($filters);

        $usersQuery = \App\Models\User::orderBy('name');
        if ($request->user()->isOrgAdmin()) {
            $usersQuery->where('organization_id', $request->user()->organization_id);
        }
        $users = $usersQuery->get();
        
        // Acciones disponibles
        $actions = [
            'create' => 'Crear',
            'update' => 'Actualizar',
            'delete' => 'Eliminar',
            'view' => 'Ver',
            'share' => 'Compartir',
            'unshare' => 'Dejar de compartir',
        ];
        
        // Tipos de modelos
        $modelTypes = [
            \App\Models\VaultItem::class => 'Vault Item',
            \App\Models\Group::class => 'Grupo',
        ];

        return view('audit.index', [
            'logs' => $logs,
            'stats' => $stats,
            'users' => $users,
            'actions' => $actions,
            'modelTypes' => $modelTypes,
            'filters' => $filters,
        ]);
    }

    /**
     * Exportar logs a CSV
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['user_id', 'action', 'model_type', 'start_date', 'end_date']);

        if ($request->user()->isOrgAdmin()) {
            $filters['organization_id'] = $request->user()->organization_id;
        }

        $csvContent = $this->auditService->exportCsv($filters);
        
        $filename = 'audit_logs_' . date('Y-m-d_His') . '.csv';
        
        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
