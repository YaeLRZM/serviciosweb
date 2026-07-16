<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesApiPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArtesanoRequest extends FormRequest
{
    use AuthorizesApiPermission;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->allowIfCan('editarArtesanos');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|string|max:255',
        ];
    }
}
