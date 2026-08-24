<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {

    public function status(Request $request): JsonResponse {
        return response()->json(['success' => true], Response::HTTP_OK);
    }

    public function register(Request $request): JsonResponse {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return response()->json(['success' => true], Response::HTTP_CREATED);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->tokenResponse($user, $validated['device_name']);
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $currentToken = $request->user()->currentAccessToken();
        $deviceName = $validated['device_name'] ?? $currentToken->name;
        $response = $this->tokenResponse($request->user(), $deviceName);

        $currentToken->delete();

        return $response;
    }

    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function destroyAll(Request $request): Response
    {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }

    private function tokenResponse(User $user, string $deviceName, int $status = Response::HTTP_OK): JsonResponse
    {
        $expiresAt = now()->addMinutes(config('oculus.access_token_expiration_minutes'));
        $token = $user->createToken($deviceName, ['game-ticket:create'], $expiresAt);

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => [
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $expiresAt->toISOString(),
                ],
            ],
        ], $status);
    }
}
