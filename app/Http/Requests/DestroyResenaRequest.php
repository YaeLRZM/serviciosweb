<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Foundation\Http\FormRequest;

class DestroyResenaRequest extends FormRequest
{
    use AuthorizesApiPermission;

    public function authorize(): bool
    {
        return $this->allowIfCan('eliminarResenas');
    }

    public function rules(): array
    {
        return [];
    }
}
