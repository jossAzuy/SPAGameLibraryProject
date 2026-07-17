<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Models\User;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class FavoriteController extends Controller
{
    public function __construct(
        private readonly FavoriteService $service
    ) {}

    public function index(
        Request $request
    ): AnonymousResourceCollection {
        /** @var User $user */
        $user = $request->user();

        return FavoriteResource::collection(
            $this->service->list($user)
        );
    }

    public function store(
        StoreFavoriteRequest $request
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $favorite = $this->service->add(
            $user,
            (string) $request->validated('game_id')
        );

        return response()->json([
            'message' => 'Juego agregado a favoritos.',
            'data' => new FavoriteResource($favorite),
        ], Response::HTTP_CREATED);
    }

    public function destroy(
        Request $request,
        string $game
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $this->service->remove($user, $game);

        return response()->json(
            data: null,
            status: Response::HTTP_NO_CONTENT
        );
    }
}
