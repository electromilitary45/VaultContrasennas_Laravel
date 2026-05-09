<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controlador de Notificaciones
 * 
 * Maneja la visualización y gestión de notificaciones del usuario.
 */
class NotificationController extends Controller
{
    /**
     * Constructor con inyección de dependencias
     */
    public function __construct(
        private NotificationService $notificationService
    ) {
        // El middleware 'auth' se aplica en las rutas
    }

    /**
     * Mostrar lista de notificaciones agrupadas por fecha.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $organizationId = $user->isPlatformSuperAdmin() ? null : $user->organization_id;
        
        $unreadOnly = $request->boolean('unread_only', false);
        
        $notifications = $this->notificationService->getNotifications(
            $user,
            $organizationId,
            limit: 100,
            unreadOnly: $unreadOnly
        );

        // Agrupar por fecha
        $grouped = $notifications->groupBy(function ($notification) {
            $date = $notification->created_at;
            $now = now();
            
            if ($date->isToday()) {
                return 'Hoy';
            } elseif ($date->isYesterday()) {
                return 'Ayer';
            } elseif ($date->isCurrentWeek()) {
                return 'Esta semana';
            } elseif ($date->isCurrentMonth()) {
                return 'Este mes';
            } else {
                return $date->format('F Y');
            }
        });

        $unreadCount = $this->notificationService->getUnreadCount($user, $organizationId);

        return view('notifications.index', [
            'groupedNotifications' => $grouped,
            'unreadCount' => $unreadCount,
            'unreadOnly' => $unreadOnly,
        ]);
    }

    /**
     * Obtener contador de notificaciones no leídas (AJAX).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizationId = $user->isPlatformSuperAdmin() ? null : $user->organization_id;
        
        $count = $this->notificationService->getUnreadCount($user, $organizationId);

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Marcar una notificación como leída.
     *
     * @param Request $request
     * @param Notification $notification
     * @return RedirectResponse|JsonResponse
     */
    public function markAsRead(Request $request, Notification $notification): RedirectResponse|JsonResponse
    {
        // Verificar que la notificación pertenece al usuario autenticado
        if ($notification->user_id !== $request->user()->id) {
            abort(403, 'No tienes permisos para esta acción.');
        }

        $this->notificationService->markAsRead($notification);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída.',
            ]);
        }

        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }

    /**
     * Marcar todas las notificaciones como leídas.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $organizationId = $user->isPlatformSuperAdmin() ? null : $user->organization_id;
        
        $count = $this->notificationService->markAllAsRead($user, $organizationId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} notificaciones marcadas como leídas.",
                'count' => $count,
            ]);
        }

        return redirect()->back()->with('success', "{$count} notificaciones marcadas como leídas.");
    }
}
