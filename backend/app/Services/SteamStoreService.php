<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SteamStoreService
{
    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function getAppDetails(int $appId): array
    {
        $url = rtrim(
            (string) config('services.steam.store_url'),
            '/'
        ).'/appdetails';

        $response = Http::acceptJson()
            ->withHeaders([
                'User-Agent' => 'GameLibrary/1.0',
            ])
            ->retry(
                times: 3,
                sleepMilliseconds: 1000,
                throw: false
            )
            ->timeout(20)
            ->get($url, [
                'appids' => $appId,
                'l' => config('services.steam.language', 'spanish'),
                'cc' => config('services.steam.country', 'mx'),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                "Steam respondió con HTTP {$response->status()}."
            );
        }

        $payload = $response->json((string) $appId);

        if (
            ! is_array($payload)
            || ($payload['success'] ?? false) !== true
            || ! is_array($payload['data'] ?? null)
        ) {
            throw new RuntimeException(
                "Steam no devolvió información para el App ID {$appId}."
            );
        }

        return $payload['data'];
    }

    public function transform(int $appId, array $data): array
    {
        $title = trim((string) ($data['name'] ?? "Steam Game {$appId}"));

        return [
            'steam_app_id' => $appId,
            'steam_url' => "https://store.steampowered.com/app/{$appId}",
            'title' => $title,
            'slug' => Str::slug($title).'-steam-'.$appId,
            'description' => $this->description($data),
            'genres' => $this->descriptions($data['genres'] ?? []),
            'platforms' => $this->platforms($data['platforms'] ?? []),
            'developer' => $this->firstValue(
                $data['developers'] ?? [],
                'Desarrollador desconocido'
            ),
            'publisher' => $this->firstValue(
                $data['publishers'] ?? [],
                null
            ),
            'release_year' => $this->releaseYear(
                $data['release_date']['date'] ?? null
            ),
            'rating' => $this->rating($data),
            'tags' => $this->descriptions($data['categories'] ?? []),
            'cover_url' => $data['header_image'] ?? null,
            'trailer_path' => null,
            'trailer_url' => $this->trailerUrl($data),
            'is_active' => true,
        ];
    }

    private function description(array $data): string
    {
        $description = $data['short_description']
            ?? $data['detailed_description']
            ?? 'Descripción no disponible.';

        $description = html_entity_decode(
            strip_tags((string) $description),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        return trim(preg_replace('/\s+/', ' ', $description) ?? '');
    }

    private function descriptions(array $items): array
    {
        return collect($items)
            ->pluck('description')
            ->filter(fn (mixed $value): bool => is_string($value))
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->unique(fn (string $value): string => mb_strtolower($value))
            ->values()
            ->all();
    }

    private function platforms(array $platforms): array
    {
        $names = [
            'windows' => 'PC',
            'mac' => 'macOS',
            'linux' => 'Linux',
        ];

        return collect($names)
            ->filter(
                fn (string $name, string $key): bool => ($platforms[$key] ?? false) === true
            )
            ->values()
            ->all();
    }

    private function firstValue(
        array $values,
        ?string $default
    ): ?string {
        $value = collect($values)
            ->first(fn (mixed $item): bool => is_string($item));

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : $default;
    }

    private function releaseYear(mixed $date): ?int
    {
        if (! is_string($date)) {
            return null;
        }

        preg_match('/\b(19|20)\d{2}\b/', $date, $matches);

        return isset($matches[0])
            ? (int) $matches[0]
            : null;
    }

    private function rating(array $data): ?float
    {
        $score = $data['metacritic']['score'] ?? null;

        if (! is_numeric($score)) {
            return null;
        }

        return round(((float) $score) / 10, 1);
    }

    private function trailerUrl(array $data): ?string
    {
        $movies = $data['movies'] ?? [];

        if (! is_array($movies) || $movies === []) {
            return null;
        }

        $movie = $movies[0] ?? null;

        if (! is_array($movie)) {
            return null;
        }

        return $movie['mp4']['max']
            ?? $movie['mp4']['480']
            ?? $movie['webm']['max']
            ?? $movie['webm']['480']
            ?? null;
    }
}
