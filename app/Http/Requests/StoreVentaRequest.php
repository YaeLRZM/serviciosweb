<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Compra mínima: usuario autenticado con permiso crearCompras
     * (rol user lo tiene; admin también vía admin permissions).
     * user_id / total / tienda_id / estado NO se aceptan del cliente.
     */
    public function authorize(): bool
    {
        $user = $this->user('api');
        if (! $user) {
            return false;
        }

        // Comprador normal o admin. Vendedor también puede comprar como user
        // si tiene el permiso (roles vendedor lo incluyen en seed).
        return $this->allowIfCan('crearCompras')
            || $user->hasRole('admin')
            || $user->hasRole('user');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.articulo_id' => ['required', 'integer', 'exists:articulos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:999'],
            // Opcional: si no viene, el controller elige el primer forma_pago.
            'forma_pago_id' => ['sometimes', 'nullable', 'integer', 'exists:forma_pagos,id'],
            // Simulación: tarjeta | efectivo (legacy sin campo sigue funcionando).
            'metodo_pago' => ['sometimes', 'nullable', 'string', 'in:tarjeta,efectivo'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Debes enviar al menos un artículo.',
            'items.*.articulo_id.exists' => 'Uno de los artículos no existe.',
            'items.*.cantidad.min' => 'La cantidad mínima por artículo es 1.',
        ];
    }
}
