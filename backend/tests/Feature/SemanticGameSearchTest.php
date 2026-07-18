<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\SemanticSearch;
use App\Models\Game;
use Mockery;
use RuntimeException;
use Tests\TestCase;

final class SemanticGameSearchTest extends TestCase
{
    public function test_it_returns_semantic_search_results(): void
    {
        $game = new Game([
            'title' => 'ELDEN RING',
            'slug' => 'elden-ring',
            'description' => 'Juego de rol de fantasía.',
            'genres' => ['Acción', 'Rol'],
            'platforms' => ['PC'],
            'developer' => 'FromSoftware',
            'publisher' => 'FromSoftware',
            'release_year' => 2022,
            'rating' => 9.4,
            'tags' => ['Difícil'],
            'is_active' => true,
        ]);

        $game->setAttribute('_id', '6a587d6a341d937a92098c52');

        $service = Mockery::mock(SemanticSearch::class);

        $service
            ->shouldReceive('searchGames')
            ->once()
            ->with(
                'juego de rol de fantasía con combates difíciles',
                5,
            )
            ->andReturn([
                [
                    'game' => $game,
                    'distance' => 0.627,
                    'similarity' => 0.6146,
                ],
            ]);

        $this->app->instance(
            SemanticSearch::class,
            $service,
        );

        $response = $this->getJson(
            '/api/games/semantic-search?' . http_build_query([
                'q' => 'juego de rol de fantasía con combates difíciles',
                'limit' => 5,
            ]),
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.0.game.title', 'ELDEN RING')
            ->assertJsonPath('data.0.distance', 0.627)
            ->assertJsonPath('data.0.similarity', 0.6146)
            ->assertJsonPath(
                'meta.query',
                'juego de rol de fantasía con combates difíciles',
            )
            ->assertJsonPath('meta.limit', 5)
            ->assertJsonPath('meta.count', 1);
    }

    public function test_query_is_required(): void
    {
        $response = $this->getJson(
            '/api/games/semantic-search',
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['q']);
    }

    public function test_limit_must_be_valid(): void
    {
        $response = $this->getJson(
            '/api/games/semantic-search?q=fantasía&limit=1000',
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['limit']);
    }

    public function test_it_returns_service_unavailable_when_search_fails(): void
    {
        $service = Mockery::mock(SemanticSearch::class);

        $service
            ->shouldReceive('searchGames')
            ->once()
            ->with('fantasía', 5)
            ->andThrow(new RuntimeException(
                'ChromaDB unavailable',
            ));

        $this->app->instance(
            SemanticSearch::class,
            $service,
        );

        $response = $this->getJson(
            '/api/games/semantic-search?q=fantasía&limit=5',
        );

        $response
            ->assertStatus(503)
            ->assertJson([
                'message' => 'No fue posible realizar la búsqueda semántica.',
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}