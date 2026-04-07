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

    protected function prepareForValidation()
    {
        // 1. Obtenemos el valor crudo que envió el formulario
        $rawProductId = $this->input('product_id');

        if ($rawProductId) {
            // 2. Si viene con el prefijo (ej. 'prod_15'), lo limpiamos
            if (str_contains($rawProductId, '_')) {
                $parts = explode('_', $rawProductId);
                $cleanId = end($parts);
                
                // 3. Forzamos a Laravel a reemplazar el dato viejo por el limpio
                $this->merge([
                    'product_id' => $cleanId
                ]);
            }
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    
    public function rules(): array
    {
        //dd($this->all());
        return [
            'surgical_kit_template_id' => 'required|exists:surgical_kit_templates,id',
            'product_id'               => 'required|exists:products,id',
            'quantity'                 => 'required|integer|min:1',
        ];
    }
    public function messages(): array
    {
        return [
            'product_id.required' => 'Por favor, selecciona un artículo de la lista.',
            'product_id.exists'   => 'El artículo seleccionado no es válido o no existe.',
            'quantity.required'   => 'La cantidad es obligatoria.',
            'quantity.min'        => 'La cantidad debe ser al menos 1.',
        ];
    }


}
