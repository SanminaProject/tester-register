<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

// TODO: is this file needed since the auth controllers are already created elsewhere?
// should we delete this file?

class AuthController extends ApiController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        if (Role::where('name', 'Guest')->exists()) {
            $user->syncRoles(['Guest']);
        }

        $token = $user->createToken(
            'api-token',
            ['*'],
            now()->addMinutes((int) config('sanctum.expiration', 1440))
        )->plainTextToken;

        return $this->success('Register successful', [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'roles' => $user->getRoleNames()->values(),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        $token = $user->createToken(
            'api-token',
            ['*'],
            now()->addMinutes((int) config('sanctum.expiration', 1440))
        )->plainTextToken;

        return $this->success('Login successful', [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'roles' => $user->getRoleNames()->values(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success('Logout successful');
    }
}
