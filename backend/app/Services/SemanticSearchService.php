<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game;
use App\Contracts\SemanticSearch;
use RuntimeException;

final class SemanticSearchService implements SemanticSearch
{
    public function __construct(
        private readonly EmbeddingService $embeddingService,
        private readonly ChromaService $chromaService,
    ) {}

    /**
     * Genera el embedding de un juego y lo guarda en ChromaDB.
     */
    public function indexGame(Game $game): void
    {
        $gameId = (string) $game->getKey();

        if ($gameId === '') {
            throw new RuntimeException(
                'No es posible indexar un juego sin identificador.',
            );
        }

        $document = $this->buildDocument($game);

        $embedding = $this->embeddingService->embed($document);

        $this->chromaService->upsert(
            ids: [$gameId],
            embeddings: [$embedding],
            documents: [$document],
            metadatas: [[
                'game_id' => $gameId,
                'title' => (string) $game->title,
                'type' => 'game',
            ]],
        );
    }

    /**
     * Construye el texto utilizado para generar el embedding.
     */
    public function buildDocument(Game $game): string
    {
        $parts = [];

        $this->addTextPart(
            $parts,
            'Título',
            $game->title ?? null,
        );

        $this->addTextPart(
            $parts,
            'Descripción',
            $game->description ?? null,
        );

        $this->addTextPart(
            $parts,
            'Géneros',
            $game->genres ?? $game->genre ?? null,
        );

        $this->addTextPart(
            $parts,
            'Plataformas',
            $game->platforms ?? $game->platform ?? null,
        );

        $this->addTextPart(
            $parts,
            'Desarrollador',
            $game->developer ?? null,
        );

        $this->addTextPart(
            $parts,
            'Editor',
            $game->publisher ?? null,
        );

        if ($parts === []) {
            throw new RuntimeException(
                'El juego no contiene información indexable.',
            );
        }

        return implode("\n", $parts);
    }

    /**
     * @param array<int, string> $parts
     */
    private function addTextPart(
        array &$parts,
        string $label,
        mixed $value,
    ): void {
        $normalizedValue = $this->normalizeValue($value);

        if ($normalizedValue === null) {
            return;
        }

        $parts[] = "{$label}: {$normalizedValue}";
    }

    private function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value !== '' ? $value : null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $values = array_filter(
                array_map(
                    fn(mixed $item): ?string => $this->normalizeValue($item),
                    $value,
                ),
            );

            return $values !== []
                ? implode(', ', $values)
                : null;
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return $this->normalizeValue($value->all());
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $stringValue = trim((string) $value);

            return $stringValue !== '' ? $stringValue : null;
        }

        return null;
    }

    /**
     * Realiza una búsqueda semántica en ChromaDB.
     *
     * @return array<string, mixed>
     */
    public function search(
        string $query,
        int $results = 10,
    ): array {
        $query = trim($query);

        if ($query === '') {
            throw new RuntimeException(
                'La consulta de búsqueda no puede estar vacía.',
            );
        }

        if ($results < 1) {
            throw new RuntimeException(
                'La cantidad de resultados debe ser mayor que cero.',
            );
        }

        $embedding = $this->embeddingService->embed($query);

        return $this->chromaService->query(
            embedding: $embedding,
            results: $results,
        );
    }

    /**
     * Busca juegos semánticamente y recupera sus datos desde MongoDB.
     *
     * @return array<int, array{
     *     game: Game,
     *     distance: float|null,
     *     similarity: float|null
     * }>
     */
    public function searchGames(
        string $query,
        int $results = 10,
    ): array {
        $chromaResult = $this->search($query, $results);

        $ids = $chromaResult['ids'][0] ?? [];
        $distances = $chromaResult['distances'][0] ?? [];

        if (! is_array($ids) || $ids === []) {
            return [];
        }

        $ids = array_values(array_filter(
            $ids,
            static fn(mixed $id): bool =>
            is_string($id) && trim($id) !== '',
        ));

        if ($ids === []) {
            return [];
        }

        $games = Game::query()
            ->whereIn('_id', $ids)
            ->get()
            ->keyBy(
                static fn(Game $game): string =>
                (string) $game->getKey(),
            );

        $resultsList = [];

        foreach ($ids as $position => $gameId) {
            $game = $games->get($gameId);

            if (! $game instanceof Game) {
                continue;
            }

            $distance = $distances[$position] ?? null;
            $distance = is_numeric($distance)
                ? (float) $distance
                : null;

            $resultsList[] = [
                'game' => $game,
                'distance' => $distance,
                'similarity' => $distance !== null
                    ? $this->distanceToSimilarity($distance)
                    : null,
            ];
        }

        return $resultsList;
    }

    /**
     * Convierte una distancia de ChromaDB a un valor orientativo entre 0 y 1.
     */
    private function distanceToSimilarity(float $distance): float
    {
        return round(
            1 / (1 + max(0, $distance)),
            4,
        );
    }


    public function removeGame(Game $game): void
    {
        $gameId = (string) $game->getKey();

        if ($gameId === '') {
            throw new RuntimeException(
                'No es posible eliminar del índice un juego sin identificador.',
            );
        }

        $this->chromaService->delete([$gameId]);
    }
}
