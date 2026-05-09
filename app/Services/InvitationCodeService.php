<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationInvitationCode;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para códigos de invitación.
 *
 * Generación y validación de códigos para registro en una organización.
 */
class InvitationCodeService
{
    /**
     * Genera un código de invitación para la organización.
     *
     * @return OrganizationInvitationCode
     */
    public function generateForOrganization(Organization $organization, User $createdBy): OrganizationInvitationCode
    {
        $code = OrganizationInvitationCode::create([
            'organization_id' => $organization->id,
            'code' => OrganizationInvitationCode::generateUniqueCode(),
            'created_by_user_id' => $createdBy->id,
        ]);

        Log::info('Código de invitación generado', [
            'created_by_user_id' => $createdBy->id,
            'created_by_email' => $createdBy->email,
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'code_id' => $code->id,
            'timestamp' => now(),
        ]);

        return $code;
    }

    /**
     * Busca un código válido (existe, no usado, org existe). Retorna el modelo o null.
     */
    public function findValidUnusedCode(string $code): ?OrganizationInvitationCode
    {
        $inv = OrganizationInvitationCode::unused()
            ->where('code', $code)
            ->with('organization')
            ->first();

        if (!$inv || !$inv->organization) {
            return null;
        }

        return $inv;
    }
}
