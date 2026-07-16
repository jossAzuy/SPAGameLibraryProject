<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(
        RegisterRequest $request
    ): JsonResponse {
        $user = $this->authService->register(
            $request->validated()
        );

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'data' => new UserResource($user),
        ], Response::HTTP_CREATED);
    }

    public function login(
        LoginRequest $request
    ): JsonResponse {
        $user = $this->authService->login(
            $request->validated(),
            $request
        );

        return response()->json([
            'message' => 'Sesión iniciada correctamente.',
            'data' => new UserResource($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->currentUser($request);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
