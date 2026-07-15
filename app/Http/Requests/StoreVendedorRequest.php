<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVendedorRequest extends FormRequest
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
        return [
            'tienda_id' => ['required', 'exists:tiendas,id'],
            'user_id' => ['required', 'exists:users,id'],
            'codigo_ine' => ['required', 'string', 'max:13', 'unique:vendedors,codigo_ine'],
            'foto_frontal_ine_link' => ['required', 'string', 'max:100'],
            'foto_trasera_ine_link' => ['required', 'string', 'max:100'],
            'estatus' => ['required', 'string', 'max:100'],
        ];
    }
}
