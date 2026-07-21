<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use App\Models\Resena;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResenaRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Solo el autor de la reseña (o admin) con permiso editarResenas.
     */
    public function authorize(): bool
    {
        if (! $this->allowIfCan('editarResenas')) {
            return false;
        }

        $user = $this->user('api');
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        /** @var mixed $resena */
        $resena = $this->route('resena');
        if (! $resena instanceof Resena) {
            return false;
        }

        return (int) $resena->user_id === (int) $user->id;
    }

    /**
     * Alineado a columnas reales: calificacion, comentario.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'calificacion' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comentario' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
