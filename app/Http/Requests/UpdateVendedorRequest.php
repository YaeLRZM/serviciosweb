<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendedorRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vendedor = $this->route('vendedor');

        return [
            'tienda_id' => ['sometimes', 'required', 'exists:tiendas,id'],
            'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'codigo_ine' => [
                'sometimes',
                'required',
                'string',
                'max:13',
                Rule::unique('vendedors', 'codigo_ine')->ignore($vendedor?->id),
            ],
            'foto_frontal_ine_link' => ['sometimes', 'required', 'string', 'max:100'],
            'foto_trasera_ine_link' => ['sometimes', 'required', 'string', 'max:100'],
            'estatus' => ['sometimes', 'required', 'string', 'max:100'],
        ];
    }
}
