<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\SemanticSearchService;
use Illuminate\Console\Command;
use Throwable;

final class IndexGamesForSemanticSearch extends Command
{
    protected $signature = 'games:index-search
                            {--chunk=50 : Cantidad de juegos procesados por bloque}
                            {--only-active : Indexar únicamente juegos activos}';

    protected $description = 'Genera embeddings e indexa los juegos en ChromaDB';

    public function handle(
        SemanticSearchService $semanticSearchService,
    ): int {
        $chunkSize = (int) $this->option('chunk');

        if ($chunkSize < 1) {
            $this->error(
                'La opción --chunk debe ser mayor que cero.',
            );

            return self::FAILURE;
        }

        $query = Game::query();

        if ((bool) $this->option('only-active')) {
            $query->where('is_active', true);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('No se encontraron juegos para indexar.');

            return self::SUCCESS;
        }

        $this->info(
            sprintf(
                'Se indexarán %d juegos en ChromaDB.',
                $total,
            ),
        );

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $indexed = 0;
        $failed = 0;

        $query
            ->orderBy('_id')
            ->chunk(
                $chunkSize,
                function ($games) use (
                    $semanticSearchService,
                    $progressBar,
                    &$indexed,
                    &$failed,
                ): void {
                    foreach ($games as $game) {
                        try {
                            $semanticSearchService->indexGame($game);
                            $indexed++;
                        } catch (Throwable $exception) {
                            $failed++;

                            $this->newLine();
                            $this->error(
                                sprintf(
                                    'No se pudo indexar el juego %s (%s): %s',
                                    (string) ($game->title ?? 'Sin título'),
                                    (string) $game->getKey(),
                                    $exception->getMessage(),
                                ),
                            );
                        } finally {
                            $progressBar->advance();
                        }
                    }
                },
            );

        $progressBar->finish();
        $this->newLine(2);

        $this->info(
            sprintf(
                'Indexación terminada. Correctos: %d. Fallidos: %d.',
                $indexed,
                $failed,
            ),
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}