<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('user_id', $request->user_id)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'user_id' => ['Kredensial tidak valid.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'user_id' => $user->user_id,
                'role' => $user->role,
                'store_id' => $user->store_id,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('store');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'user_id' => $user->user_id,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'store_id' => $user->store_id,
            'store' => $user->store ? ['id' => $user->store->id, 'name' => $user->store->name] : null,
        ]);
    }
}
