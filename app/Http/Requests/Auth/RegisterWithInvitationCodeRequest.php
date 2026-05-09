<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Services\InvitationCodeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Form Request para registro con código de invitación.
 */
class RegisterWithInvitationCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $service = app(InvitationCodeService::class);

        return [
            'invitation_code' => [
                'required',
                'string',
                'max:64',
                function (string $attribute, mixed $value, \Closure $fail) use ($service): void {
                    if (!$service->findValidUnusedCode((string) $value)) {
                        $fail('El código de invitación no es válido o ya fue utilizado.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'invitation_code.required' => 'El código de invitación es obligatorio.',
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }
}
