<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\SemanticSearch;
use App\Http\Controllers\Controller;
use App\Http\Requests\SemanticGameSearchRequest;
use App\Http\Resources\GameResource;
use Illuminate\Http\JsonResponse;
use Throwable;

final class SemanticGameSearchController extends Controller
{
    public function __invoke(
        SemanticGameSearchRequest $request,
        SemanticSearch $semanticSearchService,
    ): JsonResponse {
        $validated = $request->validated();

        $query = trim((string) $validated['q']);
        $limit = (int) ($validated['limit'] ?? 10);

        try {
            $results = $semanticSearchService->searchGames(
                query: $query,
                results: $limit,
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible realizar la búsqueda semántica.',
            ], 503);
        }

        return response()->json([
            'data' => array_map(
                static fn (array $result): array => [
                    'game' => (new GameResource(
                        $result['game'],
                    ))->resolve(),
                    'distance' => $result['distance'],
                    'similarity' => $result['similarity'],
                ],
                $results,
            ),
            'meta' => [
                'query' => $query,
                'limit' => $limit,
                'count' => count($results),
            ],
        ]);
    }
}