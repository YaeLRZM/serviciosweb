<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Foundation\Http\FormRequest;

class DestroyCompraRequest extends FormRequest
{
    use AuthorizesApiPermission;

    public function authorize(): bool
    {
        return $this->allowIfCan('eliminarCompras');
    }

    public function rules(): array
    {
        return [];
    }
}
