<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $game = $this->getRelation('game');

        return [
            'id' => (string) $this->getKey(),
            'user_id' => (string) $this->user_id,
            'game_id' => (string) $this->game_id,
            'game' => $game instanceof Game
                ? new GameResource($game)
                : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
