<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use App\Models\Articulo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreImagenArticuloRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Vendedor dueño de la tienda del artículo, o admin.
     * Reutiliza permiso editarArticulos (misma capacidad de gestión de productos).
     */
    public function authorize(): bool
    {
        if (! $this->allowIfCan('editarArticulos') && ! $this->allowIfCan('crearArticulos')) {
            return false;
        }

        $user = $this->user('api');
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        $articuloId = (int) $this->input('articulo_id');
        if ($articuloId <= 0) {
            return false;
        }

        $articulo = Articulo::query()->find($articuloId);
        if (! $articulo) {
            return false;
        }

        $user->loadMissing('vendedor');
        $tiendaId = $user->vendedor?->tienda_id;

        return $tiendaId && (int) $tiendaId === (int) $articulo->tienda_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'articulo_id' => ['required', 'integer', 'exists:articulos,id'],
            'url' => ['required', 'string', 'url', 'max:2000'],
            'es_principal' => ['sometimes', 'boolean'],
        ];
    }
}
