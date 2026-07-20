<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use App\Models\Articulo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticuloRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Vendedor solo edita artículos de su tienda; admin con permiso puede todo.
     */
    public function authorize(): bool
    {
        if (! $this->allowIfCan('editarArticulos')) {
            return false;
        }

        $user = $this->user('api');
        if (! $user) {
            return false;
        }

        // Admin web/API con rol admin: sin restricción de tienda.
        if ($user->hasRole('admin')) {
            return true;
        }

        /** @var Articulo $articulo */
        $articulo = $this->route('articulo');
        if (! $articulo instanceof Articulo) {
            return false;
        }

        $user->loadMissing('vendedor');
        $tiendaId = $user->vendedor?->tienda_id;
        if (! $tiendaId) {
            return false;
        }

        return (int) $articulo->tienda_id === (int) $tiendaId;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Partial update: el vendedor puede mandar solo algunos campos.
        return [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'disponible' => ['sometimes', 'boolean'],
            'color' => ['sometimes', 'string', 'max:255'],
            'tela' => ['sometimes', 'string', 'max:255'],
            'bordado' => ['sometimes', 'string', 'max:255'],
            'talla' => ['sometimes', 'string', 'max:50'],
            'region' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
