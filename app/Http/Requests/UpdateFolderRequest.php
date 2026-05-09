<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se manejará en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:1'],
            'parent_id' => ['nullable', 'integer', 'exists:folders,id'],
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
            'name.required' => 'El nombre de la carpeta es obligatorio.',
            'name.min' => 'El nombre de la carpeta no puede estar vacío.',
            'name.max' => 'El nombre de la carpeta no puede exceder 255 caracteres.',
            'parent_id.exists' => 'La carpeta padre seleccionada no existe.',
        ];
    }
}
