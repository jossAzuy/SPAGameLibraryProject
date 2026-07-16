<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Se restringirá a administradores en el sprint de autenticación.
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:150',
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'genres' => [
                'sometimes',
                'required',
                'array',
                'min:1',
                'max:10',
            ],
            'genres.*' => [
                'required',
                'string',
                'max:60',
            ],
            'platforms' => [
                'sometimes',
                'required',
                'array',
                'min:1',
                'max:15',
            ],
            'platforms.*' => [
                'required',
                'string',
                'max:80',
            ],
            'developer' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'publisher' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],
            'release_year' => [
                'sometimes',
                'required',
                'integer',
                'min:1950',
                'max:'.((int) date('Y') + 5),
            ],
            'rating' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:10',
            ],
            'tags' => [
                'sometimes',
                'array',
                'max:30',
            ],
            'tags.*' => [
                'string',
                'max:60',
            ],
            'cover_url' => [
                'sometimes',
                'nullable',
                'url',
                'max:2048',
            ],
            'trailer_path' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'trailer_url' => [
                'sometimes',
                'nullable',
                'url',
                'max:2048',
            ],
            'is_active' => [
                'sometimes',
                Rule::in([true, false, 0, 1, '0', '1']),
            ],
        ];
    }
}
