<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrganizationRequest;
use App\Http\Requests\Admin\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Services\AuditService;
use App\Services\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controlador para gestión de organizaciones (solo platform super admin).
 */
class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationService $organizationService,
        private AuditService $auditService
    ) {}

    /**
     * Listar organizaciones.
     */
    public function index(): View
    {
        $organizations = Organization::withCount('users')->latest()->paginate(15);

        return view('admin.organizations.index', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Formulario para crear organización.
     */
    public function create(): View
    {
        return view('admin.organizations.create');
    }

    /**
     * Crear organización y super admin de la org. Muestra credenciales una sola vez.
     */
    public function store(StoreOrganizationRequest $request): View
    {
        $result = $this->organizationService->createOrganization(
            $request->validated(),
            $request->user()
        );

        Log::info('Organización y super admin creados', [
            'admin_id' => $request->user()->id,
            'admin_email' => $request->user()->email,
            'organization_id' => $result['organization']->id,
            'organization_name' => $result['organization']->name,
            'org_admin_user_id' => $result['admin']->id,
            'org_admin_email' => $result['email'],
            'timestamp' => now(),
        ]);

        return view('admin.organizations.created', $result);
    }

    /**
     * Regenerar contraseña temporal del super admin de la organización.
     */
    public function regeneratePassword(Request $request, Organization $organization): View
    {
        $result = $this->organizationService->regenerateTemporaryPassword(
            $organization,
            $request->user()
        );

        Log::info('Contraseña temporal regenerada para organización', [
            'admin_id' => $request->user()->id,
            'organization_id' => $organization->id,
            'org_admin_user_id' => $result['admin']->id,
            'timestamp' => now(),
        ]);

        $this->auditService->log(
            'organization_password_regenerated',
            'Organization',
            (int) $organization->id,
            ['org_admin_user_id' => $result['admin']->id, 'org_admin_email' => $result['email']],
            $request,
            ['organization_id' => $organization->id]
        );

        return view('admin.organizations.password-regenerated', $result);
    }

    /**
     * Ver detalle de organización.
     */
    public function show(Organization $organization): View
    {
        $organization->loadCount('users');
        $organization->load('users');

        return view('admin.organizations.show', [
            'organization' => $organization,
        ]);
    }

    /**
     * Formulario para editar organización.
     */
    public function edit(Organization $organization): View
    {
        return view('admin.organizations.edit', [
            'organization' => $organization,
        ]);
    }

    /**
     * Actualizar organización.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $organization->update($request->validated());

        Log::info('Organización actualizada', [
            'admin_id' => $request->user()->id,
            'admin_email' => $request->user()->email,
            'organization_id' => $organization->id,
            'timestamp' => now(),
        ]);

        return redirect()
            ->route('admin.organizations.show', $organization)
            ->with('success', 'Organización actualizada correctamente.');
    }
}
