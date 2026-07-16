<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Temporalmente abierto hasta implementar autenticación y roles.
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'genres' => [
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
                'required',
                'string',
                'max:150',
            ],
            'publisher' => [
                'nullable',
                'string',
                'max:150',
            ],
            'release_year' => [
                'required',
                'integer',
                'min:1950',
                'max:'.((int) date('Y') + 5),
            ],
            'rating' => [
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
                'nullable',
                'url',
                'max:2048',
            ],
            'trailer_path' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'trailer_url' => [
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

    public function messages(): array
    {
        return [
            'title.required' => 'El título del juego es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'genres.required' => 'Debes seleccionar al menos un género.',
            'genres.min' => 'Debes seleccionar al menos un género.',
            'platforms.required' => 'Debes seleccionar al menos una plataforma.',
            'platforms.min' => 'Debes seleccionar al menos una plataforma.',
            'developer.required' => 'El desarrollador es obligatorio.',
            'release_year.required' => 'El año de lanzamiento es obligatorio.',
            'rating.between' => 'La puntuación debe encontrarse entre 0 y 10.',
        ];
    }
}
