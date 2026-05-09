<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVaultItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se manejará en el controlador/policy
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir exp_month y exp_year a enteros si están presentes y son strings numéricos
        if ($this->has('exp_month') && $this->exp_month !== '' && is_numeric($this->exp_month)) {
            $this->merge([
                'exp_month' => (int) $this->exp_month,
            ]);
        }

        if ($this->has('exp_year') && $this->exp_year !== '' && is_numeric($this->exp_year)) {
            $this->merge([
                'exp_year' => (int) $this->exp_year,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $type = $this->input('type');
        
        $rules = [
            'type' => ['required', 'string', Rule::in(['auth', 'note', 'card', 'api_key', 'ssh_key', 'env_file'])],
            'title' => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', 'integer', 'exists:folders,id'],
            'favorite' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];

        // Reglas específicas por tipo
        if ($type === 'auth') {
            $rules['username'] = ['nullable', 'string', 'max:255'];
            $rules['password'] = ['nullable', 'string', 'max:500'];
            $rules['uri'] = ['nullable', 'array'];
            $rules['uri.*'] = ['nullable', 'url', 'max:500'];
            $rules['totp_secret'] = ['nullable', 'string', 'max:255'];
        } elseif ($type === 'card') {
            $rules['cardholder_name'] = ['nullable', 'string', 'max:255'];
            $rules['card_number'] = ['nullable', 'string', 'max:19'];
            $rules['brand'] = ['nullable', 'string', 'max:50'];
            $rules['exp_month'] = ['nullable', 'integer', 'min:1', 'max:12'];
            $rules['exp_year'] = ['nullable', 'integer', 'min:1900', 'max:2100'];
            $rules['security_code'] = ['nullable', 'string', 'max:4'];
        } elseif ($type === 'api_key') {
            $rules['api_key'] = ['nullable', 'string', 'max:500'];
            $rules['host'] = ['nullable', 'string', 'max:500'];
        } elseif ($type === 'ssh_key') {
            $rules['public_key'] = ['nullable', 'string', 'max:2000'];
            $rules['private_key'] = ['nullable', 'string', 'max:5000'];
            $rules['passphrase'] = ['nullable', 'string', 'max:500'];
            $rules['host'] = ['nullable', 'string', 'max:500'];
        } elseif ($type === 'env_file') {
            $rules['env_content'] = ['required', 'string', 'max:50000'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de item es requerido.',
            'type.in' => 'El tipo de item no es válido.',
            'title.required' => 'El título es requerido.',
            'title.max' => 'El título no puede exceder 255 caracteres.',
            'username.required_if' => 'El nombre de usuario es requerido para items de tipo auth.',
            'password.required_if' => 'La contraseña es requerida para items de tipo auth.',
        ];
    }
}
