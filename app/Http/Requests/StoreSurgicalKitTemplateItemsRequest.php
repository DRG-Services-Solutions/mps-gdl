<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurgicalKitTemplateItemsRequest extends FormRequest
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
            'surgical_kit_template_id' => 'required|exists:surgical_kit_templates,id',
            'product_id'               => 'required|exists:products,id',
            'quantity_required'        => 'required|integer|min:1',
        ];
    }
}
