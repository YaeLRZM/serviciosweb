<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->allowIfCan('crearCategorias');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ];
    }
}
