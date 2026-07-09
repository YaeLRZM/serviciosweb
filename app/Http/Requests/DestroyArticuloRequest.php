<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Foundation\Http\FormRequest;

class DestroyArticuloRequest extends FormRequest
{
    use AuthorizesApiPermission;

    public function authorize(): bool
    {
        return $this->allowIfCan('eliminarArticulos');
    }

    public function rules(): array
    {
        return [];
    }
}
