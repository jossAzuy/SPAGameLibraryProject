<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->getKey(),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'genres' => $this->genres ?? [],
            'platforms' => $this->platforms ?? [],
            'developer' => $this->developer,
            'publisher' => $this->publisher,
            'release_year' => $this->release_year,
            'rating' => $this->rating,
            'tags' => $this->tags ?? [],
            'cover_url' => $this->cover_url,
            'trailer_path' => $this->trailer_path,
            'trailer_url' => $this->trailer_url,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'steam_app_id' => $this->steam_app_id,
            'steam_url' => $this->steam_url,
        ];
    }
}
