<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'identity_number' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'max:1024'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);
        $user = User::where('identity_number', $data['identity_number'])->first();
        $validPassword = Hash::check($data['password'], $user?->password ?? '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (! $user || ! $validPassword) {
            throw ValidationException::withMessages(['identity_number' => 'NIM/NIP atau kata sandi tidak sesuai.']);
        }
        $expiresAt = now()->addDays(7);

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $user->createToken($data['device_name'], ['*'], $expiresAt)->plainTextToken,
            'expires_at' => $expiresAt->toIso8601String(),
            'data' => new UserResource($user),
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }
}
