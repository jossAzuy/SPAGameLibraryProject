<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepository $users
    ) {}

    public function register(array $data): User
    {
        $user = $this->users->create([
            'name' => trim((string) $data['name']),
            'email' => mb_strtolower(trim((string) $data['email'])),
            'password' => $data['password'],
            'role' => 'user',
            'is_active' => true,
        ]);

        Auth::login($user);

        return $user;
    }

    public function login(
        array $credentials,
        Request $request
    ): User {
        $email = mb_strtolower(
            trim((string) $credentials['email'])
        );

        $user = $this->users->findByEmail($email);

        if (
            ! $user instanceof User
            || ! Hash::check(
                (string) $credentials['password'],
                (string) $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Las credenciales proporcionadas no son correctas.',
                ],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => [
                    'La cuenta se encuentra deshabilitada.',
                ],
            ]);
        }

        Auth::login(
            $user,
            (bool) ($credentials['remember'] ?? false)
        );

        $request->session()->regenerate();

        return $user;
    }

    public function currentUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        return $user;
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
