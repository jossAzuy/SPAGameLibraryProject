<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Collection;

class FavoriteRepository
{
    public function allForUser(string $userId): Collection
    {
        return Favorite::query()
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function findForUserAndGame(
        string $userId,
        string $gameId
    ): ?Favorite {
        return Favorite::query()
            ->where('user_id', $userId)
            ->where('game_id', $gameId)
            ->first();
    }

    public function create(
        string $userId,
        string $gameId
    ): Favorite {
        return Favorite::query()->create([
            'user_id' => $userId,
            'game_id' => $gameId,
        ]);
    }

    public function delete(Favorite $favorite): bool
    {
        return (bool) $favorite->delete();
    }

    public function gameIdsForUser(string $userId): array
    {
        return Favorite::query()
            ->where('user_id', $userId)
            ->pluck('game_id')
            ->map(
                fn (mixed $gameId): string => (string) $gameId
            )
            ->values()
            ->all();
    }
}
