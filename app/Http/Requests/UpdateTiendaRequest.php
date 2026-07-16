<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTiendaRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->allowIfCan('editarTiendas');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string|nullable',
            'rfc_moral' => 'sometimes|string|max:12',
        ];
    }
}
