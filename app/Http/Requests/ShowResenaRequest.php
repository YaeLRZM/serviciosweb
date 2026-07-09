<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowResenaRequest extends FormRequest
{
    /**
     * Lectura pública: no requiere autenticación ni permiso.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
