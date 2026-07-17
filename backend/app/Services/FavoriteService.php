<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Favorite;
use App\Models\Game;
use App\Models\User;
use App\Repositories\FavoriteRepository;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FavoriteService
{
    public function __construct(
        private readonly FavoriteRepository $favorites
    ) {}

    public function list(User $user): Collection
    {
        $favorites = $this->favorites->allForUser(
            (string) $user->getKey()
        );

        $games = Game::query()
            ->whereIn(
                '_id',
                $favorites
                    ->pluck('game_id')
                    ->map(
                        fn (mixed $id): string => (string) $id
                    )
                    ->all()
            )
            ->get()
            ->keyBy(
                fn (Game $game): string => (string) $game->getKey()
            );

        return $favorites
            ->map(function (Favorite $favorite) use ($games): Favorite {
                $favorite->setRelation(
                    'game',
                    $games->get((string) $favorite->game_id)
                );

                return $favorite;
            })
            ->filter(
                fn (Favorite $favorite): bool => $favorite->getRelation('game') instanceof Game
            )
            ->values();
    }

    public function add(User $user, string $gameId): Favorite
    {
        $userId = (string) $user->getKey();

        $existing = $this->favorites->findForUserAndGame(
            $userId,
            $gameId
        );

        if ($existing instanceof Favorite) {
            throw new ConflictHttpException(
                'El juego ya está guardado como favorito.'
            );
        }

        $game = Game::query()->find($gameId);

        if (! $game instanceof Game) {
            throw new NotFoundHttpException(
                'El juego seleccionado no existe.'
            );
        }

        $favorite = $this->favorites->create(
            $userId,
            $gameId
        );

        $favorite->setRelation('game', $game);

        return $favorite;
    }

    public function remove(User $user, string $gameId): void
    {
        $favorite = $this->favorites->findForUserAndGame(
            (string) $user->getKey(),
            $gameId
        );

        if (! $favorite instanceof Favorite) {
            throw new NotFoundHttpException(
                'El juego no se encuentra en tus favoritos.'
            );
        }

        $this->favorites->delete($favorite);
    }

    public function gameIds(User $user): array
    {
        return $this->favorites->gameIdsForUser(
            (string) $user->getKey()
        );
    }
}
