<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\SemanticGameSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::get(
    '/games/semantic-search',
    SemanticGameSearchController::class,
);

Route::apiResource('games', GameController::class);

Route::prefix('auth')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::post('/register', [
            AuthController::class,
            'register',
        ]);

        Route::post('/login', [
            AuthController::class,
            'login',
        ]);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [
            AuthController::class,
            'me',
        ]);

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ]);
    });
});

Route::middleware('auth:sanctum')
    ->prefix('favorites')
    ->group(function (): void {
        Route::get('/', [
            FavoriteController::class,
            'index',
        ]);

        Route::post('/', [
            FavoriteController::class,
            'store',
        ]);

        Route::delete('/{game}', [
            FavoriteController::class,
            'destroy',
        ])->where('game', '[a-fA-F0-9]{24}');
    });