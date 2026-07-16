<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game;
use App\Repositories\GameRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GameService
{
    public function __construct(
        private readonly GameRepository $repository
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $allowedSortFields = [
            'title',
            'release_year',
            'rating',
            'created_at',
            'updated_at',
        ];

        $filters['sort_by'] = in_array(
            $filters['sort_by'] ?? '',
            $allowedSortFields,
            true
        )
            ? $filters['sort_by']
            : 'created_at';

        $filters['sort_direction'] = strtolower(
            (string) ($filters['sort_direction'] ?? 'desc')
        ) === 'asc'
            ? 'asc'
            : 'desc';

        return $this->repository->paginate($filters);
    }

    public function find(string $id): Game
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Game
    {
        $data = $this->normalize($data);
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['is_active'] ??= true;

        return $this->repository->create($data);
    }

    public function update(Game $game, array $data): Game
    {
        $data = $this->normalize($data);

        if (
            isset($data['title'])
            && $data['title'] !== $game->title
        ) {
            $data['slug'] = $this->generateUniqueSlug(
                $data['title'],
                (string) $game->getKey()
            );
        }

        return $this->repository->update($game, $data);
    }

    public function delete(Game $game): bool
    {
        return $this->repository->delete($game);
    }

    private function normalize(array $data): array
    {
        foreach (['genres', 'platforms', 'tags'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $data[$field] = collect($data[$field] ?? [])
                ->filter(fn (mixed $value): bool => is_string($value))
                ->map(fn (string $value): string => trim($value))
                ->filter()
                ->unique(fn (string $value): string => mb_strtolower($value))
                ->values()
                ->all();
        }

        foreach (['title', 'developer', 'publisher'] as $field) {
            if (
                array_key_exists($field, $data)
                && is_string($data[$field])
            ) {
                $data[$field] = trim($data[$field]);
            }
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOL
            );
        }

        return Arr::except($data, ['_id', 'id']);
    }

    private function generateUniqueSlug(
        string $title,
        ?string $ignoredId = null
    ): string {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(10);
        $counter = 2;

        while (
            Game::query()
                ->where('slug', $slug)
                ->when(
                    $ignoredId,
                    fn ($query) => $query->where('_id', '!=', $ignoredId)
                )
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
