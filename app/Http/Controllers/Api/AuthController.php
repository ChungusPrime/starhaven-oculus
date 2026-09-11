<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {

    public function status(Request $request): JsonResponse {
        return response()->json(['success' => true], Response::HTTP_OK);
    }

    public function register(Request $request): JsonResponse {

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

        $user = User::where('username', $validated['username'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
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
                'characters' => $user->characters,
                'token' => [
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $expiresAt->toISOString(),
                ],
            ],
        ], $status);
    }

    public function createCharacter(Request $request): JsonResponse {

        $validated = $request->validate([
            'character_name' => ['required', 'string', 'max:255'],
            'character_class' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $characterName = $validated['character_name'];
        $characterClass = $validated['character_class'];

        // create new character instance (if needed)
        $character = new Character();

        $character->name = $characterName;

        $character->user_id = $user->id;

        $character->user()->associate($user);

        $character->data = json_encode([
            'level' => 1,
            'experience' => 0,
            'class' => $characterClass,
            'inventory' => [],
            'skills' => [],
            'quests' => [],
            'achievements' => [],
            'friends' => [],
            'settings' => [],
            'reputation' => 0,
            'currency' => 0,
            'region' => "Starhaven Pioneer Base"
        ]);

        $character->save();

        return response()->json([
            'success' => true,
            'character' => $character,
        ], Response::HTTP_OK);

    }

}
