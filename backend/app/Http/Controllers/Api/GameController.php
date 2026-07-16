<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    public function __construct(
        private readonly GameService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $games = $this->service->paginate(
            $request->only([
                'search',
                'genre',
                'platform',
                'developer',
                'release_year',
                'rating_min',
                'rating_max',
                'is_active',
                'sort_by',
                'sort_direction',
                'per_page',
            ])
        );

        return GameResource::collection($games);

        /* return ApiResponse::success(
            data: GameResource::collection($games),
            message: 'Games retrieved successfully.'
        ); */
    }

    public function store(StoreGameRequest $request): JsonResponse
    {
        $game = $this->service->create($request->validated());

        return (new GameResource($game))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(string $game): GameResource
    {
        return new GameResource(
            $this->service->find($game)
        );
    }

    public function update(
        UpdateGameRequest $request,
        string $game
    ): GameResource {
        $model = $this->service->find($game);

        return new GameResource(
            $this->service->update(
                $model,
                $request->validated()
            )
        );
    }

    public function destroy(string $game): JsonResponse
    {
        $model = $this->service->find($game);

        $this->service->delete($model);

        return response()->json(
            data: null,
            status: Response::HTTP_NO_CONTENT
        );
    }
}
