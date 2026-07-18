<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SemanticGameSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|int>>
     */
    public function rules(): array
    {
        return [
            'q' => [
                'required',
                'string',
                'min:2',
                'max:500',
            ],
            'limit' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.required' => 'Debes proporcionar una consulta de búsqueda.',
            'q.string' => 'La consulta de búsqueda debe ser texto.',
            'q.min' => 'La consulta debe contener al menos 2 caracteres.',
            'q.max' => 'La consulta no puede superar los 500 caracteres.',
            'limit.integer' => 'El límite debe ser un número entero.',
            'limit.min' => 'El límite debe ser al menos 1.',
            'limit.max' => 'El límite no puede ser mayor que 50.',
        ];
    }
}