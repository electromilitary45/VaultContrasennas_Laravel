<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupAccessRequest;
use App\Services\GroupAccessRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controlador para gestionar solicitudes de acceso a grupos.
 */
class GroupAccessRequestController extends Controller
{
    public function __construct(
        private GroupAccessRequestService $accessRequestService
    ) {
    }

    /**
     * Crear una solicitud de acceso a un grupo.
     */
    public function store(Request $request, Group $group): RedirectResponse
    {
        $user = Auth::user();

        // Verificar que el grupo pertenezca a la organización del usuario
        if ($user->organization_id !== null && $group->organization_id !== $user->organization_id) {
            abort(403, 'No tienes permisos para solicitar acceso a este grupo.');
        }

        // Verificar que el usuario no sea el propietario
        if ($group->owner_user_id === $user->id) {
            return redirect()
                ->back()
                ->with('error', 'Eres el propietario de este grupo.');
        }

        // Verificar que el usuario no sea ya miembro activo
        $isMember = $group->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($isMember) {
            return redirect()
                ->back()
                ->with('error', 'Ya eres miembro de este grupo.');
        }

        try {
            $message = $request->input('message');

            $this->accessRequestService->createRequest($group, $user, $message);

            return redirect()
                ->route('groups.index', ['tab' => 'available'])
                ->with('success', 'Solicitud de acceso enviada. Recibirás una notificación cuando sea respondida.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al crear la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Aceptar una solicitud de acceso.
     */
    public function accept(Request $request, Group $group, GroupAccessRequest $accessRequest): RedirectResponse
    {
        $user = Auth::user();

        // Verificar que la solicitud pertenezca al grupo
        if ($accessRequest->group_id !== $group->id) {
            abort(404, 'Solicitud no encontrada.');
        }

        // Verificar permisos (solo owner/admin del grupo)
        $userRole = $group->getUserRole($user);
        if (!in_array($userRole, ['owner', 'admin'])) {
            abort(403, 'No tienes permisos para aceptar solicitudes de este grupo.');
        }

        try {
            $this->accessRequestService->acceptRequest($accessRequest, $user);

            return redirect()
                ->back()
                ->with('success', 'Solicitud de acceso aceptada. El usuario ahora es miembro del grupo.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al aceptar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar una solicitud de acceso.
     */
    public function reject(Request $request, Group $group, GroupAccessRequest $accessRequest): RedirectResponse
    {
        $user = Auth::user();

        // Verificar que la solicitud pertenezca al grupo
        if ($accessRequest->group_id !== $group->id) {
            abort(404, 'Solicitud no encontrada.');
        }

        // Verificar permisos (solo owner/admin del grupo)
        $userRole = $group->getUserRole($user);
        if (!in_array($userRole, ['owner', 'admin'])) {
            abort(403, 'No tienes permisos para rechazar solicitudes de este grupo.');
        }

        try {
            $this->accessRequestService->rejectRequest($accessRequest, $user);

            return redirect()
                ->back()
                ->with('success', 'Solicitud de acceso rechazada.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al rechazar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Lista de solicitudes pendientes del usuario.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $pendingRequests = $this->accessRequestService->getPendingRequestsForUser($user);

        return view('groups.access-requests.index', [
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * Lista de solicitudes pendientes de un grupo (para owner/admin).
     */
    public function pendingForGroup(Group $group): View
    {
        $user = Auth::user();

        // Verificar permisos
        $userRole = $group->getUserRole($user);
        if (!in_array($userRole, ['owner', 'admin'])) {
            abort(403, 'No tienes permisos para ver las solicitudes de este grupo.');
        }

        $pendingRequests = $this->accessRequestService->getPendingRequestsForGroup($group);

        return view('groups.access-requests.pending', [
            'group' => $group,
            'pendingRequests' => $pendingRequests,
        ]);
    }
}
