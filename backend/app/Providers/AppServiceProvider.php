<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\SemanticSearch;
use App\Services\SemanticSearchService;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            SemanticSearch::class,
            SemanticSearchService::class,
        );
    }

    public function boot(): void
    {
        //
    }
}