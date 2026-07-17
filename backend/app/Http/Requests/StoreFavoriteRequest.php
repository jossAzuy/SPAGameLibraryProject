<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Game;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'game_id' => [
                'required',
                'string',
                'regex:/^[a-f\d]{24}$/i',
                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ): void {
                    if (
                        ! Game::query()
                            ->where('_id', (string) $value)
                            ->exists()
                    ) {
                        $fail('El juego seleccionado no existe.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'game_id.required' => 'El identificador del juego es obligatorio.',
            'game_id.string' => 'El identificador del juego no es válido.',
            'game_id.regex' => 'El identificador del juego no es válido.',
        ];
    }
}
