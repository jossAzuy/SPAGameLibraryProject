<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\GameObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use MongoDB\Laravel\Eloquent\Model;

#[ObservedBy([GameObserver::class])]

class Game extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'games';

    protected $fillable = [
        'steam_app_id',
        'steam_url',
        'title',
        'slug',
        'description',
        'genres',
        'platforms',
        'developer',
        'publisher',
        'release_year',
        'rating',
        'tags',
        'cover_url',
        'trailer_path',
        'trailer_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'steam_app_id' => 'integer',
            'genres' => 'array',
            'platforms' => 'array',
            'tags' => 'array',
            'release_year' => 'integer',
            'rating' => 'float',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
