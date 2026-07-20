<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticuloRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Solo quien tiene crearArticulos y (vendedor con tienda o admin).
     */
    public function authorize(): bool
    {
        if (! $this->allowIfCan('crearArticulos')) {
            return false;
        }

        $user = $this->user('api');
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        $user->loadMissing('vendedor');

        return (bool) $user->vendedor?->tienda_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'color' => ['required', 'string', 'max:255'],
            'tela' => ['required', 'string', 'max:255'],
            'bordado' => ['required', 'string', 'max:255'],
            'disponible' => ['sometimes', 'boolean'],
            // Opcionales con default en el controller:
            'talla' => ['sometimes', 'string', 'max:50'],
            'region' => ['sometimes', 'string', 'max:255'],
            'artesano_id' => ['sometimes', 'integer', 'exists:artesanos,id'],
            // tienda_id NO se acepta del cliente para vendedor (se fuerza en store).
        ];
    }
}
