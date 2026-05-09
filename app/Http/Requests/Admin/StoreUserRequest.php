<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Services\UserManagementService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para crear usuario manualmente (org admin).
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOrgAdmin() ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return app(UserManagementService::class)->createUserManuallyRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }
}
