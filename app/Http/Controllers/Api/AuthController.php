<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Models\Universe;

class AuthController extends Controller {

    public function register(Request $request): JsonResponse {
        // Canonicalize usernames before validation so registration, login, and
        // uniqueness checks all use the same case-insensitive identity.
        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username'))),
        ]);

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[a-z0-9_]+$/', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'username' => $validated['username'],
            'password' => $validated['password'],
        ]);

        return response()->json(['success' => true], Response::HTTP_CREATED);
    }

    public function login(Request $request): JsonResponse {

        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username'))),
        ]);

        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()->where('username', $validated['username'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $expiresAt = now()->addMinutes(config('oculus.access_token_expiration_minutes'));
        
        // Player tokens may create short-lived tickets. Only a game server with
        // its separate shared credential can consume those tickets.
        // create sanctum token for the user
        $token = $user->createToken($validated['device_name'], ['game-ticket:create'], $expiresAt);

        return response()->json([
            'data' => [
                'user' => $user,
                'character' => $user->activeCharacter()->first(),
                'character_history' => $user->characterHistory()->get(),
                'universes' => Universe::all(),
                'token' => [
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $expiresAt->toISOString(),
                ],
            ],
        ]);

    }

    public function show(Request $request): JsonResponse {
        return response()->json([
            'data' => [
                'user' => $request->user(),
                'character' => $request->user()->activeCharacter()->first(),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse {
        $validated = $request->validate([
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $currentToken = $request->user()->currentAccessToken();
        $deviceName = $validated['device_name'] ?? $currentToken->name;

        // Issue the replacement before revoking the current token so a failed
        // token creation cannot leave the player without a valid session.
        $response = $this->tokenResponse($request->user(), $deviceName);
        $currentToken->delete();
        return $response;
    }

    public function destroy(Request $request): Response {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    public function destroyAll(Request $request): Response {
        $request->user()->tokens()->delete();
        return response()->noContent();
    }

    private function tokenResponse(User $user, string $deviceName): JsonResponse {
        $expiresAt = now()->addMinutes(config('oculus.access_token_expiration_minutes'));
        // Player tokens may create short-lived tickets. Only a game server with
        // its separate shared credential can consume those tickets.
        $token = $user->createToken($deviceName, ['game-ticket:create'], $expiresAt);
        return response()->json([
            'data' => [
                'user' => $user,
                'character' => $user->activeCharacter()->first(),
                'universes' => Universe::configuredWithLatestStats(),
                'token' => [
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $expiresAt->toISOString(),
                ],
            ],
        ]);
    }
}
