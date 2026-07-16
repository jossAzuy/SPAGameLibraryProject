<?php

use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::apiResource('games', GameController::class);
