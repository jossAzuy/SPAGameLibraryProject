<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Game;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GameRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Game::query();

        $this->applyFilters($query, $filters);

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $perPage = min((int) ($filters['per_page'] ?? 12), 100);

        return $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }

    public function findOrFail(string $id): Game
    {
        $game = Game::query()->find($id);

        if (! $game instanceof Game) {
            throw (new ModelNotFoundException)->setModel(Game::class, [$id]);
        }

        return $game;
    }

    public function create(array $data): Game
    {
        return Game::query()->create($data);
    }

    public function update(Game $game, array $data): Game
    {
        $game->fill($data);
        $game->save();

        return $game->refresh();
    }

    public function delete(Game $game): bool
    {
        return (bool) $game->delete();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('title', 'regex', $search, 'i')
                    ->orWhere('description', 'regex', $search, 'i')
                    ->orWhere('developer', 'regex', $search, 'i')
                    ->orWhere('publisher', 'regex', $search, 'i')
                    ->orWhere('tags', 'regex', $search, 'i');
            });
        }

        if (! empty($filters['genre'])) {
            $query->where('genres', $filters['genre']);
        }

        if (! empty($filters['platform'])) {
            $query->where('platforms', $filters['platform']);
        }

        if (! empty($filters['developer'])) {
            $query->where(
                'developer',
                'regex',
                trim((string) $filters['developer']),
                'i'
            );
        }

        if (! empty($filters['release_year'])) {
            $query->where(
                'release_year',
                (int) $filters['release_year']
            );
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where(
                'is_active',
                filter_var(
                    $filters['is_active'],
                    FILTER_VALIDATE_BOOL
                )
            );
        }

        if (isset($filters['rating_min'])) {
            $query->where(
                'rating',
                '>=',
                (float) $filters['rating_min']
            );
        }

        if (isset($filters['rating_max'])) {
            $query->where(
                'rating',
                '<=',
                (float) $filters['rating_max']
            );
        }
    }
}
