<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurgicalKitTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:surgical_kit_templates,code',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Oye, necesitas ponerle un nombre a esta receta de kit.',
            'name.max' => 'El nombre está muy largo, trata de resumirlo un poco.',
            'code.unique' => 'Este código ya está siendo usado por otro kit quirúrgico. Intenta con uno nuevo.',
        ];
    }
}
