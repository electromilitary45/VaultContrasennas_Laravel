<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InvitationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador para códigos de invitación (solo org admins).
 */
class InvitationCodeController extends Controller
{
    public function __construct(
        private InvitationCodeService $invitationCodeService
    ) {}

    /**
     * Listar códigos de la organización del usuario.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $organization = $user->organization;

        $codes = $organization->invitationCodes()
            ->with('createdBy', 'usedBy')
            ->latest()
            ->paginate(15);

        return view('admin.invitation-codes.index', [
            'codes' => $codes,
            'newCode' => session('invitation_code_new'),
            'newCodeUrl' => session('invitation_code_url'),
        ]);
    }

    /**
     * Generar un nuevo código de invitación.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $organization = $user->organization;

        $code = $this->invitationCodeService->generateForOrganization($organization, $user);

        return redirect()
            ->route('admin.invitation-codes.index')
            ->with('invitation_code_new', $code->code)
            ->with('invitation_code_url', route('register', ['code' => $code->code]));
    }
}
