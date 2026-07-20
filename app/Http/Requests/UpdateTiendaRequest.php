<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use App\Models\Tienda;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTiendaRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Admin con editarTiendas: cualquier tienda.
     * Vendedor con editarTiendas: solo su propia tienda (ownership).
     */
    public function authorize(): bool
    {
        if (! $this->allowIfCan('editarTiendas')) {
            return false;
        }

        $user = $this->user('api');
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        /** @var mixed $tienda */
        $tienda = $this->route('tienda');
        if (! $tienda instanceof Tienda) {
            return false;
        }

        $user->loadMissing('vendedor');
        $tiendaId = $user->vendedor?->tienda_id;
        if (! $tiendaId) {
            return false;
        }

        return (int) $tiendaId === (int) $tienda->id;
    }

    /**
     * Campos mínimos de perfil de tienda (sin branding/imágenes).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:5000'],
            // RFC no se expone en la UI de vendedor (dato fiscal sensible).
            'rfc_moral' => ['sometimes', 'string', 'max:13'],
        ];
    }
}
