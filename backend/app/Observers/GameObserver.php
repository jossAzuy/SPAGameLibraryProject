<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Game;
// use App\Services\SemanticSearchService;
use App\Contracts\SemanticSearch;
use Throwable;

final class GameObserver
{
    public function __construct(
        private readonly SemanticSearch $semanticSearchService,
    ) {
    }

    public function created(Game $game): void
    {
        $this->index($game);
    }

    public function updated(Game $game): void
    {
        $this->index($game);
    }

    public function deleted(Game $game): void
    {
        try {
            $this->semanticSearchService->removeGame($game);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function index(Game $game): void
    {
        try {
            $this->semanticSearchService->indexGame($game);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
