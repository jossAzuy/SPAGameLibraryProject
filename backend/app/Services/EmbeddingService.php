<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class EmbeddingService
{
    private string $baseUrl;

    private string $model;

    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config('ollama.url'),
            '/',
        );

        $this->model = (string) config('ollama.embedding_model');

        $this->timeout = (int) config('ollama.timeout');
    }

    /**
     * @return array<int, float>
     */
    public function embed(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            throw new RuntimeException(
                'No es posible generar un embedding para un texto vacío.',
            );
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post(
                    "{$this->baseUrl}/api/embed",
                    [
                        'model' => $this->model,
                        'input' => $text,
                    ],
                );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'No fue posible conectar con Ollama.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'Ollama respondió con el estado %d: %s',
                    $response->status(),
                    $response->body(),
                ),
            );
        }

        $embedding = $response->json('embeddings.0');

        if (! is_array($embedding) || $embedding === []) {
            throw new RuntimeException(
                'Ollama no devolvió un embedding válido.',
            );
        }

        return array_map(
            static fn (mixed $value): float => (float) $value,
            $embedding,
        );
    }
}