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
     * Solo el autor de la reseña o un administrador.
     * (No hace falta el permiso Spatie si es el dueño: evita 403 innecesarios.)
     */
    public function authorize(): bool
    {
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
