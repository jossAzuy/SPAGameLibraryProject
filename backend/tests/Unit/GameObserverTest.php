<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Contracts\SemanticSearch;
use App\Models\Game;
use App\Observers\GameObserver;
use Mockery;
use Tests\TestCase;

final class GameObserverTest extends TestCase
{
    public function test_created_indexes_game(): void
    {
        $game = new Game();

        $service = Mockery::mock(SemanticSearch::class);

        $service
            ->shouldReceive('indexGame')
            ->once()
            ->with($game);

        $observer = new GameObserver($service);

        $observer->created($game);

        $this->assertTrue(true);
    }

    public function test_updated_indexes_game(): void
    {
        $game = new Game();

        $service = Mockery::mock(SemanticSearch::class);

        $service
            ->shouldReceive('indexGame')
            ->once()
            ->with($game);

        $observer = new GameObserver($service);

        $observer->updated($game);

        $this->assertTrue(true);
    }

    public function test_deleted_removes_game(): void
    {
        $game = new Game();

        $service = Mockery::mock(SemanticSearch::class);

        $service
            ->shouldReceive('removeGame')
            ->once()
            ->with($game);

        $observer = new GameObserver($service);

        $observer->deleted($game);

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}