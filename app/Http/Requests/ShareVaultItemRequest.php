<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareVaultItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se manejará en el controlador/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'share_type' => ['required', 'string', Rule::in(['user', 'group'])],
            'user_id' => [
                'required_if:share_type,user',
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'group_id' => [
                'required_if:share_type,group',
                'nullable',
                'integer',
                'exists:groups,id',
            ],
            'permission' => [
                'required',
                'string',
                Rule::in(['view', 'edit', 'admin']),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'share_type.required' => 'Debes especificar el tipo de compartir (user o group).',
            'share_type.in' => 'El tipo de compartir debe ser "user" o "group".',
            'user_id.required_if' => 'Debes seleccionar un usuario cuando compartes con usuario.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'group_id.required_if' => 'Debes seleccionar un grupo cuando compartes con grupo.',
            'group_id.exists' => 'El grupo seleccionado no existe.',
            'permission.required' => 'Debes especificar un permiso.',
            'permission.in' => 'El permiso debe ser: view, edit o admin.',
        ];
    }
}
