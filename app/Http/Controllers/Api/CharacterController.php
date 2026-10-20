<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character as PlayerCharacter;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CharacterController extends Controller {

    public function store(Request $request): JsonResponse {

        $character = DB::transaction(function () use ($request): ?PlayerCharacter {

            $user = User::query()->whereKey($request->user()->getKey())->lockForUpdate()->firstOrFail();

            if ($user->activeCharacter()->exists()) {
                return null;
            }

            return $user->characters()->create([
                'name' => $this->generateCharacterName(),
                'status' => 'active',
                'area' => 'Starhaven Pioneer Base',
                'x' => 560,
                'y' => 624,
            ]);

        });

        if ($character === null) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active UNIT.',
            ], Response::HTTP_CONFLICT);
        }

        return response()->json([
            'success' => true,
            'character' => $character,
        ], Response::HTTP_CREATED);
    }

    private function generateCharacterName(): string {
        do {
            $name = sprintf('UNIT#%s-%s-%s', Str::upper(Str::random(3)), Str::upper(Str::random(3)), Str::upper(Str::random(3)),);
        } while (PlayerCharacter::query()->where('name', $name)->exists());

        return $name;
    }
}