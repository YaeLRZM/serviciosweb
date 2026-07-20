<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreResenaRequest extends FormRequest
{
    use AuthorizesApiPermission;

    public function authorize(): bool
    {
        return $this->allowIfCan('crearResenas');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Alineado al modelo/migración: articulo_id, calificacion, comentario.
        return [
            'articulo_id' => ['required', 'integer', 'exists:articulos,id'],
            'calificacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
