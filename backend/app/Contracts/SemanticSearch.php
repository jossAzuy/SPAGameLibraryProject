<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Game;

interface SemanticSearch
{
    public function indexGame(Game $game): void;

    /**
     * @return array<int, array{
     *     game: Game,
     *     distance: float,
     *     similarity: float
     * }>
     */
    public function searchGames(
        string $query,
        int $results = 10,
    ): array;

    public function removeGame(Game $game): void;
}